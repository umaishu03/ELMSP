<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Staff;
use App\Models\LeaveType;
use App\Models\Leave;

$staff = Staff::first();
$type = LeaveType::first();
if (! $staff || ! $type) {
    echo "Missing staff or leave type\n";
    exit(1);
}

$today = date('Y-m-d');
$leave = Leave::create([
    'staff_id' => $staff->id,
    'leave_type_id' => $type->id,
    'start_date' => $today,
    'end_date' => $today,
    'total_days' => 1,
    'reason' => 'Test leave',
    'attachment' => null,
]);

echo "Created leave id={$leave->id} status={$leave->status}\n";

// show leave balance row
$lb = \App\Models\LeaveBalance::where('staff_id', $staff->id)->where('leave_type_id', $type->id)->first();
if ($lb) {
    echo "LeaveBalance: total={$lb->total_days} used={$lb->used_days} remaining={$lb->remaining_days}\n";
} else {
    echo "No LeaveBalance row found for staff {$staff->id} type {$type->id}\n";
}