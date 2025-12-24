<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds a nullable `leave_id` foreign key to `shifts`.
     */
    public function up()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->unsignedBigInteger('leave_id')->nullable()->after('rest_day');
            $table->foreign('leave_id')->references('id')->on('leaves')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropForeign(['leave_id']);
            $table->dropColumn('leave_id');
        });
    }
};
