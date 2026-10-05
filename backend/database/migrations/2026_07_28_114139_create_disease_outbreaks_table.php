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
        Schema::create('disease_outbreaks', function (Blueprint $table) {

    $table->id();

    $table->foreignId('disease_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->string('region');

    $table->string('district');

    $table->unsignedInteger('reported_cases');

    $table->string('risk_level')
        ->default('low');

    $table->date('reported_on');

    $table->boolean('active')
        ->default(true);

    $table->timestamps();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disease_outbreaks');
    }
};
