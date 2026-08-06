<?php

namespace Database\Seeders;

use App\Models\DanhMucDoAn;
use App\Models\DoAn;
use App\Models\BienTheDoAn;
use App\Models\ChiTietCombo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
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
                    'sku' => 'POPCORN-SALTY',
                    'name' => 'Bắp mặn',
                    'category_name' => 'Đồ ăn',
                    'sort_order' => 30,
                ],
                [
                    'sku' => 'COKE-330',
                    'name' => 'Coca Cola',
                    'category_name' => 'Nước uống',
                    'sort_order' => 40,
                ],
                [
                    'sku' => 'PEPSI-330',
                    'name' => 'Pepsi',
                    'category_name' => 'Nước uống',
                    'sort_order' => 50,
                ],
                [
                    'sku' => 'FANTA-330',
                    'name' => 'Fanta cam',
                    'category_name' => 'Nước uống',
                    'sort_order' => 60,
                ],
                [
                    'sku' => 'COMBO-1',
                    'name' => 'Combo 1 bắp 2 nước',
                    'category_name' => 'Combo',
                    'sort_order' => 70,
                ],
                [
                    'sku' => 'COMBO-FAMILY',
                    'name' => 'Combo gia đình',
                    'category_name' => 'Combo',
                    'sort_order' => 80,
                ],
            ];

            $images = collect(File::exists(public_path('storage/foods')) ? File::files(public_path('storage/foods')) : []);

            foreach ($foods as $foodData) {
                $category = DanhMucDoAn::updateOrCreate(
                    ['name' => $foodData['category_name']],
                    [
                        'slug' => Str::slug($foodData['category_name']),
                        'is_combo' => $foodData['category_name'] === 'Combo',
                    ]
                );

                DoAn::updateOrCreate(
                    ['sku' => $foodData['sku']],
                    [
                        'name' => $foodData['name'],
                        'category_id' => $category->id,
                        'sort_order' => $foodData['sort_order'],
                        'image' => $images->isNotEmpty() ? $images->random()->getFilename() : 'placeholder.png',
                        'description' => fake()->sentence(8),
                        'is_active' => true,
                        'price' => null,
                    ]
                );
            }

            $this->createOrUpdateVariant('POPCORN-SWEET', 'Small', 50000, 120);
            $this->createOrUpdateVariant('POPCORN-SWEET', 'Large', 70000, 80);
            $this->createOrUpdateVariant('POPCORN-CHEESE', 'Small', 55000, 110);
            $this->createOrUpdateVariant('POPCORN-CHEESE', 'Large', 75000, 90);
            $this->createOrUpdateVariant('POPCORN-SALTY', 'Small', 52000, 100);
            $this->createOrUpdateVariant('POPCORN-SALTY', 'Large', 72000, 75);
            $this->createOrUpdateVariant('COKE-330', '330ml', 15000, 180);
            $this->createOrUpdateVariant('COKE-330', '550ml', 25000, 120);
            $this->createOrUpdateVariant('PEPSI-330', '330ml', 14000, 160);
            $this->createOrUpdateVariant('PEPSI-330', '550ml', 24000, 100);
            $this->createOrUpdateVariant('FANTA-330', '330ml', 14000, 140);
            $this->createOrUpdateVariant('FANTA-330', '550ml', 24000, 90);

            $this->createCombo('COMBO-1', [
                ['food_sku' => 'POPCORN-SWEET', 'variant' => 'Small', 'quantity' => 1],
                ['food_sku' => 'COKE-330', 'variant' => '330ml', 'quantity' => 2],
            ], 30000);

            $this->createCombo('COMBO-FAMILY', [
                ['food_sku' => 'POPCORN-CHEESE', 'variant' => 'Large', 'quantity' => 2],
                ['food_sku' => 'COKE-330', 'variant' => '550ml', 'quantity' => 2],
                ['food_sku' => 'PEPSI-330', 'variant' => '550ml', 'quantity' => 2],
            ], 200000);
        });
    }

    protected function createOrUpdateVariant(string $foodSku, string $value, float $price, int $stock): void
    {
        $food = DoAn::where('sku', $foodSku)->first();
        if (! $food) {
            return;
        }

        $food->variants()->updateOrCreate(
            ['value' => $value],
            [
                'price' => $price,
                'stock_quantity' => $stock,
                'is_active' => true,
            ]
        );
    }

    protected function createCombo(string $comboSku, array $items, float $price): void
    {
        $combo = DoAn::where('sku', $comboSku)->first();
        if (! $combo) {
            return;
        }

        $combo->comboItems()->delete();

        foreach ($items as $item) {
            $food = DoAn::where('sku', $item['food_sku'])->first();
            if (! $food) {
                continue;
            }

            $variant = $food->variants()->where('value', $item['variant'])->first();
            if (! $variant) {
                continue;
            }

            ChiTietCombo::create([
                'combo_food_id' => $combo->id,
                'food_variant_id' => $variant->id,
                'quantity' => $item['quantity'],
            ]);
        }

        $combo->update(['price' => $price]);
    }
}
