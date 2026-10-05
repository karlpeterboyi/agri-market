<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_workflow_logs', function (Blueprint $table) {

            $table->id();

            $table->foreignId('loan_application_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('action');

            $table->string('from_status')->nullable();

            $table->string('to_status');

            $table->text('remarks')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_workflow_logs');
    }
};