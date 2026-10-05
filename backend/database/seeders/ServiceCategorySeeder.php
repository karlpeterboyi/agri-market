<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceCategory;
use Illuminate\Support\Str;

class ServiceCategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [

            "Tractor Hire",

            "Ploughing",

            "Harvesting",

            "Crop Spraying",

            "Irrigation",

            "Soil Testing",

            "Veterinary Services",

            "Artificial Insemination",

            "Livestock Transport",

            "Crop Transport",

            "Cold Storage",

            "Warehousing",

            "Drone Mapping",

            "Equipment Repair",

            "Extension Services",

            "Financial Services",

            "Agricultural Insurance",

            "Input Supply",

            "Seedling Nursery",

            "Greenhouse Installation"

        ];

        foreach ($categories as $category) {

            ServiceCategory::firstOrCreate(
                ['slug' => Str::slug($category)],
                [
                    'name' => $category,
                    'description' => $category
                ]
            );

        }
    }
}