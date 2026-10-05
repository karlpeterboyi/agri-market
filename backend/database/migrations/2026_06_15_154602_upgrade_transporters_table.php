<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transporters', function (Blueprint $table) {

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('company_name')->nullable();

            $table->string('contact_person')->nullable();

            $table->string('phone')->nullable();

            $table->string('email')->nullable();

            $table->decimal('rating',3,2)
                ->default(0);

            $table->boolean('verified')
                ->default(false);

            $table->string('operating_region')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('transporters', function (Blueprint $table) {

            $table->dropColumn([
                'company_name',
                'contact_person',
                'phone',
                'email',
                'rating',
                'verified',
                'operating_region'
            ]);

            $table->dropConstrainedForeignId('user_id');
        });
    }
};