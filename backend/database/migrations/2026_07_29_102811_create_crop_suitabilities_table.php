<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crop_suitabilities', function (Blueprint $table) {

            $table->id();

            $table->foreignId('crop_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('agro_ecological_zone_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('rating',[
                'excellent',
                'good',
                'moderate',
                'poor',
                'unsuitable',
            ]);

            $table->decimal('score',5,2)->default(0);

            $table->text('reason')->nullable();

            $table->json('recommended_varieties')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crop_suitabilities');
    }
};