<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_recommendations', function (Blueprint $table) {

            $table->id();

            $table->foreignId('farm_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('category');

            $table->string('priority')
                ->default('medium');

            $table->string('title');

            $table->text('recommendation');

            $table->json('inputs')->nullable();

            $table->decimal('confidence',5,2)
                ->default(0);

            $table->boolean('accepted')
                ->default(false);

            $table->timestamp('accepted_at')
                ->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_recommendations');
    }
};