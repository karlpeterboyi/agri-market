<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            $table->foreignId('organisation_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->morphs('documentable');

            $table->string('name');

            $table->string('original_name');

            $table->string('disk')->default('public');

            $table->string('path');

            $table->string('mime_type');

            $table->unsignedBigInteger('size');

            $table->string('category')->nullable();

            $table->string('visibility')->default('private');

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->softDeletes();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};