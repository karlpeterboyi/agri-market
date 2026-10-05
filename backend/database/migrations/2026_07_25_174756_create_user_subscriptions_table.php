<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('subscription_price_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('starts_at');

            $table->date('expires_at');

            $table->enum('status', [
                'active',
                'expired',
                'cancelled',
                'pending'
            ])->default('pending');

            $table->boolean('auto_renew')
                ->default(true);

            $table->string('payment_reference')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};