<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        // Retrieve or create a provider user
        $provider = User::where('email', 'provider@mkulimahub.com')->first() 
            ?? User::first() 
            ?? User::factory()->create([
                'name' => 'Default Provider',
                'email' => 'provider@mkulimahub.com',
            ]);

        // Retrieve or create a service category
        $category = ServiceCategory::first() 
            ?? ServiceCategory::create([
                'name' => 'General Agriculture',
                'slug' => 'general-agriculture',
            ]);

        Service::updateOrCreate(
            [
                'title' => 'GreenFields Agri Services Ltd',
            ],
            [
                'provider_id' => $provider->id,
                'service_category_id' => $category->id,
                'title' => 'GreenFields Agri Services Ltd',
                'description' => 'Professional mechanized farming services including tractor hire, ploughing, harrowing, planting, harvesting, irrigation installation and farm consultancy.',
                'price' => 150000.00,
                'pricing_type' => 'starting_from',
                'phone' => '255712345678',
                'mobile' => '255712345678',
                'email' => 'info@greenfields.co.tz',
                'website' => 'https://greenfields.co.tz',
                'region' => 'Morogoro',
                'district' => 'Kilosa',
                'ward' => 'Kilosa Urban',
                'village' => 'Mikumi',
                'latitude' => -6.83000000,
                'longitude' => 37.67000000,
                'cover_photo' => 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=1200&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1464226184884-fa280b87c399?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=900&q=80',
                ],
                'features' => [
                    'Modern tractors',
                    'Professional operators',
                    'Fuel included',
                    'Transport included',
                    'Emergency support',
                    'Same-day booking',
                    'Affordable pricing',
                    'Available across multiple regions',
                ],
                'available_from' => '07:00:00',
                'available_to' => '18:00:00',
                'verified' => true,
                'featured' => true,
                'status' => 'active',
            ]
        );
    }
}
