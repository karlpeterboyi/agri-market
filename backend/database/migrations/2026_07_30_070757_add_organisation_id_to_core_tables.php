<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [

            'farms',
            'farm_warehouses',
            'accounts',
            'journal_entries',
            'loans',
            'research_projects',

        ];

        foreach ($tables as $table) {

            if (Schema::hasTable($table)) {

                Schema::table($table, function (Blueprint $table) {

                    $table->foreignId('organisation_id')
                        ->nullable()
                        ->after('id')
                        ->constrained()
                        ->nullOnDelete();

                });

            }

        }
    }

    public function down(): void
    {
        $tables = [

            'farms',
            'farm_warehouses',
            'accounts',
            'journal_entries',
            'loans',
            'research_projects',

        ];

        foreach ($tables as $table) {

            if (Schema::hasTable($table)) {

                Schema::table($table, function (Blueprint $table) {

                    $table->dropConstrainedForeignId(
                        'organisation_id'
                    );

                });

            }

        }
    }
};