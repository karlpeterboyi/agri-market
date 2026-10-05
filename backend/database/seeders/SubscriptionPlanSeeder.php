<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [

            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Basic access to the MkulimaHub marketplace.',
                'active' => true,
            ],

            [
                'name' => 'Pro Farmer',
                'slug' => 'pro-farmer',
                'description' => 'For commercial farmers and growing agribusinesses.',
                'active' => true,
            ],

            [
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'Ideal for agro-dealers, machinery owners and service providers.',
                'active' => true,
            ],

            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'For cooperatives, NGOs, exporters and institutions.',
                'active' => true,
            ],

        ];

        foreach ($plans as $plan) {

            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );

        }
    }
}