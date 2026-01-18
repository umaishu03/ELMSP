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
        Schema::table('ot_claims', function (Blueprint $table) {
            // Drop foreign key constraint first if it exists
            if (Schema::hasColumn('ot_claims', 'payroll_id')) {
                try {
                    $table->dropForeign(['payroll_id']);
                } catch (\Throwable $e) {
                    // Ignore if foreign key doesn't exist
                }
                // Drop the column
                $table->dropColumn('payroll_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ot_claims', function (Blueprint $table) {
            // Re-add the column
            $table->unsignedBigInteger('payroll_id')->nullable()->after('id');
            // Re-add foreign key constraint
            $table->foreign('payroll_id')->references('id')->on('payrolls')->onDelete('set null');
        });
    }
};
