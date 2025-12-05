<?php

namespace Database\Seeders;

use App\Models\BostaCity;
use Illuminate\Database\Seeder;

class BostaCitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            ['bosta_id' => 'Jrb6X6ucjiYgMP4T7', 'name' => 'Alexandria', 'name_ar' => 'الاسكندريه', 'code' => 'EG-02', 'alias' => 'الإسكندرية', 'sector' => 2, 'pickup_availability' => true, 'drop_off_availability' => true, 'hub' => ['_id' => 'sX3kDPgeSLy9sCZTc', 'name' => 'Alexandria Hub']],
            ['bosta_id' => '7mDPAohM3ArSZmWTm', 'name' => 'Assuit', 'name_ar' => 'اسيوط', 'code' => 'EG-17', 'alias' => 'أسيوط', 'sector' => 4, 'pickup_availability' => true, 'drop_off_availability' => true, 'hub' => ['_id' => '-yfYSfnZG', 'name' => 'Assiut Hub']],
            ['bosta_id' => 'kLvZ5JY6LJPL5chzN', 'name' => 'Aswan', 'name_ar' => 'اسوان', 'code' => 'EG-21', 'alias' => 'أسوان', 'sector' => 5, 'pickup_availability' => true, 'drop_off_availability' => true, 'hub' => ['_id' => '030000000050', 'name' => 'Aswan Hub']],
            ['bosta_id' => 'LzbbvTzZ7D2CgE2PL', 'name' => 'Bani Suif', 'name_ar' => 'بني سويف', 'code' => 'EG-16', 'alias' => 'Beni Suef', 'sector' => 4, 'pickup_availability' => true, 'drop_off_availability' => true, 'hub' => ['_id' => '030000000002', 'name' => 'Beni Suef Hub']],
            ['bosta_id' => 'g3GchTSmCgR2JynsJ', 'name' => 'Behira', 'name_ar' => 'البحيره', 'code' => 'EG-04', 'alias' => 'البحيرة', 'sector' => 2, 'pickup_availability' => true, 'drop_off_availability' => true, 'hub' => ['_id' => '030000000008', 'name' => 'Behira Hub']],
            ['bosta_id' => 'FceDyHXwpSYYF9zGW', 'name' => 'Cairo', 'name_ar' => 'القاهره', 'code' => 'EG-01', 'alias' => 'القاهرة', 'sector' => 1, 'pickup_availability' => true, 'drop_off_availability' => true, 'hub' => null],
            ['bosta_id' => 'RrDhS8YYsXAwZ9Zfo', 'name' => 'Dakahlia', 'name_ar' => 'الدقهليه', 'code' => 'EG-05', 'alias' => 'الدقهلية', 'sector' => 3, 'pickup_availability' => true, 'drop_off_availability' => true, 'hub' => ['_id' => 'NemvLs9CtpZynzAEC', 'name' => 'El-Mansoura Hub']],
            ['bosta_id' => 'qoZvYcZ8Cqji4pGp5', 'name' => 'Damietta', 'name_ar' => 'دمياط', 'code' => 'EG-14', 'alias' => 'Damietta', 'sector' => 3, 'pickup_availability' => true, 'drop_off_availability' => true, 'hub' => ['_id' => 'P92etvNPPibhC53cE', 'name' => 'Domiatta Hub']],
            ['bosta_id' => 'yp3atroeTwnyiBNKE', 'name' => 'El Kalioubia', 'name_ar' => 'القليوبيه', 'code' => 'EG-06', 'alias' => 'القليوبية', 'sector' => 3, 'pickup_availability' => true, 'drop_off_availability' => true, 'hub' => ['_id' => 'rSZY93PmRd6fWhPcL', 'name' => 'Banha Hub']],
            ['bosta_id' => 'BW5MiNxEirB7tuz2y', 'name' => 'Fayoum', 'name_ar' => 'الفيوم', 'code' => 'EG-15', 'alias' => 'Faiyum', 'sector' => 4, 'pickup_availability' => true, 'drop_off_availability' => true, 'hub' => ['_id' => '030000000018', 'name' => 'Fayoum Hub']],
            ['bosta_id' => 'K3RwC677J8kJytdZD', 'name' => 'Gharbia', 'name_ar' => 'الغربيه', 'code' => 'EG-07', 'alias' => 'الغربية', 'sector' => 3, 'pickup_availability' => true, 'drop_off_availability' => true, 'hub' => ['_id' => 'vwqbdwsR3evA64Wwj', 'name' => 'Tanta Hub']],
            ['bosta_id' => '0064Qb0OgcA', 'name' => 'Giza', 'name_ar' => 'الجيزه', 'code' => 'EG-25', 'alias' => 'الجيزة', 'sector' => 1, 'pickup_availability' => true, 'drop_off_availability' => true, 'hub' => ['_id' => 'gX7ZErsWaTwFQuQsj', 'name' => 'Mohandseen Hub']],
            ['bosta_id' => 'PJqNriLtFtx2cfkKP', 'name' => 'Ismailia', 'name_ar' => 'الاسماعيليه', 'code' => 'EG-11', 'alias' => 'Isamilia', 'sector' => 3, 'pickup_availability' => true, 'drop_off_availability' => true, 'hub' => ['_id' => 'p6pZtWMSMaQJPyJcK', 'name' => 'Ismailia Hub']],
            ['bosta_id' => 'ByP7rFCjL6XzF6j4S', 'name' => 'Kafr Alsheikh', 'name_ar' => 'كفر الشيخ', 'code' => 'EG-08', 'alias' => 'Kafr Al-Sheikh', 'sector' => 3, 'pickup_availability' => true, 'drop_off_availability' => true, 'hub' => ['_id' => '030000000054', 'name' => 'Kafrelsheikh Hub']],
            ['bosta_id' => 'wgYEdH2WMzxGE2Ztp', 'name' => 'Luxor', 'name_ar' => 'الاقصر', 'code' => 'EG-22', 'alias' => 'الأقصر', 'sector' => 5, 'pickup_availability' => false, 'drop_off_availability' => true, 'hub' => ['_id' => '030000000049', 'name' => 'Luxor Hub']],
            ['bosta_id' => 'KBpGiRZJMIx', 'name' => 'Matrouh', 'name_ar' => 'مرسي مطروح', 'code' => 'EG-28', 'alias' => 'Matrouh', 'sector' => 5, 'pickup_availability' => true, 'drop_off_availability' => true, 'hub' => ['_id' => '030000000013', 'name' => 'Matrouh Hub']],
            ['bosta_id' => 'si6eLnKjXqTFTMBj9', 'name' => 'Menya', 'name_ar' => 'المنيا', 'code' => 'EG-19', 'alias' => 'Minya', 'sector' => 4, 'pickup_availability' => true, 'drop_off_availability' => true, 'hub' => ['_id' => '030000000001', 'name' => 'Minya Hub']],
            ['bosta_id' => 'ruBSjGBDX9wpRa3cc', 'name' => 'Monufia', 'name_ar' => 'المنوفيه', 'code' => 'EG-09', 'alias' => 'Menofia', 'sector' => 3, 'pickup_availability' => true, 'drop_off_availability' => true, 'hub' => ['_id' => '030000000004', 'name' => 'Monufia Hub']],
            ['bosta_id' => 'w4yDVHVJWqa4HpbzA', 'name' => 'New Valley', 'name_ar' => 'الوادي الجديد', 'code' => 'EG-24', 'alias' => 'New Valley', 'sector' => 7, 'pickup_availability' => false, 'drop_off_availability' => true, 'hub' => ['_id' => '030000000083', 'name' => 'New Valley Hub']],
            ['bosta_id' => '2hGtNLfRgqGrJjnW9', 'name' => 'North Coast', 'name_ar' => 'الساحل الشمالي', 'code' => 'EG-03', 'alias' => 'North Coast', 'sector' => 6, 'pickup_availability' => true, 'drop_off_availability' => true, 'hub' => ['_id' => 'sX3kDPgeSLy9sCZTc', 'name' => 'Alexandria Hub']],
            ['bosta_id' => 'ZuCaDAVQlPT', 'name' => 'North Sinai', 'name_ar' => 'شمال سيناء', 'code' => 'EG-27', 'alias' => 'North Sinai', 'sector' => 7, 'pickup_availability' => false, 'drop_off_availability' => false, 'hub' => null],
            ['bosta_id' => 'skFtf6ZmKo8kBEBDK', 'name' => 'Port Said', 'name_ar' => 'بور سعيد', 'code' => 'EG-13', 'alias' => 'Port Said', 'sector' => 3, 'pickup_availability' => true, 'drop_off_availability' => true, 'hub' => ['_id' => 'P92etvNPPibhC53cE', 'name' => 'Domiatta Hub']],
            ['bosta_id' => 'vfTHTes3uGjAszgtg', 'name' => 'Qena', 'name_ar' => 'قنا', 'code' => 'EG-20', 'alias' => 'Qena', 'sector' => 5, 'pickup_availability' => true, 'drop_off_availability' => true, 'hub' => ['_id' => '030000000011', 'name' => 'Qena Hub']],
            ['bosta_id' => 'r5TscLCNSjR2GimxQ', 'name' => 'Red Sea', 'name_ar' => 'البحر الاحمر', 'code' => 'EG-23', 'alias' => 'Red Sea', 'sector' => 5, 'pickup_availability' => true, 'drop_off_availability' => true, 'hub' => ['_id' => '030000000006', 'name' => 'Huarghada Hub']],
            ['bosta_id' => '6ExcoGbpYHnggP8JD', 'name' => 'Sharqia', 'name_ar' => 'الشرقيه', 'code' => 'EG-10', 'alias' => 'الشرقية', 'sector' => 3, 'pickup_availability' => true, 'drop_off_availability' => true, 'hub' => ['_id' => 'qTAYcfpuacECeX9d7', 'name' => 'El-Sharkia Hub']],
            ['bosta_id' => 'n3EENg2adhuR9xBZK', 'name' => 'Sohag', 'name_ar' => 'سوهاج', 'code' => 'EG-18', 'alias' => 'Sohag', 'sector' => 4, 'pickup_availability' => true, 'drop_off_availability' => true, 'hub' => ['_id' => '030000000079', 'name' => 'Sohag Hub']],
            ['bosta_id' => 'nG_c44vHQht', 'name' => 'South Sinai', 'name_ar' => 'جنوب سيناء', 'code' => 'EG-26', 'alias' => 'South Sinai', 'sector' => 7, 'pickup_availability' => false, 'drop_off_availability' => true, 'hub' => ['_id' => '030000000031', 'name' => 'Sharm EL Sheikh Hub']],
            ['bosta_id' => 'PickurJ5uJZ9rDTHW', 'name' => 'Suez', 'name_ar' => 'السويس', 'code' => 'EG-12', 'alias' => 'Suez', 'sector' => 3, 'pickup_availability' => true, 'drop_off_availability' => true, 'hub' => ['_id' => '4d9a064f4b341bf748f5rq85', 'name' => 'Suez Hub']],
        ];

        foreach ($cities as $city) {
            BostaCity::updateOrCreate(
                ['bosta_id' => $city['bosta_id']],
                $city
            );
        }

        $this->command->info('BOSTA cities seeded successfully!');
    }
}
