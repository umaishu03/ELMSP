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
        Schema::table('leaves', function (Blueprint $table) {
            // Drop the old foreign key constraint
            $table->dropForeign(['user_id']);
        });

        // Map user_id -> staff_id for existing leaves
        $leavesCount = DB::table('leaves')->count();
        if ($leavesCount > 0) {
            DB::statement('UPDATE leaves l 
                INNER JOIN staff st ON l.user_id = st.user_id 
                SET l.user_id = st.id');
        }

        Schema::table('leaves', function (Blueprint $table) {
            // Rename column from user_id to staff_id
            $table->renameColumn('user_id', 'staff_id');
        });

        // Add the new foreign key constraint to staff table
        Schema::table('leaves', function (Blueprint $table) {
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
        });

        // Sync department from staff table
        if ($leavesCount > 0) {
            DB::statement('UPDATE leaves l 
                JOIN staff st ON l.staff_id = st.id 
                SET l.department = st.department');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            // Drop the new foreign key constraint
            $table->dropForeign(['staff_id']);
        });

        Schema::table('leaves', function (Blueprint $table) {
            // Rename column back from staff_id to user_id
            $table->renameColumn('staff_id', 'user_id');
        });

        // Revert data mapping
        $leavesCount = DB::table('leaves')->count();
        if ($leavesCount > 0) {
            DB::statement('UPDATE leaves l 
                INNER JOIN staff st ON l.user_id = st.id 
                SET l.user_id = st.user_id');
        }

        Schema::table('leaves', function (Blueprint $table) {
            // Add back the old foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
