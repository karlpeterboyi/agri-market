<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\MachineryBrand;
use App\Models\MachineryCategory;
use App\Models\MachineryModel;

class MachineryModelSeeder extends Seeder
{
    public function run(): void
    {
        $models = [

            'John Deere' => [
                '5055E',
                '5065E',
                '5075E',
                '6110B'
            ],

            'Kubota' => [
                'L4508',
                'MU5501',
                'M6040'
            ],

            'Massey Ferguson' => [
                '240',
                '260',
                '375'
            ],

            'Mahindra' => [
                '575 DI',
                '475 DI'
            ],

            'New Holland' => [
                'TT55',
                'TT75'
            ]

        ];

        $tractorCategory = MachineryCategory::where('slug','tractors')->first();

        foreach ($models as $brandName => $brandModels) {

            $brand = MachineryBrand::where('name',$brandName)->first();

            if (!$brand || !$tractorCategory) {
                continue;
            }

            foreach ($brandModels as $model) {

                MachineryModel::updateOrCreate(

                    [
                        'slug' => Str::slug($brandName.'-'.$model)
                    ],

                    [
                        'machinery_brand_id' => $brand->id,

                        'machinery_category_id' => $tractorCategory->id,

                        'name' => $model,

                        'horsepower' => rand(45,120),

                        'fuel_type' => 'Diesel',

                        'transmission' => 'Manual',

                        'active' => true,
                    ]

                );

            }

        }
    }
}