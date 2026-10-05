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
        Schema::create('disease_reports', function (Blueprint $table) {

    $table->id();

    $table->foreignId('user_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->foreignId('disease_id')
        ->nullable()
        ->constrained()
        ->nullOnDelete();

    $table->foreignId('extension_officer_id')
        ->nullable()
        ->constrained()
        ->nullOnDelete();

    $table->string('commodity_type');

    $table->string('commodity_name');

    $table->text('symptoms');

    $table->string('region');

    $table->string('district');

    $table->decimal('latitude',10,7)->nullable();

    $table->decimal('longitude',10,7)->nullable();

    $table->string('status')->default('pending');

    $table->string('diagnosis_source')->default('manual');

    $table->timestamps();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disease_reports');
    }
};
