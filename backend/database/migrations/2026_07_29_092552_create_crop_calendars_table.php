<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crop_calendars', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('crop');

            $table->string('variety')->nullable();

            $table->string('region');

            $table->string('district');

            $table->date('planting_date');

            $table->date('expected_harvest_date')->nullable();

            $table->string('season');

            $table->string('status')->default('active');

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crop_calendars');
    }
};