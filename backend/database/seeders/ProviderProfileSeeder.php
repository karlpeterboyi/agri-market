<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProviderProfile;
use App\Models\User;

class ProviderProfileSeeder extends Seeder
{
    public function run(): void
    {
        $user=User::first();

        ProviderProfile::updateOrCreate(

        ['user_id'=>$user->id],

        [

        'business_name'=>'GreenFields Agri Services Ltd',

        'phone'=>'255712345678',

        'whatsapp'=>'255712345678',

        'email'=>'info@greenfields.co.tz',

        'website'=>'https://greenfields.co.tz',

        'business_registration'=>'123456',

        'tin'=>'100-200-300',

        'vrn'=>'400-500',

        'years_experience'=>12,

        'about'=>'Professional mechanized farming company serving farmers across Tanzania.',

        'service_regions'=>[
            'Morogoro',
            'Dodoma',
            'Iringa',
            'Mbeya'
        ],

        'business_hours'=>[
            'Monday'=>'07:00-18:00',
            'Tuesday'=>'07:00-18:00',
            'Wednesday'=>'07:00-18:00',
            'Thursday'=>'07:00-18:00',
            'Friday'=>'07:00-18:00',
            'Saturday'=>'08:00-15:00'
        ],

        'certifications'=>[
            'Tanzania Tractor Operators Association',
            'Ministry of Agriculture Certified',
            'OSHA Tanzania'
        ],

        'verified'=>true,

        'rating'=>4.9,

        'reviews'=>248,

        'completed_jobs'=>1458

        ]);

    }
}