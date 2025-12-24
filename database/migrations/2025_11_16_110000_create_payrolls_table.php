<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Period
            $table->year('year');
            $table->unsignedTinyInteger('month');
            
            // Base components
            $table->decimal('basic_salary', 10, 2);
            $table->decimal('fixed_commission', 10, 2)->default(0)->comment('Fixed commission (RM200 after 3 months)');
            
            // OT and public holiday pay
            $table->decimal('public_holiday_hours', 5, 2)->default(0);
            $table->decimal('public_holiday_pay', 10, 2)->default(0)->comment('Public holiday hours * RM15.38');
            
            $table->decimal('fulltime_ot_hours', 5, 2)->default(0);
            $table->decimal('fulltime_ot_pay', 10, 2)->default(0)->comment('Fulltime OT hours * RM12.26');
            
            $table->decimal('public_holiday_ot_hours', 5, 2)->default(0);
            $table->decimal('public_holiday_ot_pay', 10, 2)->default(0)->comment('Public holiday OT hours * RM21.68');
            
            // Total calculations
            $table->decimal('gross_salary', 10, 2);
            $table->decimal('total_deductions', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2);
            
            // Status: draft, approved, paid
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            
            // Payment date
            $table->date('payment_date')->nullable();
            
            // Notes
            $table->text('remarks')->nullable();
            
            $table->timestamps();
            
            $table->index(['user_id', 'year', 'month']);
            $table->unique(['user_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
