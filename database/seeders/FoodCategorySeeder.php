<?php

namespace Database\Seeders;

use App\Models\FoodCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;


class FoodCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Đồ ăn',
            'Nước uống',
            'Combo',
        ];

        foreach ($categories as $name) {
            FoodCategory::updateOrCreate(
                ['name' => $name],
                [   
                    'slug' => Str::slug($name),
                    'name' => $name,
                ]
            );
        }
    }
}