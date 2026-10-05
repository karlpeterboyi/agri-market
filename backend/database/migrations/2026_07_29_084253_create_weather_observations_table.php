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
        Schema::create('weather_observations', function (Blueprint $table) {

    $table->id();

    $table->foreignId('weather_station_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->timestamp('recorded_at');

    $table->decimal('temperature',5,2);

    $table->decimal('humidity',5,2);

    $table->decimal('rainfall',8,2)->default(0);

    $table->decimal('wind_speed',8,2)->default(0);

    $table->decimal('pressure',8,2)->nullable();

    $table->string('condition');

    $table->timestamps();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_observations');
    }
};
