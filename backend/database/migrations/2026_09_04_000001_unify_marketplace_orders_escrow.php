<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'marketplace_type')) {
                $table->string('marketplace_type')->default('product')->after('id');
            }
            if (!Schema::hasColumn('orders', 'marketplace_id')) {
                $table->unsignedBigInteger('marketplace_id')->nullable()->after('listing_id');
            }
            if (!Schema::hasColumn('orders', 'platform_fee')) {
                $table->decimal('platform_fee', 12, 2)->nullable()->after('total_amount');
            }
            if (!Schema::hasColumn('orders', 'seller_net')) {
                $table->decimal('seller_net', 12, 2)->nullable()->after('platform_fee');
            }
            if (!Schema::hasColumn('orders', 'title')) {
                $table->string('title')->nullable()->after('seller_net');
            }
        });

        // Allow non-product marketplace orders (listing_id may be null)
        try {
            DB::statement('ALTER TABLE orders ALTER COLUMN listing_id DROP NOT NULL');
        } catch (\Throwable $e) {
            // ignore if already nullable / driver specific
        }

        try {
            // Drop FK to product_listings if present so machinery/input orders can omit listing_id
            DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_listing_id_foreign');
        } catch (\Throwable $e) {
            // ignore
        }
    }

    public function down(): void
    {
        // non-destructive
    }
};
