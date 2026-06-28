<?php

namespace Database\Seeders;

use App\Models\Food;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        $foods = [
            [
                'sku' => 'POPCORN-SWEET',
                'name' => 'Bắp ngọt',
                'category' => 'Bắp rang',
                'price' => 45000,
                'stock_quantity' => 120,
                'min_stock_quantity' => 20,
                'sort_order' => 10,
            ],
            [
                'sku' => 'POPCORN-CHEESE',
                'name' => 'Bắp phô mai',
                'category' => 'Bắp rang',
                'price' => 55000,
                'stock_quantity' => 90,
                'min_stock_quantity' => 15,
                'sort_order' => 20,
            ],
            [
                'sku' => 'DRINK-COKE',
                'name' => 'Coca Cola',
                'category' => 'Nước uống',
                'price' => 25000,
                'stock_quantity' => 180,
                'min_stock_quantity' => 30,
                'sort_order' => 30,
            ],
            [
                'sku' => 'DRINK-PEPSI',
                'name' => 'Pepsi',
                'category' => 'Nước uống',
                'price' => 25000,
                'stock_quantity' => 160,
                'min_stock_quantity' => 30,
                'sort_order' => 40,
            ],
            [
                'sku' => 'COMBO-1CORN-2DRINK',
                'name' => 'Combo 1 bắp 2 nước',
                'category' => 'Combo',
                'price' => 95000,
                'stock_quantity' => 80,
                'min_stock_quantity' => 10,
                'sort_order' => 50,
            ],
            [
                'sku' => 'COMBO-FAMILY',
                'name' => 'Combo gia đình',
                'category' => 'Combo',
                'price' => 155000,
                'stock_quantity' => 45,
                'min_stock_quantity' => 8,
                'sort_order' => 60,
            ],
        ];

        foreach ($foods as $food) {
            Food::updateOrCreate(
                ['sku' => $food['sku']],
                $food + [
                    'image' => null,
                    'description' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
