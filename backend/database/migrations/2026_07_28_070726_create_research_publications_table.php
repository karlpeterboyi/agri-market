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
        Schema::create('research_publications', function (Blueprint $table) {

    $table->id();

    $table->foreignId('research_institution_id')
          ->constrained()
          ->cascadeOnDelete();

    $table->foreignId('researcher_id')
          ->constrained()
          ->cascadeOnDelete();

    $table->string('title');

    $table->text('abstract');

    $table->longText('content');

    $table->string('category');

    $table->string('crop')->nullable();

    $table->string('livestock')->nullable();

    $table->string('file')->nullable();

    $table->date('published_on');

    $table->boolean('featured')->default(false);

    $table->timestamps();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research_publications');
    }
};
