<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farmer_advisories', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('crop_calendar_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('advisory_date');

            $table->string('title');

            $table->text('summary');

            $table->json('recommendations');

            $table->string('priority')
                ->default('medium');

            $table->boolean('read')
                ->default(false);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farmer_advisories');
    }
};