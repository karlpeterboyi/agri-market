<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            $table->foreignId('organisation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('farm_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('title');

            $table->text('description')->nullable();

            $table->enum('priority',[
                'low',
                'medium',
                'high',
                'critical'
            ])->default('medium');

            $table->enum('status',[
                'pending',
                'in_progress',
                'completed',
                'cancelled'
            ])->default('pending');

            $table->date('start_date')->nullable();

            $table->date('due_date')->nullable();

            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};