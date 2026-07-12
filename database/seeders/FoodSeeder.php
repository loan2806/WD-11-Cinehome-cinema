<?php

namespace Database\Seeders;

use App\Models\DanhMucDoAn;
use App\Models\DoAn;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        $foods = [
            [
                'sku' => 'POPCORN-SWEET',
                'name' => 'Bắp ngọt',
                'category_name' => 'Đồ ăn',
                'sort_order' => 10,
            ],
            [
                'sku' => 'POPCORN-CHEESE',
                'name' => 'Bắp phô mai',
                'category_name' => 'Đồ ăn',
                'sort_order' => 20,
            ],
            [
                'sku' => 'COKE-330',
                'name' => 'Coca Cola',
                'category_name' => 'Nước uống',
                'sort_order' => 30,
            ],
            [
                'sku' => 'PEPSI-330',
                'name' => 'Pepsi',
                'category_name' => 'Nước uống',
                'sort_order' => 40,
            ],
            [
                'sku' => 'COMBO-1',
                'name' => 'Combo 1 bắp 2 nước',
                'category_name' => 'Combo',
                'sort_order' => 50,
            ],
            [
                'sku' => 'COMBO-FAMILY',
                'name' => 'Combo gia đình',
                'category_name' => 'Combo',
                'sort_order' => 60,
            ],
        ];

        $imagePath = storage_path('app/public/foods');

        $images = collect();

        if (File::exists($imagePath)) {
            $images = collect(File::files($imagePath));
        }

        foreach ($foods as $food) {
            $category = DanhMucDoAn::firstOrCreate(
                ['name' => $food['category_name']],
                ['slug' => Str::slug($food['category_name'])]
            );

            DoAn::updateOrCreate(
                ['sku' => $food['sku']],
                [
                    'name' => $food['name'],
                    'category_id' => $category->id,
                    'sort_order' => $food['sort_order'],
                    'image' => $images->isNotEmpty()
                        ? $images->random()->getFilename()
                        : 'placeholder.png',
                    'description' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
