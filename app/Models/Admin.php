<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $table = 'admin';

    protected $fillable = [
        'user_id',
        'employee_id',
        'department',
        'admin_level',
        'permissions',
        'appointment_date',
        'status',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'permissions' => 'array',
    ];

    /**
     * Get the user that owns the admin record
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department name
     */
    public function getDepartmentNameAttribute()
    {
        return ucfirst($this->department);
    }

    /**
     * Check if admin is active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if admin has specific permission
     */
    public function hasPermission($permission)
    {
        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Get admin level display name
     */
    public function getAdminLevelDisplayAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->admin_level));
    }
}