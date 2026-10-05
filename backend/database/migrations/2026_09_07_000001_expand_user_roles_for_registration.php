<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        try {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role::text = ANY (ARRAY[
                'admin'::text,
                'farmer'::text,
                'buyer'::text,
                'processor'::text,
                'provider'::text,
                'agrodealer'::text,
                'transporter'::text,
                'financier'::text,
                'educator'::text,
                'financial_institution'::text
            ]))");
        } catch (\Throwable $e) {
            // ignore if constraint name differs
        }
    }

    public function down(): void
    {
        // no-op
    }
};
