<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\LivestockCategory;

class LivestockCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [

            'Cattle',

            'Dairy',

            'Goats',

            'Sheep',

            'Poultry',

            'Pigs',

            'Rabbits',

            'Camels',

            'Fish',

            'Beekeeping'

        ];

        foreach ($categories as $category) {

            LivestockCategory::updateOrCreate([

                'name' => $category,

                'slug' => Str::slug($category)

            ]);

        }
    }
}