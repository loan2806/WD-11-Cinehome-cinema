<?php

namespace Database\Seeders;

use App\Models\LoaiGhe;
use Illuminate\Database\Seeder;

class LoaiGheSeeder extends Seeder
{
    public function run(): void
    {
        $loaiGhes = [
            [
                'ten_loai' => 'Thường',
                'mo_ta' => 'Ghế ngồi tiêu chuẩn',
                'phu_thu' => 0,
                'mau_sac' => '#4b5563',
                'la_couple' => false,
            ],
            [
                'ten_loai' => 'VIP',
                'mo_ta' => 'Ghế ngồi cao cấp, rộng rãi hơn',
                'phu_thu' => 20000,
                'mau_sac' => '#facc15',
                'la_couple' => false,
            ],
            [
                'ten_loai' => 'Couple',
                'mo_ta' => 'Ghế đôi dành cho 2 người',
                'phu_thu' => 50000,
                'mau_sac' => '#f43f5e',
                'la_couple' => true,
            ],
        ];

        foreach ($loaiGhes as $loai) {
            LoaiGhe::firstOrCreate(
                ['ten_loai' => $loai['ten_loai']],
                $loai
            );
        }
    }
}
