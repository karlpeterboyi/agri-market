<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_schedules', function (Blueprint $table) {

            $table->id();

            $table->foreignId('workflow_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('frequency',[
                'once',
                'daily',
                'weekly',
                'monthly',
                'seasonal',
                'yearly'
            ]);

            $table->date('start_date');

            $table->date('end_date')->nullable();

            $table->timestamp('last_run_at')->nullable();

            $table->timestamp('next_run_at');

            $table->boolean('active')->default(true);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_schedules');
    }
};