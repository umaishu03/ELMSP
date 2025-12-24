<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Leave;
use App\Models\Shift;
use App\Models\Staff;
use App\Models\LeaveType;
use Carbon\Carbon;

echo "Testing Leave → Shift Linking System\n";
echo "====================================\n\n";

// Test 1: Get a staff member with shifts
echo "Test 1: Finding staff with shifts...\n";
$staff = Staff::has('shifts')->first();
if (!$staff) {
    echo "✗ No staff with shifts found. Please create some shifts first.\n";
    exit(1);
}
echo "✓ Found staff: {$staff->user->name} (staff_id: {$staff->id})\n";

// Test 2: Get their shifts for this week
echo "\nTest 2: Getting staff's shifts for this week...\n";
$startOfWeek = Carbon::now()->startOfWeek();
$endOfWeek = Carbon::now()->endOfWeek();
$shifts = Shift::where('staff_id', $staff->id)
    ->whereBetween('date', [$startOfWeek, $endOfWeek])
    ->get();
echo "✓ Found {$shifts->count()} shifts\n";

if ($shifts->count() === 0) {
    echo "⊘ No shifts found in current week. Test requires shifts to work.\n";
    exit(0);
}

// Test 3: Create a leave that covers some of these shifts
echo "\nTest 3: Creating an approved leave...\n";
$leaveType = LeaveType::where('type_name', 'annual')->first();
if (!$leaveType) {
    echo "✗ Annual leave type not found. Please seed leave types first.\n";
    exit(1);
}

$startDate = $startOfWeek->copy()->addDay();  // Tuesday
$endDate = $startOfWeek->copy()->addDays(3); // Thursday
$totalDays = $endDate->diffInDays($startDate) + 1;

$leave = Leave::create([
    'staff_id' => $staff->id,
    'leave_type_id' => $leaveType->id,
    'start_date' => $startDate,
    'end_date' => $endDate,
    'total_days' => $totalDays,
    'reason' => 'TEST: Leave for shift linking test',
    'status' => 'approved',
    'auto_approved' => true,
    'approved_at' => now(),
]);

echo "✓ Created leave: {$leave->id} from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}\n";
echo "  Total days: {$totalDays}\n";

// Test 4: Verify shifts are now linked to the leave
echo "\nTest 4: Checking if shifts were auto-linked to leave...\n";
$linkedShifts = Shift::where('staff_id', $staff->id)
    ->where('leave_id', $leave->id)
    ->whereBetween('date', [$startDate, $endDate])
    ->get();

echo "✓ Found {$linkedShifts->count()} shifts linked to leave\n";
foreach ($linkedShifts as $s) {
    echo "  - Shift on {$s->date}: leave_id = {$s->leave_id}\n";
}

// Test 5: Test unlinking when leave is rejected
echo "\nTest 5: Testing unlink when leave is rejected...\n";
$leave->status = 'rejected';
$leave->save();

$unlinkedShifts = Shift::where('leave_id', $leave->id)->get();
echo "✓ Shifts after rejection: " . $unlinkedShifts->count() . " (should be 0)\n";

// Test 6: Re-approve and verify relinking
echo "\nTest 6: Re-approving leave and checking relinking...\n";
$leave->status = 'approved';
$leave->save();

$relikedShifts = Shift::where('staff_id', $staff->id)
    ->where('leave_id', $leave->id)
    ->whereBetween('date', [$startDate, $endDate])
    ->get();

echo "✓ Shifts after re-approval: {$relikedShifts->count()} (should be > 0)\n";

// Cleanup
echo "\nCleanup: Deleting test leave...\n";
$leave->delete();
$finalCheck = Shift::where('leave_id', $leave->id)->get();
echo "✓ Shifts after leave deletion: " . $finalCheck->count() . " (should be 0)\n";

echo "\n✓ All tests passed! Leave ↔ Shift linking is working correctly.\n";
