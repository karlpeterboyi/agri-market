<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_bookings', function (Blueprint $table) {

            $table->date('booking_date')->nullable()->after('completed_date');

            $table->time('booking_time')->nullable()->after('booking_date');

            $table->string('booking_reference')->unique()->nullable()->after('provider_id');

            $table->string('region')->nullable();

            $table->string('district')->nullable();

            $table->text('farm_location')->nullable();

            $table->decimal('quantity',10,2)->nullable();

            $table->string('unit')->nullable();

            $table->decimal('total_price',15,2)->nullable();

            $table->text('notes')->nullable();

        });
    }

    public function down(): void
    {
        Schema::table('service_bookings', function (Blueprint $table) {

            $table->dropColumn([
                'booking_reference',
                'booking_date',
                'booking_time',
                'region',
                'district',
                'farm_location',
                'quantity',
                'unit',
                'total_price',
                'notes',
            ]);

        });
    }
};