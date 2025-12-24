<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('leave_type'); // annual, medical, replacement, etc.
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('total_days', 5, 2)->default(0);
            $table->text('reason')->nullable();
            $table->string('attachment')->nullable();

            $table->decimal('ot_hours', 5, 2)->nullable();   // for OT-to-replacement
            $table->integer('days_entitled')->nullable();     // OT converted days
            $table->integer('max_days')->nullable()->comment('Maximum allowed leave days for this leave type');

            $table->boolean('requires_approval')->default(false)
                  ->comment('True if admin approval is required');

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('leaves');
    }
};
