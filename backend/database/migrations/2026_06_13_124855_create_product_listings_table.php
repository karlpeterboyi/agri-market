<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_listings', function (Blueprint $table) {

    $table->id();

    $table->foreignId('seller_id')
        ->constrained('users')
        ->cascadeOnDelete();

    $table->foreignId('commodity_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->decimal('quantity',12,2);

    $table->string('unit');

    $table->string('grade')->nullable();

    $table->decimal('price',12,2);

    $table->string('region');

    $table->string('district');

    $table->text('description')->nullable();

    $table->enum('status',[
        'available',
        'sold',
        'expired'
    ])->default('available');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_listings');
    }
};
