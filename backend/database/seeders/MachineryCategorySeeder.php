<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MachineryCategory;
use Illuminate\Support\Str;

class MachineryCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [

            "Tractors",
            "Harvesters",
            "Ploughs",
            "Seed Drills",
            "Planters",
            "Cultivators",
            "Boom Sprayers",
            "Water Pumps",
            "Irrigation Systems",
            "Generators",

            "Milk Processing Equipment",
            "Feed Mixers",
            "Silage Choppers",

            "Greenhouses",

            "Solar Dryers",

            "Rice Mills",
            "Maize Mills",
            "Coffee Processing Machines",
            "Oil Press Machines",

            "Trailers",

            "Livestock Equipment",

            "Cold Storage Equipment",

        ];

        foreach ($categories as $category) {

            MachineryCategory::updateOrCreate(
                ['slug' => Str::slug($category)],
                [
                    'name' => $category,
                    'description' => $category,
                ]
            );

        }
    }
}