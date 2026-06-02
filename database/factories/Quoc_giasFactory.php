<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\QuocGia;

class QuocGiaFactory extends Factory
{
    protected $model = QuocGia::class;

    public function definition(): array
    {
        $name = fake()->country();

        // generate a simple ISO-like code
        $code = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 2)) ?: 'ZZ';

        return [
            'ten_quoc_gia' => $name,
            'ma_quoc_gia' => $code . fake()->randomNumber(2, true),
            'trang_thai' => 1,
        ];
    }
}
