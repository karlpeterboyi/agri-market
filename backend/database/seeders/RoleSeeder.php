<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [

            /*
            |--------------------------------------------------------------------------
            | System
            |--------------------------------------------------------------------------
            */

            'System Administrator',
            'Platform Administrator',
            'Auditor',

            /*
            |--------------------------------------------------------------------------
            | Marketplace
            |--------------------------------------------------------------------------
            */

            'Farmer',
            'Buyer',
            'Trader',
            'Exporter',
            'Aggregator',

            /*
            |--------------------------------------------------------------------------
            | Logistics
            |--------------------------------------------------------------------------
            */

            'Transport Provider',
            'Warehouse Manager',
            'Warehouse Staff',
            'Logistics Manager',

            /*
            |--------------------------------------------------------------------------
            | Finance
            |--------------------------------------------------------------------------
            */

            'Institution Officer',
            'Loan Officer',
            'Credit Officer',
            'Finance Officer',
            'Institution Manager',

            /*
            |--------------------------------------------------------------------------
            | Research & Extension
            |--------------------------------------------------------------------------
            */

            'Researcher',
            'University',
            'Extension Officer',
            'Veterinarian',
            'Agronomist',

            /*
            |--------------------------------------------------------------------------
            | Machinery
            |--------------------------------------------------------------------------
            */

            'Equipment Owner',
            'Equipment Operator',

            /*
            |--------------------------------------------------------------------------
            | Services
            |--------------------------------------------------------------------------
            */

            'Service Provider',
            'Input Supplier',

        ];

        foreach ($roles as $role) {

            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);

            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'sanctum',
            ]);

        }
    }
}