<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Official announcements from ministries / LGAs / agencies
        Schema::create('government_announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->nullable(); // subsidy, regulation, training, weather_alert, market, general
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->text('summary')->nullable();
            $table->longText('body')->nullable();
            $table->string('source_organisation')->nullable(); // e.g. Ministry of Agriculture, LGA Morogoro
            $table->string('region')->nullable(); // null = national
            $table->string('district')->nullable();
            $table->date('published_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('attachment_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Subsidy / support programmes
        Schema::create('subsidy_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('program_type'); // fertilizer, seed, equipment, credit_guarantee, insurance_premium, other
            $table->text('description')->nullable();
            $table->string('implementing_agency')->nullable();
            $table->decimal('budget_total', 18, 2)->nullable();
            $table->decimal('unit_value', 12, 2)->nullable(); // e.g. subsidy per bag / per acre
            $table->string('unit_label')->nullable(); // bag, acre, farmer
            $table->unsignedInteger('max_units_per_farmer')->nullable();
            $table->json('eligibility_rules')->nullable();
            $table->json('required_documents')->nullable();
            $table->json('target_regions')->nullable();
            $table->json('target_crops')->nullable();
            $table->date('application_start')->nullable();
            $table->date('application_end')->nullable();
            $table->date('season_start')->nullable();
            $table->date('season_end')->nullable();
            $table->string('status')->default('draft'); // draft, open, closed, completed
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Farmer applications to subsidy programmes
        Schema::create('subsidy_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number')->unique();
            $table->foreignId('subsidy_program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('farm_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('requested_units')->default(1);
            $table->decimal('requested_value', 12, 2)->nullable();
            $table->string('status')->default('draft'); // draft, submitted, under_review, approved, rejected, disbursed
            $table->text('purpose')->nullable();
            $table->json('applicant_data')->nullable(); // snapshot of farmer/farm info
            $table->text('review_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            $table->timestamps();
        });

        // Simple agricultural registrations / licences
        Schema::create('agricultural_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number')->unique();
            $table->string('registration_type'); // farm, trader, input_dealer, processor, exporter, cooperative
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('farm_id')->nullable()->constrained()->nullOnDelete();
            $table->string('business_name')->nullable();
            $table->string('region');
            $table->string('district');
            $table->string('ward')->nullable();
            $table->json('details')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected, suspended, expired
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Official statistics / food security indicators (simple key-value time series)
        Schema::create('agricultural_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('indicator_code'); // e.g. maize_production_mt, food_insecurity_pct
            $table->string('indicator_name');
            $table->string('category'); // production, prices, food_security, inputs, trade
            $table->string('region')->nullable(); // null = national
            $table->string('district')->nullable();
            $table->year('year');
            $table->unsignedTinyInteger('month')->nullable(); // null = annual
            $table->decimal('value', 18, 4);
            $table->string('unit')->nullable(); // MT, TZS/kg, %, ha
            $table->string('source')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['indicator_code', 'year', 'region']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agricultural_statistics');
        Schema::dropIfExists('agricultural_registrations');
        Schema::dropIfExists('subsidy_applications');
        Schema::dropIfExists('subsidy_programs');
        Schema::dropIfExists('government_announcements');
    }
};
