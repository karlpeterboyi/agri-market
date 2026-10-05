<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soil_profiles', function (Blueprint $table) {

            $table->id();

            $table->foreignId('agro_ecological_zone_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('soil_type');

            $table->decimal('ph_min',4,2)->nullable();

            $table->decimal('ph_max',4,2)->nullable();

            $table->decimal('organic_matter',5,2)->nullable();

            $table->string('drainage')->nullable();

            $table->string('texture')->nullable();

            $table->string('fertility')->nullable();

            $table->json('nutrient_levels')->nullable();

            $table->json('recommended_amendments')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soil_profiles');
    }
};