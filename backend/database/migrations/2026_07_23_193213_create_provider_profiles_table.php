<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_profiles', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('business_name');

            $table->string('business_registration')->nullable();

            $table->string('tin')->nullable();

            $table->string('vrn')->nullable();

            $table->string('phone');

            $table->string('whatsapp')->nullable();

            $table->string('email')->nullable();

            $table->string('website')->nullable();

            $table->string('logo')->nullable();

            $table->string('cover_photo')->nullable();

            $table->text('about')->nullable();

            $table->integer('years_experience')->default(0);

            $table->json('service_regions')->nullable();

            $table->json('business_hours')->nullable();

            $table->json('certifications')->nullable();

            $table->boolean('verified')->default(false);

            $table->decimal('rating',3,2)->default(0);

            $table->unsignedInteger('reviews')->default(0);

            $table->unsignedInteger('completed_jobs')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_profiles');
    }
};