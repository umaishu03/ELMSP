<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking ot_claims table structure...\n\n";

$columns = DB::select('DESCRIBE ot_claims');
echo "Columns in ot_claims:\n";
foreach ($columns as $col) {
    echo "  - " . $col->Field . " (" . $col->Type . ")" . ($col->Null === 'NO' ? " NOT NULL" : " NULL") . "\n";
}

echo "\nChecking for user_id column: ";
$hasUserId = false;
$hasOvertimeId = false;
$hasLeaveId = false;
$hasPayrollId = false;

foreach ($columns as $col) {
    if ($col->Field === 'user_id') $hasUserId = true;
    if ($col->Field === 'overtime_id') $hasOvertimeId = true;
    if ($col->Field === 'leave_id') $hasLeaveId = true;
    if ($col->Field === 'payroll_id') $hasPayrollId = true;
}

echo ($hasUserId ? "✗ FOUND (ERROR)" : "✓ NOT FOUND") . "\n";
echo "Has overtime_id: " . ($hasOvertimeId ? "✓ YES" : "✗ NO") . "\n";
echo "Has leave_id: " . ($hasLeaveId ? "✓ YES" : "✗ NO") . "\n";
echo "Has payroll_id: " . ($hasPayrollId ? "✓ YES" : "✗ NO") . "\n";

echo "\n✓ Schema check complete!\n";
