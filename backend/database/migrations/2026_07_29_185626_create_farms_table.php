<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farms', function (Blueprint $table) {

            $table->id();

            $table->foreignId('owner_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('name');

            $table->string('farm_code')->unique();

            $table->text('description')->nullable();

            $table->enum('farm_type', [
                'crop',
                'livestock',
                'mixed',
                'poultry',
                'aquaculture',
                'horticulture',
                'research',
                'demonstration',
                'plantation',
            ])->default('crop');

            $table->enum('ownership_type', [
                'individual',
                'family',
                'company',
                'cooperative',
                'government',
                'institution',
                'ngo',
            ])->default('individual');

            $table->string('country')->default('Tanzania');

            $table->string('region');

            $table->string('district');

            $table->string('ward')->nullable();

            $table->string('village')->nullable();

            $table->string('address')->nullable();

            $table->decimal('latitude', 10, 7)->nullable();

            $table->decimal('longitude', 10, 7)->nullable();

            $table->decimal('elevation', 8, 2)->nullable();

            $table->decimal('total_area_hectares', 12, 4)->default(0);

            $table->decimal('cultivated_area_hectares', 12, 4)->default(0);

            $table->decimal('irrigated_area_hectares', 12, 4)->default(0);

            $table->string('registration_number')->nullable();

            $table->string('certification')->nullable();

            $table->enum('status', [
                'draft',
                'active',
                'inactive',
                'archived',
            ])->default('active');

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->softDeletes();

            $table->index([
                'owner_id',
                'status',
            ]);

            $table->index([
                'region',
                'district',
            ]);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farms');
    }
};