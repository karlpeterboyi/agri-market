<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_institutions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('financial_institution_category_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');

            $table->string('slug')->unique();

            $table->string('short_name')->nullable();

            $table->text('description')->nullable();

            $table->string('logo')->nullable();

            $table->string('website')->nullable();

            $table->string('email')->nullable();

            $table->string('phone')->nullable();

            $table->string('contact_person')->nullable();

            $table->string('head_office')->nullable();

            $table->string('country')->default('Tanzania');

            $table->json('regions')->nullable();

            $table->boolean('offers_online_application')->default(false);

            $table->boolean('verified')->default(false);

            $table->boolean('featured')->default(false);

            $table->boolean('active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_institutions');
    }
};