<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ot_claims', function (Blueprint $table) {
            if (!Schema::hasColumn('ot_claims', 'ot_ids')) {
                $table->json('ot_ids')->nullable()->after('claim_type')->comment('List of overtime ids included in this claim');
            }
        });

        Schema::table('overtimes', function (Blueprint $table) {
            if (!Schema::hasColumn('overtimes', 'claimed')) {
                $table->boolean('claimed')->default(false)->after('remarks')->comment('Whether this OT record has been claimed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ot_claims', function (Blueprint $table) {
            if (Schema::hasColumn('ot_claims', 'ot_ids')) {
                $table->dropColumn('ot_ids');
            }
        });

        Schema::table('overtimes', function (Blueprint $table) {
            if (Schema::hasColumn('overtimes', 'claimed')) {
                $table->dropColumn('claimed');
            }
        });
    }
};
