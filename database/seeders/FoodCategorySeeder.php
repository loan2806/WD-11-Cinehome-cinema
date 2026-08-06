<?php

namespace Database\Seeders;

use App\Models\DanhMucDoAn;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;


class FoodCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
        [
            'name' => 'Đồ ăn',
            'is_combo' => false,
        ],
        [
            'name' => 'Nước uống',
            'is_combo' => false,
        ],
        [
            'name' => 'Combo',
            'is_combo' => true,
        ],
    ];

    foreach ($categories as $category) {

        DanhMucDoAn::updateOrCreate(
            ['name' => $category['name']],
            [
                'slug' => Str::slug($category['name']),
                'name' => $category['name'],
                'is_combo' => $category['is_combo'],
            ]
        );
        }
    }
}