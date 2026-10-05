<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;
use App\Models\PlanFeature;

class PlanFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            'Starter' => [
                'crop_listings' => [
                    'name' => 'Crop Listings',
                    'limit' => 10,
                ],
                'livestock_listings' => [
                    'name' => 'Livestock Listings',
                    'limit' => 5,
                ],
                'machinery_listings' => [
                    'name' => 'Machinery Listings',
                    'limit' => 3,
                ],
                'service_listings' => [
                    'name' => 'Service Listings',
                    'limit' => 5,
                ],
                'featured_listings' => [
                    'name' => 'Featured Listings',
                    'limit' => 0,
                ],
                'gallery_photos' => [
                    'name' => 'Gallery Photos',
                    'limit' => 5,
                ],
                'verified_badge' => [
                    'name' => 'Verified Provider Badge',
                ],
                'analytics' => [
                    'name' => 'Analytics Dashboard',
                ],
                'priority_support' => [
                    'name' => 'Priority Support',
                ],
                'tenders' => [
                    'name' => 'Tender Marketplace',
                ],
                'api_access' => [
                    'name' => 'API Access',
                ],
                'advertising' => [
                    'name' => 'Advertising Tools',
                ],
            ],

            'Standard' => [
                'crop_listings' => [
                    'name' => 'Crop Listings',
                    'limit' => 50,
                ],
                'livestock_listings' => [
                    'name' => 'Livestock Listings',
                    'limit' => 30,
                ],
                'machinery_listings' => [
                    'name' => 'Machinery Listings',
                    'limit' => 15,
                ],
                'service_listings' => [
                    'name' => 'Service Listings',
                    'limit' => 30,
                ],
                'featured_listings' => [
                    'name' => 'Featured Listings',
                    'limit' => 5,
                ],
                'gallery_photos' => [
                    'name' => 'Gallery Photos',
                    'limit' => 15,
                ],
                'verified_badge' => [
                    'name' => 'Verified Provider Badge',
                ],
                'analytics' => [
                    'name' => 'Analytics Dashboard',
                ],
                'priority_support' => [
                    'name' => 'Priority Support',
                ],
                'tenders' => [
                    'name' => 'Tender Marketplace',
                ],
                'api_access' => [
                    'name' => 'API Access',
                ],
                'advertising' => [
                    'name' => 'Advertising Tools',
                ],
            ],

            'Premium' => [
                'crop_listings' => [
                    'name' => 'Crop Listings',
                    'limit' => null,
                ],
                'livestock_listings' => [
                    'name' => 'Livestock Listings',
                    'limit' => null,
                ],
                'machinery_listings' => [
                    'name' => 'Machinery Listings',
                    'limit' => null,
                ],
                'service_listings' => [
                    'name' => 'Service Listings',
                    'limit' => null,
                ],
                'featured_listings' => [
                    'name' => 'Featured Listings',
                    'limit' => 20,
                ],
                'gallery_photos' => [
                    'name' => 'Gallery Photos',
                    'limit' => null,
                ],
                'verified_badge' => [
                    'name' => 'Verified Provider Badge',
                ],
                'analytics' => [
                    'name' => 'Analytics Dashboard',
                ],
                'priority_support' => [
                    'name' => 'Priority Support',
                ],
                'tenders' => [
                    'name' => 'Tender Marketplace',
                ],
                'api_access' => [
                    'name' => 'API Access',
                ],
                'advertising' => [
                    'name' => 'Advertising Tools',
                ],
            ],
        ];

        foreach ($plans as $planName => $features) {

            $plan = SubscriptionPlan::where('name', $planName)->first();

            if (!$plan) {
                continue;
            }

            foreach ($features as $key => $feature) {

                PlanFeature::updateOrCreate(
                    [
                        'plan_id' => $plan->id,
                        'feature_key' => $key,
                    ],
                    [
                        'feature_name' => $feature['name'],
                        'feature_value' => true,
                        'feature_limit' => $feature['limit'] ?? null,
                    ]
                );
            }
        }
    }
}