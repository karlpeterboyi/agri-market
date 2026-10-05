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
        Schema::create('research_institutions', function (Blueprint $table) {

    $table->id();

    $table->string('name');

    $table->string('acronym')->nullable();

    $table->string('institution_type');

    $table->text('description')->nullable();

    $table->string('website')->nullable();

    $table->string('email');

    $table->string('phone')->nullable();

    $table->string('region');

    $table->string('district')->nullable();

    $table->string('address')->nullable();

    $table->boolean('verified')->default(false);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research_institutions');
    }
};
