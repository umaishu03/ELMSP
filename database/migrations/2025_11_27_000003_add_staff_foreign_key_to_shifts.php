<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clean up any shifts that reference non-existent staff records
        // This migration is primarily for data cleanup.
        // The foreign key constraint is already added by migration 2025_11_27_000002
        DB::statement('
            DELETE FROM shifts 
            WHERE staff_id NOT IN (SELECT id FROM staff)
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration only cleans up data, so there's nothing to reverse
        // The foreign key constraint is managed by migration 2025_11_27_000002
    }
};
