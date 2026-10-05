<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machinery_models', function (Blueprint $table) {

            $table->id();

            $table->foreignId('machinery_brand_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('machinery_category_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('name');

            $table->string('slug')->unique();

            $table->integer('horsepower')->nullable();

            $table->string('fuel_type')->nullable();

            $table->string('transmission')->nullable();

            $table->text('description')->nullable();

            $table->boolean('active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machinery_models');
    }
};