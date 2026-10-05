<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [

            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */

            'dashboard.view',

            /*
            |--------------------------------------------------------------------------
            | Users
            |--------------------------------------------------------------------------
            */

            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            /*
            |--------------------------------------------------------------------------
            | Marketplace
            |--------------------------------------------------------------------------
            */

            'marketplace.view',
            'marketplace.manage',

            'products.view',
            'products.create',
            'products.update',
            'products.delete',

            'livestock.view',
            'livestock.create',
            'livestock.update',
            'livestock.delete',

            'machinery.view',
            'machinery.create',
            'machinery.update',
            'machinery.delete',

            'offers.create',
            'offers.accept',
            'offers.reject',

            'orders.view',
            'orders.manage',

            /*
            |--------------------------------------------------------------------------
            | Logistics
            |--------------------------------------------------------------------------
            */

            'logistics.view',
            'logistics.manage',

            'shipments.create',
            'shipments.manage',

            'warehouses.manage',

            /*
            |--------------------------------------------------------------------------
            | Payments
            |--------------------------------------------------------------------------
            */

            'payments.view',
            'payments.create',
            'payments.refund',

            /*
            |--------------------------------------------------------------------------
            | Finance
            |--------------------------------------------------------------------------
            */

            'finance.dashboard.view',

            'finance.loan-products.view',
            'finance.loan-products.manage',

            'finance.loan.create',
            'finance.loan.view-own',
            'finance.loan.view-all',

            'finance.loan.review',
            'finance.loan.approve',
            'finance.loan.reject',
            'finance.loan.disburse',

            'finance.repayments.view',
            'finance.repayments.manage',

            'finance.credit-score.view',
            'finance.credit-score.recalculate',

            'finance.reports.view',

            'finance.audit.view',

            'finance.institutions.manage',

            /*
            |--------------------------------------------------------------------------
            | Subscription
            |--------------------------------------------------------------------------
            */

            'subscriptions.view',
            'subscriptions.manage',

            /*
            |--------------------------------------------------------------------------
            | Research
            |--------------------------------------------------------------------------
            */

            'research.view',
            'research.publish',
            'research.manage',

            /*
            |--------------------------------------------------------------------------
            | Universities
            |--------------------------------------------------------------------------
            */

            'universities.view',
            'universities.manage',

            /*
            |--------------------------------------------------------------------------
            | Extension Services
            |--------------------------------------------------------------------------
            */

            'extension.view',
            'extension.manage',

            /*
            |--------------------------------------------------------------------------
            | Veterinary
            |--------------------------------------------------------------------------
            */

            'veterinary.view',
            'veterinary.manage',

            /*
            |--------------------------------------------------------------------------
            | Reports
            |--------------------------------------------------------------------------
            */

            'reports.view',

            /*
            |--------------------------------------------------------------------------
            | System
            |--------------------------------------------------------------------------
            */

            'roles.manage',
            'permissions.manage',
            'settings.manage',
        ];

        foreach ($permissions as $permission) {

            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);

        }

        /*
        |--------------------------------------------------------------------------
        | Role Assignments
        |--------------------------------------------------------------------------
        */

        $admin = Role::findByName('System Administrator');

        $admin->givePermissionTo(Permission::all());

        Role::findByName('Platform Administrator')
            ->givePermissionTo(Permission::all());

        Role::findByName('Farmer')->givePermissionTo([

            'dashboard.view',

            'marketplace.view',

            'products.view',
            'products.create',
            'products.update',

            'livestock.view',
            'livestock.create',
            'livestock.update',

            'machinery.view',

            'offers.create',

            'orders.view',

            'finance.loan.create',
            'finance.loan.view-own',

            'finance.credit-score.view',

            'subscriptions.view',

        ]);

        Role::findByName('Buyer')->givePermissionTo([

            'dashboard.view',

            'marketplace.view',

            'offers.create',

            'orders.view',

            'payments.create',

        ]);

        Role::findByName('Loan Officer')->givePermissionTo([

            'finance.dashboard.view',

            'finance.loan.review',

            'finance.loan.view-all',

            'finance.repayments.view',

        ]);

        Role::findByName('Credit Officer')->givePermissionTo([

            'finance.loan.approve',

            'finance.loan.reject',

            'finance.credit-score.view',

            'finance.credit-score.recalculate',

        ]);

        Role::findByName('Finance Officer')->givePermissionTo([

            'finance.loan.disburse',

            'finance.repayments.manage',

            'finance.reports.view',

        ]);

        Role::findByName('Institution Manager')->givePermissionTo([

            'finance.dashboard.view',

            'finance.loan.view-all',

            'finance.loan.review',

            'finance.loan.approve',

            'finance.loan.reject',

            'finance.loan.disburse',

            'finance.reports.view',

            'finance.audit.view',

            'finance.institutions.manage',

        ]);

        Role::findByName('Auditor')->givePermissionTo([

            'finance.audit.view',

            'finance.reports.view',

        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}