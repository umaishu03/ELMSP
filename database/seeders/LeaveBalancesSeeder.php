<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LeaveBalancesSeeder extends Seeder
{
    public function run(): void
    {
        $leaveTypes = \App\Models\LeaveType::all();
        $staffCursor = \App\Models\Staff::cursor();

        foreach ($staffCursor as $staff) {
            foreach ($leaveTypes as $lt) {
                // compute used days (approved leaves)
                $used = (float) \App\Models\Leave::where('staff_id', $staff->id)
                    ->where('leave_type_id', $lt->id)
                    ->where('status', 'approved')
                    ->sum('total_days');

                // determine total entitlement
                $total = $lt->max_days ?? (\App\Models\Leave::$maxLeaves[$lt->type_name] ?? 0);
                $total = is_null($total) ? 0 : (float)$total;

                $remaining = $total - $used;
                if ($remaining < 0) $remaining = 0;

                \App\Models\LeaveBalance::updateOrCreate(
                    ['staff_id' => $staff->id, 'leave_type_id' => $lt->id],
                    ['total_days' => $total, 'used_days' => $used, 'remaining_days' => $remaining]
                );
            }
        }
    }
}
