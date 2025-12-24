<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'staff_id',
        'date',
        // 'day_of_week' removed to match DB schema variations
        'start_time',
        'end_time',
        'break_minutes',
        'rest_day',
        'leave_id',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    /**
     * Relationship to Leave (nullable)
     */
    public function leave()
    {
        return $this->belongsTo(Leave::class, 'leave_id');
    }

    /**
     * Convenience accessor to get the related user through staff.
     * Use carefully; eager-load `staff.user` in controllers for performance.
     */
    public function getUserAttribute()
    {
        return $this->staff ? $this->staff->user : null;
    }
}
