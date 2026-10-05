<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_scores', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('overall_score');

            $table->integer('marketplace_score')->default(0);

            $table->integer('repayment_score')->default(0);

            $table->integer('profile_score')->default(0);

            $table->integer('reputation_score')->default(0);

            $table->integer('subscription_score')->default(0);

            $table->integer('activity_score')->default(0);

            $table->string('risk_level');

            $table->timestamp('calculated_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_scores');
    }
};