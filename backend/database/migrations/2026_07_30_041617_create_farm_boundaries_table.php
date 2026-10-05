<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farm_boundaries', function (Blueprint $table) {

            $table->id();

            $table->foreignId('farm_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
             * GeoJSON polygon.
             * Later we'll migrate to PostGIS geometry.
             */
            $table->json('boundary');

            $table->decimal(
                'area_hectares',
                12,
                4
            )->default(0);

            $table->decimal(
                'perimeter_meters',
                12,
                2
            )->default(0);

            $table->decimal(
                'centroid_latitude',
                10,
                7
            )->nullable();

            $table->decimal(
                'centroid_longitude',
                10,
                7
            )->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'farm_boundaries'
        );
    }
};