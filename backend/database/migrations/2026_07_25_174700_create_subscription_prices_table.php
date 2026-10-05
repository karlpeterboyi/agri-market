<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_prices', function (Blueprint $table) {

            $table->id();

            $table->foreignId('subscription_plan_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('billing_period', [
                'monthly',
                'quarterly',
                'biannual',
                'annual',
            ]);

            $table->decimal('price', 12, 2);

            $table->string('currency', 3)
                ->default('TZS');

            $table->boolean('active')
                ->default(true);

            $table->timestamps();

            $table->unique([
                'subscription_plan_id',
                'billing_period'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_prices');
    }
};