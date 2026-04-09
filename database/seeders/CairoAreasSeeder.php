<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

/**
 * Seed Cairo delivery districts and all Egyptian governorates.
 * Run with: php artisan db:seed --class=CairoAreasSeeder
 *
 * Fees are examples — adjust in Admin → Delivery Areas.
 */
class CairoAreasSeeder extends Seeder
{
    public function run(): void
    {
        // ── Cairo Districts ─────────────────────────────────────────────
        // type = cairo_district
        // city_names[0] is the canonical match key used in the picker
        $cairoDistricts = [
            // Central Cairo
            ['name' => 'Downtown Cairo',        'code' => 'cairo-downtown',        'fee' => 50,  'aliases' => ['Downtown Cairo', 'وسط القاهرة', 'Downtown']],
            ['name' => 'Zamalek',               'code' => 'cairo-zamalek',         'fee' => 55,  'aliases' => ['Zamalek', 'الزمالك']],
            ['name' => 'Garden City',           'code' => 'cairo-garden-city',     'fee' => 55,  'aliases' => ['Garden City', 'جاردن سيتي']],
            ['name' => 'Mohandiseen',           'code' => 'cairo-mohandiseen',     'fee' => 55,  'aliases' => ['Mohandiseen', 'المهندسين']],
            ['name' => 'Agouza',                'code' => 'cairo-agouza',          'fee' => 55,  'aliases' => ['Agouza', 'العجوزة']],
            ['name' => 'Dokki',                 'code' => 'cairo-dokki',           'fee' => 55,  'aliases' => ['Dokki', 'الدقي']],
            ['name' => 'Imbaba',                'code' => 'cairo-imbaba',          'fee' => 60,  'aliases' => ['Imbaba', 'إمبابة']],

            // East Cairo
            ['name' => 'Heliopolis (Masr El Gedida)', 'code' => 'cairo-heliopolis', 'fee' => 60,  'aliases' => ['Heliopolis', 'Masr El Gedida', 'مصر الجديدة']],
            ['name' => 'Nasr City',             'code' => 'cairo-nasr-city',       'fee' => 60,  'aliases' => ['Nasr City', 'مدينة نصر']],
            ['name' => 'El Nozha',              'code' => 'cairo-nozha',           'fee' => 60,  'aliases' => ['El Nozha', 'النزهة']],
            ['name' => 'El Obour City',         'code' => 'cairo-obour',           'fee' => 75,  'aliases' => ['El Obour City', 'مدينة العبور', 'Obour']],
            ['name' => 'Ain Shams',             'code' => 'cairo-ain-shams',       'fee' => 60,  'aliases' => ['Ain Shams', 'عين شمس']],
            ['name' => 'El Mataria',            'code' => 'cairo-mataria',         'fee' => 60,  'aliases' => ['El Mataria', 'المطرية']],
            ['name' => 'El Zeitoun',            'code' => 'cairo-zeitoun',         'fee' => 60,  'aliases' => ['El Zeitoun', 'الزيتون']],
            ['name' => 'Shoubra',               'code' => 'cairo-shoubra',         'fee' => 60,  'aliases' => ['Shoubra', 'شبرا']],
            ['name' => 'Shoubra El Kheima',     'code' => 'cairo-shoubra-kheima',  'fee' => 65,  'aliases' => ['Shoubra El Kheima', 'شبرا الخيمة']],

            // West Cairo / New
            ['name' => 'October City (6th of October)', 'code' => 'cairo-6-october', 'fee' => 70, 'aliases' => ['October City', '6th of October', 'السادس من أكتوبر', '6 October']],
            ['name' => 'Sheikh Zayed',          'code' => 'cairo-sheikh-zayed',    'fee' => 70,  'aliases' => ['Sheikh Zayed', 'الشيخ زايد']],
            ['name' => 'Haram',                 'code' => 'cairo-haram',           'fee' => 60,  'aliases' => ['Haram', 'الهرم']],
            ['name' => 'Faisal',                'code' => 'cairo-faisal',          'fee' => 60,  'aliases' => ['Faisal', 'فيصل']],
            ['name' => 'El Warraq',             'code' => 'cairo-warraq',          'fee' => 65,  'aliases' => ['El Warraq', 'الوراق']],

            // New Cairo / East
            ['name' => 'New Cairo (5th Settlement)', 'code' => 'cairo-new-cairo', 'fee' => 65,   'aliases' => ['New Cairo', '5th Settlement', 'القاهرة الجديدة', 'التجمع الخامس']],
            ['name' => 'Maadi',                 'code' => 'cairo-maadi',           'fee' => 60,  'aliases' => ['Maadi', 'المعادي']],
            ['name' => 'Helwan',                'code' => 'cairo-helwan',          'fee' => 65,  'aliases' => ['Helwan', 'حلوان']],
            ['name' => 'El Basatin',            'code' => 'cairo-basatin',         'fee' => 60,  'aliases' => ['El Basatin', 'البساتين']],
            ['name' => 'El Marg',               'code' => 'cairo-marg',            'fee' => 65,  'aliases' => ['El Marg', 'المرج']],
            ['name' => 'Mostorod',              'code' => 'cairo-mostorod',        'fee' => 70,  'aliases' => ['Mostorod', 'مسطرد']],

            // Other popular areas
            ['name' => 'Shubra El Kheima',      'code' => 'cairo-shubra-kheima',   'fee' => 65,  'aliases' => ['Shubra El Kheima', 'شبرا الخيمة']],
            ['name' => 'Badr City',             'code' => 'cairo-badr',            'fee' => 80,  'aliases' => ['Badr City', 'مدينة بدر']],
        ];

        foreach ($cairoDistricts as $d) {
            Region::updateOrCreate(
                ['code' => $d['code']],
                [
                    'name'         => $d['name'],
                    'type'         => 'cairo_district',
                    'delivery_fee' => $d['fee'],
                    'city_names'   => $d['aliases'],
                ]
            );
        }

        // ── Egyptian Governorates ────────────────────────────────────────
        // type = governorate  (Cairo handled above as districts)
        $governorates = [
            ['name' => 'Alexandria',       'code' => 'gov-alex',       'fee' => 80,  'aliases' => ['Alexandria', 'الإسكندرية']],
            ['name' => 'Giza',             'code' => 'gov-giza',       'fee' => 55,  'aliases' => ['Giza', 'الجيزة']],
            ['name' => 'Qalyubia',         'code' => 'gov-qalyubia',   'fee' => 65,  'aliases' => ['Qalyubia', 'القليوبية', 'Qalyubiyya']],
            ['name' => 'Dakahlia',         'code' => 'gov-dakahlia',   'fee' => 80,  'aliases' => ['Dakahlia', 'الدقهلية']],
            ['name' => 'Sharqia',          'code' => 'gov-sharqia',    'fee' => 80,  'aliases' => ['Sharqia', 'الشرقية', 'Sharkia']],
            ['name' => 'Monufia',          'code' => 'gov-monufia',    'fee' => 80,  'aliases' => ['Monufia', 'المنوفية', 'Menofia']],
            ['name' => 'Beheira',          'code' => 'gov-beheira',    'fee' => 85,  'aliases' => ['Beheira', 'البحيرة']],
            ['name' => 'Kafr El Sheikh',   'code' => 'gov-kafr',       'fee' => 90,  'aliases' => ['Kafr El Sheikh', 'كفر الشيخ']],
            ['name' => 'Gharbia',          'code' => 'gov-gharbia',    'fee' => 85,  'aliases' => ['Gharbia', 'الغربية']],
            ['name' => 'Minya',            'code' => 'gov-minya',      'fee' => 90,  'aliases' => ['Minya', 'المنيا']],
            ['name' => 'Assiut',           'code' => 'gov-assiut',     'fee' => 95,  'aliases' => ['Assiut', 'أسيوط', 'Asyut']],
            ['name' => 'Sohag',            'code' => 'gov-sohag',      'fee' => 95,  'aliases' => ['Sohag', 'سوهاج']],
            ['name' => 'Qena',             'code' => 'gov-qena',       'fee' => 100, 'aliases' => ['Qena', 'قنا']],
            ['name' => 'Luxor',            'code' => 'gov-luxor',      'fee' => 100, 'aliases' => ['Luxor', 'الأقصر']],
            ['name' => 'Aswan',            'code' => 'gov-aswan',      'fee' => 100, 'aliases' => ['Aswan', 'أسوان']],
            ['name' => 'Beni Suef',        'code' => 'gov-benisuef',   'fee' => 85,  'aliases' => ['Beni Suef', 'بني سويف']],
            ['name' => 'Fayoum',           'code' => 'gov-fayoum',     'fee' => 85,  'aliases' => ['Fayoum', 'الفيوم']],
            ['name' => 'Ismailia',         'code' => 'gov-ismailia',   'fee' => 85,  'aliases' => ['Ismailia', 'الإسماعيلية']],
            ['name' => 'Suez',             'code' => 'gov-suez',       'fee' => 85,  'aliases' => ['Suez', 'السويس']],
            ['name' => 'Port Said',        'code' => 'gov-portsaid',   'fee' => 85,  'aliases' => ['Port Said', 'بورسعيد']],
            ['name' => 'Damietta',         'code' => 'gov-damietta',   'fee' => 85,  'aliases' => ['Damietta', 'دمياط']],
            ['name' => 'Matruh',           'code' => 'gov-matruh',     'fee' => 120, 'aliases' => ['Matruh', 'مطروح']],
            ['name' => 'North Sinai',      'code' => 'gov-n-sinai',    'fee' => 120, 'aliases' => ['North Sinai', 'شمال سيناء']],
            ['name' => 'South Sinai',      'code' => 'gov-s-sinai',    'fee' => 120, 'aliases' => ['South Sinai', 'جنوب سيناء']],
            ['name' => 'Red Sea',          'code' => 'gov-redsea',     'fee' => 120, 'aliases' => ['Red Sea', 'البحر الأحمر']],
            ['name' => 'New Valley',       'code' => 'gov-newvalley',  'fee' => 130, 'aliases' => ['New Valley', 'الوادي الجديد']],
        ];

        foreach ($governorates as $g) {
            Region::updateOrCreate(
                ['code' => $g['code']],
                [
                    'name'         => $g['name'],
                    'type'         => 'governorate',
                    'delivery_fee' => $g['fee'],
                    'city_names'   => $g['aliases'],
                ]
            );
        }

        $this->command->info('Seeded ' . count($cairoDistricts) . ' Cairo districts and ' . count($governorates) . ' governorates.');
    }
}
