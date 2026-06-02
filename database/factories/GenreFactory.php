<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Genre;

class GenreFactory extends Factory
{
    protected $model = Genre::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Hành động',
            'Tình cảm',
            'Hài',
            'Kinh dị',
            'Phiêu lưu',
            'Trinh thám',
            'Hoạt hình',
        ]);

        return [
            'ten_the_loai' => $name,
            'slug' => Str::slug($name),
            'mo_ta' => fake()->sentence(),
            'trang_thai' => 1,
        ];
    }
}
