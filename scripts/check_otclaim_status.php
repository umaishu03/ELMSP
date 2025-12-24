<?php
// Simple script to check OTClaim status values
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->boot();

use App\Models\OTClaim;

$claims = OTClaim::limit(10)->get();

echo "OTClaim Status Check:\n";
echo str_repeat("=", 80) . "\n";

foreach ($claims as $claim) {
    $status = $claim->status;
    $statusType = is_null($status) ? 'NULL' : (empty($status) ? 'EMPTY_STRING' : $status);
    echo sprintf("ID: %d | Status: [%s] | User: %d | Type: %s | Created: %s\n",
        $claim->id,
        $statusType,
        $claim->user_id,
        $claim->claim_type,
        $claim->created_at
    );
}

echo str_repeat("=", 80) . "\n";
echo "Total OTClaims: " . OTClaim::count() . "\n";
echo "Pending: " . OTClaim::where('status', 'pending')->count() . "\n";
echo "Approved: " . OTClaim::where('status', 'approved')->count() . "\n";
echo "Rejected: " . OTClaim::where('status', 'rejected')->count() . "\n";
echo "NULL status: " . OTClaim::whereNull('status')->count() . "\n";
