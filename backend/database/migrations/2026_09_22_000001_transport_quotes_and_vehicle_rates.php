<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'price_per_km')) {
                $table->decimal('price_per_km', 12, 2)->default(0)->after('capacity');
            }
            if (!Schema::hasColumn('vehicles', 'price_per_mile')) {
                $table->decimal('price_per_mile', 12, 2)->nullable()->after('price_per_km');
            }
            if (!Schema::hasColumn('vehicles', 'base_latitude')) {
                $table->decimal('base_latitude', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'base_longitude')) {
                $table->decimal('base_longitude', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'base_region')) {
                $table->string('base_region')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'base_district')) {
                $table->string('base_district')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'name')) {
                $table->string('name')->nullable()->after('registration_number');
            }
            if (!Schema::hasColumn('vehicles', 'notes')) {
                $table->text('notes')->nullable();
            }
        });

        Schema::table('transporters', function (Blueprint $table) {
            if (!Schema::hasColumn('transporters', 'base_latitude')) {
                $table->decimal('base_latitude', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('transporters', 'base_longitude')) {
                $table->decimal('base_longitude', 10, 7)->nullable();
            }
        });

        if (!Schema::hasTable('transport_quotes')) {
            Schema::create('transport_quotes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('transporter_id')->constrained()->cascadeOnDelete();
                $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('distance_km', 12, 2);
                $table->decimal('price_per_km', 12, 2);
                $table->decimal('total_cost', 15, 2);
                $table->decimal('pickup_lat', 10, 7)->nullable();
                $table->decimal('pickup_lng', 10, 7)->nullable();
                $table->decimal('dropoff_lat', 10, 7)->nullable();
                $table->decimal('dropoff_lng', 10, 7)->nullable();
                $table->string('status')->default('suggested'); // suggested, selected, accepted, rejected
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_quotes');
    }
};
