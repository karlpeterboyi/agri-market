<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Farm;
use App\Models\FieldBlock;
use App\Models\FarmWarehouse;
use App\Models\User;

class FarmSeeder extends Seeder
{
    public function run(): void
    {
        $farmers = User::whereIn('role', ['farmer', 'admin'])->get();

        $demoFarms = [
            [
                'name' => 'Mwalimu Family Farm',
                'region' => 'Morogoro',
                'district' => 'Mvomero',
                'farm_type' => 'mixed',
                'total_area_hectares' => 12.5,
                'cultivated_area_hectares' => 9.0,
                'irrigated_area_hectares' => 2.5,
            ],
            [
                'name' => 'Aisha Horticulture Garden',
                'region' => 'Arusha',
                'district' => 'Arusha Rural',
                'farm_type' => 'horticulture',
                'total_area_hectares' => 3.2,
                'cultivated_area_hectares' => 2.8,
                'irrigated_area_hectares' => 2.0,
            ],
            [
                'name' => 'Kilimo Bora Estate',
                'region' => 'Mbeya',
                'district' => 'Mbeya Rural',
                'farm_type' => 'crop',
                'total_area_hectares' => 45.0,
                'cultivated_area_hectares' => 38.0,
                'irrigated_area_hectares' => 5.0,
            ],
        ];

        foreach ($farmers as $index => $user) {
            $demo = $demoFarms[$index % count($demoFarms)];

            $farm = Farm::firstOrCreate(
                [
                    'owner_id' => $user->id,
                    'name' => $demo['name'] . ' (' . $user->name . ')',
                ],
                [
                    'farm_type' => $demo['farm_type'],
                    'ownership_type' => 'individual',
                    'country' => 'Tanzania',
                    'region' => $demo['region'],
                    'district' => $demo['district'],
                    'total_area_hectares' => $demo['total_area_hectares'],
                    'cultivated_area_hectares' => $demo['cultivated_area_hectares'],
                    'irrigated_area_hectares' => $demo['irrigated_area_hectares'],
                    'status' => 'active',
                    'organisation_id' => $user->organisations()->first()?->id,
                ]
            );

            // Create sample field blocks
            if ($farm->fieldBlocks()->count() === 0) {
                FieldBlock::create([
                    'farm_id' => $farm->id,
                    'name' => 'Block A – Main Field',
                    'boundary' => [],
                    'area_hectares' => round($demo['cultivated_area_hectares'] * 0.6, 2),
                    'status' => 'active',
                ]);

                FieldBlock::create([
                    'farm_id' => $farm->id,
                    'name' => 'Block B – Secondary',
                    'boundary' => [],
                    'area_hectares' => round($demo['cultivated_area_hectares'] * 0.4, 2),
                    'status' => 'active',
                ]);
            }

            // Create a main warehouse
            if ($farm->warehouses()->count() === 0) {
                FarmWarehouse::create([
                    'farm_id' => $farm->id,
                    'name' => 'Main Store',
                    'type' => 'main',
                    'location' => $demo['district'],
                    'active' => true,
                    'organisation_id' => $farm->organisation_id,
                ]);
            }
        }
    }
}
