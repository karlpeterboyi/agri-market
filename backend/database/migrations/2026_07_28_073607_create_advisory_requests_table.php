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
        Schema::create('advisory_requests', function (Blueprint $table) {

    $table->id();

    $table->foreignId('farmer_id')
        ->constrained('users')
        ->cascadeOnDelete();

    $table->foreignId('extension_officer_id')
        ->nullable()
        ->constrained()
        ->nullOnDelete();

    $table->string('category');

    $table->string('subject');

    $table->longText('description');

    $table->string('priority')
        ->default('normal');

    $table->string('status')
        ->default('pending');

    $table->timestamps();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advisory_requests');
    }
};
