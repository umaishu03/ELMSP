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
        Schema::create('overtimes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // OT type: 'fulltime' (RM12.26), 'public_holiday' (RM21.68), or 'public_holiday_work' (RM15.38)
            $table->enum('ot_type', ['fulltime', 'public_holiday', 'public_holiday_work']);
            
            // Date of the overtime
            $table->date('ot_date');
            
            // Hours claimed for this OT
            $table->decimal('hours', 5, 2);
            
            // Status: pending, approved, rejected
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            
            // Admin remarks/notes
            $table->text('remarks')->nullable();
            
            $table->timestamps();
            
            // Indexes for filtering
            $table->index(['user_id', 'status']);
            $table->index('ot_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtimes');
    }
};
