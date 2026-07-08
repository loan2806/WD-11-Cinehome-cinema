<?php

namespace Database\Seeders;

use App\Models\BienTheDoAn;
use App\Models\DoAn;
use App\Models\Food;
use App\Models\FoodVariant;
use Illuminate\Database\Seeder;

class FoodVariantSeeder extends Seeder
{
    public function run(): void
    {
        $variants = [

            // Bắp ngọt
            [
                'food_sku' => 'POPCORN-SWEET',
                'value' => 'S',
                'price' => 45000,
                'stock_quantity' => 120,
            ],
            [
                'food_sku' => 'POPCORN-SWEET',
                'value' => 'M',
                'price' => 55000,
                'stock_quantity' => 100,
            ],
            [
                'food_sku' => 'POPCORN-SWEET',
                'value' => 'L',
                'price' => 65000,
                'stock_quantity' => 80,
            ],

            // Bắp phô mai
            [
                'food_sku' => 'POPCORN-CHEESE',
                'value' => 'S',
                'price' => 50000,
                'stock_quantity' => 100,
            ],
            [
                'food_sku' => 'POPCORN-CHEESE',
                'value' => 'M',
                'price' => 60000,
                'stock_quantity' => 90,
            ],
            [
                'food_sku' => 'POPCORN-CHEESE',
                'value' => 'L',
                'price' => 70000,
                'stock_quantity' => 70,
            ],

            // Coca
            [
                'food_sku' => 'COKE-330',
                'value' => '330ml',
                'price' => 25000,
                'stock_quantity' => 180,
            ],
            [
                'food_sku' => 'COKE-330',
                'value' => '500ml',
                'price' => 35000,
                'stock_quantity' => 120,
            ],

            // Pepsi
            [
                'food_sku' => 'PEPSI-330',
                'value' => '330ml',
                'price' => 25000,
                'stock_quantity' => 160,
            ],
            [
                'food_sku' => 'PEPSI-330',
                'value' => '500ml',
                'price' => 35000,
                'stock_quantity' => 100,
            ],
            [
                'food_sku' => 'COMBO-1',
                'value' => 'Standard',
                'price' => 90000,
                'stock_quantity' => 50,
            ],
            [
                'food_sku' => 'COMBO-FAMILY',
                'value' => 'Family',
                'price' => 150000,
                'stock_quantity' => 30,
            ],
        ];

        foreach ($variants as $variant) {

            $food = DoAn::where(
                'sku',
                $variant['food_sku']
            )->first();

            if (! $food) {
                continue;
            }

            BienTheDoAn::updateOrCreate(
                [
                    'food_id' => $food->id,
                    'value' => $variant['value'],
                ],
                [
                    'price' => $variant['price'],
                    'stock_quantity' => $variant['stock_quantity'],
                    'is_active' => true,
                ]
            );
        }
    }
}
