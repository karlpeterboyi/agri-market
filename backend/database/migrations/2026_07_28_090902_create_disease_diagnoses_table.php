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
        Schema::create('disease_diagnoses', function (Blueprint $table) {

    $table->id();

    $table->foreignId('disease_report_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->foreignId('disease_id')
        ->nullable()
        ->constrained()
        ->nullOnDelete();

    $table->decimal('confidence',5,2);

    $table->string('model_name');

    $table->string('model_version')->nullable();

    $table->json('predictions')->nullable();

    $table->boolean('verified_by_expert')
        ->default(false);

    $table->timestamps();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disease_diagnoses');
    }
};
