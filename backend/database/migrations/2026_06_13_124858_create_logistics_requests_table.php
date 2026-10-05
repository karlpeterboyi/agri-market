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
        Schema::create('logistics_requests', function (Blueprint $table) {
    $table->id();

    $table->foreignId('order_id')->constrained()->cascadeOnDelete();
    $table->foreignId('listing_id')->constrained('product_listings')->cascadeOnDelete();

    $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();

    $table->string('pickup_region');
    $table->string('pickup_district');

    $table->string('delivery_region');
    $table->string('delivery_district');

    $table->decimal('quantity', 12, 2);

    // core workflow status
    $table->string('status')->default('pending');
    // pending | assigned | in_transit | delivered | cancelled

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logistics_requests');
    }
};
