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
        Schema::create('service_quotes', function (Blueprint $table) {

    $table->id();

    $table->foreignId('service_request_id')
          ->constrained()
          ->cascadeOnDelete();

    $table->foreignId('provider_id')
          ->constrained('users')
          ->cascadeOnDelete();

    $table->decimal('price',12,2);

    $table->integer('estimated_days')
          ->nullable();

    $table->text('message')
          ->nullable();

    $table->string('status')
          ->default('pending');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_quotes');
    }
};
