<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('farm_visits', function (Blueprint $table) {

    $table->id();

    $table->foreignId('advisory_request_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->dateTime('scheduled_at');

    $table->decimal('visit_fee',12,2);

    $table->string('status')
        ->default('scheduled');

    $table->text('recommendations')
        ->nullable();

    $table->timestamps();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farm_visits');
    }
};
