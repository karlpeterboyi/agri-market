<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crop_cycles', function (Blueprint $table) {

            $table->id();

            $table->foreignId('farm_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('field_block_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('crop_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('crop_variety_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('season');

            $table->date('planting_date');

            $table->date('expected_harvest_date')->nullable();

            $table->date('actual_harvest_date')->nullable();

            $table->decimal('area_hectares',10,4);

            $table->decimal('expected_yield',10,2)->nullable();

            $table->decimal('actual_yield',10,2)->nullable();

            $table->enum('status',[
                'planned',
                'planted',
                'growing',
                'flowering',
                'harvesting',
                'completed',
                'failed',
            ])->default('planned');

            $table->json('metadata')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crop_cycles');
    }
};