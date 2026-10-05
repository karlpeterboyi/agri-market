<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organisations', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            $table->string('name');

            $table->string('slug')->unique();

            $table->enum('type',[
                'individual',
                'cooperative',
                'company',
                'ngo',
                'government',
                'university',
                'research',
                'financial_institution',
                'input_supplier',
                'processor',
                'exporter',
                'warehouse_operator',
                'logistics_provider',
            ]);

            $table->string('registration_number')->nullable();

            $table->string('tax_number')->nullable();

            $table->string('email')->nullable();

            $table->string('phone')->nullable();

            $table->string('website')->nullable();

            $table->string('logo')->nullable();

            $table->text('description')->nullable();

            $table->string('country')->default('Tanzania');

            $table->string('region')->nullable();

            $table->string('district')->nullable();

            $table->string('ward')->nullable();

            $table->string('village')->nullable();

            $table->string('address')->nullable();

            $table->decimal('latitude',10,7)->nullable();

            $table->decimal('longitude',10,7)->nullable();

            $table->boolean('verified')->default(false);

            $table->boolean('active')->default(true);

            $table->json('settings')->nullable();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organisations');
    }
};