<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OTClaim;
use App\Models\Overtime;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OTClaimApprovalController extends Controller
{
    /**
     * Approve a pending OT claim
     */
    public function approve(OTClaim $otClaim)
    {
        // Get user early for error messages
        $user = $this->getUserFromClaim($otClaim);
        $staffName = $user ? $user->name : 'Unknown Staff';
        
        // Get raw data directly from database to ensure we have the correct status and claim_type
        // This bypasses any model caching issues
        $rawData = DB::table('ot_claims')->where('id', $otClaim->id)->first();
        
        if (!$rawData) {
            \Log::error('OT Claim not found in database', ['claim_id' => $otClaim->id]);
            return back()->with('error', "OT Claim not found for {$staffName}.");
        }
        
        // Verify claim_type from database - MUST be 'payroll' for salary claims
        $claimType = $rawData->claim_type;
        $status = $rawData->status ?? 'pending';
        
        // Log the claim type to debug
        \Log::info('Processing approval request', [
            'claim_id' => $otClaim->id,
            'claim_type' => $claimType,
            'status' => $status,
        ]);
        
        // If status is null or empty, set it to pending (default)
        if (is_null($status) || $status === '') {
            $status = 'pending';
            DB::table('ot_claims')->where('id', $otClaim->id)->update(['status' => 'pending']);
            $status = 'pending';
        }
        
        // Ensure claim_type matches what we expect (should be 'payroll' for salary claims)
        if ($claimType !== $otClaim->claim_type) {
            \Log::warning('Claim type mismatch', [
                'claim_id' => $otClaim->id,
                'model_claim_type' => $otClaim->claim_type,
                'db_claim_type' => $claimType,
            ]);
            // Use the database value
            $otClaim->claim_type = $claimType;
        }
        
        // Log for debugging
        \Log::info('Approving OT Claim', [
            'claim_id' => $otClaim->id,
            'status' => $status,
            'claim_type' => $claimType,
            'user_id' => $user ? $user->id : null,
        ]);
        
        if ($status !== 'pending') {
            $statusDisplay = $status ?: '(empty/null)';
            \Log::warning('OT Claim approval failed - not pending', [
                'claim_id' => $otClaim->id,
                'current_status' => $statusDisplay,
            ]);
            $claimTypeLabel = $claimType === 'payroll' ? 'Salary Claim' : 'Replacement Leave Claim';
            return back()->with('error', "{$claimTypeLabel} for {$staffName} has already been {$statusDisplay}. Only pending claims can be approved.");
        }

        // Update ONLY the status field - do not change claim_type
        DB::table('ot_claims')
            ->where('id', $otClaim->id)
            ->update(['status' => 'approved']);
        
        // Refresh the model
        $otClaim->refresh();

        // If it's a payroll claim, generate payroll record for the staff member
        // Use claimType from database to ensure we're checking the correct type
        if ($claimType === 'payroll') {
            $this->generatePayrollFromClaim($otClaim);
        }

        \Log::info('OT Claim approved successfully', [
            'claim_id' => $otClaim->id,
            'user_id' => $user ? $user->id : null,
        ]);

        $staffName = $user ? $user->name : 'Unknown Staff';
        $claimTypeLabel = $claimType === 'payroll' ? 'Salary Claim' : 'Replacement Leave Claim';
        return back()->with('success', "{$claimTypeLabel} approved successfully for {$staffName}! Payroll has been updated.");
    }

    /**
     * Reject a pending OT claim
     */
    public function reject(OTClaim $otClaim)
    {
        // Get raw data directly from database to ensure we have the correct status and claim_type
        // This bypasses any model caching issues
        $rawData = DB::table('ot_claims')->where('id', $otClaim->id)->first();
        
        // Get user early for error messages
        $user = $this->getUserFromClaim($otClaim);
        $staffName = $user ? $user->name : 'Unknown Staff';
        
        if (!$rawData) {
            \Log::error('OT Claim not found in database', ['claim_id' => $otClaim->id]);
            return back()->with('error', "OT Claim not found for {$staffName}.");
        }
        
        $status = $rawData->status ?? 'pending';
        $claimType = $rawData->claim_type;
        
        // Log the claim type to debug
        \Log::info('Processing rejection request', [
            'claim_id' => $otClaim->id,
            'claim_type' => $claimType,
            'status' => $status,
        ]);
        
        // If status is null or empty, set it to pending (default)
        if (is_null($status) || $status === '') {
            $status = 'pending';
            DB::table('ot_claims')->where('id', $otClaim->id)->update(['status' => 'pending']);
            $status = 'pending';
        }
        
        if ($status !== 'pending') {
            $statusDisplay = $status ?: '(empty/null)';
            $claimTypeLabel = $claimType === 'payroll' ? 'Salary Claim' : 'Replacement Leave Claim';
            return back()->with('error', "{$claimTypeLabel} for {$staffName} has already been {$statusDisplay}. Only pending claims can be rejected.");
        }

        // User already retrieved above
        $claimTypeLabel = $claimType === 'payroll' ? 'Salary Claim' : 'Replacement Leave Claim';
        
        // Get the overtime IDs from the claim before updating status
        // First try from raw DB data (JSON string), then from model (array cast)
        $otIds = null;
        
        // Try getting from raw DB query first
        if (isset($rawData->ot_ids)) {
            if (is_string($rawData->ot_ids)) {
                $otIds = json_decode($rawData->ot_ids, true);
            } elseif (is_array($rawData->ot_ids)) {
                $otIds = $rawData->ot_ids;
            }
        }
        
        // If still not valid, get from model (uses array cast)
        if (empty($otIds) || !is_array($otIds)) {
            $otIds = $otClaim->ot_ids ?? null;
        }
        
        // Ensure ot_ids is an array and convert string IDs to integers
        // This is important because JSON arrays may contain string numbers like ["3"] or ["2", "1"]
        if (!empty($otIds) && is_array($otIds)) {
            $otIds = array_map(function($id) {
                // Convert to integer, handling both string and integer inputs
                return (int) $id;
            }, $otIds);
            $otIds = array_filter($otIds, function($id) {
                // Remove any zeros or invalid IDs
                return $id > 0;
            });
            $otIds = array_values($otIds); // Re-index array
        }
        
        // Update ONLY the status field - do not change claim_type
        DB::table('ot_claims')
            ->where('id', $otClaim->id)
            ->update(['status' => 'rejected']);
        
        // Refresh the model to get latest status
        $otClaim->refresh();

        // Free up the overtime hours by setting claimed = false
        // This makes them available again for claiming on the claim page
        if (!empty($otIds) && is_array($otIds) && count($otIds) > 0) {
            // Use DB facade for direct update to ensure proper type matching
            // Update ALL overtime records in the claim to claimed = false
            // This ensures the hours are available again, regardless of current claimed status
            $updatedCount = DB::table('overtimes')
                ->whereIn('id', $otIds)
                ->update(['claimed' => false]);
            
            \Log::info('OT hours freed up after claim rejection', [
                'claim_id' => $otClaim->id,
                'ot_ids' => $otIds,
                'ot_ids_raw' => $rawData->ot_ids ?? null,
                'total_ot_ids' => count($otIds),
                'updated_count' => $updatedCount,
            ]);
            
            // Verify the update worked
            if ($updatedCount === 0) {
                \Log::warning('No overtime records were updated after claim rejection', [
                    'claim_id' => $otClaim->id,
                    'ot_ids' => $otIds,
                    'note' => 'This may indicate the OT records do not exist or were already unclaimed',
                ]);
            } else {
                \Log::info('Successfully freed up overtime hours', [
                    'claim_id' => $otClaim->id,
                    'records_updated' => $updatedCount,
                    'total_ot_ids' => count($otIds),
                ]);
            }
        } else {
            \Log::warning('No OT IDs found to free up after claim rejection', [
                'claim_id' => $otClaim->id,
                'ot_ids_raw' => $rawData->ot_ids ?? null,
                'ot_ids_processed' => $otIds ?? null,
            ]);
        }

        return back()->with('success', "{$claimTypeLabel} rejected successfully for {$staffName}!");
    }

    /**
     * Get user from claim (from overtime records since user_id column was removed)
     */
    private function getUserFromClaim(OTClaim $claim)
    {
        // Get user from overtime records in ot_ids (works for both payroll and replacement leave claims)
        if ($claim->ot_ids && is_array($claim->ot_ids) && !empty($claim->ot_ids)) {
            $firstOtId = $claim->ot_ids[0];
            $overtime = \App\Models\Overtime::with('staff.user')->find($firstOtId);
            if ($overtime && $overtime->staff && $overtime->staff->user) {
                return $overtime->staff->user;
            }
        }
        
        return null;
    }

    /**
     * Generate payroll record when a payroll claim is approved
     */
    private function generatePayrollFromClaim(OTClaim $claim)
    {
        $user = $this->getUserFromClaim($claim);
        
        if (!$user) {
            \Log::warning('User not found for OT Claim', [
                'claim_id' => $claim->id,
                'claim_type' => $claim->claim_type,
                'ot_ids' => $claim->ot_ids,
            ]);
            return;
        }
        
        $staff = $user->staff;
        $now = now();
        
        if (!$staff) {
            \Log::warning('Staff record not found for OT Claim', ['user_id' => $user->id, 'claim_id' => $claim->id]);
            return;
        }

        try {
            // Get or create payroll record
            $payroll = Payroll::where('user_id', $user->id)
                ->where('year', $now->year)
                ->where('month', $now->month)
                ->first();

            $monthsWorked = $staff->hire_date ? $staff->hire_date->diffInMonths($now) : 0;
            $fixedCommission = $monthsWorked >= 3 ? 200 : 0;

            if (!$payroll) {
                // Create new payroll for this month
                $payroll = Payroll::create([
                    'user_id' => $user->id,
                    'year' => $now->year,
                    'month' => $now->month,
                    'basic_salary' => $staff->salary ?? 0,
                    'fixed_commission' => $fixedCommission,
                    'public_holiday_hours' => 0,
                    'public_holiday_pay' => 0,
                    'fulltime_ot_hours' => $claim->fulltime_hours ?? 0,
                    'fulltime_ot_pay' => ($claim->fulltime_hours ?? 0) * 12.26,
                    'public_holiday_ot_hours' => $claim->public_holiday_hours ?? 0,
                    'public_holiday_ot_pay' => ($claim->public_holiday_hours ?? 0) * 21.68,
                    'total_deductions' => 0,
                    'status' => 'pending',
                ]);
                
                $payroll->gross_salary = $payroll->calculateGrossSalary();
                $payroll->net_salary = $payroll->calculateNetSalary();
                $payroll->save();
            } else {
                // Update existing payroll with claim hours
                $payroll->fulltime_ot_hours = ($payroll->fulltime_ot_hours ?? 0) + ($claim->fulltime_hours ?? 0);
                $payroll->fulltime_ot_pay = $payroll->fulltime_ot_hours * 12.26;
                $payroll->public_holiday_ot_hours = ($payroll->public_holiday_ot_hours ?? 0) + ($claim->public_holiday_hours ?? 0);
                $payroll->public_holiday_ot_pay = $payroll->public_holiday_ot_hours * 21.68;
                
                // Recalculate gross and net salary
                $payroll->gross_salary = $payroll->calculateGrossSalary();
                $payroll->net_salary = $payroll->calculateNetSalary();
                $payroll->save();
            }

            \Log::info('Payroll generated/updated for OT Claim', [
                'payroll_id' => $payroll->id,
                'user_id' => $user->id,
                'claim_id' => $claim->id,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error generating payroll for OT Claim', [
                'claim_id' => $claim->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
