<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create leave_types and leave_balances must run before this migration (000009)
        // Add leave_type_id column
        Schema::table('leaves', function (Blueprint $table) {
            if (!Schema::hasColumn('leaves', 'leave_type_id')) {
                $table->unsignedBigInteger('leave_type_id')->nullable()->after('staff_id');
            }
        });

        // Migrate existing textual leave_type values into leave_types and set FK
        $rows = DB::table('leaves')->select('id', 'leave_type')->whereNotNull('leave_type')->get();
        $typeMap = [];
        foreach ($rows as $r) {
            $type = trim(strtolower($r->leave_type));
            if ($type === '') continue;
            if (!isset($typeMap[$type])) {
                // Insert or get existing
                $existing = DB::table('leave_types')->where('type_name', $type)->first();
                if ($existing) {
                    $typeMap[$type] = $existing->id;
                } else {
                    $id = DB::table('leave_types')->insertGetId([
                        'type_name' => $type,
                        'description' => null,
                        'requires_approval' => 0,
                        'deduct_from_balance' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $typeMap[$type] = $id;
                }
            }
            if (isset($typeMap[$type])) {
                DB::table('leaves')->where('id', $r->id)->update(['leave_type_id' => $typeMap[$type]]);
            }
        }

        // Make column non-nullable if desired (keep nullable to be safe)
        Schema::table('leaves', function (Blueprint $table) {
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onDelete('restrict');
        });

        // Optionally drop the old textual column if present
        if (Schema::hasColumn('leaves', 'leave_type')) {
            Schema::table('leaves', function (Blueprint $table) {
                $table->dropColumn('leave_type');
            });
        }
    }

    public function down(): void
    {
        // Add back textual leave_type column
        Schema::table('leaves', function (Blueprint $table) {
            if (!Schema::hasColumn('leaves', 'leave_type')) {
                $table->string('leave_type')->nullable()->after('staff_id');
            }
        });

        // Map leave_type_id back to textual values where possible
        $rows = DB::table('leaves')->select('id', 'leave_type_id')->whereNotNull('leave_type_id')->get();
        foreach ($rows as $r) {
            $typeRec = DB::table('leave_types')->where('id', $r->leave_type_id)->first();
            if ($typeRec) {
                DB::table('leaves')->where('id', $r->id)->update(['leave_type' => $typeRec->type_name]);
            }
        }

        // Drop FK and column
        Schema::table('leaves', function (Blueprint $table) {
            if (Schema::hasColumn('leaves', 'leave_type_id')) {
                $table->dropForeign(['leave_type_id']);
                $table->dropColumn('leave_type_id');
            }
        });
    }
};
