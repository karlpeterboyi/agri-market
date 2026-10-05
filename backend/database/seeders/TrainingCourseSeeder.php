<?php

namespace Database\Seeders;

use App\Models\TrainingCourse;
use App\Models\User;
use Illuminate\Database\Seeder;

class TrainingCourseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();

        $courses = [
            [
                'title' => 'Utunzaji Bora wa Mahindi (Maize Production)',
                'category' => 'crop_production',
                'level' => 'beginner',
                'language' => 'sw',
                'duration_minutes' => 90,
                'description' => 'Jifunze mbinu bora za kulima mahindi kuanzia uchaguzi wa mbegu hadi uvunaji.',
                'is_free' => true,
                'is_published' => true,
                'featured' => true,
                'learning_outcomes' => ['Kuchagua mbegu bora', 'Upandaji sahihi', 'Udhibiti wa wadudu', 'Uvunaji na uhifadhi'],
                'modules' => ['Utangulizi', 'Maandalizi ya shamba', 'Upandaji', 'Utunzaji', 'Uvunaji'],
            ],
            [
                'title' => 'Dairy Cattle Management Basics',
                'category' => 'livestock',
                'level' => 'beginner',
                'language' => 'en',
                'duration_minutes' => 120,
                'description' => 'Essential skills for smallholder dairy farmers in East Africa.',
                'is_free' => true,
                'is_published' => true,
                'featured' => true,
                'learning_outcomes' => ['Housing', 'Feeding', 'Health', 'Milk hygiene'],
                'modules' => ['Introduction', 'Housing & Comfort', 'Nutrition', 'Disease prevention', 'Milking'],
            ],
            [
                'title' => 'Record Keeping & Farm Profitability',
                'category' => 'finance',
                'level' => 'intermediate',
                'language' => 'en',
                'duration_minutes' => 75,
                'description' => 'How to use MkulimaHub Farm ERP and simple books to know if your farm is making money.',
                'is_free' => true,
                'is_published' => true,
                'featured' => false,
                'learning_outcomes' => ['Activity costing', 'Crop cycle profitability', 'Wallet & cashflow'],
                'modules' => ['Why records matter', 'Using Farm ERP', 'Cost centres', 'Simple P&L'],
            ],
            [
                'title' => 'Integrated Pest Management (IPM)',
                'category' => 'pest_disease',
                'level' => 'intermediate',
                'language' => 'en',
                'duration_minutes' => 100,
                'description' => 'Reduce pesticide use while protecting yields through IPM principles.',
                'is_free' => true,
                'is_published' => true,
                'featured' => false,
                'learning_outcomes' => ['Scouting', 'Thresholds', 'Biological control', 'Safe pesticide use'],
                'modules' => ['IPM principles', 'Scouting techniques', 'Decision making', 'Options'],
            ],
            [
                'title' => 'Climate-Smart Agriculture for Tanzania',
                'category' => 'climate',
                'level' => 'beginner',
                'language' => 'sw',
                'duration_minutes' => 80,
                'description' => 'Mbinu za kilimo zinazostahimili mabadiliko ya tabia nchi.',
                'is_free' => true,
                'is_published' => true,
                'featured' => true,
                'learning_outcomes' => ['Conservation agriculture', 'Water harvesting', 'Drought-tolerant varieties'],
                'modules' => ['Utangulizi', 'Uhifadhi wa maji', 'Mbegu stahimilivu', 'Mifumo ya kilimo'],
            ],
        ];

        foreach ($courses as $data) {
            TrainingCourse::firstOrCreate(
                ['title' => $data['title']],
                array_merge($data, ['created_by' => $admin?->id])
            );
        }
    }
}
