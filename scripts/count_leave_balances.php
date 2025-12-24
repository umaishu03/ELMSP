<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\LeaveBalance;

$count = LeaveBalance::count();
echo "leave_balances count: $count\n\n";

$sample = LeaveBalance::with(['staff','leaveType'])->limit(10)->get();
if ($sample->isEmpty()) {
    echo "No sample rows.\n";
    exit(0);
}
foreach ($sample as $r) {
    echo sprintf("staff_id=%d, leave_type=%s, total=%.2f, used=%.2f, remaining=%.2f\n",
        $r->staff_id,
        $r->leaveType?->type_name ?? 'unknown',
        $r->total_days,
        $r->used_days,
        $r->remaining_days
    );
}
