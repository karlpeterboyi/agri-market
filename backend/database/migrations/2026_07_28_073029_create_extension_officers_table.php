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
        Schema::create('extension_officers', function (Blueprint $table) {

    $table->id();

    $table->foreignId('user_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->foreignId('research_institution_id')
        ->nullable()
        ->constrained()
        ->nullOnDelete();

    $table->string('profession');

    $table->string('specialization');

    $table->string('registration_number')->nullable();

    $table->string('phone');

    $table->string('region');

    $table->string('district');

    $table->decimal('latitude',10,7)->nullable();

    $table->decimal('longitude',10,7)->nullable();

    $table->decimal('consultation_fee',12,2)
        ->default(0);

    $table->boolean('available')
        ->default(true);

    $table->boolean('verified')
        ->default(false);

    $table->timestamps();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extension_officers');
    }
};
