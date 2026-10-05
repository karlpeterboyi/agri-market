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
        Schema::create('institution_preferences', function (Blueprint $table) {

    $table->id();

    $table->foreignId('financial_institution_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->json('supported_crops')->nullable();

    $table->json('supported_livestock')->nullable();

    $table->json('supported_regions')->nullable();

    $table->json('target_groups')->nullable();

    $table->decimal('minimum_farm_size',10,2)->nullable();

    $table->decimal('maximum_loan_amount',15,2)->nullable();

    $table->boolean('women_programme')->default(false);

    $table->boolean('youth_programme')->default(false);

    $table->boolean('mechanisation')->default(false);

    $table->boolean('climate_smart')->default(false);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institution_preferences');
    }
};
