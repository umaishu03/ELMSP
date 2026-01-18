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
            if (Schema::hasColumn('ot_claims', 'leave_id')) {
                try {
                    $table->dropForeign(['leave_id']);
                } catch (\Throwable $e) {
                    // Ignore if foreign key doesn't exist
                }
                // Drop the column
                $table->dropColumn('leave_id');
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
            $table->unsignedBigInteger('leave_id')->nullable()->after('id');
            // Re-add foreign key constraint
            $table->foreign('leave_id')->references('id')->on('leaves')->onDelete('set null');
        });
    }
};
