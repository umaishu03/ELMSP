<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_id',
        'department',
        'hire_date',
        'salary',
        'status',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'salary' => 'decimal:2',
    ];

    // Department employee limits (total staff + admin per department)
    public static $departmentLimits = [
        'manager' => 1,
        'supervisor' => 2,
        'cashier' => 5,
        'barista' => 4,
        'joki' => 4,
        'waiter' => 11,
        'kitchen' => 8,
    ];

    /**
     * Get the user that owns the staff record
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all shifts for this staff member
     */
    public function shifts()
    {
        return $this->hasMany(Shift::class, 'staff_id');
    }

    /**
     * Get the department name
     */
    public function getDepartmentNameAttribute()
    {
        return ucfirst($this->department);
    }

    /**
     * Check if staff is active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Get all leaves for this staff member
     */
    public function leaves()
    {
        return $this->user->leaves();
    }

    /**
     * Get all overtimes for this staff member
     */
    public function overtimes()
    {
        return $this->user->overtimes();
    }

    /**
     * Get all OT claims for this staff member
     */
    public function otClaims()
    {
        return $this->user->otClaims();
    }

    /**
     * Get all payroll records for this staff member
     */
    public function payrolls()
    {
        return $this->user->payrolls();
    }

    /**
     * Check if department has reached its employee limit
     * Counts Staff records in the department
     * 
     * @param string $department
     * @param int|null $excludeStaffId Staff ID to exclude from count (for updates)
     * @param int|null $excludeAdminId (Deprecated - admin table removed, kept for backward compatibility)
     * @return array ['reached' => bool, 'current' => int, 'limit' => int]
     */
    public static function checkDepartmentLimit($department, $excludeStaffId = null, $excludeAdminId = null)
    {
        $limit = self::$departmentLimits[$department] ?? null;
        
        if ($limit === null) {
            return [
                'reached' => false,
                'current' => 0,
                'limit' => null,
                'message' => 'No limit set for this department'
            ];
        }

        // Count active staff in this department
        $staffQuery = self::where('department', $department)
            ->where('status', 'active');
        if ($excludeStaffId) {
            $staffQuery->where('id', '!=', $excludeStaffId);
        }
        $staffCount = $staffQuery->count();

        // Admin table removed - only count staff records
        // Admin users are identified by users.role = 'admin' but don't have separate records
        $currentCount = $staffCount;

        $departmentName = ucfirst($department);
        return [
            'reached' => $currentCount >= $limit,
            'current' => $currentCount,
            'limit' => $limit,
            'message' => "{$departmentName} limit: {$limit} person" . ($limit > 1 ? 's' : '') . " only. Current: {$currentCount} person" . ($currentCount > 1 ? 's' : '') . " already registered."
        ];
    }
}