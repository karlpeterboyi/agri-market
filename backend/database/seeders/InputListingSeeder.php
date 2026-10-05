<?php

namespace Database\Seeders;

use App\Models\InputCategory;
use App\Models\InputListing;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InputListingSeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::whereIn('role', ['agrodealer', 'provider', 'farmer', 'admin'])->first()
            ?? User::first();
        if (!$seller) {
            return;
        }

        $categories = [
            'Seeds' => 'seeds',
            'Fertilizers' => 'fertilizers',
            'Agrochemicals' => 'agrochemicals',
            'Animal Feed' => 'animal-feed',
            'Veterinary Medicines' => 'veterinary',
        ];

        foreach ($categories as $name => $slug) {
            InputCategory::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );
        }

        $items = [
            ['cat' => 'seeds', 'name' => 'Certified Maize Seed (Hybrid)', 'price' => 8500, 'stock' => 500, 'unit' => 'kg', 'brand' => 'SeedCo', 'region' => 'Morogoro'],
            ['cat' => 'seeds', 'name' => 'Rice Seed – SARO 5', 'price' => 4500, 'stock' => 300, 'unit' => 'kg', 'brand' => 'ASA', 'region' => 'Mbeya'],
            ['cat' => 'fertilizers', 'name' => 'NPK 23:10:5 (50kg)', 'price' => 85000, 'stock' => 200, 'unit' => 'bag', 'brand' => 'Minjingu', 'region' => 'Dar es Salaam'],
            ['cat' => 'fertilizers', 'name' => 'Urea (50kg)', 'price' => 78000, 'stock' => 150, 'unit' => 'bag', 'brand' => 'Yara', 'region' => 'Dar es Salaam'],
            ['cat' => 'fertilizers', 'name' => 'DAP (50kg)', 'price' => 92000, 'stock' => 100, 'unit' => 'bag', 'brand' => 'Yara', 'region' => 'Arusha'],
            ['cat' => 'agrochemicals', 'name' => 'Herbicide Glyphosate 1L', 'price' => 18000, 'stock' => 80, 'unit' => 'litre', 'brand' => 'Roundup', 'region' => 'Morogoro'],
            ['cat' => 'animal-feed', 'name' => 'Dairy Meal 50kg', 'price' => 55000, 'stock' => 120, 'unit' => 'bag', 'brand' => 'Hill Feeds', 'region' => 'Arusha'],
            ['cat' => 'animal-feed', 'name' => 'Layers Mash 50kg', 'price' => 48000, 'stock' => 90, 'unit' => 'bag', 'brand' => 'Interchick', 'region' => 'Dar es Salaam'],
            ['cat' => 'veterinary', 'name' => 'Newcastle Vaccine (100 doses)', 'price' => 15000, 'stock' => 50, 'unit' => 'vial', 'brand' => 'VetAgro', 'region' => 'Dodoma'],
            ['cat' => 'seeds', 'name' => 'Tomato Seed – Tanya', 'price' => 25000, 'stock' => 40, 'unit' => 'packet', 'brand' => 'East-West Seed', 'region' => 'Arusha'],
        ];

        foreach ($items as $item) {
            $cat = InputCategory::where('slug', $item['cat'])->first();
            if (!$cat) {
                continue;
            }

            InputListing::updateOrCreate(
                [
                    'seller_id' => $seller->id,
                    'name' => $item['name'],
                ],
                [
                    'input_category_id' => $cat->id,
                    'description' => $item['name'] . ' available for farmers.',
                    'price' => $item['price'],
                    'stock' => $item['stock'],
                    'unit' => $item['unit'],
                    'brand' => $item['brand'],
                    'region' => $item['region'],
                    'district' => $item['region'],
                    'featured' => true,
                    'status' => 'available',
                ]
            );
        }
    }
}
