<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QuocGia;

class QuocGiaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['ten_quoc_gia' => 'Việt Nam', 'ma_quoc_gia' => 'VN', 'trang_thai' => 1],
            ['ten_quoc_gia' => 'United States', 'ma_quoc_gia' => 'US', 'trang_thai' => 1],
            ['ten_quoc_gia' => 'United Kingdom', 'ma_quoc_gia' => 'GB', 'trang_thai' => 1],
            ['ten_quoc_gia' => 'Japan', 'ma_quoc_gia' => 'JP', 'trang_thai' => 1],
            ['ten_quoc_gia' => 'South Korea', 'ma_quoc_gia' => 'KR', 'trang_thai' => 1],
            ['ten_quoc_gia' => 'China', 'ma_quoc_gia' => 'CN', 'trang_thai' => 1],
            ['ten_quoc_gia' => 'France', 'ma_quoc_gia' => 'FR', 'trang_thai' => 1],
            ['ten_quoc_gia' => 'Germany', 'ma_quoc_gia' => 'DE', 'trang_thai' => 1],
            ['ten_quoc_gia' => 'Australia', 'ma_quoc_gia' => 'AU', 'trang_thai' => 1],
            ['ten_quoc_gia' => 'India', 'ma_quoc_gia' => 'IN', 'trang_thai' => 1],
        ];

            // Use upsert to avoid duplicate key errors when seeding multiple times
            QuocGia::upsert($countries, ['ma_quoc_gia'], ['ten_quoc_gia', 'trang_thai']);
    }
}
