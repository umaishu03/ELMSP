<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OTClaim;
use App\Models\Payroll;
use Illuminate\Http\Request;

class OTClaimApprovalController extends Controller
{
    /**
     * Approve a pending OT claim
     */
    public function approve(OTClaim $claim)
    {
        // Refresh the model from database to get the latest status
        $claim->refresh();
        
        // Log for debugging
        \Log::info('Approving OT Claim', [
            'claim_id' => $claim->id,
            'status' => $claim->status,
            'claim_type' => $claim->claim_type,
            'user_id' => $claim->user ? $claim->user->id : null,
        ]);
        
        if (empty($claim->status) || $claim->status !== 'pending') {
            $statusDisplay = empty($claim->status) ? '(empty/null)' : $claim->status;
            \Log::warning('OT Claim approval failed - not pending', [
                'claim_id' => $claim->id,
                'current_status' => $statusDisplay,
            ]);
            return back()->with('error', "This claim has already been {$statusDisplay}. Only pending claims can be approved.");
        }

        // Update status to approved
        $claim->status = 'approved';
        $claim->save();

        // If it's a payroll claim, generate payroll record for the staff member
        if ($claim->claim_type === 'payroll') {
            $this->generatePayrollFromClaim($claim);
        }

        \Log::info('OT Claim approved successfully', [
            'claim_id' => $claim->id,
            'user_id' => $claim->user ? $claim->user->id : null,
        ]);

        return back()->with('success', 'OT Claim approved successfully! Payroll has been updated.');
    }

    /**
     * Reject a pending OT claim
     */
    public function reject(OTClaim $claim)
    {
        // Refresh the model from database to get the latest status
        $claim->refresh();
        
        if (empty($claim->status) || $claim->status !== 'pending') {
            $statusDisplay = empty($claim->status) ? '(empty/null)' : $claim->status;
            return back()->with('error', "This claim has already been {$statusDisplay}. Only pending claims can be rejected.");
        }

        $claim->update(['status' => 'rejected']);

        return back()->with('success', 'OT Claim rejected successfully!');
    }

    /**
     * Generate payroll record when a payroll claim is approved
     */
    private function generatePayrollFromClaim(OTClaim $claim)
    {
        $user = $claim->user;
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
