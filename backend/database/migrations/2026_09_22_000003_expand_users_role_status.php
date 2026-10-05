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
            // ignore
        }

        try {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_status_check');
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_status_check CHECK (status::text = ANY (ARRAY[
                'active'::text,
                'pending'::text,
                'inactive'::text,
                'suspended'::text,
                'banned'::text
            ]))");
        } catch (\Throwable $e) {
            // ignore
        }
    }

    public function down(): void
    {
    }
};
