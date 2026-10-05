<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_listings', function (Blueprint $table) {
            if (!Schema::hasColumn('product_listings', 'featured_image')) {
                $table->string('featured_image')->nullable()->after('description');
            }
            if (!Schema::hasColumn('product_listings', 'image_2')) {
                $table->string('image_2')->nullable()->after('featured_image');
            }
            if (!Schema::hasColumn('product_listings', 'image_3')) {
                $table->string('image_3')->nullable()->after('image_2');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_listings', function (Blueprint $table) {
            foreach (['featured_image', 'image_2', 'image_3'] as $col) {
                if (Schema::hasColumn('product_listings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
