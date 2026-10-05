<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_subscriptions')) {
            return;
        }
        Schema::table('user_subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('user_subscriptions', 'meta')) {
                $table->json('meta')->nullable();
            }
        });
        // nullable price for sandbox without plan seed
        try {
            DB::statement('ALTER TABLE user_subscriptions ALTER COLUMN subscription_price_id DROP NOT NULL');
        } catch (\Throwable $e) {
            // sqlite or already nullable
        }
    }

    public function down(): void
    {
    }
};
