<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use \Illuminate\Database\Console\Seeds\WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            QuocGiaSeeder::class,
            TheLoaiSeeder::class,
            PhimSeeder::class,
            TaiKhoanSeeder::class,
            LoaiGheSeeder::class,
            RapChieuPhimSeeder::class,
            PhongChieuSeeder::class,
            SuatChieuSeeder::class,
            VeXemPhimSeeder::class,
            NhanVienSeeder::class,
        ]);
    }
}
