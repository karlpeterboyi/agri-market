<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machinery_listings', function (Blueprint $table) {

            $table->id();

            $table->foreignId('owner_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('machinery_category_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('machinery_brand_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('machinery_model_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('title');

            $table->text('description');

            $table->year('manufacture_year')->nullable();

            $table->string('condition')->default('Used');

            $table->integer('horsepower')->nullable();

            $table->integer('engine_hours')->nullable();

            $table->string('fuel_type')->nullable();

            $table->string('transmission')->nullable();

            $table->decimal('sale_price',15,2)->nullable();

            $table->decimal('rental_price',15,2)->nullable();

            $table->enum('rental_period',[
                'hour',
                'day',
                'week',
                'month'
            ])->nullable();

            $table->boolean('for_sale')->default(false);

            $table->boolean('for_rent')->default(true);

            $table->boolean('operator_included')->default(false);

            $table->string('region');

            $table->string('district');

            $table->string('ward')->nullable();

            $table->string('village')->nullable();

            $table->decimal('latitude',10,7)->nullable();

            $table->decimal('longitude',10,7)->nullable();

            $table->string('cover_photo')->nullable();

            $table->json('gallery')->nullable();

            $table->boolean('available')->default(true);

            $table->boolean('verified')->default(false);

            $table->boolean('featured')->default(false);

            $table->enum('status',[
                'draft',
                'active',
                'booked',
                'sold',
                'maintenance'
            ])->default('active');

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machinery_listings');
    }
};