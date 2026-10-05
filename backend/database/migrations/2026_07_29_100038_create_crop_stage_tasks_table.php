<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crop_stage_tasks', function (Blueprint $table) {

            $table->id();

            $table->foreignId('crop_growth_stage_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('task');

            $table->string('task_type');

            $table->integer('days_after_stage');

            $table->enum('priority',[
                'low',
                'medium',
                'high',
                'critical',
            ]);

            $table->boolean('mandatory')
                ->default(true);

            $table->text('instructions')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crop_stage_tasks');
    }
};