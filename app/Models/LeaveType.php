<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveType extends Model
{
    use HasFactory;

    protected $table = 'leave_types';

    protected $fillable = [
        'type_name',
        'description',
        'requires_approval',
        'deduct_from_balance',
        'max_days',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'deduct_from_balance' => 'boolean',
    ];

    public function balances()
    {
        return $this->hasMany(\App\Models\LeaveBalance::class, 'leave_type_id');
    }
}
