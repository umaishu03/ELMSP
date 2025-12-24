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
        // Check if shifts table has user_id column (hasn't been migrated yet)
        if (Schema::hasColumn('shifts', 'user_id')) {
            Schema::table('shifts', function (Blueprint $table) {
                // Drop the old foreign key constraint
                $table->dropForeign(['user_id']);
            });

            // Update shifts table: map user_id values to corresponding staff.id
            // Only update if there are any shifts
            $shiftsCount = DB::table('shifts')->count();
            if ($shiftsCount > 0) {
                DB::statement('UPDATE shifts s 
                    INNER JOIN staff st ON s.user_id = st.user_id 
                    SET s.user_id = st.id');
            }

            Schema::table('shifts', function (Blueprint $table) {
                // Rename column from user_id to staff_id
                $table->renameColumn('user_id', 'staff_id');
            });

            // Add foreign key constraint to staff table
            Schema::table('shifts', function (Blueprint $table) {
                $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('shifts', 'staff_id')) {
            Schema::table('shifts', function (Blueprint $table) {
                // Drop the new foreign key constraint
                $table->dropForeign(['staff_id']);
            });

            Schema::table('shifts', function (Blueprint $table) {
                // Rename column back from staff_id to user_id
                $table->renameColumn('staff_id', 'user_id');
            });

            // Revert the data mapping - only if there are shifts
            $shiftsCount = DB::table('shifts')->count();
            if ($shiftsCount > 0) {
                DB::statement('UPDATE shifts s 
                    INNER JOIN staff st ON s.user_id = st.id 
                    SET s.user_id = st.user_id');
            }

            Schema::table('shifts', function (Blueprint $table) {
                // Add back the old foreign key constraint
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }
};
