<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->string('slug')->unique();

            $table->text('description')->nullable();

            $table->integer('listing_limit')->default(0);

            $table->integer('featured_listings')->default(0);

            $table->boolean('analytics')->default(false);

            $table->boolean('verified_badge')->default(false);

            $table->boolean('priority_support')->default(false);

            $table->boolean('business_page')->default(false);

            $table->boolean('active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};