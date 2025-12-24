<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ot_claims', function (Blueprint $table) {
            if (!Schema::hasColumn('ot_claims', 'overtime_id')) {
                $table->unsignedBigInteger('overtime_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('ot_claims', 'leave_id')) {
                $table->unsignedBigInteger('leave_id')->nullable()->after('overtime_id');
            }
            if (!Schema::hasColumn('ot_claims', 'payroll_id')) {
                $table->unsignedBigInteger('payroll_id')->nullable()->after('leave_id');
            }
        });

        // Best-effort: if ot_ids contains a single overtime id, populate overtime_id
        try {
            $rows = DB::table('ot_claims')->select('id','ot_ids')->whereNotNull('ot_ids')->get();
            foreach ($rows as $r) {
                $decoded = json_decode($r->ot_ids, true);
                if (is_array($decoded) && count($decoded) === 1) {
                    $oid = $decoded[0];
                    if ($oid) {
                        DB::table('ot_claims')->where('id', $r->id)->update(['overtime_id' => $oid]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore mapping errors; manual migration may be required
        }

        // Add foreign key constraints
        Schema::table('ot_claims', function (Blueprint $table) {
            if (Schema::hasColumn('ot_claims', 'overtime_id')) {
                $table->foreign('overtime_id')->references('id')->on('overtimes')->onDelete('set null');
            }
            if (Schema::hasColumn('ot_claims', 'leave_id')) {
                $table->foreign('leave_id')->references('id')->on('leaves')->onDelete('set null');
            }
            if (Schema::hasColumn('ot_claims', 'payroll_id')) {
                $table->foreign('payroll_id')->references('id')->on('payrolls')->onDelete('set null');
            }
        });

        // Drop user_id column if present (drop FK first if exists)
        Schema::table('ot_claims', function (Blueprint $table) {
            if (Schema::hasColumn('ot_claims', 'user_id')) {
                try {
                    $table->dropForeign(['user_id']);
                } catch (\Throwable $e) {
                    // ignore if no foreign key
                }
                $table->dropColumn('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ot_claims', function (Blueprint $table) {
            if (!Schema::hasColumn('ot_claims', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                // no foreign key re-added automatically
            }
        });

        Schema::table('ot_claims', function (Blueprint $table) {
            if (Schema::hasColumn('ot_claims', 'overtime_id')) {
                try { $table->dropForeign(['overtime_id']); } catch (\Throwable $e) {}
                $table->dropColumn('overtime_id');
            }
            if (Schema::hasColumn('ot_claims', 'leave_id')) {
                try { $table->dropForeign(['leave_id']); } catch (\Throwable $e) {}
                $table->dropColumn('leave_id');
            }
            if (Schema::hasColumn('ot_claims', 'payroll_id')) {
                try { $table->dropForeign(['payroll_id']); } catch (\Throwable $e) {}
                $table->dropColumn('payroll_id');
            }
        });
    }
};
