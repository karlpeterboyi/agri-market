<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_applications', function (Blueprint $table) {

            $table->id();

            $table->foreignId('loan_product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('application_number')->unique();

            $table->decimal('requested_amount', 15, 2);

            $table->integer('repayment_period_months');

            $table->text('purpose');

            $table->decimal('annual_income', 15, 2)
                ->nullable();

            $table->decimal('farm_size', 10, 2)
                ->nullable();

            $table->string('farm_size_unit')
                ->default('acre');

            $table->json('uploaded_documents')
                ->nullable();

            $table->text('remarks')
                ->nullable();

            $table->enum('status', [

                'draft',

                'submitted',

                'under_review',

                'approved',

                'rejected',

                'cancelled',

                'disbursed',

                'completed'

            ])->default('draft');

            $table->timestamp('submitted_at')
                ->nullable();

            $table->timestamp('approved_at')
                ->nullable();

            $table->timestamp('rejected_at')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_applications');
    }
};