<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_steps', function (Blueprint $table) {

            $table->id();

            $table->foreignId('workflow_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');

            $table->integer('step_order');

            $table->string('task_title');

            $table->text('task_description')->nullable();

            $table->string('assign_role')->nullable();

            $table->integer('due_after_days')->default(0);

            $table->boolean('approval_required')->default(false);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_steps');
    }
};