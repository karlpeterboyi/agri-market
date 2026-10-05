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
        Schema::create('weather_alerts', function (Blueprint $table) {

    $table->id();

    $table->foreignId('weather_station_id')
        ->nullable()
        ->constrained()
        ->nullOnDelete();

    $table->string('alert_type');

    $table->string('severity');

    $table->string('title');

    $table->longText('message');

    $table->string('region');

    $table->string('district')->nullable();

    $table->timestamp('starts_at');

    $table->timestamp('ends_at');

    $table->boolean('active')->default(true);

    $table->timestamps();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_alerts');
    }
};
