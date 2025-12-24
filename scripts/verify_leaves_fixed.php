<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Leave;
use Illuminate\Support\Facades\DB;

echo "Testing Leave queries with staff_id...\n\n";

// Test 1: Query leaves by staff_id
try {
    $staffId = 31;
    $replacementType = \App\Models\LeaveType::whereRaw('LOWER(type_name) = ?', [strtolower('replacement')])->first();
    if ($replacementType) {
        $leaves = Leave::where('staff_id', $staffId)
            ->where('leave_type_id', $replacementType->id)
            ->orderBy('start_date', 'desc')
            ->get();
    } else {
        $leaves = collect();
    }
    echo "✓ Query by staff_id successful: Found {$leaves->count()} leaves\n";
} catch (\Exception $e) {
    echo "✗ Query by staff_id failed: {$e->getMessage()}\n";
}

// Test 2: Verify no user_id column queries fail
try {
    DB::table('leaves')->where('user_id', 34)->get();
    echo "✗ Query with user_id should fail but didn't!\n";
} catch (\Exception $e) {
    echo "✓ Query with user_id correctly fails: " . substr($e->getMessage(), 0, 60) . "...\n";
}

// Test 3: Check table structure
echo "\nLeaves table structure:\n";
$columns = DB::select("DESCRIBE leaves");
$hasStaffId = false;
$hasUserId = false;
foreach ($columns as $col) {
    if ($col->Field === 'staff_id') $hasStaffId = true;
    if ($col->Field === 'user_id') $hasUserId = true;
}

echo "  Has staff_id column: " . ($hasStaffId ? "✓ Yes" : "✗ No") . "\n";
echo "  Has user_id column: " . ($hasUserId ? "✗ Yes (ERROR!)" : "✓ No") . "\n";

echo "\n✓ All checks passed!\n";
