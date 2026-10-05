<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_disbursements', function (Blueprint $table) {

            $table->id();

            $table->foreignId('loan_application_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('reference_number')->unique();

            $table->decimal('approved_amount', 15, 2);

            $table->decimal('disbursed_amount', 15, 2);

            $table->date('disbursement_date')->nullable();

            $table->enum('channel', [
                'bank',
                'mobile_money',
                'wallet',
                'cash',
            ]);

            $table->string('account_name')->nullable();

            $table->string('account_number')->nullable();

            $table->string('bank_name')->nullable();

            $table->string('mobile_network')->nullable();

            $table->string('phone_number')->nullable();

            $table->string('transaction_reference')->nullable();

            $table->enum('status', [
                'pending',
                'processing',
                'successful',
                'failed',
                'reversed',
            ])->default('pending');

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_disbursements');
    }
};