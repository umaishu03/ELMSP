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
        // Only try to drop the column if it exists
        if (Schema::hasColumn('staff', 'position')) {
            Schema::table('staff', function (Blueprint $table) {
                $table->dropColumn('position');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->string('position')->nullable();
        });
    }
};
