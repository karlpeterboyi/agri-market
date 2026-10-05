<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_updates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transport_assignment_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('status');

            $table->text('notes')->nullable();

            $table->decimal('latitude',10,7)->nullable();
            $table->decimal('longitude',10,7)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_updates');
    }
};