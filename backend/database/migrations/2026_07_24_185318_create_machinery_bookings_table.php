<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machinery_bookings', function (Blueprint $table) {

            $table->id();

            $table->foreignId('machinery_listing_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('owner_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->date('start_date');

            $table->date('end_date');

            $table->decimal('total_price',15,2);

            $table->string('payment_status')
                ->default('pending');

            $table->string('status')
                ->default('pending');

            $table->boolean('operator_required')
                ->default(false);

            $table->text('notes')
                ->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machinery_bookings');
    }
};