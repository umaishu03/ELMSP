<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OTClaim extends Model
{
    use HasFactory;

    protected $table = 'ot_claims';

    protected $fillable = [
        'claim_type',
        'ot_ids',
        'fulltime_hours',
        'public_holiday_hours',
        'replacement_days',
        'status',
        'remarks',
    ];

    protected $casts = [
        'ot_ids' => 'array',
        'fulltime_hours' => 'float',
        'public_holiday_hours' => 'float',
        'replacement_days' => 'float',
    ];

    /**
     * Get the user that owns this claim
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Get pending claims
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Get approved claims
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: Get replacement leave claims
     */
    public function scopeReplacementLeave($query)
    {
        return $query->where('claim_type', 'replacement_leave');
    }

    /**
     * Scope: Get payroll claims
     */
    public function scopePayroll($query)
    {
        return $query->where('claim_type', 'payroll');
    }

    /**
     * Calculate replacement days (total hours / 8)
     */
    public function calculateReplacementDays()
    {
        $totalHours = ($this->fulltime_hours ?? 0) + ($this->public_holiday_hours ?? 0);
        return floor($totalHours / 8);
    }

    /**
     * Calculate payroll amounts for payroll claim
     */
    public function calculatePayrollAmounts()
    {
        if ($this->claim_type !== 'payroll') {
            return null;
        }

        $fulltime_pay = ($this->fulltime_hours ?? 0) * 12.26;
        $public_holiday_pay = ($this->public_holiday_hours ?? 0) * 21.68;

        return [
            'fulltime_hours' => $this->fulltime_hours ?? 0,
            'fulltime_pay' => $fulltime_pay,
            'public_holiday_hours' => $this->public_holiday_hours ?? 0,
            'public_holiday_pay' => $public_holiday_pay,
            'total_hours' => ($this->fulltime_hours ?? 0) + ($this->public_holiday_hours ?? 0),
            'total_pay' => $fulltime_pay + $public_holiday_pay,
        ];
    }
}
