<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::statement('ALTER TABLE logistics_requests DROP CONSTRAINT IF EXISTS logistics_requests_listing_id_foreign');
        } catch (\Throwable $e) {
        }
        try {
            DB::statement('ALTER TABLE logistics_requests ALTER COLUMN listing_id DROP NOT NULL');
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
    }
};
