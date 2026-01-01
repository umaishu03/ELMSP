<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update unpaid leave max_days to 10
        DB::table('leave_types')
            ->where('type_name', 'unpaid')
            ->update(['max_days' => 10]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to null (unlimited) if needed
        DB::table('leave_types')
            ->where('type_name', 'unpaid')
            ->update(['max_days' => null]);
    }
};
