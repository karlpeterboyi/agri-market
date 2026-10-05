<?php

namespace Database\Seeders;

use App\Models\AgriculturalStatistic;
use App\Models\GovernmentAnnouncement;
use App\Models\SubsidyProgram;
use App\Models\User;
use Illuminate\Database\Seeder;

class GovernmentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();

        // Announcements
        $announcements = [
            [
                'title' => 'National Fertilizer Subsidy Programme 2026/27 Now Open',
                'category' => 'subsidy',
                'priority' => 'high',
                'summary' => 'Farmers can now apply for subsidised fertiliser for the 2026/27 season.',
                'source_organisation' => 'Ministry of Agriculture',
                'region' => null,
                'is_published' => true,
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Morogoro LGA – Free Soil Testing Campaign',
                'category' => 'training',
                'priority' => 'normal',
                'summary' => 'Free soil testing for smallholder farmers in Mvomero and Kilosa districts.',
                'source_organisation' => 'Morogoro Regional Secretariat',
                'region' => 'Morogoro',
                'is_published' => true,
                'published_at' => now()->subDays(7),
            ],
            [
                'title' => 'Updated Phytosanitary Requirements for Export Crops',
                'category' => 'regulation',
                'priority' => 'high',
                'summary' => 'New SPS requirements effective from October 2026 for coffee, cashew and horticulture exports.',
                'source_organisation' => 'Tanzania Plant Health Authority',
                'region' => null,
                'is_published' => true,
                'published_at' => now()->subDays(1),
            ],
        ];

        foreach ($announcements as $data) {
            GovernmentAnnouncement::firstOrCreate(
                ['title' => $data['title']],
                array_merge($data, ['created_by' => $admin?->id])
            );
        }

        // Subsidy programmes
        SubsidyProgram::firstOrCreate(
            ['code' => 'FERT-2026'],
            [
                'name' => 'National Fertilizer Subsidy 2026/27',
                'program_type' => 'fertilizer',
                'description' => 'Subsidised NPK and Urea for registered smallholder farmers.',
                'implementing_agency' => 'Ministry of Agriculture',
                'unit_value' => 25000,
                'unit_label' => 'bag (50kg)',
                'max_units_per_farmer' => 4,
                'target_regions' => ['Morogoro', 'Mbeya', 'Iringa', 'Ruvuma', 'Rukwa'],
                'target_crops' => ['Maize', 'Rice', 'Sunflower'],
                'application_start' => now()->subDays(10),
                'application_end' => now()->addMonths(2),
                'status' => 'open',
                'is_active' => true,
                'created_by' => $admin?->id,
            ]
        );

        SubsidyProgram::firstOrCreate(
            ['code' => 'SEED-MAIZE-26'],
            [
                'name' => 'Improved Maize Seed Support',
                'program_type' => 'seed',
                'description' => 'Certified maize seed voucher for priority districts.',
                'implementing_agency' => 'ASA / Ministry of Agriculture',
                'unit_value' => 15000,
                'unit_label' => 'kg',
                'max_units_per_farmer' => 10,
                'target_crops' => ['Maize'],
                'application_start' => now()->subDays(5),
                'application_end' => now()->addMonth(),
                'status' => 'open',
                'is_active' => true,
                'created_by' => $admin?->id,
            ]
        );

        // Sample statistics
        $stats = [
            ['indicator_code' => 'maize_production_mt', 'indicator_name' => 'Maize Production', 'category' => 'production', 'region' => null, 'year' => 2024, 'value' => 6700000, 'unit' => 'MT', 'source' => 'NBS / MoA'],
            ['indicator_code' => 'maize_production_mt', 'indicator_name' => 'Maize Production', 'category' => 'production', 'region' => null, 'year' => 2025, 'value' => 7100000, 'unit' => 'MT', 'source' => 'NBS / MoA'],
            ['indicator_code' => 'rice_production_mt', 'indicator_name' => 'Rice Production', 'category' => 'production', 'region' => null, 'year' => 2025, 'value' => 2400000, 'unit' => 'MT', 'source' => 'NBS / MoA'],
            ['indicator_code' => 'food_insecurity_pct', 'indicator_name' => 'Population Facing Food Insecurity', 'category' => 'food_security', 'region' => null, 'year' => 2025, 'value' => 12.4, 'unit' => '%', 'source' => 'WFP / MoA'],
            ['indicator_code' => 'fertilizer_price_npk', 'indicator_name' => 'NPK Average Price', 'category' => 'prices', 'region' => null, 'year' => 2025, 'value' => 85000, 'unit' => 'TZS/50kg', 'source' => 'MoA Market Info'],
            ['indicator_code' => 'maize_price_wholesale', 'indicator_name' => 'Maize Wholesale Price', 'category' => 'prices', 'region' => 'Morogoro', 'year' => 2025, 'value' => 720, 'unit' => 'TZS/kg', 'source' => 'MIT'],
        ];

        foreach ($stats as $s) {
            AgriculturalStatistic::firstOrCreate(
                [
                    'indicator_code' => $s['indicator_code'],
                    'region' => $s['region'],
                    'year' => $s['year'],
                    'month' => null,
                ],
                $s
            );
        }
    }
}
