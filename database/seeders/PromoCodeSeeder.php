<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PromoCode;
use Illuminate\Support\Carbon;

class PromoCodeSeeder extends Seeder
{
    public function run(): void
    {
        PromoCode::create([
            'code' => 'WELCOME10',
            'description' => '10% off on first purchase',
            'type' => 'percentage',
            'value' => 10,
            'max_uses' => 100,
            'active' => true,
            'expires_at' => Carbon::now()->addMonths(6),
        ]);

        PromoCode::create([
            'code' => 'FLAT50',
            'description' => 'Flat 50 off',
            'type' => 'fixed',
            'value' => 50,
            'max_uses' => null,
            'active' => true,
            'expires_at' => Carbon::now()->addMonths(6),
        ]);
    }
}
