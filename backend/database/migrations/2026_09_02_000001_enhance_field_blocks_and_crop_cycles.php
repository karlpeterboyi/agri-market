<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('field_blocks', function (Blueprint $table) {
            if (!Schema::hasColumn('field_blocks', 'code')) {
                $table->string('code')->nullable()->after('name');
            }
            if (!Schema::hasColumn('field_blocks', 'soil_type')) {
                $table->string('soil_type')->nullable();
            }
            if (!Schema::hasColumn('field_blocks', 'irrigation_type')) {
                $table->string('irrigation_type')->nullable();
            }
            if (!Schema::hasColumn('field_blocks', 'area_unit')) {
                $table->string('area_unit')->default('hectares');
            }
        });

        // Postgres: allow null boundary so blocks can be created without GPS polygon
        try {
            DB::statement('ALTER TABLE field_blocks ALTER COLUMN boundary DROP NOT NULL');
        } catch (\Throwable $e) {
            // ignore if already nullable or driver differs
        }

        // Backfill null boundaries
        try {
            DB::table('field_blocks')->whereNull('boundary')->update(['boundary' => json_encode([])]);
        } catch (\Throwable $e) {
            // ignore
        }
    }

    public function down(): void
    {
        // non-destructive down
    }
};
