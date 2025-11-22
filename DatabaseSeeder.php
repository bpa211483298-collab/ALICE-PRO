<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);
        
        // Create test users with specific roles
        $this->call([
            TestUsersSeeder::class,
        ]);
        
        // Create additional test users
        for ($i = 1; $i <= 3; $i++) {
            User::create([
                'name' => 'Test User ' . ($i + 3),
                'email' => 'user' . ($i + 3) . '@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ])->assignRole('user');
        }
    }
}
