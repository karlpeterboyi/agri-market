<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farm_activities', function (Blueprint $table) {

            $table->id();

            $table->foreignId('farm_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('field_block_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('crop_cycle_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('activity_type', [

                'land_preparation',
                'planting',
                'gap_filling',
                'thinning',
                'weeding',
                'fertilizer',
                'manure',
                'compost',
                'lime',
                'irrigation',
                'spraying',
                'pesticide',
                'fungicide',
                'herbicide',
                'scouting',
                'soil_test',
                'tissue_test',
                'pruning',
                'mulching',
                'staking',
                'harvesting',
                'post_harvest',
                'storage',
                'transport',
                'other',

            ]);

            $table->string('title');

            $table->text('description')->nullable();

            $table->timestamp('activity_date');

            $table->decimal('quantity',12,2)->nullable();

            $table->string('unit')->nullable();

            $table->decimal('cost',12,2)->nullable();

            $table->decimal('labour_cost',12,2)->nullable();

            $table->integer('workers')->nullable();

            $table->integer('duration_minutes')->nullable();

            $table->decimal('latitude',10,7)->nullable();

            $table->decimal('longitude',10,7)->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->softDeletes();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farm_activities');
    }
};