<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_stations', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->string('provider');

            $table->string('station_code')->nullable();

            $table->string('country')->default('Tanzania');

            $table->string('region');

            $table->string('district')->nullable();

            $table->decimal('latitude',10,7);

            $table->decimal('longitude',10,7);

            $table->decimal('elevation',8,2)->nullable();

            $table->boolean('active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_stations');
    }
};