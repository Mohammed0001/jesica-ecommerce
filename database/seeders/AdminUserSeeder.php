<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'ADMIN')->first();

        if (!$adminRole) {
            $this->command->error('Admin role not found. Please run RoleSeeder first.');
            return;
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@jesicariad.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@jesicariad.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role_id' => $adminRole->id,
                'date_of_birth' => '1985-01-01',
                'phone' => '+1-555-0123',
                'region_id' => 1, // Assuming US region
            ]
        );

        $this->command->info('Admin user created: admin@jesicariad.com / password123');
    }
}
