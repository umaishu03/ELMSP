<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            // Add nullable overtime_id column that links to overtimes table
            $table->foreignId('overtime_id')
                  ->nullable()
                  ->after('leave_id')
                  ->constrained('overtimes')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['overtime_id']);
            // Drop the column
            $table->dropColumn('overtime_id');
        });
    }
};
