<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('livestock_images', function (Blueprint $table) {

            $table->id();

            $table->foreignId('livestock_listing_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('image');

            $table->boolean('featured')
                ->default(false);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('livestock_images');
    }
};