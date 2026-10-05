<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crop_calendar_activities', function (Blueprint $table) {

            $table->id();

            $table->foreignId('crop_calendar_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('activity');

            $table->date('scheduled_date');

            $table->string('status')
                ->default('pending');

            $table->text('recommendation')
                ->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crop_calendar_activities');
    }
};