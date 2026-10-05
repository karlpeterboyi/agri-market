<?php

namespace Database\Seeders;

use App\Models\Commodity;
use App\Models\ProductListing;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductListingSeeder extends Seeder
{
    public function run(): void
    {
        $farmers = User::where('role', 'farmer')->get();
        if ($farmers->isEmpty()) {
            $this->command?->warn('No farmers found — skip ProductListingSeeder');
            return;
        }

        $samples = [
            ['commodity' => 'Maize', 'quantity' => 50, 'unit' => 'bags', 'grade' => 'Grade 1', 'price' => 65000, 'region' => 'Morogoro', 'district' => 'Mvomero', 'description' => 'Dry white maize, well dried and clean.'],
            ['commodity' => 'Rice', 'quantity' => 30, 'unit' => 'bags', 'grade' => 'Super', 'price' => 120000, 'region' => 'Mbeya', 'district' => 'Kyela', 'description' => 'Aromatic rice from Kyela.'],
            ['commodity' => 'Beans', 'quantity' => 20, 'unit' => 'bags', 'grade' => 'Mixed', 'price' => 180000, 'region' => 'Arusha', 'district' => 'Arusha Rural', 'description' => 'Yellow and red beans mix.'],
            ['commodity' => 'Tomatoes', 'quantity' => 500, 'unit' => 'kg', 'grade' => 'Fresh', 'price' => 1500, 'region' => 'Arusha', 'district' => 'Meru', 'description' => 'Fresh tomatoes, ready for market.'],
            ['commodity' => 'Onions', 'quantity' => 200, 'unit' => 'kg', 'grade' => 'Red', 'price' => 2000, 'region' => 'Singida', 'district' => 'Singida', 'description' => 'Red onions, good keeping quality.'],
            ['commodity' => 'Coffee', 'quantity' => 10, 'unit' => 'bags', 'grade' => 'AA', 'price' => 450000, 'region' => 'Kilimanjaro', 'district' => 'Moshi Rural', 'description' => 'Arabica parchment coffee.'],
            ['commodity' => 'Sunflower', 'quantity' => 15, 'unit' => 'bags', 'grade' => 'Standard', 'price' => 95000, 'region' => 'Dodoma', 'district' => 'Kongwa', 'description' => 'Sunflower seed for oil.'],
            ['commodity' => 'Cassava', 'quantity' => 1000, 'unit' => 'kg', 'grade' => 'Fresh', 'price' => 400, 'region' => 'Mwanza', 'district' => 'Misungwi', 'description' => 'Fresh cassava roots.'],
            ['commodity' => 'Bananas', 'quantity' => 200, 'unit' => 'bunches', 'grade' => 'Cooking', 'price' => 8000, 'region' => 'Kagera', 'district' => 'Bukoba', 'description' => 'Cooking bananas.'],
            ['commodity' => 'Groundnuts', 'quantity' => 25, 'unit' => 'bags', 'grade' => 'Shelled', 'price' => 220000, 'region' => 'Ruvuma', 'district' => 'Songea', 'description' => 'Clean shelled groundnuts.'],
            ['commodity' => 'Milk', 'quantity' => 100, 'unit' => 'litres', 'grade' => 'Fresh', 'price' => 1200, 'region' => 'Arusha', 'district' => 'Arusha', 'description' => 'Fresh cow milk daily supply available.'],
            ['commodity' => 'Eggs', 'quantity' => 30, 'unit' => 'trays', 'grade' => 'Large', 'price' => 12000, 'region' => 'Dar es Salaam', 'district' => 'Ilala', 'description' => 'Table eggs, 30 per tray.'],
        ];

        foreach ($samples as $i => $s) {
            $commodity = Commodity::where('name', 'like', '%' . $s['commodity'] . '%')->first()
                ?? Commodity::where('name', $s['commodity'])->first();
            if (!$commodity) {
                continue;
            }
            $seller = $farmers[$i % $farmers->count()];

            ProductListing::updateOrCreate(
                [
                    'seller_id' => $seller->id,
                    'commodity_id' => $commodity->id,
                    'region' => $s['region'],
                ],
                [
                    'quantity' => $s['quantity'],
                    'unit' => $s['unit'],
                    'grade' => $s['grade'],
                    'price' => $s['price'],
                    'district' => $s['district'],
                    'description' => $s['description'],
                    'status' => 'available',
                ]
            );
        }
    }
}
