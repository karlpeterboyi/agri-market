<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPrice;

class SubscriptionPriceSeeder extends Seeder
{
    public function run(): void
    {
        $prices = [

            'free' => [
                'annual' => 0,
            ],

            'pro-farmer' => [
                'monthly' => 5000,
                'quarterly' => 14000,
                'biannual' => 27000,
                'annual' => 50000,
            ],

            'business' => [
                'monthly' => 25000,
                'quarterly' => 70000,
                'biannual' => 130000,
                'annual' => 250000,
            ],

            'enterprise' => [
                'monthly' => 100000,
                'quarterly' => 280000,
                'biannual' => 540000,
                'annual' => 1000000,
            ],

        ];

        foreach ($prices as $slug => $periods) {

            $plan = SubscriptionPlan::where('slug', $slug)->first();

            if (!$plan) {
                continue;
            }

            foreach ($periods as $period => $price) {

                SubscriptionPrice::updateOrCreate(
                    [
                        'subscription_plan_id' => $plan->id,
                        'billing_period' => $period,
                    ],
                    [
                        'price' => $price,
                        'currency' => 'TZS',
                        'active' => true,
                    ]
                );

            }
        }
    }
}