<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('livestock_listings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seller_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('livestock_category_id')
                ->constrained('livestock_categories')
                ->cascadeOnDelete();

            $table->foreignId('livestock_breed_id')
                ->constrained('livestock_breeds')
                ->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->enum('sex', ['Male', 'Female']);

            $table->integer('age_months');
            $table->decimal('weight', 8, 2);
            $table->decimal('price', 12, 2);
            $table->integer('quantity')->default(1);

            $table->string('health_status')->default('Healthy');
            $table->boolean('vaccinated')->default(false);
            $table->string('vaccination_details')->nullable();

            $table->string('region');
            $table->string('district');

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->enum('status', [
                'Available',
                'Reserved',
                'Sold'
            ])->default('Available');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('livestock_listings');
    }
};