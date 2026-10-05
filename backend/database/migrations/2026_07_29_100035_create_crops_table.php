<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crops', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->string('scientific_name')->nullable();

            $table->enum('category', [
                'food',
                'cash',
                'horticulture',
                'forage',
                'industrial',
                'fruit',
                'vegetable',
                'spice',
            ]);

            $table->text('description')->nullable();

            $table->string('image')->nullable();

            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crops');
    }
};