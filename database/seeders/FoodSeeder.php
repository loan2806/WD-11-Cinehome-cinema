<?php

namespace Database\Seeders;

use App\Models\Food;
use App\Models\FoodCategory;
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
                'image' => 'bap1.png',
            ],
            [
                'sku' => 'POPCORN-CHEESE',
                'name' => 'Bắp phô mai',
                'category_name' => 'Đồ ăn',
                'sort_order' => 20,
                'image' => 'bap.png',
            ],
            [
                'sku' => 'COKE-330',
                'name' => 'Coca Cola',
                'category_name' => 'Nước uống',
                'sort_order' => 30,
                'image' => 'coca.png',
            ],
            [
                'sku' => 'PEPSI-330',
                'name' => 'Pepsi',
                'category_name' => 'Nước uống',
                'sort_order' => 40,
                'image' => 'pep.png',
            ],
            [
                'sku' => 'COMBO-1',
                'name' => 'Combo 1 bắp 2 nước',
                'category_name' => 'Combo',
                'sort_order' => 50,
                'image' => 'cb1.png',
            ],
            [
                'sku' => 'COMBO-FAMILY',
                'name' => 'Combo gia đình',
                'category_name' => 'Combo',
                'sort_order' => 60,
                'image' => 'cbfml.png',
            ],
        ];
        $images = File::files(public_path('storage/foods'));
        foreach ($foods as $food) {

            $category = FoodCategory::firstOrCreate(
                ['name' => $food['category_name']],
                ['slug' => Str::slug($food['category_name'])]
            );

            Food::updateOrCreate(
                ['sku' => $food['sku']],
                [
                    'name' => $food['name'],
                    'category_id' => $category?->id,
                    'sort_order' => $food['sort_order'],
                    'image' => count($images)
                        ? collect($images)->random()->getFilename()
                        : null,
                    'description' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
