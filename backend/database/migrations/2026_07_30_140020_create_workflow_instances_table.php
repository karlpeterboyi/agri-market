<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_instances', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            $table->foreignId('workflow_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->morphs('workflowable');

            $table->enum('status',[
                'pending',
                'running',
                'completed',
                'cancelled'
            ])->default('pending');

            $table->integer('current_step')->default(1);

            $table->timestamp('started_at')->nullable();

            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_instances');
    }
};