<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FinancialInstitutionCategory;

class FinancialInstitutionCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [

            [
                'name' => 'Commercial Bank',
                'slug' => 'commercial-bank',
                'description' => 'Licensed commercial banking institutions',
            ],

            [
                'name' => 'Microfinance Institution',
                'slug' => 'microfinance',
                'description' => 'Licensed microfinance institutions',
            ],

            [
                'name' => 'SACCOS',
                'slug' => 'saccos',
                'description' => 'Savings and Credit Cooperative Societies',
            ],

            [
                'name' => 'Development Finance Institution',
                'slug' => 'development-finance',
                'description' => 'Development finance organisations',
            ],

            [
                'name' => 'Agricultural Finance',
                'slug' => 'agricultural-finance',
                'description' => 'Agriculture-focused finance providers',
            ],

            [
                'name' => 'Government Fund',
                'slug' => 'government-fund',
                'description' => 'Government financing programmes',
            ],

            [
                'name' => 'NGO Financing',
                'slug' => 'ngo-financing',
                'description' => 'NGOs providing agricultural finance',
            ],

        ];

        foreach ($categories as $category) {

            FinancialInstitutionCategory::updateOrCreate(

                [
                    'slug' => $category['slug'],
                ],

                $category

            );
        }
    }
}