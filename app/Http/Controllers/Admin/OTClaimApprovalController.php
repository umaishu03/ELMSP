<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OTClaim;
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
        // Get raw data directly from database to ensure we have the correct status and claim_type
        // This bypasses any model caching issues
        $rawData = DB::table('ot_claims')->where('id', $otClaim->id)->first();
        
        if (!$rawData) {
            \Log::error('OT Claim not found in database', ['claim_id' => $otClaim->id]);
            return back()->with('error', 'OT Claim not found.');
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
        
        // Get user from overtime records (since user_id column was removed)
        $user = $this->getUserFromClaim($otClaim);
        
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
            return back()->with('error', "This claim has already been {$statusDisplay}. Only pending claims can be approved.");
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

        return back()->with('success', 'OT Claim approved successfully! Payroll has been updated.');
    }

    /**
     * Reject a pending OT claim
     */
    public function reject(OTClaim $otClaim)
    {
        // Get raw data directly from database to ensure we have the correct status and claim_type
        // This bypasses any model caching issues
        $rawData = DB::table('ot_claims')->where('id', $otClaim->id)->first();
        
        if (!$rawData) {
            \Log::error('OT Claim not found in database', ['claim_id' => $otClaim->id]);
            return back()->with('error', 'OT Claim not found.');
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
            return back()->with('error', "This claim has already been {$statusDisplay}. Only pending claims can be rejected.");
        }

        // Update ONLY the status field - do not change claim_type
        DB::table('ot_claims')
            ->where('id', $otClaim->id)
            ->update(['status' => 'rejected']);
        
        // Refresh the model
        $otClaim->refresh();

        return back()->with('success', 'OT Claim rejected successfully!');
    }

    /**
     * Get user from claim (from overtime records since user_id column was removed)
     */
    private function getUserFromClaim(OTClaim $claim)
    {
        // For payroll claims, get user from overtime records in ot_ids
        if ($claim->claim_type === 'payroll' && $claim->ot_ids && is_array($claim->ot_ids) && !empty($claim->ot_ids)) {
            $firstOtId = $claim->ot_ids[0];
            $overtime = \App\Models\Overtime::with('staff.user')->find($firstOtId);
            if ($overtime && $overtime->staff && $overtime->staff->user) {
                return $overtime->staff->user;
            }
        }
        // For replacement leave claims, get user from leave->staff->user
        elseif ($claim->claim_type === 'replacement_leave' && $claim->leave) {
            $claim->load('leave.staff.user');
            if ($claim->leave->staff && $claim->leave->staff->user) {
                return $claim->leave->staff->user;
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
