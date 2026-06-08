<?php

namespace Database\Factories;

use App\Models\RapChieuPhim;
use Illuminate\Database\Eloquent\Factories\Factory;

class RapChieuPhimFactory extends Factory
{
    protected $model = RapChieuPhim::class;

    public function definition(): array
    {
        return [
            'ten_rap' => 'CineHome ' . fake()->city(),
            'dia_chi' => fake()->address(),
            'thanh_pho' => fake()->randomElement(['Hà Nội', 'Hưng Yên', 'Hồ Chí Minh']),
            'so_dien_thoai' => fake()->phoneNumber(),
            'hinh_anh' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=1200',
            'vi_do' => '21.0285',
            'kinh_do' => '105.8542',
        ];
    }
}