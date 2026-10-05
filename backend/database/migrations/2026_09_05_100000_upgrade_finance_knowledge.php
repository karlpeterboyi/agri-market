<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Expand allowed user roles (PostgreSQL CHECK)
        try {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        } catch (\Throwable $e) {
        }
        try {
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role::text = ANY (ARRAY[
                'farmer'::text, 'buyer'::text, 'processor'::text, 'provider'::text,
                'agrodealer'::text, 'transporter'::text, 'admin'::text,
                'financier'::text, 'educator'::text
            ]))");
        } catch (\Throwable $e) {
            // SQLite or already applied
        }

        Schema::table('financial_institutions', function (Blueprint $table) {
            if (!Schema::hasColumn('financial_institutions', 'owner_user_id')) {
                $table->foreignId('owner_user_id')->nullable()->after('id')
                    ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('financial_institutions', 'institution_type')) {
                $table->string('institution_type')->default('bank')->after('name');
                // bank | mfi | sacco | insurer | fintech | government
            }
        });

        Schema::table('research_institutions', function (Blueprint $table) {
            if (!Schema::hasColumn('research_institutions', 'owner_user_id')) {
                $table->foreignId('owner_user_id')->nullable()->after('id')
                    ->constrained('users')->nullOnDelete();
            }
        });

        if (!Schema::hasTable('insurance_products')) {
            Schema::create('insurance_products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('financial_institution_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('insurance_type')->default('crop');
                // crop | livestock | equipment | weather | life | multi
                $table->text('description')->nullable();
                $table->decimal('premium_rate', 8, 4)->nullable(); // e.g. 0.05 = 5%
                $table->decimal('minimum_premium', 15, 2)->nullable();
                $table->decimal('maximum_cover', 15, 2)->nullable();
                $table->unsignedInteger('coverage_period_months')->nullable();
                $table->json('covered_risks')->nullable();
                $table->json('eligibility')->nullable();
                $table->json('required_documents')->nullable();
                $table->boolean('online_application')->default(true);
                $table->boolean('featured')->default(false);
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('insurance_applications')) {
            Schema::create('insurance_applications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('insurance_product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('applicant_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('sum_insured', 15, 2);
                $table->decimal('premium_amount', 15, 2)->nullable();
                $table->string('status')->default('draft');
                // draft | submitted | under_review | approved | rejected | active | expired | claimed
                $table->json('farm_details')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('decided_at')->nullable();
                $table->timestamps();
            });
        }

        // Course delivery formats
        Schema::table('training_courses', function (Blueprint $table) {
            if (!Schema::hasColumn('training_courses', 'format')) {
                $table->string('format')->default('self_paced')->after('level');
                // self_paced | video | live | hybrid | downloadable
            }
            if (!Schema::hasColumn('training_courses', 'certificate_enabled')) {
                $table->boolean('certificate_enabled')->default(true);
            }
            if (!Schema::hasColumn('training_courses', 'max_enrollments')) {
                $table->unsignedInteger('max_enrollments')->nullable();
            }
            if (!Schema::hasColumn('training_courses', 'starts_at')) {
                $table->timestamp('starts_at')->nullable();
            }
            if (!Schema::hasColumn('training_courses', 'ends_at')) {
                $table->timestamp('ends_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_applications');
        Schema::dropIfExists('insurance_products');
    }
};
