<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\FinancialInstitution;
use App\Models\FinancialInstitutionCategory;

class FinancialInstitutionSeeder extends Seeder
{
    public function run(): void
    {
        $institutions = [

            // Commercial Banks

            [
                'category' => 'commercial-bank',
                'name' => 'CRDB Bank Plc',
                'short_name' => 'CRDB',
                'website' => 'https://www.crdbbank.co.tz',
                'head_office' => 'Dar es Salaam',
                'phone' => '+255222194400',
                'offers_online_application' => true,
                'verified' => true,
                'featured' => true,
            ],

            [
                'category' => 'commercial-bank',
                'name' => 'NMB Bank Plc',
                'short_name' => 'NMB',
                'website' => 'https://www.nmbbank.co.tz',
                'head_office' => 'Dar es Salaam',
                'offers_online_application' => true,
                'verified' => true,
            ],

            [
                'category' => 'commercial-bank',
                'name' => 'NBC Bank',
                'short_name' => 'NBC',
                'website' => 'https://www.nbc.co.tz',
                'head_office' => 'Dar es Salaam',
                'offers_online_application' => true,
                'verified' => true,
            ],

            // Agricultural Finance

            [
                'category' => 'agricultural-finance',
                'name' => 'Tanzania Agricultural Development Bank',
                'short_name' => 'TADB',
                'website' => 'https://www.tadb.co.tz',
                'head_office' => 'Dodoma',
                'offers_online_application' => true,
                'verified' => true,
                'featured' => true,
            ],

            [
                'category' => 'agricultural-finance',
                'name' => 'PASS Trust',
                'short_name' => 'PASS',
                'website' => 'https://www.pass.or.tz',
                'head_office' => 'Dar es Salaam',
                'offers_online_application' => true,
                'verified' => true,
            ],

            // Microfinance

            [
                'category' => 'microfinance',
                'name' => 'FINCA Tanzania',
                'short_name' => 'FINCA',
                'website' => 'https://www.finca.co.tz',
                'head_office' => 'Dar es Salaam',
                'offers_online_application' => true,
                'verified' => true,
            ],

            [
                'category' => 'microfinance',
                'name' => 'VisionFund Tanzania',
                'short_name' => 'VisionFund',
                'website' => 'https://www.visionfund.or.tz',
                'head_office' => 'Arusha',
                'offers_online_application' => true,
                'verified' => true,
            ],

            [
                'category' => 'microfinance',
                'name' => 'BRAC Tanzania Finance',
                'short_name' => 'BRAC',
                'website' => 'https://www.brac.net',
                'head_office' => 'Dar es Salaam',
                'offers_online_application' => true,
                'verified' => true,
            ],

            // Development Finance

            [
                'category' => 'development-finance',
                'name' => 'African Development Bank',
                'short_name' => 'AfDB',
                'website' => 'https://www.afdb.org',
                'head_office' => 'Abidjan',
                'verified' => true,
            ],

            // Government

            [
                'category' => 'government-fund',
                'name' => 'Small Industries Development Organization',
                'short_name' => 'SIDO',
                'website' => 'https://www.sido.go.tz',
                'head_office' => 'Dar es Salaam',
                'verified' => true,
            ],

        ];

        foreach ($institutions as $item) {

            $category = FinancialInstitutionCategory::where(
                'slug',
                $item['category']
            )->first();

            if (!$category) {
                continue;
            }

            FinancialInstitution::updateOrCreate(

                [
                    'slug' => Str::slug($item['name']),
                ],

                [

                    'financial_institution_category_id' => $category->id,

                    'name' => $item['name'],

                    'slug' => Str::slug($item['name']),

                    'short_name' => $item['short_name'],

                    'website' => $item['website'],

                    'head_office' => $item['head_office'],

                    'phone' => $item['phone'] ?? null,

                    'offers_online_application' =>
                        $item['offers_online_application'] ?? false,

                    'verified' =>
                        $item['verified'] ?? false,

                    'featured' =>
                        $item['featured'] ?? false,

                    'active' => true,

                    'country' => 'Tanzania',

                    'regions' => null,

                ]

            );
        }
    }
}