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
}