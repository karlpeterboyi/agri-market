<?php

namespace Database\Seeders;

use App\Models\Crop;
use Illuminate\Database\Seeder;

class CropSeeder extends Seeder
{
    public function run(): void
    {
        $crops = [
            ['name' => 'Maize', 'scientific_name' => 'Zea mays', 'category' => 'cereal', 'description' => 'Staple cereal crop across Tanzania'],
            ['name' => 'Rice', 'scientific_name' => 'Oryza sativa', 'category' => 'cereal', 'description' => 'Paddy rice'],
            ['name' => 'Beans', 'scientific_name' => 'Phaseolus vulgaris', 'category' => 'legume', 'description' => 'Common beans'],
            ['name' => 'Cassava', 'scientific_name' => 'Manihot esculenta', 'category' => 'root', 'description' => 'Cassava roots'],
            ['name' => 'Tomato', 'scientific_name' => 'Solanum lycopersicum', 'category' => 'vegetable', 'description' => 'Fresh market tomato'],
            ['name' => 'Onion', 'scientific_name' => 'Allium cepa', 'category' => 'vegetable', 'description' => 'Bulb onion'],
            ['name' => 'Sunflower', 'scientific_name' => 'Helianthus annuus', 'category' => 'oilseed', 'description' => 'Oilseed sunflower'],
            ['name' => 'Coffee', 'scientific_name' => 'Coffea arabica', 'category' => 'cash', 'description' => 'Arabica coffee'],
            ['name' => 'Banana', 'scientific_name' => 'Musa spp.', 'category' => 'fruit', 'description' => 'Cooking and dessert bananas'],
            ['name' => 'Groundnuts', 'scientific_name' => 'Arachis hypogaea', 'category' => 'legume', 'description' => 'Peanuts'],
            ['name' => 'Sorghum', 'scientific_name' => 'Sorghum bicolor', 'category' => 'cereal', 'description' => 'Drought-tolerant cereal'],
            ['name' => 'Potato', 'scientific_name' => 'Solanum tuberosum', 'category' => 'root', 'description' => 'Irish potato'],
        ];

        foreach ($crops as $c) {
            Crop::firstOrCreate(
                ['name' => $c['name']],
                array_merge($c, ['active' => true])
            );
        }
    }
}
