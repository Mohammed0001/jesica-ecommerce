<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

/**
 * Seed delivery regions from Bosta's Egyptian city list.
 * Run with: php artisan db:seed --class=CairoAreasSeeder
 *
 * Fees are defaults — adjust in Admin → Delivery Areas.
 */
class CairoAreasSeeder extends Seeder
{
    public function run(): void
    {
        // Source: BostaCitySeeder (matches Bosta's city IDs exactly)
        // sector 1 = Greater Cairo, 2 = Alexandria belt, 3 = Delta,
        // 4 = Middle Egypt, 5 = Upper Egypt / coasts, 6 = North Coast, 7 = Remote
        $cities = [
            // Greater Cairo — sector 1
            [
                'bosta_id' => 'FceDyHXwpSYYF9zGW',
                'name'     => 'Cairo',
                'code'     => 'EG-01',
                'fee'      => 50,
                'aliases'  => ['Cairo', 'القاهرة', 'القاهره', 'Al Qahirah'],
            ],
            [
                'bosta_id' => '0064Qb0OgcA',
                'name'     => 'Giza',
                'code'     => 'EG-25',
                'fee'      => 55,
                'aliases'  => ['Giza', 'الجيزة', 'الجيزه'],
            ],

            // Alexandria belt — sector 2
            [
                'bosta_id' => 'Jrb6X6ucjiYgMP4T7',
                'name'     => 'Alexandria',
                'code'     => 'EG-02',
                'fee'      => 80,
                'aliases'  => ['Alexandria', 'الإسكندرية', 'الاسكندريه'],
            ],
            [
                'bosta_id' => 'g3GchTSmCgR2JynsJ',
                'name'     => 'Beheira',
                'code'     => 'EG-04',
                'fee'      => 85,
                'aliases'  => ['Beheira', 'البحيرة', 'البحيره'],
            ],

            // Delta — sector 3
            [
                'bosta_id' => 'yp3atroeTwnyiBNKE',
                'name'     => 'Qalyubia',
                'code'     => 'EG-06',
                'fee'      => 65,
                'aliases'  => ['Qalyubia', 'القليوبية', 'القليوبيه', 'El Kalioubia'],
            ],
            [
                'bosta_id' => 'RrDhS8YYsXAwZ9Zfo',
                'name'     => 'Dakahlia',
                'code'     => 'EG-05',
                'fee'      => 80,
                'aliases'  => ['Dakahlia', 'الدقهلية', 'الدقهليه'],
            ],
            [
                'bosta_id' => '6ExcoGbpYHnggP8JD',
                'name'     => 'Sharqia',
                'code'     => 'EG-10',
                'fee'      => 80,
                'aliases'  => ['Sharqia', 'الشرقية', 'الشرقيه', 'Sharkia'],
            ],
            [
                'bosta_id' => 'ruBSjGBDX9wpRa3cc',
                'name'     => 'Monufia',
                'code'     => 'EG-09',
                'fee'      => 80,
                'aliases'  => ['Monufia', 'المنوفية', 'المنوفيه', 'Menofia'],
            ],
            [
                'bosta_id' => 'K3RwC677J8kJytdZD',
                'name'     => 'Gharbia',
                'code'     => 'EG-07',
                'fee'      => 85,
                'aliases'  => ['Gharbia', 'الغربية', 'الغربيه'],
            ],
            [
                'bosta_id' => 'ByP7rFCjL6XzF6j4S',
                'name'     => 'Kafr El Sheikh',
                'code'     => 'EG-08',
                'fee'      => 90,
                'aliases'  => ['Kafr El Sheikh', 'كفر الشيخ', 'Kafr Alsheikh', 'Kafr Al-Sheikh'],
            ],
            [
                'bosta_id' => 'qoZvYcZ8Cqji4pGp5',
                'name'     => 'Damietta',
                'code'     => 'EG-14',
                'fee'      => 85,
                'aliases'  => ['Damietta', 'دمياط'],
            ],
            [
                'bosta_id' => 'PJqNriLtFtx2cfkKP',
                'name'     => 'Ismailia',
                'code'     => 'EG-11',
                'fee'      => 85,
                'aliases'  => ['Ismailia', 'الإسماعيلية', 'الاسماعيليه'],
            ],
            [
                'bosta_id' => 'skFtf6ZmKo8kBEBDK',
                'name'     => 'Port Said',
                'code'     => 'EG-13',
                'fee'      => 85,
                'aliases'  => ['Port Said', 'بورسعيد', 'بور سعيد'],
            ],
            [
                'bosta_id' => 'PickurJ5uJZ9rDTHW',
                'name'     => 'Suez',
                'code'     => 'EG-12',
                'fee'      => 85,
                'aliases'  => ['Suez', 'السويس'],
            ],

            // Middle Egypt — sector 4
            [
                'bosta_id' => 'LzbbvTzZ7D2CgE2PL',
                'name'     => 'Beni Suef',
                'code'     => 'EG-16',
                'fee'      => 85,
                'aliases'  => ['Beni Suef', 'بني سويف', 'Bani Suif'],
            ],
            [
                'bosta_id' => 'BW5MiNxEirB7tuz2y',
                'name'     => 'Fayoum',
                'code'     => 'EG-15',
                'fee'      => 85,
                'aliases'  => ['Fayoum', 'الفيوم', 'Faiyum'],
            ],
            [
                'bosta_id' => 'si6eLnKjXqTFTMBj9',
                'name'     => 'Minya',
                'code'     => 'EG-19',
                'fee'      => 90,
                'aliases'  => ['Minya', 'المنيا', 'Menya'],
            ],
            [
                'bosta_id' => '7mDPAohM3ArSZmWTm',
                'name'     => 'Assiut',
                'code'     => 'EG-17',
                'fee'      => 95,
                'aliases'  => ['Assiut', 'أسيوط', 'Assuit', 'Asyut'],
            ],
            [
                'bosta_id' => 'n3EENg2adhuR9xBZK',
                'name'     => 'Sohag',
                'code'     => 'EG-18',
                'fee'      => 95,
                'aliases'  => ['Sohag', 'سوهاج'],
            ],

            // Upper Egypt / coasts — sector 5
            [
                'bosta_id' => 'vfTHTes3uGjAszgtg',
                'name'     => 'Qena',
                'code'     => 'EG-20',
                'fee'      => 100,
                'aliases'  => ['Qena', 'قنا'],
            ],
            [
                'bosta_id' => 'wgYEdH2WMzxGE2Ztp',
                'name'     => 'Luxor',
                'code'     => 'EG-22',
                'fee'      => 100,
                'aliases'  => ['Luxor', 'الأقصر', 'الاقصر'],
            ],
            [
                'bosta_id' => 'kLvZ5JY6LJPL5chzN',
                'name'     => 'Aswan',
                'code'     => 'EG-21',
                'fee'      => 100,
                'aliases'  => ['Aswan', 'أسوان', 'اسوان'],
            ],
            [
                'bosta_id' => 'r5TscLCNSjR2GimxQ',
                'name'     => 'Red Sea',
                'code'     => 'EG-23',
                'fee'      => 120,
                'aliases'  => ['Red Sea', 'البحر الأحمر', 'البحر الاحمر'],
            ],
            [
                'bosta_id' => 'KBpGiRZJMIx',
                'name'     => 'Matrouh',
                'code'     => 'EG-28',
                'fee'      => 120,
                'aliases'  => ['Matrouh', 'مرسي مطروح', 'مطروح'],
            ],
            [
                'bosta_id' => 'nG_c44vHQht',
                'name'     => 'South Sinai',
                'code'     => 'EG-26',
                'fee'      => 120,
                'aliases'  => ['South Sinai', 'جنوب سيناء'],
            ],

            // North Coast — sector 6
            [
                'bosta_id' => '2hGtNLfRgqGrJjnW9',
                'name'     => 'North Coast',
                'code'     => 'EG-03',
                'fee'      => 120,
                'aliases'  => ['North Coast', 'الساحل الشمالي'],
            ],

            // Remote — sector 7
            [
                'bosta_id' => 'ZuCaDAVQlPT',
                'name'     => 'North Sinai',
                'code'     => 'EG-27',
                'fee'      => 120,
                'aliases'  => ['North Sinai', 'شمال سيناء'],
            ],
            [
                'bosta_id' => 'w4yDVHVJWqa4HpbzA',
                'name'     => 'New Valley',
                'code'     => 'EG-24',
                'fee'      => 130,
                'aliases'  => ['New Valley', 'الوادي الجديد'],
            ],
        ];

        foreach ($cities as $city) {
            Region::updateOrCreate(
                ['bosta_id' => $city['bosta_id']],
                [
                    'name'         => $city['name'],
                    'code'         => $city['code'],
                    'type'         => 'governorate',
                    'delivery_fee' => $city['fee'],
                    'city_names'   => $city['aliases'],
                ]
            );
        }

        $this->command->info('Seeded ' . count($cities) . ' Bosta delivery regions.');
    }
}
