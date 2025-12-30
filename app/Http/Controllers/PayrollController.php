<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\OTClaim;
use App\Models\Shift;

class PayrollController extends Controller
{
    public function staffPayroll(Request $request)
    {
        // Get selected month from request, default to current month
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        list($year, $month) = explode('-', $selectedMonth);
        
        // Get all staff with user info
        $staffList = Staff::with('user')
            ->where('status', 'active')
            ->get()
            ->map(function ($staff) use ($year, $month) {
                $user = $staff->user;
                $monthsWorked = $staff->hire_date ? $staff->hire_date->diffInMonths(now()) : 0;
                $fixedCommission = $monthsWorked >= 3 ? 200 : 0;
                $fullBasicSalary = $staff->salary ?? 0;
                $department = $staff->department;
                $role = $user->role ?? '';
                $name = $user->name ?? '';
                $hireDate = $staff->hire_date;

                // Calculate working days based on shifts assigned in timetable
                $monthStart = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
                $monthEnd = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();
                
                // Determine the start date for calculation (hire date or month start, whichever is later)
                $calcStartDate = $hireDate && $hireDate->gt($monthStart) ? $hireDate->copy() : $monthStart->copy();
                
                // Get all shifts for this staff in the selected month
                $shifts = Shift::where('staff_id', $staff->id)
                    ->whereBetween('date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
                    ->get();
                
                // Calculate total expected working days in the month
                // Staff work 6 days per week (1 rest day per week)
                $totalWorkingDaysInMonth = 0;
                $currentDate = $monthStart->copy();
                $daysFromStart = 0;
                
                while ($currentDate->lte($monthEnd)) {
                    // Only count days from hire date onwards (if hired mid-month)
                    if ($currentDate->gte($calcStartDate->startOfDay())) {
                        $daysFromStart++;
                    }
                    $currentDate->addDay();
                }
                
                // Calculate expected working days: total days - rest days (1 per week)
                // Weeks = days / 7, rest days = weeks × 1
                $weeks = $daysFromStart / 7;
                $restDays = floor($weeks); // 1 rest day per week
                $totalWorkingDaysInMonth = $daysFromStart - $restDays;
                
                // Count actual shifts assigned (exclude rest days and leave days)
                $workingDays = 0;
                
                foreach ($shifts as $shift) {
                    // Parse shift date (handle both Carbon instances and strings)
                    $shiftDate = $shift->date instanceof \Carbon\Carbon 
                        ? $shift->date 
                        : \Carbon\Carbon::parse($shift->date);
                    
                    // Only count shifts that are:
                    // 1. Not rest days (rest_day = false or null)
                    // 2. Not on leave (leave_id is null)
                    // 3. Have start_time and end_time (actual working shift)
                    // 4. On or after the calculation start date (hire date or month start)
                    $isRestDay = $shift->rest_day ?? false;
                    $isOnLeave = !empty($shift->leave_id);
                    $hasWorkingHours = !empty($shift->start_time) && !empty($shift->end_time);
                    $isAfterStartDate = $shiftDate->startOfDay()->gte($calcStartDate->startOfDay());
                    
                    if (!$isRestDay && !$isOnLeave && $hasWorkingHours && $isAfterStartDate) {
                        $workingDays++;
                    }
                }
                
                // Calculate pro-rated basic salary
                $basic_salary = $totalWorkingDaysInMonth > 0 
                    ? ($fullBasicSalary / $totalWorkingDaysInMonth) * $workingDays 
                    : 0;

                // Calculate OT claims for payroll (approved, selected month)
                // Get approved OT claims for payroll that belong to this staff
                // Since user_id was removed from ot_claims, we match through overtime records
                $staffOvertimeIds = \App\Models\Overtime::where('staff_id', $staff->id)->pluck('id')->toArray();
                
                // Get all approved payroll claims for the selected month
                $allOtClaims = OTClaim::where('claim_type', 'payroll')
                    ->approved()
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year)
                    ->get();
                
                // Filter claims that belong to this staff
                $otClaims = $allOtClaims->filter(function($claim) use ($user, $staff, $staffOvertimeIds) {
                    // Check if linked to this user's payroll
                    if ($claim->payroll_id) {
                        $payroll = \App\Models\Payroll::find($claim->payroll_id);
                        if ($payroll && $payroll->user_id == $user->id) {
                            return true;
                        }
                    }
                    
                    // Check if linked to staff's overtime
                    if ($claim->overtime_id && in_array($claim->overtime_id, $staffOvertimeIds)) {
                        return true;
                    }
                    
                    // Check if ot_ids contains any of staff's overtime IDs
                    $claimOtIds = $claim->ot_ids ?? [];
                    if (is_array($claimOtIds) && !empty(array_intersect($claimOtIds, $staffOvertimeIds))) {
                        return true;
                    }
                    
                    return false;
                });

                $normal_ot_hours = $otClaims->sum('fulltime_hours');
                $ph_ot_hours = $otClaims->sum('public_holiday_hours');

                // Public holiday pay (worked on PH, not OT):
                $public_holiday_hours = $staff->public_holiday_hours ?? 0;
                $public_holiday_pay = 15.38 * $public_holiday_hours;

                // Get marketing bonus and status from payroll record if exists, otherwise default to 0
                $marketingBonus = 0;
                $payrollStatus = 'draft';
                $payrollRecord = \App\Models\Payroll::where('user_id', $user->id)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->first();
                if ($payrollRecord) {
                    $marketingBonus = $payrollRecord->marketing_bonus ?? 0;
                    $payrollStatus = $payrollRecord->status ?? 'draft';
                }

                return (object) [
                    'name' => $name,
                    'role' => $role,
                    'department' => $department,
                    'basic_salary' => $basic_salary,
                    'full_basic_salary' => $fullBasicSalary,
                    'fixed_commission' => $fixedCommission,
                    'marketing_bonus' => $marketingBonus,
                    'public_holiday_hours' => $public_holiday_hours,
                    'normal_ot_hours' => $normal_ot_hours,
                    'ph_ot_hours' => $ph_ot_hours,
                    'hire_date' => $hireDate,
                    'working_days' => $workingDays,
                    'total_working_days' => $totalWorkingDaysInMonth,
                    'is_full_month' => $workingDays == $totalWorkingDaysInMonth,
                    'user_id' => $user->id,
                    'payroll_status' => $payrollStatus,
                ];
            });

        // Get overall payroll status for the selected month
        $overallStatus = 'draft';
        $payrollsForMonth = \App\Models\Payroll::where('year', $year)
            ->where('month', $month)
            ->get();
        
        if ($payrollsForMonth->count() > 0) {
            $statuses = $payrollsForMonth->pluck('status')->unique()->toArray();
            // If all are approved/paid, show the highest status
            if (in_array('paid', $statuses)) {
                $overallStatus = 'paid';
            } elseif (in_array('approved', $statuses)) {
                $overallStatus = 'approved';
            } else {
                $overallStatus = 'draft';
            }
        }

        return view('admin.managePayroll', compact('staffList', 'selectedMonth', 'overallStatus'));
    }

    /**
     * Recalculate and update payroll record with current data
     */
    private function recalculatePayroll($userId, $year, $month, $marketingBonus = null)
    {
        $user = \App\Models\User::find($userId);
        if (!$user || !$user->staff) {
            return null;
        }

        $staff = $user->staff;
        $monthsWorked = $staff->hire_date ? $staff->hire_date->diffInMonths(now()) : 0;
        $fixedCommission = $monthsWorked >= 3 ? 200 : 0;
        $fullBasicSalary = $staff->salary ?? 0;

        // Calculate working days based on shifts
        $monthStart = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();
        $calcStartDate = $staff->hire_date && $staff->hire_date->gt($monthStart) 
            ? $staff->hire_date->copy() 
            : $monthStart->copy();

        // Calculate total expected working days in the month (6 days per week)
        $totalWorkingDaysInMonth = 0;
        $currentDate = $monthStart->copy();
        $daysFromStart = 0;
        
        while ($currentDate->lte($monthEnd)) {
            if ($currentDate->gte($calcStartDate->startOfDay())) {
                $daysFromStart++;
            }
            $currentDate->addDay();
        }
        
        $weeks = $daysFromStart / 7;
        $restDays = floor($weeks); // 1 rest day per week
        $totalWorkingDaysInMonth = $daysFromStart - $restDays;

        // Get all shifts for this staff in the selected month
        $shifts = Shift::where('staff_id', $staff->id)
            ->whereBetween('date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
            ->get();

        // Count actual shifts assigned
        $workingDays = 0;
        foreach ($shifts as $shift) {
            $shiftDate = $shift->date instanceof \Carbon\Carbon 
                ? $shift->date 
                : \Carbon\Carbon::parse($shift->date);
            
            $isRestDay = $shift->rest_day ?? false;
            $isOnLeave = !empty($shift->leave_id);
            $hasWorkingHours = !empty($shift->start_time) && !empty($shift->end_time);
            $isAfterStartDate = $shiftDate->startOfDay()->gte($calcStartDate->startOfDay());
            
            if (!$isRestDay && !$isOnLeave && $hasWorkingHours && $isAfterStartDate) {
                $workingDays++;
            }
        }

        $basic_salary = $totalWorkingDaysInMonth > 0 
            ? ($fullBasicSalary / $totalWorkingDaysInMonth) * $workingDays 
            : 0;

        // Get OT claims for this staff
        $staffOvertimeIds = \App\Models\Overtime::where('staff_id', $staff->id)->pluck('id')->toArray();
        
        $allOtClaims = OTClaim::where('claim_type', 'payroll')
            ->approved()
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get();
        
        $otClaims = $allOtClaims->filter(function($claim) use ($user, $staff, $staffOvertimeIds) {
            if ($claim->payroll_id) {
                $payroll = \App\Models\Payroll::find($claim->payroll_id);
                if ($payroll && $payroll->user_id == $user->id) {
                    return true;
                }
            }
            if ($claim->overtime_id && in_array($claim->overtime_id, $staffOvertimeIds)) {
                return true;
            }
            $claimOtIds = $claim->ot_ids ?? [];
            if (is_array($claimOtIds) && !empty(array_intersect($claimOtIds, $staffOvertimeIds))) {
                return true;
            }
            return false;
        });

        $normal_ot_hours = $otClaims->sum('fulltime_hours');
        $ph_ot_hours = $otClaims->sum('public_holiday_hours');
        $normal_ot_pay = $normal_ot_hours * 12.26;
        $ph_ot_pay = $ph_ot_hours * 21.68;
        $public_holiday_hours = $staff->public_holiday_hours ?? 0;
        $public_holiday_pay = 15.38 * $public_holiday_hours;

        // Get existing payroll record
        $payroll = \App\Models\Payroll::where('user_id', $userId)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        // Use provided marketing bonus or get from existing record
        if ($marketingBonus === null) {
            $marketingBonus = $payroll ? ($payroll->marketing_bonus ?? 0) : 0;
        }

        // Calculate gross salary
        $grossSalary = $basic_salary + $fixedCommission + $marketingBonus 
            + $public_holiday_pay + $normal_ot_pay + $ph_ot_pay;

        // Get existing status or default to draft
        $status = $payroll ? ($payroll->status ?? 'draft') : 'draft';

        // Update or create payroll record
        if ($payroll) {
            $payroll->update([
                'basic_salary' => $basic_salary,
                'fixed_commission' => $fixedCommission,
                'marketing_bonus' => $marketingBonus,
                'public_holiday_hours' => $public_holiday_hours,
                'public_holiday_pay' => $public_holiday_pay,
                'fulltime_ot_hours' => $normal_ot_hours,
                'fulltime_ot_pay' => $normal_ot_pay,
                'public_holiday_ot_hours' => $ph_ot_hours,
                'public_holiday_ot_pay' => $ph_ot_pay,
                'gross_salary' => $grossSalary,
                'net_salary' => $grossSalary - ($payroll->total_deductions ?? 0),
            ]);
        } else {
            $payroll = \App\Models\Payroll::create([
                'user_id' => $userId,
                'year' => $year,
                'month' => $month,
                'basic_salary' => $basic_salary,
                'fixed_commission' => $fixedCommission,
                'marketing_bonus' => $marketingBonus,
                'public_holiday_hours' => $public_holiday_hours,
                'public_holiday_pay' => $public_holiday_pay,
                'fulltime_ot_hours' => $normal_ot_hours,
                'fulltime_ot_pay' => $normal_ot_pay,
                'public_holiday_ot_hours' => $ph_ot_hours,
                'public_holiday_ot_pay' => $ph_ot_pay,
                'gross_salary' => $grossSalary,
                'total_deductions' => 0,
                'net_salary' => $grossSalary,
                'status' => $status,
            ]);
        }

        return $payroll;
    }

    /**
     * Update marketing bonuses for staff
     */
    public function updateBonus(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'bonus' => 'required|array',
            'bonus.*' => 'nullable|numeric|min:0',
        ]);

        $selectedMonth = $request->input('month');
        list($year, $month) = explode('-', $selectedMonth);
        $bonuses = $request->input('bonus', []);

        foreach ($bonuses as $userId => $bonusAmount) {
            $bonusAmount = floatval($bonusAmount) ?? 0;
            
            // Recalculate payroll with new marketing bonus
            $this->recalculatePayroll($userId, $year, $month, $bonusAmount);
        }

        return redirect()->route('admin.payroll', ['month' => $selectedMonth])
            ->with('success', 'Marketing bonuses updated successfully!');
    }

    /**
     * Update payroll status (draft → approved → paid, or revert to draft)
     */
    public function publishPayroll(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'staff_ids' => 'nullable|string',
            'publish_all' => 'nullable|boolean',
            'status' => 'required|in:draft,approved,paid',
        ]);

        $selectedMonth = $request->input('month');
        $targetStatus = $request->input('status');
        list($year, $month) = explode('-', $selectedMonth);

        $updated = 0;
        $created = 0;
        $statusMessages = [
            'draft' => 'reverted to draft',
            'approved' => 'published',
            'paid' => 'marked as paid',
        ];

        // Determine current status based on target status
        $currentStatus = null;
        if ($targetStatus === 'approved') {
            $currentStatus = 'draft';
        } elseif ($targetStatus === 'paid') {
            $currentStatus = 'approved';
        } elseif ($targetStatus === 'draft') {
            // Can revert from approved or paid
            $currentStatus = ['approved', 'paid'];
        }

        // Check if publish_all is set to '1' (string) or true
        $publishAll = $request->input('publish_all');
        $isPublishAll = ($publishAll === '1' || $publishAll === true || $publishAll === 1);
        
        // Get staff_ids and check if any are selected
        $staffIdsInput = $request->input('staff_ids', '');
        $staffIds = [];
        
        if (!empty($staffIdsInput) && trim($staffIdsInput) !== '') {
            $staffIds = array_filter(array_map('trim', explode(',', $staffIdsInput)));
        }

        // Get list of staff to process
        $staffToProcess = [];
        if ($isPublishAll || empty($staffIds)) {
            // Get all active staff
            $allStaff = Staff::with('user')
                ->where('status', 'active')
                ->get();
            foreach ($allStaff as $staff) {
                if ($staff->user) {
                    $staffToProcess[] = $staff->user->id;
                }
            }
        } elseif (!empty($staffIds)) {
            $staffToProcess = $staffIds;
        } else {
            return redirect()->route('admin.payroll', ['month' => $selectedMonth])
                ->with('error', 'Please select staff or use the "All" option.');
        }

        // Ensure payroll records exist for all staff before updating status
        // Recalculate all payrolls to ensure they match current data
        foreach ($staffToProcess as $userId) {
            $payroll = $this->recalculatePayroll($userId, $year, $month);
            if ($payroll && !$payroll->wasRecentlyCreated) {
                // Record was updated, not created
            } elseif ($payroll) {
                $created++;
            }
        }

        // Now update status for all payrolls
        if ($isPublishAll || empty($staffIds)) {
            // Update all payrolls with current status
            $query = \App\Models\Payroll::where('year', $year)
                ->where('month', $month);
            
            if (is_array($currentStatus)) {
                $query->whereIn('status', $currentStatus);
            } else {
                $query->where('status', $currentStatus);
            }
            
            $updated = $query->update(['status' => $targetStatus]);
            
            $message = "All payrolls {$statusMessages[$targetStatus]} successfully! {$updated} payroll record(s) updated.";
            if ($created > 0) {
                $message .= " {$created} new payroll record(s) created.";
            }
        } elseif (!empty($staffIds)) {
            // Update selected staff payrolls
            $query = \App\Models\Payroll::where('year', $year)
                ->where('month', $month)
                ->whereIn('user_id', $staffIds);
            
            if (is_array($currentStatus)) {
                $query->whereIn('status', $currentStatus);
            } else {
                $query->where('status', $currentStatus);
            }
            
            $updated = $query->update(['status' => $targetStatus]);
            
            $message = "Selected payrolls {$statusMessages[$targetStatus]} successfully! {$updated} payroll record(s) updated.";
            if ($created > 0) {
                $message .= " {$created} new payroll record(s) created.";
            }
        }

        return redirect()->route('admin.payroll', ['month' => $selectedMonth])
            ->with('success', $message);
    }

    /**
     * Sync all payroll records for a month to match current calculations
     */
    public function syncPayroll(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $selectedMonth = $request->input('month');
        list($year, $month) = explode('-', $selectedMonth);

        // Get all active staff
        $allStaff = Staff::with('user')
            ->where('status', 'active')
            ->get();

        $synced = 0;
        $created = 0;

        foreach ($allStaff as $staff) {
            if (!$staff->user) {
                continue;
            }

            $userId = $staff->user->id;
            $payroll = $this->recalculatePayroll($userId, $year, $month);
            
            if ($payroll) {
                if ($payroll->wasRecentlyCreated) {
                    $created++;
                } else {
                    $synced++;
                }
            }
        }

        $message = "Payroll sync completed! {$synced} record(s) updated, {$created} new record(s) created.";
        
        return redirect()->route('admin.payroll', ['month' => $selectedMonth])
            ->with('success', $message);
    }

    public function staffPayslip()
    {
        // Get all staff for selection
        $staffList = Staff::with('user')
            ->where('status', 'active')
            ->get();
        
        return view('admin.staffPayslip', compact('staffList'));
    }

    /**
     * Get payslip for a specific staff and month (JSON response)
     */
    public function getStaffPayslip($userId, $month)
    {
        // Validate month format YYYY-MM
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            return response()->json(['success' => false, 'message' => 'Invalid month format'], 400);
        }

        list($year, $monthNum) = explode('-', $month);

        // Get payroll record
        $payroll = \App\Models\Payroll::where('user_id', $userId)
            ->where('year', $year)
            ->where('month', $monthNum)
            ->first();

        if (!$payroll) {
            return response()->json(['success' => false, 'message' => 'Payslip not found for selected period'], 404);
        }

        // Restrict payslip viewing to approved or paid status only
        if (!in_array($payroll->status, ['approved', 'paid'])) {
            $statusLabel = ucfirst($payroll->status);
            return response()->json([
                'success' => false, 
                'message' => "Payslip is not available yet. Current status: {$statusLabel}. Please wait until payroll is published."
            ], 403);
        }

        // Get user and staff details
        $user = \App\Models\User::find($userId);
        $staff = $user->staff;

        // Render payslip HTML
        $html = view('partials.payslip-template', [
            'payroll' => $payroll,
            'user' => $user,
            'staff' => $staff,
            'month' => $month
        ])->render();

        return response()->json(['success' => true, 'html' => $html]);
    }

    /**
     * Export payslip as PDF (admin)
     */
    public function exportStaffPayslipPdf($userId, $month)
    {
        try {
        // Validate month format
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            abort(400, 'Invalid month format');
        }

        list($year, $monthNum) = explode('-', $month);

        // Get payroll record
        $payroll = \App\Models\Payroll::where('user_id', $userId)
            ->where('year', $year)
            ->where('month', $monthNum)
                ->first();

            if (!$payroll) {
                abort(404, 'Payslip not found for selected period');
            }

            // Restrict payslip viewing to approved or paid status only
            if (!in_array($payroll->status, ['approved', 'paid'])) {
                $statusLabel = ucfirst($payroll->status);
                abort(403, "Payslip is not available yet. Current status: {$statusLabel}. Please wait until payroll is published.");
            }

        $user = \App\Models\User::find($userId);
            
            if (!$user) {
                abort(404, 'User not found');
            }
            
        $staff = $user->staff;

            if (!$staff) {
                abort(404, 'Staff record not found');
            }

            // Generate PDF filename (sanitize for filename)
            $sanitizedName = preg_replace('/[^a-z0-9_]/', '_', strtolower($user->name));
            $filename = 'payslip_' . $sanitizedName . '_' . $month . '.pdf';
            
            // Render payslip HTML
            $html = view('partials.payslip-template', [
                'payroll' => $payroll,
                'user' => $user,
                'staff' => $staff,
                'month' => $month
            ])->render();

            // Generate PDF using DomPDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('enable-local-file-access', true);
            
            // Return PDF with proper headers
            return $pdf->download($filename, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Exception $e) {
            \Log::error('PDF Export Error (Admin): ' . $e->getMessage(), [
                'month' => $month,
                'user_id' => $userId,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return a user-friendly error page
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error generating PDF: ' . $e->getMessage()
                ], 500);
            }
            
            abort(500, 'Error generating PDF. Please try again or contact support.');
        }
    }

    /**
     * Send payslip via email (admin)
     */
    public function emailStaffPayslip(Request $request, $userId, $month)
    {
        // Validate month format
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            return response()->json(['success' => false, 'message' => 'Invalid month format'], 400);
        }

        list($year, $monthNum) = explode('-', $month);

        // Get payroll and user
        $payroll = \App\Models\Payroll::where('user_id', $userId)
            ->where('year', $year)
            ->where('month', $monthNum)
            ->first();

        if (!$payroll) {
            return response()->json(['success' => false, 'message' => 'Payslip not found for selected period'], 404);
        }

        // Restrict payslip emailing to approved or paid status only
        if (!in_array($payroll->status, ['approved', 'paid'])) {
            $statusLabel = ucfirst($payroll->status);
            return response()->json([
                'success' => false, 
                'message' => "Payslip cannot be sent. Current status: {$statusLabel}. Please wait until payroll is published."
            ], 403);
        }

        $user = \App\Models\User::find($userId);

        if (!$user || !$user->email) {
            return response()->json(['success' => false, 'message' => 'User email not found'], 404);
        }

        try {
        // Send email with payslip
            \Mail::to($user->email)->send(new \App\Mail\PayslipMail($payroll, $user, $month));

            return response()->json([
                'success' => true, 
                'message' => 'Payslip sent successfully to ' . $user->email
            ]);
        } catch (\Exception $e) {
            \Log::error('Error sending payslip email: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Error sending email. Please check your email configuration or try again later.'
            ], 500);
        }
    }
    public function payslip()
    {
        return view('staff.payslip');
    }

    /**
     * Get current staff's payslip for a specific month (JSON response)
     */
    public function getPayslip($month)
    {
        // Validate month format YYYY-MM
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            return response()->json(['success' => false, 'message' => 'Invalid month format'], 400);
        }

        list($year, $monthNum) = explode('-', $month);

        // Get current user
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Get payroll record
        $payroll = \App\Models\Payroll::where('user_id', $user->id)
            ->where('year', $year)
            ->where('month', $monthNum)
            ->first();

        if (!$payroll) {
            return response()->json(['success' => false, 'message' => 'Payslip not found for selected period'], 404);
        }

        // Restrict payslip viewing to approved or paid status only
        if (!in_array($payroll->status, ['approved', 'paid'])) {
            $statusLabel = ucfirst($payroll->status);
            return response()->json([
                'success' => false, 
                'message' => "Payslip is not available yet. Current status: {$statusLabel}. Please wait until payroll is published."
            ], 403);
        }

        $staff = $user->staff;

        // Render payslip HTML
        $html = view('partials.payslip-template', [
            'payroll' => $payroll,
            'user' => $user,
            'staff' => $staff,
            'month' => $month
        ])->render();

        return response()->json(['success' => true, 'html' => $html]);
    }

    /**
     * Export current staff's payslip as PDF
     */
    public function exportPayslipPdf($month)
    {
        try {
        // Validate month format
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            abort(400, 'Invalid month format');
        }

        list($year, $monthNum) = explode('-', $month);

        // Get current user
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthorized');
        }

        // Get payroll record
        $payroll = \App\Models\Payroll::where('user_id', $user->id)
            ->where('year', $year)
            ->where('month', $monthNum)
                ->first();

            if (!$payroll) {
                abort(404, 'Payslip not found for selected period');
            }

            // Restrict payslip viewing to approved or paid status only
            if (!in_array($payroll->status, ['approved', 'paid'])) {
                $statusLabel = ucfirst($payroll->status);
                abort(403, "Payslip is not available yet. Current status: {$statusLabel}. Please wait until payroll is published.");
            }

        $staff = $user->staff;

            if (!$staff) {
                abort(404, 'Staff record not found');
            }

            // Generate PDF filename (sanitize for filename)
            $sanitizedName = preg_replace('/[^a-z0-9_]/', '_', strtolower($user->name));
            $filename = 'payslip_' . $sanitizedName . '_' . $month . '.pdf';
            
            // Render payslip HTML
            $html = view('partials.payslip-template', [
                'payroll' => $payroll,
                'user' => $user,
                'staff' => $staff,
                'month' => $month
            ])->render();

            // Generate PDF using DomPDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('enable-local-file-access', true);
            
            // Return PDF with proper headers
            return $pdf->download($filename, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Exception $e) {
            \Log::error('PDF Export Error: ' . $e->getMessage(), [
                'month' => $month,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return a user-friendly error page
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error generating PDF: ' . $e->getMessage()
                ], 500);
            }
            
            abort(500, 'Error generating PDF. Please try again or contact support.');
        }
    }
}
