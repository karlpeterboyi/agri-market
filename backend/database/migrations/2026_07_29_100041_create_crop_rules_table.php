<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crop_rules', function (Blueprint $table) {

            $table->id();

            $table->foreignId('crop_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('crop_growth_stage_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('rule_type');

            $table->json('conditions');

            $table->json('actions');

            $table->integer('priority')
                ->default(1);

            $table->boolean('enabled')
                ->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crop_rules');
    }
};