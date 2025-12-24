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
        Schema::table('leaves', function (Blueprint $table) {
            $columnsToDelete = [];
            
            if (Schema::hasColumn('leaves', 'ot_hours')) {
                $columnsToDelete[] = 'ot_hours';
            }
            if (Schema::hasColumn('leaves', 'days_entitled')) {
                $columnsToDelete[] = 'days_entitled';
            }
            if (Schema::hasColumn('leaves', 'max_days')) {
                $columnsToDelete[] = 'max_days';
            }
            if (Schema::hasColumn('leaves', 'requires_approval')) {
                $columnsToDelete[] = 'requires_approval';
            }
            if (Schema::hasColumn('leaves', 'ot_records')) {
                $columnsToDelete[] = 'ot_records';
            }
            
            if (!empty($columnsToDelete)) {
                $table->dropColumn($columnsToDelete);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->decimal('ot_hours', 5, 2)->nullable()->after('total_days');
            $table->integer('days_entitled')->nullable()->after('ot_hours');
            $table->integer('max_days')->nullable()->after('days_entitled');
            $table->boolean('requires_approval')->default(false)->after('max_days');
            $table->json('ot_records')->nullable()->after('requires_approval');
        });
    }
};
