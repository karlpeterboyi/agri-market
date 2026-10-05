<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crop_varieties', function (Blueprint $table) {

            $table->id();

            $table->foreignId('crop_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');

            $table->string('breeder')->nullable();

            $table->integer('maturity_days')->nullable();

            $table->decimal('yield_per_hectare',8,2)->nullable();

            $table->string('yield_unit')->default('tonnes/ha');

            $table->string('drought_tolerance')->nullable();

            $table->string('heat_tolerance')->nullable();

            $table->string('disease_resistance')->nullable();

            $table->json('recommended_regions')->nullable();

            $table->text('description')->nullable();

            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crop_varieties');
    }
};