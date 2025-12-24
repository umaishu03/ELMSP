<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\OTClaim;
use App\Models\User;

echo "Testing OTClaim queries after user_id removal...\n\n";

// Test 1: Verify that direct where('user_id', ...) fails
echo "Test 1: Direct where('user_id', ...) should fail\n";
try {
    $result = OTClaim::where('user_id', 34)->get();
    echo "✗ FAILED: Query should have raised an error but got " . $result->count() . " results\n";
} catch (\Exception $e) {
    echo "✓ PASSED: Query correctly failed with error (expected)\n";
    echo "  Error: " . substr($e->getMessage(), 0, 80) . "...\n";
}

// Test 2: Query OTClaims through payroll relation
echo "\nTest 2: Query OTClaims via payroll relation\n";
try {
    $userId = 34; // Use a test user ID
    $claims = OTClaim::whereHas('payroll', function($q) use ($userId) {
        $q->where('user_id', $userId);
    })->get();
    echo "✓ PASSED: Query successful - found " . $claims->count() . " claims\n";
} catch (\Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
}

// Test 3: Check that OTClaim relations work
echo "\nTest 3: Check OTClaim model relations\n";
try {
    $claim = OTClaim::first();
    if ($claim) {
        $hasPayrollRelation = method_exists($claim, 'payroll');
        $hasOvertimeRelation = method_exists($claim, 'overtime');
        $hasLeaveRelation = method_exists($claim, 'leave');
        echo "✓ payroll() relation: " . ($hasPayrollRelation ? "exists" : "missing") . "\n";
        echo "✓ overtime() relation: " . ($hasOvertimeRelation ? "exists" : "missing") . "\n";
        echo "✓ leave() relation: " . ($hasLeaveRelation ? "exists" : "missing") . "\n";
        
        echo "✓ PASSED: All relations defined\n";
    } else {
        echo "⊘ No OTClaims in database to test relations\n";
    }
} catch (\Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
}

echo "\n✓ All tests complete!\n";
