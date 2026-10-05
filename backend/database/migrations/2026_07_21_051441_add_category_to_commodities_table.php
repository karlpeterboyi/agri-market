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
    Schema::table('commodities', function (Blueprint $table) {

        $table->foreignId('commodity_category_id')
              ->nullable()
              ->after('id')
              ->constrained()
              ->cascadeOnDelete();

    });
}

public function down(): void
{
    Schema::table('commodities', function (Blueprint $table) {

        $table->dropForeign(['commodity_category_id']);

        $table->dropColumn('commodity_category_id');

    });
}
};
