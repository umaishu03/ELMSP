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
        Schema::table('payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('payrolls', 'extra_day_pay')) {
                $table->decimal('extra_day_pay', 10, 2)->default(0)->after('marketing_bonus')->comment('Extra day pay for working more than required days');
            }
            if (!Schema::hasColumn('payrolls', 'extra_days')) {
                $table->unsignedTinyInteger('extra_days')->default(0)->after('extra_day_pay')->comment('Number of extra days worked beyond required');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (Schema::hasColumn('payrolls', 'extra_days')) {
                $table->dropColumn('extra_days');
            }
            if (Schema::hasColumn('payrolls', 'extra_day_pay')) {
                $table->dropColumn('extra_day_pay');
            }
        });
    }
};
