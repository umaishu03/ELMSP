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
        // Fix any NULL or empty status values
        DB::table('ot_claims')
            ->whereNull('status')
            ->orWhere('status', '')
            ->update(['status' => 'pending']);

        // Ensure status column cannot be NULL at database level
        // Note: MySQL enum columns can have NULL, so we need to modify the column
        DB::statement("ALTER TABLE `ot_claims` MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to nullable (if needed)
        DB::statement("ALTER TABLE `ot_claims` MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected') NULL DEFAULT 'pending'");
    }
};
