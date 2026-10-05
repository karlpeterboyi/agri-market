<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category')->nullable(); // crop_production, livestock, finance, marketing, climate, digital
            $table->string('level')->default('beginner'); // beginner, intermediate, advanced
            $table->string('language')->default('sw'); // sw, en
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->string('video_url')->nullable();
            $table->longText('content')->nullable(); // markdown / html
            $table->json('learning_outcomes')->nullable();
            $table->json('modules')->nullable(); // list of module titles
            $table->boolean('is_free')->default(true);
            $table->decimal('price', 12, 2)->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('featured')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('research_institution_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('enrollments_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('enrolled'); // enrolled, in_progress, completed, dropped
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->string('certificate_code')->nullable()->unique();
            $table->timestamps();

            $table->unique(['training_course_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_enrollments');
        Schema::dropIfExists('training_courses');
    }
};
