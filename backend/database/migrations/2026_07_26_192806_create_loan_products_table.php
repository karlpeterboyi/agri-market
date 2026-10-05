<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_products', function (Blueprint $table) {

            $table->id();

            $table->foreignId('financial_institution_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');

            $table->string('slug')->unique();

            $table->text('description')->nullable();

            $table->string('loan_type');

            $table->decimal('minimum_amount', 15, 2);

            $table->decimal('maximum_amount', 15, 2);

            $table->decimal('interest_rate', 5, 2);

            $table->integer('minimum_duration_months');

            $table->integer('maximum_duration_months');

            $table->decimal('processing_fee', 15, 2)
                ->default(0);

            $table->boolean('requires_collateral')
                ->default(false);

            $table->boolean('online_application')
                ->default(true);

            $table->json('eligibility')->nullable();

            $table->json('required_documents')->nullable();

            $table->boolean('featured')->default(false);

            $table->boolean('active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_products');
    }
};