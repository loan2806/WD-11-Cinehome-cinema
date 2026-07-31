<?php

namespace App\Services;

use App\Models\BienTheDoAn;
use App\Models\DoAn;

class FoodInventoryService
{
    /**
     * Trừ kho theo danh sách món (mỗi phần tử cần key food_id, quantity;
     * có thể kèm variant_id nếu khách chọn một biến thể/size cụ thể).
     * Combo sẽ tự động trừ kho của từng biến thể thành phần.
     */
    public function deduct(iterable $items): void
    {
        foreach ($this->groupItems($items) as $group) {
            $food = DoAn::with(['category', 'comboItems.variant'])->lockForUpdate()->find($group['food_id']);

            if (!$food) {
                continue;
            }

            $quantity = $group['quantity'];

            if ($food->isCombo()) {
                $this->deductCombo($food, $quantity);
                continue;
            }

            $variant = $this->resolveVariantForUpdate($food, $group['variant_id']);

            if (!$variant || $variant->stock_quantity < $quantity) {
                $available = (int) ($variant?->stock_quantity ?? 0);

                throw new \RuntimeException("Món {$food->name} chỉ còn {$available}, không đủ để đặt {$quantity} phần.");
            }

            $variant->decrement('stock_quantity', $quantity);
        }
    }

    /**
     * Hoàn lại kho (dùng khi hủy/xóa hóa đơn hoặc vé).
     */
    public function restore(iterable $items): void
    {
        foreach ($this->groupItems($items) as $group) {
            $food = DoAn::with(['category', 'comboItems.variant'])->find($group['food_id']);

            if (!$food) {
                continue;
            }

            $quantity = $group['quantity'];

            if ($food->isCombo()) {
                $this->restoreCombo($food, $quantity);
                continue;
            }

            $variant = $this->resolveVariantForUpdate($food, $group['variant_id']);

            if ($variant) {
                $variant->increment('stock_quantity', $quantity);
            }
        }
    }

    /**
     * Gom nhóm theo cặp (food_id, variant_id) để không trộn lẫn các size
     * khác nhau của cùng một món trong cùng một giỏ hàng.
     */
    private function groupItems(iterable $items)
    {
        return collect($items)
            ->filter(fn ($item) => filled(data_get($item, 'food_id')))
            ->groupBy(fn ($item) => data_get($item, 'food_id') . ':' . (data_get($item, 'variant_id') ?? data_get($item, 'food_variant_id') ?? ''))
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'food_id' => (int) data_get($first, 'food_id'),
                    'variant_id' => data_get($first, 'variant_id') ?? data_get($first, 'food_variant_id'),
                    'quantity' => $group->sum(fn ($item) => (int) data_get($item, 'quantity')),
                ];
            });
    }

    private function resolveVariantForUpdate(DoAn $food, $variantId): ?BienTheDoAn
    {
        if ($variantId) {
            $variant = BienTheDoAn::where('food_id', $food->id)
                ->whereKey($variantId)
                ->lockForUpdate()
                ->first();

            if ($variant) {
                return $variant;
            }
        }

        return $this->saleVariantForUpdate($food);
    }

    private function saleVariantForUpdate(DoAn $food): ?BienTheDoAn
    {
        return $food->variants()
            ->where('is_active', true)
            ->orderBy('price')
            ->orderBy('id')
            ->lockForUpdate()
            ->first();
    }

    private function deductCombo(DoAn $food, int $quantity): void
    {
        if ($food->comboItems->isEmpty()) {
            throw new \RuntimeException("Combo {$food->name} chưa có thành phần, không thể bán.");
        }

        foreach ($food->comboItems as $comboItem) {
            $needed = (int) $comboItem->quantity * $quantity;
            $variant = BienTheDoAn::lockForUpdate()->find($comboItem->food_variant_id);

            if (!$variant || $variant->stock_quantity < $needed) {
                $available = (int) ($variant?->stock_quantity ?? 0);

                throw new \RuntimeException("Combo {$food->name} không đủ kho thành phần, cần {$needed} nhưng chỉ còn {$available}.");
            }

            $variant->decrement('stock_quantity', $needed);
        }
    }

    private function restoreCombo(DoAn $food, int $quantity): void
    {
        foreach ($food->comboItems as $comboItem) {
            BienTheDoAn::whereKey($comboItem->food_variant_id)
                ->increment('stock_quantity', (int) $comboItem->quantity * $quantity);
        }
    }
}
