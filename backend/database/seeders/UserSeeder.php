<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds users matching the production CHECK constraint on users.role:
 * farmer | buyer | processor | provider | agrodealer | transporter | admin
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $users = [
            // Admin
            [
                'email' => 'admin@mkulimahub.co.tz',
                'name' => 'System Administrator',
                'phone' => '0755000001',
                'role' => 'admin',
            ],
            // Farmers
            [
                'email' => 'john.farmer@example.com',
                'name' => 'John Mwalimu',
                'phone' => '0712345678',
                'role' => 'farmer',
            ],
            [
                'email' => 'aisha.farmer@example.com',
                'name' => 'Aisha Juma',
                'phone' => '0755123456',
                'role' => 'farmer',
            ],
            [
                'email' => 'peter.farmer@example.com',
                'name' => 'Peter Kimaro',
                'phone' => '0744123456',
                'role' => 'farmer',
            ],
            // Buyers
            [
                'email' => 'buyer@example.com',
                'name' => 'Dar Fresh Traders',
                'phone' => '0711111111',
                'role' => 'buyer',
            ],
            [
                'email' => 'aggregator@example.com',
                'name' => 'Morogoro Aggregators Ltd',
                'phone' => '0788123456',
                'role' => 'buyer',
            ],
            // Processor
            [
                'email' => 'processor@example.com',
                'name' => 'Kilimo Processing Co',
                'phone' => '0755000010',
                'role' => 'processor',
            ],
            // Service provider
            [
                'email' => 'vet@example.com',
                'name' => 'Dr. Grace Mwangi',
                'phone' => '0766123456',
                'role' => 'provider',
            ],
            [
                'email' => 'provider@mkulimahub.com',
                'name' => 'GreenFields Agri Services',
                'phone' => '0755000020',
                'role' => 'provider',
            ],
            // Agrodealer (inputs)
            [
                'email' => 'agrodealer@example.com',
                'name' => 'Umoja Agro Dealers',
                'phone' => '0755000030',
                'role' => 'agrodealer',
            ],
            // Transporter
            [
                'email' => 'transporter@example.com',
                'name' => 'Safari Logistics TZ',
                'phone' => '0755000040',
                'role' => 'transporter',
            ],
        ];

        foreach ($users as $u) {
            User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'phone' => $u['phone'],
                    'role' => $u['role'],
                    'status' => 'active',
                    'password' => $password,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
