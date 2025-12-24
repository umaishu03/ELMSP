<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            if (Schema::hasColumn('leaves', 'department')) {
                $table->dropColumn('department');
            }
        });

        Schema::table('shifts', function (Blueprint $table) {
            if (Schema::hasColumn('shifts', 'department')) {
                $table->dropColumn('department');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            if (!Schema::hasColumn('leaves', 'department')) {
                $table->string('department')->nullable()->after('staff_id');
            }
        });

        Schema::table('shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('shifts', 'department')) {
                $table->string('department')->nullable()->after('staff_id');
            }
        });
    }
};
