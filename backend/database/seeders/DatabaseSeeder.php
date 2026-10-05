<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CommodityCategorySeeder::class,
            CommoditySeeder::class,
            CropSeeder::class,
            LivestockCategorySeeder::class,
            MachineryCategorySeeder::class,
            MachineryBrandSeeder::class,
            MachineryModelSeeder::class,
            ServiceCategorySeeder::class,
            ServiceSeeder::class,
            ProviderProfileSeeder::class,
            ServicePackageSeeder::class,
            SubscriptionPlanSeeder::class,
            SubscriptionPriceSeeder::class,
            PlanFeatureSeeder::class,
            FinancialInstitutionCategorySeeder::class,
            FinancialInstitutionSeeder::class,
            LoanProductSeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            ResearchInstitutionSeeder::class,
            FarmSeeder::class,
            TrainingCourseSeeder::class,
            GovernmentSeeder::class,
            ProductListingSeeder::class,
            InputListingSeeder::class,
        ]);
    }
}