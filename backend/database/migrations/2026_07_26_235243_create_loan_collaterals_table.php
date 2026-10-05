<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_collaterals', function (Blueprint $table) {

            $table->id();

            $table->foreignId('loan_application_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('collateral_type');

            $table->string('title');

            $table->text('description')->nullable();

            $table->decimal('estimated_value', 15, 2);

            $table->string('ownership_reference')->nullable();

            $table->string('location')->nullable();

            $table->json('supporting_documents')->nullable();

            $table->boolean('verified')->default(false);

            $table->timestamp('verified_at')->nullable();

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('verification_notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_collaterals');
    }
};