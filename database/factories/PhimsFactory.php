<?php

namespace Database\Factories;

use App\Models\Phims;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Phims>
 */
class PhimsFactory extends Factory
{
    protected $model = Phims::class;

    public function definition(): array
    {
        $tenPhim = fake()->unique()->sentence(3) . ' (Việt hóa)';

        return [
            'ten_phim'        => $tenPhim,
            // Tạo slug trực tiếp tại factory để không phụ thuộc vào event model khi seed
            'slug'            => Str::slug($tenPhim) . '-' . uniqid(),
            'mo_ta'           => fake()->paragraph(),
            'thoi_luong'      => fake()->randomElement([90, 120, 145, 160]),
            'gioi_han_tuoi'   => fake()->randomElement(['P', 'T13', 'T16', 'T18']),
            'ngon_ngu'        => 'Tiếng Việt / Phụ đề',
            'dao_dien'        => fake()->name(),
            'dien_vien'       => fake()->name() . ', ' . fake()->name(),
        ];
    }
}