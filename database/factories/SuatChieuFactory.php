<?php

namespace Database\Factories;

use App\Models\SuatChieu;
use App\Models\Phims;
use App\Models\RapChieuPhim;
use Illuminate\Database\Eloquent\Factories\Factory;

class SuatChieuFactory extends Factory
{
    protected $model = SuatChieu::class;

    public function definition(): array
    {
        return [
            'phim_id' => Phims::inRandomOrder()->value('id') ?? Phims::factory(),
            'rap_chieu_phim_id' => RapChieuPhim::inRandomOrder()->value('id') ?? RapChieuPhim::factory(),
            'thoi_gian_chieu' => now()->addDays(rand(1, 7))->setTime(rand(8, 23), fake()->randomElement([0, 30])),
            'gia_ve' => fake()->randomElement([80000, 90000, 120000]),
        ];
    }
}