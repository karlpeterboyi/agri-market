<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\LoanProduct;
use App\Models\FinancialInstitution;

class LoanProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [

            [
                'institution' => 'Tanzania Agricultural Development Bank',
                'name' => 'Crop Production Loan',
                'loan_type' => 'crop',
                'minimum_amount' => 500000,
                'maximum_amount' => 500000000,
                'interest_rate' => 9.5,
                'minimum_duration_months' => 6,
                'maximum_duration_months' => 36,
                'requires_collateral' => true,
                'featured' => true,
            ],

            [
                'institution' => 'Tanzania Agricultural Development Bank',
                'name' => 'Tractor & Machinery Financing',
                'loan_type' => 'machinery',
                'minimum_amount' => 10000000,
                'maximum_amount' => 2000000000,
                'interest_rate' => 8.5,
                'minimum_duration_months' => 12,
                'maximum_duration_months' => 84,
                'requires_collateral' => true,
                'featured' => true,
            ],

            [
                'institution' => 'CRDB Bank Plc',
                'name' => 'Agribusiness Working Capital',
                'loan_type' => 'working_capital',
                'minimum_amount' => 1000000,
                'maximum_amount' => 1000000000,
                'interest_rate' => 14.0,
                'minimum_duration_months' => 3,
                'maximum_duration_months' => 60,
                'requires_collateral' => true,
            ],

            [
                'institution' => 'NMB Bank Plc',
                'name' => 'Livestock Development Loan',
                'loan_type' => 'livestock',
                'minimum_amount' => 1000000,
                'maximum_amount' => 300000000,
                'interest_rate' => 13.5,
                'minimum_duration_months' => 6,
                'maximum_duration_months' => 60,
                'requires_collateral' => true,
            ],

            [
                'institution' => 'FINCA Tanzania',
                'name' => 'Smallholder Farmer Loan',
                'loan_type' => 'crop',
                'minimum_amount' => 300000,
                'maximum_amount' => 20000000,
                'interest_rate' => 18.0,
                'minimum_duration_months' => 3,
                'maximum_duration_months' => 24,
                'requires_collateral' => false,
            ],

            [
                'institution' => 'VisionFund Tanzania',
                'name' => 'Women\'s Agribusiness Loan',
                'loan_type' => 'women',
                'minimum_amount' => 500000,
                'maximum_amount' => 50000000,
                'interest_rate' => 15.0,
                'minimum_duration_months' => 6,
                'maximum_duration_months' => 36,
                'requires_collateral' => false,
            ],

            [
                'institution' => 'PASS Trust',
                'name' => 'Youth Agribusiness Finance',
                'loan_type' => 'youth',
                'minimum_amount' => 500000,
                'maximum_amount' => 100000000,
                'interest_rate' => 10.0,
                'minimum_duration_months' => 6,
                'maximum_duration_months' => 48,
                'requires_collateral' => false,
            ],

        ];

        foreach ($products as $item) {

            $institution = FinancialInstitution::where(
                'name',
                $item['institution']
            )->first();

            if (!$institution) {
                continue;
            }

            LoanProduct::updateOrCreate(

                [
                    'slug' => Str::slug($item['name']),
                ],

                [

                    'financial_institution_id' => $institution->id,

                    'name' => $item['name'],

                    'slug' => Str::slug($item['name']),

                    'loan_type' => $item['loan_type'],

                    'description' =>
                        $item['name'] . ' offered through ' . $institution->short_name,

                    'minimum_amount' => $item['minimum_amount'],

                    'maximum_amount' => $item['maximum_amount'],

                    'interest_rate' => $item['interest_rate'],

                    'minimum_duration_months' => $item['minimum_duration_months'],

                    'maximum_duration_months' => $item['maximum_duration_months'],

                    'processing_fee' => 0,

                    'requires_collateral' => $item['requires_collateral'],

                    'online_application' => true,

                    'eligibility' => [
                        'Registered farmer',
                        'Valid National ID',
                        'Business or farm location',
                    ],

                    'required_documents' => [
                        'National ID',
                        'Farm ownership or lease document',
                        'Business plan',
                    ],

                    'featured' => $item['featured'] ?? false,

                    'active' => true,

                ]

            );
        }
    }
}