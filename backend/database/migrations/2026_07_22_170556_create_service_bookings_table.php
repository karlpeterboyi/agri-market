<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_bookings', function (Blueprint $table) {

    $table->id();

    $table->foreignId('service_quote_id')
          ->constrained()
          ->cascadeOnDelete();

    $table->foreignId('customer_id')
          ->constrained('users')
          ->cascadeOnDelete();

    $table->foreignId('provider_id')
          ->constrained('users')
          ->cascadeOnDelete();

    $table->date('scheduled_date')
          ->nullable();

    $table->date('completed_date')
          ->nullable();

    $table->string('status')
          ->default('scheduled');

    $table->string('payment_status')
          ->default('pending');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_bookings');
    }
};
