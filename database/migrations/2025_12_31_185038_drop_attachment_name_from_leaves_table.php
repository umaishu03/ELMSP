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
            // Drop attachment_name column if it exists (not used, filename is derived from attachment path)
            if (Schema::hasColumn('leaves', 'attachment_name')) {
                $table->dropColumn('attachment_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            // Re-add attachment_name column if needed to rollback
            if (!Schema::hasColumn('leaves', 'attachment_name')) {
                $table->string('attachment_name')->nullable()->after('attachment');
            }
        });
    }
};
