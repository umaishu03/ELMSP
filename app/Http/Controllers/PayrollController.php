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
                
                // Ensure hire_date is a Carbon instance
                if ($hireDate && !($hireDate instanceof \Carbon\Carbon)) {
                    $hireDate = \Carbon\Carbon::parse($hireDate);
                }

                // Calculate working days based on shifts assigned in timetable
                $monthStart = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
                $monthEnd = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();
                
                // Determine the start date for counting shifts (hire date or month start, whichever is later)
                // For mid-month joins, only count shifts from hire date onwards
                if ($hireDate && $hireDate instanceof \Carbon\Carbon && $hireDate->gt($monthStart)) {
                    $calcStartDate = $hireDate->copy()->startOfDay();
                } else {
                    $calcStartDate = $monthStart->copy()->startOfDay();
                }
                
                // Get all shifts for this staff in the selected month
                $shifts = Shift::where('staff_id', $staff->id)
                    ->whereBetween('date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
                    ->get();
                
                // Industry Standard: Total working days is ALWAYS 27 (full month), regardless of hire date
                // Calculate full month working days: Dec has 31 days, minus 4 rest days (1 per week) = 27 days
                $totalDaysInMonth = $monthEnd->day; // Days in the month (28-31)
                $weeksInMonth = $totalDaysInMonth / 7;
                $restDaysInMonth = floor($weeksInMonth); // 1 rest day per week
                $totalWorkingDaysInMonth = $totalDaysInMonth - $restDaysInMonth; // Always 27 for December
                
                // Get unpaid leave type ID for filtering
                $unpaidLeaveType = \App\Models\LeaveType::whereRaw('LOWER(type_name) = ?', [strtolower('unpaid')])->first();
                $unpaidLeaveTypeId = $unpaidLeaveType ? $unpaidLeaveType->id : null;

                // Count actual shifts worked (only from hire date onwards for mid-month joins)
                // Note: Exclude paid leaves but include unpaid leave (unpaid will be deducted separately)
                $workingDays = 0;
                
                foreach ($shifts as $shift) {
                    // Parse shift date (handle both Carbon instances and strings)
                    $shiftDate = $shift->date instanceof \Carbon\Carbon 
                        ? $shift->date 
                        : \Carbon\Carbon::parse($shift->date);
                    
                    // Only count shifts that are:
                    // 1. Not rest days (rest_day = false or null)
                    // 2. Not on paid leave (unpaid leave is included, will be deducted separately)
                    // 3. Have start_time and end_time (actual working shift)
                    // 4. On or after the calculation start date (hire date or month start)
                    $isRestDay = $shift->rest_day ?? false;
                    $hasWorkingHours = !empty($shift->start_time) && !empty($shift->end_time);
                    $isAfterStartDate = $shiftDate->startOfDay()->gte($calcStartDate->startOfDay());
                    
                    // Check if on leave
                    $isOnLeave = !empty($shift->leave_id);
                    $isOnUnpaidLeave = false;
                    if ($isOnLeave && $unpaidLeaveTypeId) {
                        // Check if this leave is unpaid leave
                        $leave = \App\Models\Leave::find($shift->leave_id);
                        $isOnUnpaidLeave = $leave && $leave->leave_type_id == $unpaidLeaveTypeId;
                    }
                    
                    // Include shift if: not rest day, has working hours, after start date, and (not on leave OR on unpaid leave)
                    if (!$isRestDay && $hasWorkingHours && $isAfterStartDate && (!$isOnLeave || $isOnUnpaidLeave)) {
                        $workingDays++;
                    }
                }
                
                // Calculate pro-rated basic salary
                // Industry Standard Formula: (Full Basic Salary ÷ 27) × Shifts Worked
                // This ensures all staff are paid proportionally to the full month, regardless of hire date
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

                // Calculate public holiday pay from actual shifts worked on public holidays
                $public_holiday_hours = 0;
                $publicHolidays = $this->getPublicHolidaysForMonth($year, $month);
                
                foreach ($shifts as $shift) {
                    $shiftDate = $shift->date instanceof \Carbon\Carbon 
                        ? $shift->date 
                        : \Carbon\Carbon::parse($shift->date);
                    
                    $shiftDateStr = $shiftDate->format('Y-m-d');
                    
                    // Check if this shift is on a public holiday
                    if (in_array($shiftDateStr, $publicHolidays)) {
                        // Only count if it's a working shift (has hours, not rest day, not on leave)
                        $isRestDay = $shift->rest_day ?? false;
                        $hasWorkingHours = !empty($shift->start_time) && !empty($shift->end_time);
                        $isOnLeave = !empty($shift->leave_id);
                        
                        if (!$isRestDay && $hasWorkingHours && !$isOnLeave) {
                            // Calculate hours worked (excluding break time)
                            $startTime = \Carbon\Carbon::parse($shift->start_time);
                            $endTime = \Carbon\Carbon::parse($shift->end_time);
                            
                            // Handle overnight shifts
                            if ($endTime <= $startTime) {
                                $endTime->addDay();
                            }
                            
                            // Calculate total minutes worked
                            $totalMinutes = $startTime->diffInMinutes($endTime);
                            
                            // Subtract break minutes (break time is not paid)
                            $breakMinutes = $shift->break_minutes ?? 0;
                            $workedMinutes = $totalMinutes - $breakMinutes;
                            
                            // Convert to hours
                            $shiftHours = max(0, $workedMinutes / 60);
                            
                            $public_holiday_hours += $shiftHours;
                        }
                    }
                }
                
                $public_holiday_pay = 15.38 * $public_holiday_hours;

                // Calculate unpaid leave deduction
                $unpaidLeaveDeduction = 0;
                $unpaidLeaveType = \App\Models\LeaveType::whereRaw('LOWER(type_name) = ?', [strtolower('unpaid')])->first();
                if ($unpaidLeaveType) {
                    // Get approved unpaid leave that overlaps with this month
                    $unpaidLeaves = \App\Models\Leave::where('staff_id', $staff->id)
                        ->where('leave_type_id', $unpaidLeaveType->id)
                        ->where('status', 'approved')
                        ->where(function($query) use ($monthStart, $monthEnd) {
                            // Leave starts in month
                            $query->whereBetween('start_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
                                  // Or leave ends in month
                                  ->orWhereBetween('end_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
                                  // Or leave spans the entire month
                                  ->orWhere(function($q) use ($monthStart, $monthEnd) {
                                      $q->where('start_date', '<=', $monthStart->format('Y-m-d'))
                                        ->where('end_date', '>=', $monthEnd->format('Y-m-d'));
                                  });
                        })
                        ->get();
                    
                    // Calculate actual unpaid leave days within the month
                    $unpaidLeaveDays = 0;
                    foreach ($unpaidLeaves as $leave) {
                        $leaveStart = \Carbon\Carbon::parse($leave->start_date);
                        $leaveEnd = \Carbon\Carbon::parse($leave->end_date);
                        
                        // Calculate overlap days
                        $overlapStart = max($leaveStart, $monthStart);
                        $overlapEnd = min($leaveEnd, $monthEnd);
                        
                        if ($overlapStart <= $overlapEnd) {
                            $overlapDays = $overlapStart->diffInDays($overlapEnd) + 1;
                            $unpaidLeaveDays += $overlapDays;
                        }
                    }
                    
                    // Calculate daily rate: (Full Basic Salary ÷ 27)
                    $dailyRate = $totalWorkingDaysInMonth > 0 ? ($fullBasicSalary / $totalWorkingDaysInMonth) : 0;
                    
                    // Calculate deduction: unpaid leave days × daily rate
                    $unpaidLeaveDeduction = $unpaidLeaveDays * $dailyRate;
                }

                // Get marketing bonus and status from payroll record if exists, otherwise default to 0
                // Use calculated deduction (from unpaid leave) or fall back to stored value
                $marketingBonus = 0;
                $totalDeductions = $unpaidLeaveDeduction; // Use calculated deduction
                $payrollStatus = 'draft';
                $payrollRecord = \App\Models\Payroll::where('user_id', $user->id)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->first();
                if ($payrollRecord) {
                    $marketingBonus = $payrollRecord->marketing_bonus ?? 0;
                    // If payroll record has deductions, use that (in case there are other deductions), otherwise use calculated
                    if ($payrollRecord->total_deductions > 0) {
                        $totalDeductions = $payrollRecord->total_deductions;
                    }
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
                    'total_deductions' => $totalDeductions,
                    'public_holiday_hours' => $public_holiday_hours,
                    'normal_ot_hours' => $normal_ot_hours,
                    'ph_ot_hours' => $ph_ot_hours,
                    'hire_date' => $hireDate,
                    'working_days' => $workingDays,
                    'total_working_days' => $totalWorkingDaysInMonth, // Always 27 (full month)
                    'is_full_month' => $workingDays == $totalWorkingDaysInMonth && $calcStartDate->eq($monthStart),
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
        // Ensure hire_date is a Carbon instance
        $hireDate = $staff->hire_date;
        if ($hireDate && !($hireDate instanceof \Carbon\Carbon)) {
            $hireDate = \Carbon\Carbon::parse($hireDate);
        }
        
        // Determine the start date for calculation (hire date or month start, whichever is later)
        if ($hireDate && $hireDate instanceof \Carbon\Carbon && $hireDate->gt($monthStart)) {
            $calcStartDate = $hireDate->copy()->startOfDay();
        } else {
            $calcStartDate = $monthStart->copy()->startOfDay();
        }

        // Industry Standard: Total working days is ALWAYS based on full month, regardless of hire date
        // Calculate full month working days: month days minus rest days (1 per week)
        $totalDaysInMonth = $monthEnd->day; // Days in the month (28-31)
        $weeksInMonth = $totalDaysInMonth / 7;
        $restDaysInMonth = floor($weeksInMonth); // 1 rest day per week
        $totalWorkingDaysInMonth = $totalDaysInMonth - $restDaysInMonth; // Always 27 for December

        // Get all shifts for this staff in the selected month
        $shifts = Shift::where('staff_id', $staff->id)
            ->whereBetween('date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
            ->get();

        // Get unpaid leave type ID for filtering
        $unpaidLeaveType = \App\Models\LeaveType::whereRaw('LOWER(type_name) = ?', [strtolower('unpaid')])->first();
        $unpaidLeaveTypeId = $unpaidLeaveType ? $unpaidLeaveType->id : null;

        // Count actual shifts worked (only from hire date onwards for mid-month joins)
        // Note: Exclude paid leaves but include unpaid leave (unpaid will be deducted separately)
        $workingDays = 0;
        foreach ($shifts as $shift) {
            $shiftDate = $shift->date instanceof \Carbon\Carbon 
                ? $shift->date 
                : \Carbon\Carbon::parse($shift->date);
            
            $isRestDay = $shift->rest_day ?? false;
            $hasWorkingHours = !empty($shift->start_time) && !empty($shift->end_time);
            $isAfterStartDate = $shiftDate->startOfDay()->gte($calcStartDate->startOfDay());
            
            // Check if on leave
            $isOnLeave = !empty($shift->leave_id);
            $isOnUnpaidLeave = false;
            if ($isOnLeave && $unpaidLeaveTypeId) {
                // Check if this leave is unpaid leave
                $leave = \App\Models\Leave::find($shift->leave_id);
                $isOnUnpaidLeave = $leave && $leave->leave_type_id == $unpaidLeaveTypeId;
            }
            
            // Include shift if: not rest day, has working hours, after start date, and (not on leave OR on unpaid leave)
            if (!$isRestDay && $hasWorkingHours && $isAfterStartDate && (!$isOnLeave || $isOnUnpaidLeave)) {
                $workingDays++;
            }
        }

        // Calculate pro-rated basic salary
        // Industry Standard Formula: (Full Basic Salary ÷ 27) × Shifts Worked
        // This ensures all staff are paid proportionally to the full month, regardless of hire date
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
        
        // Calculate public holiday pay from actual shifts worked on public holidays
        $public_holiday_hours = 0;
        $publicHolidays = $this->getPublicHolidaysForMonth($year, $month);
        
        foreach ($shifts as $shift) {
            $shiftDate = $shift->date instanceof \Carbon\Carbon 
                ? $shift->date 
                : \Carbon\Carbon::parse($shift->date);
            
            $shiftDateStr = $shiftDate->format('Y-m-d');
            
            // Check if this shift is on a public holiday
            if (in_array($shiftDateStr, $publicHolidays)) {
                // Only count if it's a working shift (has hours, not rest day, not on leave)
                $isRestDay = $shift->rest_day ?? false;
                $hasWorkingHours = !empty($shift->start_time) && !empty($shift->end_time);
                $isOnLeave = !empty($shift->leave_id);
                
                if (!$isRestDay && $hasWorkingHours && !$isOnLeave) {
                    // Calculate hours worked (excluding break time)
                    $startTime = \Carbon\Carbon::parse($shift->start_time);
                    $endTime = \Carbon\Carbon::parse($shift->end_time);
                    
                    // Handle overnight shifts
                    if ($endTime <= $startTime) {
                        $endTime->addDay();
                    }
                    
                    // Calculate total minutes worked
                    $totalMinutes = $startTime->diffInMinutes($endTime);
                    
                    // Subtract break minutes (break time is not paid)
                    $breakMinutes = $shift->break_minutes ?? 0;
                    $workedMinutes = $totalMinutes - $breakMinutes;
                    
                    // Convert to hours
                    $shiftHours = max(0, $workedMinutes / 60);
                    
                    $public_holiday_hours += $shiftHours;
                }
            }
        }
        
        $public_holiday_pay = 15.38 * $public_holiday_hours;

        // Calculate unpaid leave deduction
        $unpaidLeaveDeduction = 0;
        $unpaidLeaveType = \App\Models\LeaveType::whereRaw('LOWER(type_name) = ?', [strtolower('unpaid')])->first();
        if ($unpaidLeaveType) {
            // Get approved unpaid leave that overlaps with this month
            $unpaidLeaves = \App\Models\Leave::where('staff_id', $staff->id)
                ->where('leave_type_id', $unpaidLeaveType->id)
                ->where('status', 'approved')
                ->where(function($query) use ($monthStart, $monthEnd) {
                    // Leave starts in month
                    $query->whereBetween('start_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
                          // Or leave ends in month
                          ->orWhereBetween('end_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
                          // Or leave spans the entire month
                          ->orWhere(function($q) use ($monthStart, $monthEnd) {
                              $q->where('start_date', '<=', $monthStart->format('Y-m-d'))
                                ->where('end_date', '>=', $monthEnd->format('Y-m-d'));
                          });
                })
                ->get();
            
            // Calculate actual unpaid leave days within the month
            $unpaidLeaveDays = 0;
            foreach ($unpaidLeaves as $leave) {
                $leaveStart = \Carbon\Carbon::parse($leave->start_date);
                $leaveEnd = \Carbon\Carbon::parse($leave->end_date);
                
                // Calculate overlap days
                $overlapStart = max($leaveStart, $monthStart);
                $overlapEnd = min($leaveEnd, $monthEnd);
                
                if ($overlapStart <= $overlapEnd) {
                    $overlapDays = $overlapStart->diffInDays($overlapEnd) + 1;
                    $unpaidLeaveDays += $overlapDays;
                }
            }
            
            // Calculate daily rate: (Full Basic Salary ÷ 27)
            $dailyRate = $totalWorkingDaysInMonth > 0 ? ($fullBasicSalary / $totalWorkingDaysInMonth) : 0;
            
            // Calculate deduction: unpaid leave days × daily rate
            $unpaidLeaveDeduction = $unpaidLeaveDays * $dailyRate;
        }

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

        // Calculate total deductions (unpaid leave deduction)
        $totalDeductions = $unpaidLeaveDeduction;

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
                'total_deductions' => $totalDeductions,
                'net_salary' => $grossSalary - $totalDeductions,
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
                'total_deductions' => $totalDeductions,
                'net_salary' => $grossSalary - $totalDeductions,
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

    /**
     * Get public holidays for a specific month/year
     * Returns array of date strings in Y-m-d format
     */
    private function getPublicHolidaysForMonth($year, $month)
    {
        // Public holidays for 2026 (can be extended for other years)
        $allHolidays = [
            '2026-01-01', // New Year's Day
            '2026-01-31', // Thaipusam
            '2026-02-01', // Federal Territory Day
            '2026-02-17', // Chinese New Year
            '2026-02-18', // Chinese New Year (2nd Day)
            '2026-03-20', // Hari Raya Aidilfitri
            '2026-03-21', // Hari Raya Aidilfitri (2nd Day)
            '2026-05-01', // Labour Day / Vesak Day
            '2026-05-27', // Hari Raya Aidiladha
            '2026-06-06', // Agong's Birthday
            '2026-07-16', // Awal Muharram
            '2026-08-25', // Prophet Muhammad's Birthday
            '2026-08-31', // Merdeka Day
            '2026-09-16', // Malaysia Day
            '2026-11-08', // Deepavali
            '2026-12-25', // Christmas Day
        ];

        // Filter holidays for the specific month
        $monthHolidays = [];
        foreach ($allHolidays as $holiday) {
            $holidayDate = \Carbon\Carbon::parse($holiday);
            if ($holidayDate->year == $year && $holidayDate->month == $month) {
                $monthHolidays[] = $holiday;
            }
        }

        return $monthHolidays;
    }
}
