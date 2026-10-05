<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_bank_links')) {
            return;
        }

        Schema::create('user_bank_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('nmb');
            $table->string('bank_code')->default('NMB');
            $table->string('account_number');
            $table->string('account_name')->nullable();
            $table->string('nmb_account_id')->nullable();
            $table->string('nmb_customer_id')->nullable();
            $table->boolean('verified')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'account_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_bank_links');
    }
};
