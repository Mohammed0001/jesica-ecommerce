<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'ADMIN',
                'description' => 'Administrator with full access to manage the store',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'CLIENT',
                'description' => 'Customer who can browse and purchase products',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($roles as $roleData) {
            \App\Models\Role::firstOrCreate(['name' => $roleData['name']], $roleData);
        }
    }
}
