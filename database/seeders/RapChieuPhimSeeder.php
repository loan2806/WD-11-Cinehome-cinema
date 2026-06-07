<?php

namespace Database\Seeders;

use App\Models\RapChieuPhim;
use Illuminate\Database\Seeder;

class RapChieuPhimSeeder extends Seeder
{
    public function run(): void
    {
        RapChieuPhim::firstOrCreate(
            ['ten_rap' => 'Cinehome Cinema'],
            [
                'dia_chi' => '123 Đường Nguyễn Văn Linh, Quận 7, TP.HCM',
                'thanh_pho' => 'TP. Hồ Chí Minh',
                'so_dien_thoai' => '028 1234 5678',
                'hinh_anh' => null,
                'vi_do' => 10.7290257,
                'kinh_do' => 106.6968571,
            ]
        );
    }
}
