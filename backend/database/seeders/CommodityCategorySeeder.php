<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CommodityCategory;
use Illuminate\Support\Str;

class CommodityCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Cereals',
            'Legumes',
            'Oil Crops',
            'Roots & Tubers',
            'Fruits',
            'Vegetables',
            'Spices',
            'Herbs',
            'Beverage Crops',
            'Cash Crops',
            'Livestock',
            'Poultry',
            'Dairy',
            'Aquaculture',
            'Apiculture',
            'Forestry',
            'Flowers',
            'Animal Feeds',
        ];

        foreach ($categories as $category) {
            CommodityCategory::updateOrCreate(
                [
                    'slug' => Str::slug($category),
                ],
                [
                    'name' => $category,
                ]
            );
        }
    }
}