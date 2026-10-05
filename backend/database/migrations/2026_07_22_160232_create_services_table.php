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
    Schema::create('services', function (Blueprint $table) {
    $table->id();
    $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('service_category_id')->constrained('service_categories')->onDelete('cascade');
    $table->string('title');
    $table->text('description')->nullable();
    $table->decimal('price', 12, 2)->default(0);
    $table->string('pricing_type')->default('fixed');
    $table->string('phone')->nullable();
    $table->string('mobile')->nullable();
    $table->string('email')->nullable();
    $table->string('website')->nullable();
    $table->string('region')->nullable();
    $table->string('district')->nullable();
    $table->string('ward')->nullable();
    $table->string('village')->nullable();
    $table->decimal('latitude', 10, 8)->nullable();
    $table->decimal('longitude', 11, 8)->nullable();
    $table->string('cover_photo')->nullable();
    $table->json('gallery')->nullable();
    $table->json('features')->nullable();
    $table->time('available_from')->nullable();
    $table->time('available_to')->nullable();
    $table->boolean('verified')->default(false);
    $table->boolean('featured')->default(false);
    $table->string('status')->default('active');
    $table->timestamps();
});

}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
