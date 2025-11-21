<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed basic data first
        $this->call([
            RoleSeeder::class,
            RegionSeeder::class,
        ]);

        // Create admin users
        User::factory()
            ->count(5)
            ->admin()
            ->create();

        // Create client users
        User::factory()
            ->count(20)
            ->client()
            ->create();

        // Create test admin user
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@jesicariad.com',
        ]);

        // Create test client user
        User::factory()->client()->create([
            'name' => 'Test Client',
            'email' => 'client@example.com',
        ]);

        // Seed collections and products
        $this->call([
            CollectionSeeder::class,
            ProductSeeder::class,
        ]);

        // Create addresses for some users
        $users = User::where('role_id', 2)->take(10)->get(); // Get some clients
        foreach ($users as $user) {
            \App\Models\Address::factory()
                ->count(fake()->numberBetween(1, 2))
                ->create([
                    'user_id' => $user->id,
                ]);
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin login: admin@jesicariad.com / password');
        $this->command->info('Client login: client@example.com / password');
    }
}
