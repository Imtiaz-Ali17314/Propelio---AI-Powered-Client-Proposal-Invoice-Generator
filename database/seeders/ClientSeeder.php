<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Option 1: Create 10 clients with random users
        Client::factory(10)->create();

        // Option 2: Create clients with existing user
        $user = User::first(); // Get first user
        if ($user) {
            Client::factory(5)->create([
                'user_id' => $user->id
            ]);
        }

        // Option 3: Create specific clients for testing
        $testClients = [ 
            [
                'user_id' => $user->id,
                'name' => 'Imtiaz Ali',
                'company_name' => 'Tech Innovations',
                'email' => 'imtiaz@techinnovations.com',
                'phone' => '+92-300-1234567',
                'notes' => 'Interested in AI-powered solutions'
            ],
            [
                'user_id' => $user->id,
                'name' => 'Sarah Ahmed',
                'company_name' => 'Creative Agency',
                'email' => 'sarah@creativeagency.com',
                'phone' => '+92-321-7654321',
                'notes' => 'Need a landing page redesign'
            ],
            [
                'user_id' => $user->id,
                'name' => 'Usman Khan',
                'company_name' => 'E-commerce Store',
                'email' => 'usman@ecomstore.com',
                'phone' => '+92-333-9876543',
                'notes' => 'Looking for payment integration'
            ],
            [
                'user_id' => $user->id,
                'name' => 'Fatima Noor',
                'company_name' => 'Health & Wellness',
                'email' => 'fatima@healthwellness.com',
                'phone' => '+92-345-5678901',
                'notes' => 'Need appointment booking system'
            ],
            [
                'user_id' => $user->id,
                'name' => 'Ali Raza',
                'company_name' => 'Real Estate Pro',
                'email' => 'ali@realestatepro.com',
                'phone' => '+92-312-2345678',
                'notes' => 'Property management system needed'
            ],
        ];

        // Get or create a user for these clients
        $user = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo User',
                'password' => bcrypt('password')
            ]
        );

        foreach ($testClients as $clientData) {
            Client::create(array_merge($clientData, ['user_id' => $user->id]));
        }
    }
}