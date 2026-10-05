<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_guarantors', function (Blueprint $table) {

            $table->id();

            $table->foreignId('loan_application_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('full_name');

            $table->string('national_id')->nullable();

            $table->string('phone');

            $table->string('email')->nullable();

            $table->string('relationship');

            $table->string('occupation')->nullable();

            $table->string('employer')->nullable();

            $table->decimal('annual_income', 15, 2)
                ->nullable();

            $table->string('physical_address')->nullable();

            $table->boolean('accepted')->default(false);

            $table->timestamp('accepted_at')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_guarantors');
    }
};