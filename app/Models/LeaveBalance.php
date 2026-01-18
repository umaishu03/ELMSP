<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $table = 'leave_balances';

    protected $fillable = [
        'staff_id',
        'leave_type_id',
        'total_days',
        'used_days',
        'remaining_days',
    ];

    protected $casts = [
        'total_days' => 'float',
        'used_days' => 'float',
        'remaining_days' => 'float',
    ];

    public function staff()
    {
        return $this->belongsTo(\App\Models\Staff::class, 'staff_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(\App\Models\LeaveType::class, 'leave_type_id');
    }
}
