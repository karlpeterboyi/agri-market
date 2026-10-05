<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('offer_id')->constrained('offers')->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('listing_id')->constrained('product_listings')->cascadeOnDelete();

            $table->decimal('quantity', 12, 2);
            $table->decimal('price', 12, 2);
            $table->decimal('total_amount', 12, 2)->nullable();

            $table->string('status')->default('active'); 
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['offer_id']);
            $table->dropForeign(['buyer_id']);
            $table->dropForeign(['seller_id']);
            $table->dropForeign(['listing_id']);

            $table->dropColumn([
                'offer_id',
                'buyer_id',
                'seller_id',
                'listing_id',
                'quantity',
                'price',
                'total_amount',
                'status'
            ]);
        });
    }
};
