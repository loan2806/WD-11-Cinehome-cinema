<?php

namespace Database\Seeders;

use App\Models\ChiTietCombo;
use App\Models\ComboItem;
use App\Models\DoAn;
use App\Models\Food;
use Illuminate\Database\Seeder;

class ComboFakeSeeder extends Seeder
{
    public function run(): void
    {
        $combos = [

            'COMBO-1' => [
                [
                    'sku' => 'POPCORN-SWEET',
                    'variant' => 'M',
                    'qty' => 1,
                ],
                [
                    'sku' => 'COKE-330',
                    'variant' => '330ml',
                    'qty' => 2,
                ],
            ],

            'COMBO-FAMILY' => [
                [
                    'sku' => 'POPCORN-CHEESE',
                    'variant' => 'L',
                    'qty' => 2,
                ],
                [
                    'sku' => 'COKE-330',
                    'variant' => '500ml',
                    'qty' => 2,
                ],
                [
                    'sku' => 'PEPSI-330',
                    'variant' => '500ml',
                    'qty' => 2,
                ],
            ],

        ];

        foreach ($combos as $comboSku => $items) {

            $combo = DoAn::where('sku', $comboSku)->first();

            if (!$combo) {
                continue;
            }

            ChiTietCombo::where('combo_food_id', $combo->id)->delete();

            foreach ($items as $item) {

                $food = DoAn::where('sku', $item['sku'])->first();

                if (!$food) {
                    continue;
                }

                $variant = $food->variants()
                    ->where('value', $item['variant'])
                    ->first();

                if (!$variant) {
                    continue;
                }

                ChiTietCombo::create([
                    'combo_food_id'   => $combo->id,
                    'food_variant_id' => $variant->id,
                    'quantity'        => $item['qty'],
                ]);
            }
        }
    }
}