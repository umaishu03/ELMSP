<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create staff user
        User::create([
            'name' => 'John Staff',
            'email' => 'staff@gmail.com',
            'password' => Hash::make('123456789'),
            'role' => 'staff',
            'employee_id' => 'EMP001',
        ]);

        $this->command->info('Staff user created successfully!');
        $this->command->info('Email: staff@gmail.com');
        $this->command->info('Password: 123456789');
        $this->command->info('Employee ID: EMP001');
    }
}
