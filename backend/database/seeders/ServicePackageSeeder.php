<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\ServicePackage;

class ServicePackageSeeder extends Seeder
{
    public function run(): void
    {
        $service=Service::first();

        $packages=[

            [

                'name'=>'Tractor Hire',

                'price'=>150000,

                'unit'=>'per acre'

            ],

            [

                'name'=>'Disc Ploughing',

                'price'=>220000,

                'unit'=>'per acre'

            ],

            [

                'name'=>'Harrowing',

                'price'=>180000,

                'unit'=>'per acre'

            ],

            [

                'name'=>'Planting',

                'price'=>170000,

                'unit'=>'per acre'

            ],

            [

                'name'=>'Complete Land Preparation',

                'price'=>520000,

                'unit'=>'per acre'

            ]

        ];

        foreach($packages as $package){

            ServicePackage::updateOrCreate(

                [

                    'service_id'=>$service->id,

                    'name'=>$package['name']

                ],

                $package

            );

        }

    }
}