<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Leave extends Model
{
    use HasFactory;

    protected $table = 'leaves';
    
    // Track leaves that have already had balance applied in created event
    protected static $balanceAppliedInCreated = [];

    protected $fillable = [
        'staff_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'attachment',
        'status',
        'auto_approved',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'auto_approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    // Max leave limits
    public static $maxLeaves = [
        'annual' => 14,
        'hospitalization' => 30,
        'medical' => 14,
        'emergency' => 7,
        'replacement' => null, // calculated from OT hours
        'unpaid' => 10,        // 10 days limit
        'marriage' => 3,
    ];

    // Department leave constraints (max people per day, excluding rest days)
    public static $departmentConstraints = [
        'supervisor' => 1,
        'cashier' => 2,
        'barista' => 1,
        'joki' => 1,
        'waiter' => 3,
        'kitchen' => 2,
    ];

    // Department weekly leave constraints (max people per week)
    public static $departmentWeeklyConstraints = [
        'supervisor' => 2,
        'cashier' => 4,
        'barista' => 2,
        'joki' => 2,
        'waiter' => 6,
        'kitchen' => 4,
    ];

    /**
     * Relationship to Staff
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    /**
     * Relationship to LeaveType
     */
    public function leaveType()
    {
        return $this->belongsTo(\App\Models\LeaveType::class, 'leave_type_id');
    }

    /**
     * Convenience accessor to get the related user through staff.
     */
    public function getUserAttribute()
    {
        return $this->staff ? $this->staff->user : null;
    }


    /**
     * Calculate maximum days for replacement leave
     */
    public function calculateMaxDays()
    {
        // Prefer leave type configuration if available
        if ($this->leaveType && !is_null($this->leaveType->max_days)) {
            return $this->leaveType->max_days;
        }

        $typeName = $this->leaveType?->type_name ?? null;
        if ($typeName === 'replacement') {
            return null;
        }
        return $typeName ? (self::$maxLeaves[$typeName] ?? null) : null;
    }

    /**
     * Check if employee has enough leave balance
     */
    public function checkAvailability()
    {
        $max = $this->calculateMaxDays();
        return is_null($max) ? true : ($this->total_days <= $max);
    }

    /**
     * Check department leave constraints
     * Returns true if leave can be approved, false otherwise
     */
    public function checkDepartmentConstraints()
    {
        $department = $this->staff?->department;
        $startDate = $this->start_date;
        $endDate = $this->end_date;

        // Get constraint for this department
        $maxPeople = self::$departmentConstraints[$department] ?? null;

        if ($maxPeople === null) {
            return true; // No constraint for this department
        }

        // For manager and supervisor: check 2 days per week limit
        if (in_array($department, ['manager', 'supervisor'])) {
            return $this->checkManagerSupervisorConstraint();
        }

        // For other departments: check max people per day (excluding rest days)
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Check if this date is a rest day for the staff member taking leave
            $staffShiftOnDate = \App\Models\Shift::where('staff_id', $this->staff_id)
                ->whereDate('date', $date)
                ->first();
            
            // Skip rest days - leave constraints don't apply to rest days
            if ($staffShiftOnDate && $staffShiftOnDate->rest_day) {
                continue;
            }
            
            // Get all approved leaves for this date in the department
            $leavesOnDate = self::whereHas('staff', function ($q) use ($department) {
                    $q->where('department', $department);
                })
                ->where('status', 'approved')
                ->where('auto_approved', true)
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->with('staff')
                ->get();
            
            // Count only leaves where this date is NOT a rest day for the staff member
            $leaveCountThisDay = 0;
            foreach ($leavesOnDate as $leave) {
                $shiftOnDate = \App\Models\Shift::where('staff_id', $leave->staff_id)
                    ->whereDate('date', $date)
                    ->first();
                
                // Only count if this date is NOT a rest day for this staff member
                if (!$shiftOnDate || !$shiftOnDate->rest_day) {
                    $leaveCountThisDay++;
                }
            }

            if ($leaveCountThisDay >= $maxPeople) {
                return false; // Department already has max people on this day
            }
        }

        return true; // All days have available slots
    }

    /**
     * Check manager/supervisor constraint: max 1 person per day (excluding rest days)
     */
    public function checkManagerSupervisorConstraint()
    {
        $department = $this->staff?->department;
        $startDate = $this->start_date;
        $endDate = $this->end_date;
        $maxPeople = self::$departmentConstraints[$department] ?? 1;

        // Check max people per day (excluding rest days)
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Check if this date is a rest day for the staff member taking leave
            $staffShiftOnDate = \App\Models\Shift::where('staff_id', $this->staff_id)
                ->whereDate('date', $date)
                ->first();
            
            // Skip rest days - leave constraints don't apply to rest days
            if ($staffShiftOnDate && $staffShiftOnDate->rest_day) {
                continue;
            }
            
            // Get all approved leaves for this date in the department
            $leavesOnDate = self::whereHas('staff', function ($q) use ($department) {
                    $q->where('department', $department);
                })
                ->where('status', 'approved')
                ->where('auto_approved', true)
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->with('staff')
                ->get();
            
            // Count only leaves where this date is NOT a rest day for the staff member
            $leaveCountThisDay = 0;
            foreach ($leavesOnDate as $leave) {
                $shiftOnDate = \App\Models\Shift::where('staff_id', $leave->staff_id)
                    ->whereDate('date', $date)
                    ->first();
                
                // Only count if this date is NOT a rest day for this staff member
                if (!$shiftOnDate || !$shiftOnDate->rest_day) {
                    $leaveCountThisDay++;
                }
            }

            if ($leaveCountThisDay >= $maxPeople) {
                return false; // Department already has max people on this day
            }
        }

        return true; // All days have available slots
    }

    /**
     * Check weekly leave entitlement (max 2 days per week, excluding rest days)
     * Marriage leave: max 3 days per week (since it's one-time only)
     * Exempt: Emergency, Medical, Hospitalization
     */
    public function checkWeeklyLeaveEntitlement()
    {
        $leaveTypeName = strtolower($this->leaveType?->type_name ?? '');
        if (in_array($leaveTypeName, ['emergency', 'medical', 'hospitalization'])) {
            return ['valid' => true, 'message' => 'Exempt from weekly entitlement limit'];
        }

        // Marriage leave is allowed up to 3 days per week (one-time leave)
        $maxDaysPerWeek = ($leaveTypeName === 'marriage') ? 3 : 2;

        $startDate = $this->start_date;
        $endDate = $this->end_date;

        // Check each week in the leave period
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $weekStart = $date->copy()->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            // Count leave days in this week (excluding rest days)
            $leaveDaysThisWeek = 0;
            for ($checkDate = max($date->copy(), $weekStart); $checkDate->lte(min($endDate, $weekEnd)); $checkDate->addDay()) {
                // Check if this date is a rest day
                $shiftOnDate = \App\Models\Shift::where('staff_id', $this->staff_id)
                    ->whereDate('date', $checkDate)
                    ->first();
                
                // Only count non-rest days
                if (!$shiftOnDate || !$shiftOnDate->rest_day) {
                    $leaveDaysThisWeek++;
                }
            }

            // Check existing approved leaves for this staff in this week
            $existingLeaves = self::where('staff_id', $this->staff_id)
                ->where('status', 'approved')
                ->where(function($q) use ($weekStart, $weekEnd) {
                    $q->whereBetween('start_date', [$weekStart, $weekEnd])
                      ->orWhereBetween('end_date', [$weekStart, $weekEnd])
                      ->orWhere(function($q2) use ($weekStart, $weekEnd) {
                          $q2->where('start_date', '<=', $weekStart)
                             ->where('end_date', '>=', $weekEnd);
                      });
                })
                ->where('id', '!=', $this->id ?? 0)
                ->get();

            $existingDaysThisWeek = 0;
            $hasMarriageLeave = false;
            foreach ($existingLeaves as $leave) {
                $leaveTypeNameExisting = strtolower($leave->leaveType?->type_name ?? '');
                // Skip exempt leave types
                if (in_array($leaveTypeNameExisting, ['emergency', 'medical', 'hospitalization'])) {
                    continue;
                }

                // Check if there's a marriage leave in this week
                if ($leaveTypeNameExisting === 'marriage') {
                    $hasMarriageLeave = true;
                }

                for ($checkDate = max($leave->start_date->copy(), $weekStart); $checkDate->lte(min($leave->end_date, $weekEnd)); $checkDate->addDay()) {
                    $shiftOnDate = \App\Models\Shift::where('staff_id', $leave->staff_id)
                        ->whereDate('date', $checkDate)
                        ->first();
                    
                    if (!$shiftOnDate || !$shiftOnDate->rest_day) {
                        $existingDaysThisWeek++;
                    }
                }
            }

            // If there's an existing marriage leave or current leave is marriage, allow up to 3 days
            if ($hasMarriageLeave || $leaveTypeName === 'marriage') {
                $maxDaysPerWeek = 3;
            }

            $totalDaysThisWeek = $leaveDaysThisWeek + $existingDaysThisWeek;
            if ($totalDaysThisWeek > $maxDaysPerWeek) {
                return [
                    'valid' => false,
                    'message' => "Maximum {$maxDaysPerWeek} leave days per week allowed. You have {$existingDaysThisWeek} day(s) already approved this week, and this application adds {$leaveDaysThisWeek} day(s)."
                ];
            }
        }

        return ['valid' => true, 'message' => 'Weekly entitlement check passed'];
    }

    /**
     * Check advance leave application requirement (3 days in advance)
     * All calendar days count, including weekends
     * Exempt: Emergency, Medical, Hospitalization
     */
    public function checkAdvanceApplicationRequirement()
    {
        $leaveTypeName = strtolower($this->leaveType?->type_name ?? '');
        if (in_array($leaveTypeName, ['emergency', 'medical', 'hospitalization'])) {
            return ['valid' => true, 'message' => 'Exempt from advance application requirement'];
        }

        $startDate = $this->start_date;
        $today = \Carbon\Carbon::today();
        
        // Count all calendar days from today to start date (including weekends)
        $daysCount = $today->diffInDays($startDate, false);
        
        // diffInDays returns the number of days between dates
        // If startDate is in the future, daysCount will be positive
        // We need at least 3 days (today + 3 more days = start date on day 4)
        if ($daysCount < 3) {
            return [
                'valid' => false,
                'message' => "Leave applications must be submitted at least 3 days in advance. Your selected start date is only {$daysCount} day(s) away."
            ];
        }

        return ['valid' => true, 'message' => 'Advance application requirement met'];
    }

    /**
     * Check weekend leave restriction
     * Exempt: Emergency, Medical (with MC), Hospitalization
     */
    public function checkWeekendRestriction()
    {
        $leaveTypeName = strtolower($this->leaveType?->type_name ?? '');
        $hasAttachment = !empty($this->attachment);

        // Emergency and Hospitalization are always exempt
        if (in_array($leaveTypeName, ['emergency', 'hospitalization'])) {
            return ['valid' => true, 'message' => 'Exempt from weekend restriction'];
        }

        // Medical is exempt only if MC is provided
        if ($leaveTypeName === 'medical' && $hasAttachment) {
            return ['valid' => true, 'message' => 'Medical leave with MC is exempt from weekend restriction'];
        }

        $startDate = $this->start_date;
        $endDate = $this->end_date;

        // Check if any date in the leave period falls on weekend
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Saturday = 6, Sunday = 0
            if ($date->dayOfWeek === 0 || $date->dayOfWeek === 6) {
                return [
                    'valid' => false,
                    'message' => 'Normal leave applications are not permitted on Saturdays and Sundays. Only Emergency Leave, Medical Leave (with MC), and Hospitalization Leave are allowed on weekends.'
                ];
            }
        }

        return ['valid' => true, 'message' => 'Weekend restriction check passed'];
    }

    /**
     * Check department weekly leave constraints
     * Exempt: Emergency, Medical, Hospitalization
     */
    public function checkDepartmentWeeklyConstraints()
    {
        $leaveTypeName = strtolower($this->leaveType?->type_name ?? '');
        if (in_array($leaveTypeName, ['emergency', 'medical', 'hospitalization'])) {
            return ['valid' => true, 'message' => 'Exempt from department weekly constraints'];
        }

        $department = $this->staff?->department;
        $maxPeoplePerWeek = self::$departmentWeeklyConstraints[$department] ?? null;

        if ($maxPeoplePerWeek === null) {
            return ['valid' => true, 'message' => 'No weekly constraint for this department'];
        }

        $startDate = $this->start_date;
        $endDate = $this->end_date;

        // Check each week in the leave period
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $weekStart = $date->copy()->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            // Count approved leaves in this department for this week (excluding exempt types)
            $leavesThisWeek = self::whereHas('staff', function ($q) use ($department) {
                    $q->where('department', $department);
                })
                ->where('status', 'approved')
                ->where(function($q) use ($weekStart, $weekEnd) {
                    $q->whereBetween('start_date', [$weekStart, $weekEnd])
                      ->orWhereBetween('end_date', [$weekStart, $weekEnd])
                      ->orWhere(function($q2) use ($weekStart, $weekEnd) {
                          $q2->where('start_date', '<=', $weekStart)
                             ->where('end_date', '>=', $weekEnd);
                      });
                })
                ->where('id', '!=', $this->id ?? 0)
                ->with('leaveType')
                ->get();

            // Count only non-exempt leaves
            $countThisWeek = 0;
            foreach ($leavesThisWeek as $leave) {
                $leaveTypeNameExisting = strtolower($leave->leaveType?->type_name ?? '');
                if (!in_array($leaveTypeNameExisting, ['emergency', 'medical', 'hospitalization'])) {
                    $countThisWeek++;
                }
            }

            // Add 1 for current leave if it would be approved
            $countThisWeek += 1;

            if ($countThisWeek > $maxPeoplePerWeek) {
                return [
                    'valid' => false,
                    'message' => "Department {$department} already has {$maxPeoplePerWeek} staff member(s) on leave this week. Maximum allowed is {$maxPeoplePerWeek} per week."
                ];
            }
        }

        return ['valid' => true, 'message' => 'Department weekly constraint check passed'];
    }

    /**
     * Check for overtime conflict on leave dates
     * Exempt: Emergency, Medical, Hospitalization
     */
    public function checkOvertimeConflict()
    {
        $leaveTypeName = strtolower($this->leaveType?->type_name ?? '');
        if (in_array($leaveTypeName, ['emergency', 'medical', 'hospitalization'])) {
            return ['valid' => true, 'message' => 'Exempt from overtime conflict check'];
        }

        if (!$this->staff_id || !$this->start_date || !$this->end_date) {
            return ['valid' => true, 'message' => 'Missing required information for conflict check'];
        }

        $startDate = $this->start_date;
        $endDate = $this->end_date;

        // Check for approved overtime on any date within the leave period
        $conflictingOvertimes = \App\Models\Overtime::where('staff_id', $this->staff_id)
            ->where('status', 'approved')
            ->whereBetween('ot_date', [$startDate, $endDate])
            ->get();

        if ($conflictingOvertimes->count() > 0) {
            $conflictingDates = $conflictingOvertimes->pluck('ot_date')
                ->map(function($date) {
                    return $date->format('M d, Y');
                })
                ->unique()
                ->implode(', ');

            return [
                'valid' => false,
                'message' => "Leave application cannot be approved on the same date(s) as approved overtime. You have approved overtime on: {$conflictingDates}. Please choose different dates or cancel your overtime first."
            ];
        }

        return ['valid' => true, 'message' => 'No overtime conflict detected'];
    }

    /**
     * Auto-approve or reject leave based on constraints
     */
    public function autoApproveOrReject()
    {
        $rejectionReason = null;

        // Get leave type name
        $leaveTypeName = strtolower($this->leaveType?->type_name ?? '');
        $isExemptLeave = in_array($leaveTypeName, ['emergency', 'medical', 'hospitalization']);

        // Rule 1: Check availability (leave balance)
        if (!$this->checkAvailability()) {
            $this->status = 'rejected';
            $this->auto_approved = false;
            $this->rejection_reason = 'Insufficient leave balance';
            return;
        }

        // Rule 2: Advance Leave Application Requirement (3 days in advance, all calendar days count)
        // Exempt: Emergency, Medical, Hospitalization
        if (!$isExemptLeave) {
            $advanceCheck = $this->checkAdvanceApplicationRequirement();
            if (!$advanceCheck['valid']) {
                $this->status = 'rejected';
                $this->auto_approved = false;
                $this->rejection_reason = $advanceCheck['message'];
                return;
            }
        }

        // Rule 3: Weekend Leave Restriction
        // Exempt: Emergency, Medical (with MC), Hospitalization
        if (!$isExemptLeave || ($leaveTypeName === 'medical' && !$this->attachment)) {
            $weekendCheck = $this->checkWeekendRestriction();
            if (!$weekendCheck['valid']) {
                $this->status = 'rejected';
                $this->auto_approved = false;
                $this->rejection_reason = $weekendCheck['message'];
                return;
            }
        }

        // Rule 4: Medical Certificate Requirement for Medical/Hospitalization
        if (in_array($leaveTypeName, ['medical', 'hospitalization']) && !$this->attachment) {
            $this->status = 'rejected';
            $this->auto_approved = false;
            $this->rejection_reason = 'Medical Certificate (MC) is required for ' . ucfirst($leaveTypeName) . ' leave';
            return;
        }

        // Rule 5: Weekly Leave Entitlement (max 2 days per week, excluding rest days)
        // Exempt: Emergency, Medical, Hospitalization
        if (!$isExemptLeave) {
            $weeklyEntitlementCheck = $this->checkWeeklyLeaveEntitlement();
            if (!$weeklyEntitlementCheck['valid']) {
                $this->status = 'rejected';
                $this->auto_approved = false;
                $this->rejection_reason = $weeklyEntitlementCheck['message'];
                return;
            }
        }

        // Rule 6: Department Per-Day Constraints
        // Exempt: Emergency, Medical, Hospitalization
        if (!$isExemptLeave) {
            if (!$this->checkDepartmentConstraints()) {
                $this->status = 'rejected';
                $this->auto_approved = false;
                $this->rejection_reason = 'Department daily quota exceeded for the selected date(s)';
                return;
            }
        }

        // Rule 7: Department Per-Week Constraints
        // Exempt: Emergency, Medical, Hospitalization
        if (!$isExemptLeave) {
            $weeklyDeptCheck = $this->checkDepartmentWeeklyConstraints();
            if (!$weeklyDeptCheck['valid']) {
                $this->status = 'rejected';
                $this->auto_approved = false;
                $this->rejection_reason = $weeklyDeptCheck['message'];
                return;
            }
        }

        // Rule 8: Overtime Conflict Check
        // Exempt: Emergency, Medical, Hospitalization
        if (!$isExemptLeave) {
            $overtimeConflictCheck = $this->checkOvertimeConflict();
            if (!$overtimeConflictCheck['valid']) {
                $this->status = 'rejected';
                $this->auto_approved = false;
                $this->rejection_reason = $overtimeConflictCheck['message'];
                return;
            }
        }

        // Auto-approve if all checks pass
        $this->status = 'approved';
        $this->auto_approved = true;
        $this->approved_at = now();
    }

    /**
     * Auto-set approval on creating
     */
    protected static function booted()
    {
        static::creating(function ($leave) {
            // Set initial status to pending
            if (!$leave->status) {
                $leave->status = 'pending';
            }
        });

        // After creation: auto-approve or reject based on constraints
        static::created(function ($leave) {
            // Store original status
            $originalStatus = $leave->status;
            
            // Auto-approve or reject based on constraints
            $leave->autoApproveOrReject();
            
            // If status changed to approved, apply balance immediately
            if ($originalStatus !== 'approved' && $leave->status === 'approved') {
                // Mark this leave ID as having balance applied in created event
                static::$balanceAppliedInCreated[$leave->id] = true;
                $leave->applyToBalance();
                $leave->linkShifts();
            }
            
            // Save the status change using saveQuietly to avoid triggering updated event
            $leave->saveQuietly();
        });

        // On update: if status transitions to approved, link shifts; if it transitions away, unlink
        static::updated(function ($leave) {
            // Skip if balance was already applied in created event
            if (isset(static::$balanceAppliedInCreated[$leave->id])) {
                unset(static::$balanceAppliedInCreated[$leave->id]);
                return;
            }
            
            // Only process if status was actually changed
            if (!$leave->wasChanged('status')) {
                return;
            }
            
            $original = $leave->getOriginal('status');
            $current = $leave->status;
            
            // Check if status changed to approved
            if ($original !== 'approved' && $current === 'approved') {
                // status became approved -> apply balance + link
                $leave->applyToBalance();
                $leave->linkShifts();
            } elseif ($original === 'approved' && $current !== 'approved') {
                // status changed away from approved -> revert balance + unlink
                $leave->revertFromBalance();
                $leave->unlinkShifts();
            }
        });

        // When a leave is deleted, remove links
        static::deleted(function ($leave) {
            // if it was approved, revert balance adjustments
            if ($leave->status === 'approved') {
                $leave->revertFromBalance();
            }
            $leave->unlinkShifts();
        });
    }

    /**
     * Apply this leave to the staff's leave balance (increment used_days, decrement remaining_days).
     * Creates a LeaveBalance row if none exists (best-effort) using LeaveType.max_days if available.
     */
    public function applyToBalance()
    {
        if (! $this->staff_id || ! $this->leave_type_id || ! $this->total_days) {
            Log::warning('Leave applyToBalance skipped - missing required fields', [
                'leave_id' => $this->id,
                'staff_id' => $this->staff_id,
                'leave_type_id' => $this->leave_type_id,
                'total_days' => $this->total_days
            ]);
            return;
        }

        // Check if this leave type should deduct from balance
        $leaveType = $this->leaveType;
        if (!$leaveType || !$leaveType->deduct_from_balance) {
            Log::info('Leave applyToBalance skipped - deduct_from_balance is false', [
                'leave_id' => $this->id,
                'leave_type_id' => $this->leave_type_id,
                'type_name' => $leaveType?->type_name ?? 'unknown'
            ]);
            return;
        }

        $lb = \App\Models\LeaveBalance::where('staff_id', $this->staff_id)
            ->where('leave_type_id', $this->leave_type_id)
            ->first();

        // If no leave balance row exists, create one using LeaveType.max_days as total_days
        if (! $lb) {
            $type = \App\Models\LeaveType::find($this->leave_type_id);
            $total = $type?->max_days ?? (self::$maxLeaves[$type?->type_name ?? ''] ?? 0);
            $lb = \App\Models\LeaveBalance::create([
                'staff_id' => $this->staff_id,
                'leave_type_id' => $this->leave_type_id,
                'total_days' => $total,
                'used_days' => 0,
                'remaining_days' => $total,
            ]);
            Log::info('LeaveBalance created', ['staff_id' => $this->staff_id, 'leave_type_id' => $this->leave_type_id, 'total_days' => $total]);
        }

        // Store values before update for logging
        $oldUsed = (float) $lb->used_days;
        $oldRemaining = (float) $lb->remaining_days;
        $daysToApply = (float) $this->total_days;

        // Apply used days (ensure we don't go negative)
        $used = $oldUsed + $daysToApply;
        $remaining = $oldRemaining - $daysToApply;
        if ($remaining < 0) {
            $remaining = 0;
        }

        $lb->used_days = $used;
        $lb->remaining_days = $remaining;
        $lb->save();
        
        Log::info('Leave applied to balance', [
            'leave_id' => $this->id,
            'staff_id' => $this->staff_id,
            'leave_type_id' => $this->leave_type_id,
            'total_days' => $daysToApply,
            'old_used' => $oldUsed,
            'old_remaining' => $oldRemaining,
            'new_used' => $used,
            'new_remaining' => $remaining
        ]);
    }

    /**
     * Revert previously-applied leave from the staff's leave balance (decrement used_days, increment remaining_days).
     */
    public function revertFromBalance()
    {
        if (! $this->staff_id || ! $this->leave_type_id || ! $this->total_days) {
            return;
        }

        // Check if this leave type should deduct from balance
        $leaveType = $this->leaveType;
        if (!$leaveType || !$leaveType->deduct_from_balance) {
            Log::info('Leave revertFromBalance skipped - deduct_from_balance is false', [
                'leave_id' => $this->id,
                'leave_type_id' => $this->leave_type_id,
                'type_name' => $leaveType?->type_name ?? 'unknown'
            ]);
            return;
        }

        $lb = \App\Models\LeaveBalance::where('staff_id', $this->staff_id)
            ->where('leave_type_id', $this->leave_type_id)
            ->first();

        if (! $lb) {
            return;
        }

        $used = max(0, (float) $lb->used_days - (float) $this->total_days);
        $remaining = (float) $lb->remaining_days + (float) $this->total_days;

        $lb->used_days = $used;
        $lb->remaining_days = $remaining;
        $lb->save();
        Log::info('Leave reverted from balance', ['leave_id' => $this->id, 'staff_id' => $this->staff_id, 'leave_type_id' => $this->leave_type_id, 'days' => $this->total_days, 'used' => $lb->used_days, 'remaining' => $lb->remaining_days]);
    }

    /**
     * Relationship: Leave may cover multiple shifts (linked by leave_id)
     */
    public function shifts()
    {
        return $this->hasMany(\App\Models\Shift::class, 'leave_id');
    }

    /**
     * Link shifts that fall within the leave period for this staff to this leave record.
     */
    public function linkShifts()
    {
        if (!$this->staff_id || !$this->start_date || !$this->end_date) {
            return;
        }

        \App\Models\Shift::where('staff_id', $this->staff_id)
            ->whereDate('date', '>=', $this->start_date)
            ->whereDate('date', '<=', $this->end_date)
            ->update(['leave_id' => $this->id]);
    }

    /**
     * Unlink any shifts that were linked to this leave (set leave_id to null).
     */
    public function unlinkShifts()
    {
        \App\Models\Shift::where('leave_id', $this->id)->update(['leave_id' => null]);
    }

    /**
     * Get CSS badge class for leave type
     */
    public function getLeaveTypeBadgeClass(): string
    {
        $colors = [
            'annual' => 'bg-blue-100 text-blue-800',
            'medical' => 'bg-red-100 text-red-800',
            'hospitalization' => 'bg-pink-100 text-pink-800',
            'emergency' => 'bg-orange-100 text-orange-800',
            'marriage' => 'bg-purple-100 text-purple-800',
            'replacement' => 'bg-teal-100 text-teal-800',
            'unpaid' => 'bg-gray-100 text-gray-800',
        ];

        $typeKey = $this->leaveType?->type_name ?? ($this->leave_type ?? '');
        return $colors[$typeKey] ?? 'bg-gray-100 text-gray-800';
    }
}
