<?php

namespace Database\Factories;

use App\Models\SuatChieu;
use App\Models\Phims;
use App\Models\PhongChieu;
use Illuminate\Database\Eloquent\Factories\Factory;

class SuatChieuFactory extends Factory
{
    protected $model = SuatChieu::class;

    public function definition(): array
    {
        $phongChieu = PhongChieu::inRandomOrder()->first();
        $phim = Phims::inRandomOrder()->first();
        $thoiGianChieu = now()->addDays(rand(1, 7))->setTime(rand(8, 22), fake()->randomElement([0, 30]));
        $thoiLuong = $phim->thoi_luong ?? 120;
        $thoiGianKetThuc = (clone $thoiGianChieu)->addMinutes($thoiLuong + 15);

        return [
            'phim_id' => $phim->id,
            'rap_chieu_phim_id' => $phongChieu->rap_chieu_phim_id,
            'phong_chieu_id' => $phongChieu->id,
            'thoi_gian_chieu' => $thoiGianChieu,
            'thoi_gian_ket_thuc' => $thoiGianKetThuc,
            'thoi_luong' => $thoiLuong,
            'gia_ve' => fake()->randomElement([75000, 85000, 100000, 120000]),
        ];
    }
}
