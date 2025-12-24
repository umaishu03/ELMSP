<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leaves', function (Blueprint $table) {

            if (!Schema::hasColumn('leaves', 'ot_records')) {
                $table->json('ot_records')->nullable();
            }

            if (!Schema::hasColumn('leaves', 'ot_hours')) {
                $table->float('ot_hours')->nullable();
            }

            if (!Schema::hasColumn('leaves', 'days_entitled')) {
                $table->integer('days_entitled')->nullable();
            }

            if (!Schema::hasColumn('leaves', 'requires_approval')) {
                $table->boolean('requires_approval')->default(false);
            }

            // ✅ Only add this if table exists
            if (Schema::hasTable('ot_replacements') &&
                !Schema::hasColumn('leaves', 'ot_replacement_id')) {

                $table->foreignId('ot_replacement_id')
                    ->nullable()
                    ->constrained('ot_replacements')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {

            if (Schema::hasColumn('leaves', 'ot_records')) {
                $table->dropColumn('ot_records');
            }

            if (Schema::hasColumn('leaves', 'ot_hours')) {
                $table->dropColumn('ot_hours');
            }

            if (Schema::hasColumn('leaves', 'days_entitled')) {
                $table->dropColumn('days_entitled');
            }

            if (Schema::hasColumn('leaves', 'requires_approval')) {
                $table->dropColumn('requires_approval');
            }

            if (Schema::hasColumn('leaves', 'ot_replacement_id')) {
                $table->dropConstrainedForeignId('ot_replacement_id');
            }
        });
    }
};
