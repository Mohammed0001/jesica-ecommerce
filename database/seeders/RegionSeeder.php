<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Legacy international region records.
 * These are kept as fallback data; the main delivery fee for all
 * international orders is handled via the global "international_delivery_fee"
 * setting. CairoAreasSeeder handles all Egypt-specific regions.
 */
class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // International country regions (legacy — fees now come from global setting)
        // We use updateOrCreate so re-running is safe, and we set type = 'governorate'
        // as the closest match for the enum (they are not Cairo districts).
        $regions = [
            ['name' => 'United States', 'code' => 'US'],
            ['name' => 'United Kingdom', 'code' => 'GB'],
            ['name' => 'Canada',         'code' => 'CA'],
            ['name' => 'France',         'code' => 'FR'],
            ['name' => 'Germany',        'code' => 'DE'],
            ['name' => 'Netherlands',    'code' => 'NL'],
            ['name' => 'Japan',          'code' => 'JP'],
            ['name' => 'Australia',      'code' => 'AU'],
            ['name' => 'Italy',          'code' => 'IT'],
            ['name' => 'Spain',          'code' => 'ES'],
        ];

        foreach ($regions as $region) {
            \App\Models\Region::updateOrCreate(
                ['code' => $region['code']],
                [
                    'name' => $region['name'],
                    'type' => 'governorate', // required after migration; closest enum value
                ]
            );
        }

        $this->command->info('Seeded ' . count($regions) . ' legacy international regions.');
    }
}
