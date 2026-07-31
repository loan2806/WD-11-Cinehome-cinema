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

            $images = collect(File::exists(public_path('storage/foods')) ? File::files(public_path('storage/foods')) : []);

            // Tạo món và category
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
                        'image' => $images->isNotEmpty()
                            ? $images->random()->getFilename()
                            : 'placeholder.png',
                        'description' => null,
                        'is_active' => true,
                        // giá mặc định null, sẽ set cho combos sau nếu cần
                        'price' => null,
                    ]
                );
            }

            // Tạo biến thể (variants) cho các món cụ thể
            // Popcorn variants
            $this->createOrUpdateVariant('POPCORN-SWEET', 'Small', 50000);
            $this->createOrUpdateVariant('POPCORN-SWEET', 'Large', 70000);
            $this->createOrUpdateVariant('POPCORN-CHEESE', 'Small', 55000);
            $this->createOrUpdateVariant('POPCORN-CHEESE', 'Large', 75000);

            // Coca variants
            $this->createOrUpdateVariant('COKE-330', '300ml', 15000);
            $this->createOrUpdateVariant('COKE-330', '550ml', 25000);

            // Pepsi variants
            $this->createOrUpdateVariant('PEPSI-330', '300ml', 14000);
            $this->createOrUpdateVariant('PEPSI-330', '550ml', 24000);

            // Thiết lập combo items và giá combo
            // Combo 1 bắp 2 nước: 1 x Popcorn Small + 2 x Coke 300ml, price admin set 30000 (ví dụ)
            $combo1 = DoAn::where('sku', 'COMBO-1')->first();
            if ($combo1) {
                // Xoá các chi tiết cũ nếu có
                $combo1->comboItems()->delete();

                $popcornVariant = BienTheDoAn::whereHas('doAn', function ($q) {
                    $q->where('sku', 'POPCORN-SWEET');
                })->where('value', 'Small')->first();

                $coke300 = BienTheDoAn::whereHas('doAn', function ($q) {
                    $q->where('sku', 'COKE-330');
                })->where('value', '300ml')->first();

                if ($popcornVariant) {
                    ChiTietCombo::create([
                        'combo_food_id' => $combo1->id,
                        'food_variant_id' => $popcornVariant->id,
                        'quantity' => 1,
                    ]);
                }

                if ($coke300) {
                    // thêm 2 nước
                    ChiTietCombo::create([
                        'combo_food_id' => $combo1->id,
                        'food_variant_id' => $coke300->id,
                        'quantity' => 2,
                    ]);
                }

                // Đặt giá combo do admin quy định (ví dụ 30000)
                $combo1->update(['price' => 30000]);
            }

            // Combo gia đình: 2 x Popcorn Large + 4 x Drink (mỗi 1 nước 550ml) - giá ví dụ 200000
            $comboFamily = DoAn::where('sku', 'COMBO-FAMILY')->first();
            if ($comboFamily) {
                $comboFamily->comboItems()->delete();

                $popcornLarge = BienTheDoAn::whereHas('doAn', function ($q) {
                    $q->where('sku', 'POPCORN-SWEET');
                })->where('value', 'Large')->first();

                $drink550 = BienTheDoAn::whereHas('doAn', function ($q) {
                    $q->whereIn('sku', ['COKE-330', 'PEPSI-330']);
                })->where('value', '550ml')->first();

                if ($popcornLarge) {
                    ChiTietCombo::create([
                        'combo_food_id' => $comboFamily->id,
                        'food_variant_id' => $popcornLarge->id,
                        'quantity' => 2,
                    ]);
                }

                if ($drink550) {
                    ChiTietCombo::create([
                        'combo_food_id' => $comboFamily->id,
                        'food_variant_id' => $drink550->id,
                        'quantity' => 4,
                    ]);
                }

                $comboFamily->update(['price' => 200000]);
            }
        });
    }

    /**
     * Helper: tạo hoặc update variant dựa trên food sku và value
     *
     * @param string $foodSku
     * @param string $value
     * @param int|float $price
     * @return void
     */
    protected function createOrUpdateVariant(string $foodSku, string $value, $price): void
    {
        $food = DoAn::where('sku', $foodSku)->first();
        if (! $food) return;

        // giả sử bảng biến thể có các cột: value, price, stock_quantity, is_active
        $food->variants()->updateOrCreate(
            ['value' => $value],
            [
                'price' => (float) $price,
                'stock_quantity' => 100,
                'is_active' => true,
            ]
        );
    }
}
