<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_attachments', function (Blueprint $table) {

            $table->id();

            $table->foreignId('farm_activity_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('type',[
                'photo',
                'video',
                'audio',
                'document',
            ]);

            $table->string('file_path');

            $table->string('mime_type')->nullable();

            $table->bigInteger('file_size')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_attachments');
    }
};