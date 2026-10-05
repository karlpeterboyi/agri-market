<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {

            $table->string('provider_reference')
                ->nullable()
                ->after('transaction_ref');

            $table->string('phone_number')
                ->nullable()
                ->after('provider_reference');

            $table->timestamp('paid_at')
                ->nullable()
                ->after('phone_number');

        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {

            $table->dropColumn([
                'provider_reference',
                'phone_number',
                'paid_at'
            ]);

        });
    }
};