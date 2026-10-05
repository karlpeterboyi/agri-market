<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('farm_warehouses', 'organisation_id')) {
            Schema::table('farm_warehouses', function (Blueprint $table) {
                $table->foreignId('organisation_id')
                    ->nullable()
                    ->constrained('organisations')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('farm_warehouses', 'organisation_id')) {
            Schema::table('farm_warehouses', function (Blueprint $table) {
                $table->dropForeign(['organisation_id']);
                $table->dropColumn('organisation_id');
            });
        }
    }
};