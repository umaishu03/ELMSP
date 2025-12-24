<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            // Add department to help with constraint checking
            if (!Schema::hasColumn('leaves', 'department')) {
                $table->string('department')->nullable()->after('user_id');
            }
            
            // Add auto_approved flag to track system vs manual approvals
            if (!Schema::hasColumn('leaves', 'auto_approved')) {
                $table->boolean('auto_approved')->default(false)->after('requires_approval')
                    ->comment('True if approved automatically by system');
            }
            
            // Add approval date
            if (!Schema::hasColumn('leaves', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('auto_approved');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropColumn(['department', 'auto_approved', 'approved_at']);
        });
    }
};
