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
        // Make day_of_week nullable using raw SQL (doctrine/dbal not required)
        DB::statement('ALTER TABLE `shifts` MODIFY COLUMN `day_of_week` VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert day_of_week to NOT NULL
        DB::statement('ALTER TABLE `shifts` MODIFY COLUMN `day_of_week` VARCHAR(255) NOT NULL');
    }
};
