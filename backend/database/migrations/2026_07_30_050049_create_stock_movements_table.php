<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {

            $table->id();

            $table->foreignId('inventory_item_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('farm_activity_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->enum('type',[
                'purchase',
                'usage',
                'adjustment',
                'transfer',
                'sale',
                'return',
                'loss',
            ]);

            $table->decimal('quantity',12,2);

            $table->decimal('balance_after',12,2);

            $table->text('remarks')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};