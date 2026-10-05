<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('livestock_breeds', function (Blueprint $table) {

            $table->id();

            $table->foreignId('livestock_category_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');

            $table->string('origin')->nullable();

            $table->text('description')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('livestock_breeds');
    }
};