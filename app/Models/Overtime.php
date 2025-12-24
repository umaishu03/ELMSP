<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Overtime extends Model
{
    use HasFactory;

    protected $table = 'overtimes';

    protected $fillable = [
        'staff_id',
        'ot_type',
        'ot_date',
        'hours',
        'status',
        'remarks',
        'claimed',
    ];

    protected $casts = [
        'ot_date' => 'date',
        'hours' => 'float',
        'claimed' => 'boolean',
    ];

    /**
     * Get the user that owns this overtime record
     */
    public function user()
    {
        // Backwards compatibility via accessor `getUserAttribute()`
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the staff details through user
     */
    public function staff()
    {
        return $this->belongsTo(\App\Models\Staff::class, 'staff_id');
    }

    /**
     * Backwards-compatible accessor to get the related User via staff
     */
    public function getUserAttribute()
    {
        return $this->staff?->user;
    }

    /**
     * Scope: Get pending overtimes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Get approved overtimes
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Get rate for this overtime based on type
     * - fulltime: RM 12.26/hr
     * - public_holiday: RM 21.68/hr (OT on public holiday)
     * - public_holiday_work: RM 15.38/hr (regular work on public holiday)
     */
    public function getRate()
    {
        return match($this->ot_type) {
            'public_holiday' => 21.68,
            'public_holiday_work' => 15.38,
            'fulltime' => 12.26,
            default => 12.26,
        };
    }

    /**
     * Calculate pay for this OT
     */
    public function calculatePay()
    {
        return $this->hours * $this->getRate();
    }

    /**
     * Boot method - set default status to pending
     */
    protected static function booted()
    {
        static::creating(function ($overtime) {
            // All OT applications start as pending, require admin approval
            $overtime->status = 'pending';
        });
    }

    /**
     * Validate OT against department limits (for admin review)
     * Returns array with validation status and messages
     */
    public static function validateOT($overtime)
    {
        $staff = $overtime->staff;
        if (!$staff) {
            return [
                'valid' => false,
                'message' => 'Staff record not found'
            ];
        }

        $department = $staff->department;
        
        // Get current OT applications for this day by department
        $otCountThisDay = self::whereHas('staff', function ($query) use ($department) {
            $query->where('department', $department);
        })
        ->where('ot_date', $overtime->ot_date)
        ->where('status', 'approved')
        ->count();

        // Department limits for OT
        $limits = [
            'cashier' => 2,
            'barista' => 2,
            'joki' => 2,
            'waiter' => 5,
            'kitchen' => 4,
            'manager' => 1,
            'supervisor' => 1,
        ];

        $max = $limits[$department] ?? 0;

        // Check if department limit not exceeded
        if ($otCountThisDay >= $max) {
            return [
                'valid' => false,
                'message' => "Department {$department} already has {$max} OT approvals for this date"
            ];
        }

        // Check hours limit per person
        $maxHours = in_array($department, ['manager', 'supervisor']) ? 2 : 4.5;
        if ($overtime->hours > $maxHours) {
            return [
                'valid' => false,
                'message' => "Maximum OT hours for {$department} is {$maxHours} hours/day"
            ];
        }

        return [
            'valid' => true,
            'message' => 'OT request meets all department constraints'
        ];
    }
}
