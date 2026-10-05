<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_blocks', function (Blueprint $table) {

            $table->id();

            $table->foreignId('farm_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');

            $table->json('boundary');

            $table->decimal(
                'area_hectares',
                10,
                4
            );

            $table->string('status')
                ->default('active');
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'field_blocks'
        );
    }
};