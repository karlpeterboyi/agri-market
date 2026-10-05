<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Commodity;
use App\Models\CommodityCategory;

class CommoditySeeder extends Seeder
{
    public function run(): void
    {
        $commodities = [
            // Cereals
            ['name' => 'Maize', 'category' => 'Cereals', 'description' => 'Dry maize grain (white & yellow varieties common in Tanzania)'],
            ['name' => 'Rice', 'category' => 'Cereals', 'description' => 'Paddy and milled rice (including aromatic varieties)'],
            ['name' => 'Sorghum', 'category' => 'Cereals', 'description' => 'Sorghum grain'],
            ['name' => 'Millet', 'category' => 'Cereals', 'description' => 'Finger millet and pearl millet'],
            ['name' => 'Wheat', 'category' => 'Cereals', 'description' => 'Wheat grain'],

            // Legumes
            ['name' => 'Beans', 'category' => 'Legumes', 'description' => 'Common beans (Phaseolus) – yellow, red, mixed'],
            ['name' => 'Pigeon Peas', 'category' => 'Legumes', 'description' => 'Mbaazi'],
            ['name' => 'Cowpeas', 'category' => 'Legumes', 'description' => 'Kunde'],
            ['name' => 'Groundnuts', 'category' => 'Legumes', 'description' => 'Peanuts (karanga)'],
            ['name' => 'Soybeans', 'category' => 'Legumes', 'description' => 'Soybean grain'],

            // Oil Crops
            ['name' => 'Sunflower', 'category' => 'Oil Crops', 'description' => 'Sunflower seeds'],
            ['name' => 'Sesame', 'category' => 'Oil Crops', 'description' => 'Simsim'],
            ['name' => 'Cotton Seed', 'category' => 'Oil Crops', 'description' => 'Cotton seed for oil'],

            // Roots & Tubers
            ['name' => 'Cassava', 'category' => 'Roots & Tubers', 'description' => 'Fresh and dried cassava'],
            ['name' => 'Sweet Potatoes', 'category' => 'Roots & Tubers', 'description' => 'Viazi vitamu'],
            ['name' => 'Irish Potatoes', 'category' => 'Roots & Tubers', 'description' => 'Viazi mviringo'],
            ['name' => 'Yams', 'category' => 'Roots & Tubers', 'description' => 'Viazi vikuu'],

            // Vegetables
            ['name' => 'Tomatoes', 'category' => 'Vegetables', 'description' => 'Fresh tomatoes'],
            ['name' => 'Onions', 'category' => 'Vegetables', 'description' => 'Red and white onions'],
            ['name' => 'Cabbage', 'category' => 'Vegetables', 'description' => 'Head cabbage'],
            ['name' => 'Carrots', 'category' => 'Vegetables', 'description' => 'Fresh carrots'],
            ['name' => 'Green Peppers', 'category' => 'Vegetables', 'description' => 'Hoho'],
            ['name' => 'Spinach / Amaranthus', 'category' => 'Vegetables', 'description' => 'Mchicha and spinach'],
            ['name' => 'Okra', 'category' => 'Vegetables', 'description' => 'Bamia'],

            // Fruits
            ['name' => 'Bananas', 'category' => 'Fruits', 'description' => 'Cooking and dessert bananas'],
            ['name' => 'Mangoes', 'category' => 'Fruits', 'description' => 'Various mango varieties'],
            ['name' => 'Avocados', 'category' => 'Fruits', 'description' => 'Hass and other avocados'],
            ['name' => 'Oranges', 'category' => 'Fruits', 'description' => 'Citrus oranges'],
            ['name' => 'Pineapples', 'category' => 'Fruits', 'description' => 'Fresh pineapples'],
            ['name' => 'Watermelons', 'category' => 'Fruits', 'description' => 'Fresh watermelons'],

            // Cash / Beverage
            ['name' => 'Coffee', 'category' => 'Beverage Crops', 'description' => 'Arabica and Robusta parchment / green'],
            ['name' => 'Tea', 'category' => 'Beverage Crops', 'description' => 'Made tea and green leaf'],
            ['name' => 'Cocoa', 'category' => 'Cash Crops', 'description' => 'Cocoa beans'],
            ['name' => 'Cashew Nuts', 'category' => 'Cash Crops', 'description' => 'Raw cashew nuts (RCN)'],
            ['name' => 'Cotton', 'category' => 'Cash Crops', 'description' => 'Seed cotton and lint'],
            ['name' => 'Tobacco', 'category' => 'Cash Crops', 'description' => 'Flue-cured and other tobacco'],
            ['name' => 'Sugarcane', 'category' => 'Cash Crops', 'description' => 'Sugarcane for processing'],

            // Spices & Herbs
            ['name' => 'Cloves', 'category' => 'Spices', 'description' => 'Dried cloves (Zanzibar/Pemba)'],
            ['name' => 'Cardamom', 'category' => 'Spices', 'description' => 'Green cardamom'],
            ['name' => 'Ginger', 'category' => 'Spices', 'description' => 'Fresh and dried ginger'],
            ['name' => 'Turmeric', 'category' => 'Spices', 'description' => 'Manjano'],
            ['name' => 'Chili / Pilipili', 'category' => 'Spices', 'description' => 'Hot peppers'],

            // Animal related
            ['name' => 'Milk', 'category' => 'Dairy', 'description' => 'Fresh cow milk'],
            ['name' => 'Eggs', 'category' => 'Poultry', 'description' => 'Table eggs'],
            ['name' => 'Chicken (Live)', 'category' => 'Poultry', 'description' => 'Live indigenous and broiler chickens'],
            ['name' => 'Honey', 'category' => 'Apiculture', 'description' => 'Natural honey'],
        ];

        foreach ($commodities as $data) {
            $category = CommodityCategory::where('name', $data['category'])->first();

            if (!$category) {
                $this->command?->warn("Commodity category not found: {$data['category']}");
                continue;
            }

            Commodity::updateOrCreate(
                ['name' => $data['name']],
                [
                    'commodity_category_id' => $category->id,
                    'category' => $data['category'],
                    'description' => $data['description'],
                ]
            );
        }
    }
}
