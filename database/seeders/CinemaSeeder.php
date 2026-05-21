<?php

namespace Database\Seeders;

use App\Models\Cinema;
use Illuminate\Database\Seeder;

class CinemaSeeder extends Seeder
{
    /**
     * Rạp mẫu có tọa độ thật (vùng ven Hà Nội) để kiểm tra bản đồ và Haversine.
     */
    public function run(): void
    {
        $rows = [
            [
                'name' => 'Cinema Đông Anh',
                'address' => 'Khu trung tâm huyện Đông Anh, thị trấn Đông Anh',
                'city' => 'Hà Nội',
                'latitude' => 21.136812,
                'longitude' => 105.847201,
                'phone' => '0243 111 0001',
                'status' => 'active',
            ],
            [
                'name' => 'Cinema Thanh Oai',
                'address' => 'Khu đô thị Kim Bảng, huyện Thanh Oai',
                'city' => 'Hà Nội',
                'latitude' => 20.856211,
                'longitude' => 105.766552,
                'phone' => '0243 111 0002',
                'status' => 'active',
            ],
            [
                'name' => 'Cinema Phúc Thọ',
                'address' => 'Đường Hồ Chí Minh, thị trấn Phúc Thọ',
                'city' => 'Hà Nội',
                'latitude' => 21.094552,
                'longitude' => 105.567811,
                'phone' => '0243 111 0003',
                'status' => 'active',
            ],
            [
                'name' => 'Cinema Thường Tín',
                'address' => 'Khu phố Chùa, thị trấn Thường Tín',
                'city' => 'Hà Nội',
                'latitude' => 20.854331,
                'longitude' => 105.861921,
                'phone' => '0243 111 0004',
                'status' => 'active',
            ],
            [
                'name' => 'Cinema Quốc Oai',
                'address' => 'Khu liên hợp thị trấn Quốc Oai',
                'city' => 'Hà Nội',
                'latitude' => 20.930118,
                'longitude' => 105.641552,
                'phone' => '0243 111 0005',
                'status' => 'active',
            ],
        ];

        foreach ($rows as $data) {
            Cinema::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}
