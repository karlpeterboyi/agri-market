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
        Schema::create('service_requests', function (Blueprint $table) {

    $table->id();

    $table->foreignId('user_id')
          ->constrained()
          ->cascadeOnDelete();

    $table->foreignId('service_category_id')
          ->constrained()
          ->cascadeOnDelete();

    $table->text('description');

    $table->decimal('budget',12,2)->nullable();

    $table->date('deadline')->nullable();

    $table->string('region');

    $table->string('district');

    $table->string('ward')->nullable();

    $table->string('village')->nullable();

    $table->string('status')
          ->default('open');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
