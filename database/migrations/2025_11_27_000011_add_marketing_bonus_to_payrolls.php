<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('payrolls', 'marketing_bonus')) {
                $table->decimal('marketing_bonus', 10, 2)->default(0)->after('fixed_commission');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (Schema::hasColumn('payrolls', 'marketing_bonus')) {
                $table->dropColumn('marketing_bonus');
            }
        });
    }
};
