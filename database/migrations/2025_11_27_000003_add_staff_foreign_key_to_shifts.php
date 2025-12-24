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
        // First, delete any shifts that reference non-existent staff records
        DB::statement('
            DELETE FROM shifts 
            WHERE staff_id NOT IN (SELECT id FROM staff)
        ');

        Schema::table('shifts', function (Blueprint $table) {
            // Add the foreign key constraint to staff table
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
        });
    }
};
