<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agro_ecological_zones', function (Blueprint $table) {

            $table->id();

            $table->string('country')->default('Tanzania');

            $table->string('zone');

            $table->string('code')->unique();

            $table->text('description')->nullable();

            $table->decimal('rainfall_min',8,2)->nullable();

            $table->decimal('rainfall_max',8,2)->nullable();

            $table->decimal('temperature_min',5,2)->nullable();

            $table->decimal('temperature_max',5,2)->nullable();

            $table->decimal('altitude_min',8,2)->nullable();

            $table->decimal('altitude_max',8,2)->nullable();

            $table->json('regions')->nullable();

            $table->json('districts')->nullable();

            $table->json('soil_types')->nullable();

            $table->json('dominant_crops')->nullable();

            $table->json('livestock')->nullable();

            $table->json('climate_risks')->nullable();

            $table->string('koppen_classification')->nullable();

            $table->decimal('average_humidity',5,2)->nullable();

            $table->decimal('average_wind_speed',5,2)->nullable();

            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->softDeletes();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agro_ecological_zones');
    }
};