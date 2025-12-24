<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\LeaveType;
$types = LeaveType::orderBy('id')->get();
if ($types->isEmpty()) {
    echo "No leave_types found.\n";
    exit(0);
}
foreach ($types as $t) {
    echo sprintf("%d %s (max_days=%s)\n", $t->id, $t->type_name, ($t->max_days === null ? 'null' : $t->max_days));
}
