<?php

namespace Database\Seeders;

use App\Models\CaiDatHeThong;
use Illuminate\Database\Seeder;

class CaiDatHeThongSeeder extends Seeder
{
    public function run(): void
    {
        CaiDatHeThong::updateOrCreate(
            ['id' => 1],
            [
                'ten_rap' => 'CineHome Cinema',
                'hotline' => '1900 1234',
                'email' => 'contact@cinehome.vn',
                'dia_chi' => 'Bắc Từ Liêm, Hà Nội',
                'thoi_gian_giu_ghe' => 10,
                'so_ve_toi_da_don' => 8,
                'thoi_gian_don_phong' => 15,
                'so_ngay_duoc_dat_ve_truoc' => 7,
                'gia_ngay_thuong' => 75000,
                'gia_cuoi_tuan' => 120000,
                'phu_thu_ghe_vip' => 20000,
                'bat_tat_vnpay' => false,
                'bat_tat_momo' => false,
            ]
        );
    }
}