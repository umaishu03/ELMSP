<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456789'),
            'role' => 'admin',
        ]);

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@gmail.com');
        $this->command->info('Password: 123456789');
    }
}
