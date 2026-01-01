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
        // Drop admin table - not used (admin role is determined by users.role field)
        Schema::dropIfExists('admin');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate admin table if needed to rollback
        Schema::create('admin', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('employee_id')->unique();
            $table->string('department');
            $table->string('admin_level')->default('super_admin');
            $table->json('permissions')->nullable();
            $table->date('appointment_date')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['employee_id', 'admin_level']);
        });
    }
};
