<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {

            $table->id();

            $table->foreignId('farm_warehouse_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');

            $table->string('sku')->unique();

            $table->string('category');

            $table->string('brand')->nullable();

            $table->string('unit');

            $table->decimal('quantity',12,2)->default(0);

            $table->decimal('minimum_quantity',12,2)->default(0);

            $table->decimal('unit_cost',12,2)->default(0);

            $table->string('batch_number')->nullable();

            $table->date('manufactured_at')->nullable();

            $table->date('expiry_date')->nullable();

            $table->string('supplier')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};