<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MachineryBrand;
use Illuminate\Support\Str;

class MachineryBrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [

            ['John Deere','USA'],
            ['Massey Ferguson','USA'],
            ['New Holland','Italy'],
            ['Kubota','Japan'],
            ['Mahindra','India'],
            ['Sonalika','India'],
            ['TAFE','India'],
            ['Case IH','USA'],
            ['CLAAS','Germany'],
            ['Yanmar','Japan'],
            ['Foton','China'],
            ['JCB','United Kingdom'],
            ['Caterpillar','USA'],
            ['Zoomlion','China'],
            ['Lovol','China']

        ];

        foreach ($brands as $brand) {

            MachineryBrand::updateOrCreate(

                [

                    'slug' => Str::slug($brand[0])

                ],

                [

                    'name' => $brand[0],

                    'country' => $brand[1],

                    'active' => true

                ]

            );

        }
    }
}