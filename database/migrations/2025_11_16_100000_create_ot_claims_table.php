<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ot_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Claim type: 'replacement_leave' or 'payroll'
            $table->enum('claim_type', ['replacement_leave', 'payroll']);
            
            // For replacement leave claims
            $table->decimal('fulltime_hours', 5, 2)->nullable()->comment('Fulltime OT hours to claim');
            $table->decimal('public_holiday_hours', 5, 2)->nullable()->comment('Public holiday OT hours to claim');
            
            // Days calculated from fulltime + public holiday hours / 8 for replacement leave
            $table->decimal('replacement_days', 5, 2)->nullable();
            
            // Status: pending, approved, rejected
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            
            // Admin remarks
            $table->text('remarks')->nullable();
            
            $table->timestamps();
            
            $table->index(['user_id', 'claim_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ot_claims');
    }
};
