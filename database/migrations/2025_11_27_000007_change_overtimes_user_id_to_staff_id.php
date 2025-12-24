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
        // Drop existing foreign key on user_id (if present)
        Schema::table('overtimes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // Map existing overtimes: set user_id -> staff.id (temporary) before renaming
        $count = DB::table('overtimes')->count();
        if ($count > 0) {
            DB::statement('UPDATE overtimes o INNER JOIN staff st ON o.user_id = st.user_id SET o.user_id = st.id');
        }

        // Rename column user_id to staff_id
        Schema::table('overtimes', function (Blueprint $table) {
            $table->renameColumn('user_id', 'staff_id');
        });

        // Add new foreign key to staff table
        Schema::table('overtimes', function (Blueprint $table) {
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new foreign key
        Schema::table('overtimes', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
        });

        // Rename back to user_id
        Schema::table('overtimes', function (Blueprint $table) {
            $table->renameColumn('staff_id', 'user_id');
        });

        // Revert mapped values: staff.id -> users.id
        $count = DB::table('overtimes')->count();
        if ($count > 0) {
            DB::statement('UPDATE overtimes o INNER JOIN staff st ON o.user_id = st.id SET o.user_id = st.user_id');
        }

        // Add foreign key back to users table
        Schema::table('overtimes', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
