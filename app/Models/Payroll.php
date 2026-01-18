<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'payrolls';

    protected $fillable = [
        'user_id',
        'year',
        'month',
        'basic_salary',
        'fixed_commission',
        'marketing_bonus',
        'public_holiday_hours',
        'public_holiday_pay',
        'fulltime_ot_hours',
        'fulltime_ot_pay',
        'public_holiday_ot_hours',
        'public_holiday_ot_pay',
        'gross_salary',
        'total_deductions',
        'net_salary',
        'status',
        'payment_date',
        'remarks',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'basic_salary' => 'float',
        'fixed_commission' => 'float',
        'marketing_bonus' => 'float',
        'public_holiday_hours' => 'float',
        'public_holiday_pay' => 'float',
        'fulltime_ot_hours' => 'float',
        'fulltime_ot_pay' => 'float',
        'public_holiday_ot_hours' => 'float',
        'public_holiday_ot_pay' => 'float',
        'gross_salary' => 'float',
        'total_deductions' => 'float',
        'net_salary' => 'float',
        'payment_date' => 'date',
    ];

    /**
     * Get the user that owns this payroll
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the staff details through user
     */
    public function staff()
    {
        return $this->user->staff();
    }

    /**
     * Scope: Get approved payrolls
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: Get paid payrolls
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Calculate gross salary
     * = basic_salary + fixed_commission + marketing_bonus + public_holiday_pay + fulltime_ot_pay + public_holiday_ot_pay
     */
    public function calculateGrossSalary()
    {
        return $this->basic_salary + $this->fixed_commission + ($this->marketing_bonus ?? 0)
            + $this->public_holiday_pay + $this->fulltime_ot_pay + $this->public_holiday_ot_pay;
    }

    /**
     * Calculate net salary
     * = gross_salary - total_deductions
     */
    public function calculateNetSalary()
    {
        return $this->gross_salary - $this->total_deductions;
    }

    /**
     * Generate payroll for a specific user and month
     */
    public static function generatePayroll($userId, $year, $month, $otClaimsForPayroll = [])
    {
        $user = User::find($userId);
        if (!$user || !$user->staff) {
            return null;
        }

        $staff = $user->staff;
        $basicSalary = $staff->salary;

        // Check if eligible for fixed commission (after 3 months)
        $hireDate = $staff->hire_date;
        $currentDate = now();
        $monthsSinceHire = $hireDate->diffInMonths($currentDate);
        $fixedCommission = $monthsSinceHire >= 3 ? 200 : 0;

        // Initialize values from OT claims for payroll
        $fulltime_ot_hours = 0;
        $fulltime_ot_pay = 0;
        $public_holiday_ot_hours = 0;
        $public_holiday_ot_pay = 0;
        $public_holiday_hours = 0;
        $public_holiday_pay = 0;

        // Sum up from approved OT claims for payroll
        foreach ($otClaimsForPayroll as $claim) {
            $fulltime_ot_hours += $claim->fulltime_hours ?? 0;
            $public_holiday_ot_hours += $claim->public_holiday_hours ?? 0;
        }

        // Calculate pays
        $fulltime_ot_pay = $fulltime_ot_hours * 12.26;
        $public_holiday_ot_pay = $public_holiday_ot_hours * 21.68;
        
        // Public holiday pay (RM15.38 per hour)
        // This needs to be tracked separately - for now, set to 0
        $public_holiday_pay = 0;

        // Calculate gross
        $grossSalary = $basicSalary + $fixedCommission + $public_holiday_pay 
            + $fulltime_ot_pay + $public_holiday_ot_pay;

        // Create or update payroll
        $payroll = self::updateOrCreate(
            ['user_id' => $userId, 'year' => $year, 'month' => $month],
            [
                'basic_salary' => $basicSalary,
                'fixed_commission' => $fixedCommission,
                'public_holiday_hours' => $public_holiday_hours,
                'public_holiday_pay' => $public_holiday_pay,
                'fulltime_ot_hours' => $fulltime_ot_hours,
                'fulltime_ot_pay' => $fulltime_ot_pay,
                'public_holiday_ot_hours' => $public_holiday_ot_hours,
                'public_holiday_ot_pay' => $public_holiday_ot_pay,
                'gross_salary' => $grossSalary,
                'net_salary' => $grossSalary - 0, // assuming no deductions for now
            ]
        );

        return $payroll;
    }
}
