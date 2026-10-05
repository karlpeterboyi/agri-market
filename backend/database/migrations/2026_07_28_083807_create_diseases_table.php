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
        Schema::create('diseases', function (Blueprint $table) {

    $table->id();

    $table->string('name');

    $table->string('scientific_name')->nullable();

    $table->string('category');

    $table->string('target');

    $table->text('description');

    $table->text('symptoms');

    $table->text('causes')->nullable();

    $table->text('prevention')->nullable();

    $table->text('treatment')->nullable();

    $table->text('recommended_products')->nullable();

    $table->string('severity')->default('medium');

    $table->boolean('reportable')->default(false);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diseases');
    }
};
