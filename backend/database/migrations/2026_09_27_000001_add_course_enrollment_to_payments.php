<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'course_enrollment_id')) {
                $table->unsignedBigInteger('course_enrollment_id')->nullable()->after('user_subscription_id');
                $table->index('course_enrollment_id');
            }
            if (!Schema::hasColumn('payments', 'payment_type')) {
                $table->string('payment_type')->nullable()->after('status');
            }
            if (!Schema::hasColumn('payments', 'meta')) {
                $table->json('meta')->nullable();
            }
        });

        if (Schema::hasTable('course_enrollments') && !Schema::hasColumn('course_enrollments', 'payment_status')) {
            Schema::table('course_enrollments', function (Blueprint $table) {
                $table->string('payment_status')->default('not_required')->after('status'); // not_required|pending|paid|failed
                $table->decimal('amount_paid', 12, 2)->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'course_enrollment_id')) {
                $table->dropColumn('course_enrollment_id');
            }
        });
    }
};
