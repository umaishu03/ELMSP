<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LeaveTypesSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['type_name' => 'annual', 'description' => 'Annual leave', 'requires_approval' => 0, 'deduct_from_balance' => 1, 'max_days' => 14],
            ['type_name' => 'hospitalization', 'description' => 'Hospitalization leave', 'requires_approval' => 0, 'deduct_from_balance' => 1, 'max_days' => 30],
            ['type_name' => 'medical', 'description' => 'Medical leave', 'requires_approval' => 0, 'deduct_from_balance' => 1, 'max_days' => 14],
            ['type_name' => 'emergency', 'description' => 'Emergency leave', 'requires_approval' => 0, 'deduct_from_balance' => 1, 'max_days' => 7],
            ['type_name' => 'replacement', 'description' => 'Replacement leave from OT', 'requires_approval' => 1, 'deduct_from_balance' => 0, 'max_days' => null],
            ['type_name' => 'marriage', 'description' => 'Marriage leave', 'requires_approval' => 0, 'deduct_from_balance' => 1, 'max_days' => 6],
            ['type_name' => 'unpaid', 'description' => 'Unpaid leave', 'requires_approval' => 0, 'deduct_from_balance' => 0, 'max_days' => 10],
        ];

        foreach ($defaults as $d) {
            \App\Models\LeaveType::updateOrCreate(
                ['type_name' => $d['type_name']],
                [
                    'description' => $d['description'],
                    'requires_approval' => $d['requires_approval'],
                    'deduct_from_balance' => $d['deduct_from_balance'],
                    'max_days' => $d['max_days'],
                ]
            );
        }
    }
}
