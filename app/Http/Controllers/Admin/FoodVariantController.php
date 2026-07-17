<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CapnhatFoodVariantRequest;
use App\Http\Requests\Admin\ThemmoiFoodVariantRequest;
use App\Models\Doan;
use App\Models\BienTheDoAn;
use App\Services\AdminNotificationService;
use App\Traits\Loggable;
use Illuminate\Http\Request;

class FoodVariantController extends Controller
{
    use Loggable;

    public function index(Doan $food)
    {
        $variants = $food->variants()
            ->orderBy('value')
            ->paginate(10);

        return view(
            'admin.foods.variants.index',
            compact('food', 'variants')
        );
    }

    public function create(Doan $food)
    {
        return view(
            'admin.foods.variants.create',
            compact('food')
        );
    }

    public function store(
        ThemmoiFoodVariantRequest $request,
        Doan $food
    ) {
        $exists = $food->variants()
            ->where('value', trim($request->value))
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', 'Biến thể này đã tồn tại.');
        }

        $variant = $food->variants()->create(
            $request->validated()
        );
        $food->update([
            'price' => $food->variants()->min('price')
        ]);

        AdminNotificationService::push(
            'Thêm biến thể',
            "{$food->name} - {$variant->value}",
            'Success'
        );

        $this->ghiNhatKy(
            $request,
            'Thêm biến thể',
            'Quản lý biến thể',
            "Thêm {$food->name} - {$variant->value}"
        );

        return redirect()
            ->route(
                'admin.foods.edit',
                $food
            )
            ->with(
                'success',
                'Đã thêm biến thể.'
            );
    }

    public function edit(
        Doan $food,
        BienTheDoAn $variant
    ) {
        abort_if(
            $variant->food_id != $food->id,
            404
        );

        return view(
            'admin.foods.variants.edit',
            compact(
                'food',
                'variant'
            )
        );
    }
    public function update(
        CapnhatFoodVariantRequest $request,
        Doan $food,
        BienTheDoAn $variant
    ) {
        abort_if($variant->food_id != $food->id, 404);

        $exists = $food->variants()
            ->where('value', trim($request->value))
            ->where('id', '!=', $variant->id)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', 'Biến thể này đã tồn tại.');
        }

        $variant->update($request->validated());

        $food->update([
            'price' => $food->variants()->min('price')
        ]);

        $this->ghiNhatKy(
            $request,
            'Cập nhật biến thể',
            'Quản lý biến thể',
            "Cập nhật {$food->name} - {$variant->value}"
        );

        return redirect()
            ->route('admin.foods.edit', $food)
            ->with('success', 'Đã cập nhật biến thể.');
    }

    public function updateStock(
        Request $request,
        Doan $food,
        BienTheDoAn $variant
    ) {
        abort_if(
            $variant->food_id != $food->id,
            404
        );

        $request->validate([
            'adjustment' => [
                'required',
                'integer',
                'not_in:0'
            ]
        ]);

        $newStock =
            $variant->stock_quantity +
            $request->adjustment;

        if ($newStock < 0) {

            return back()->withErrors([
                'adjustment' =>
                'Tồn kho không đủ.'
            ]);
        }

        $variant->update([
            'stock_quantity' => $newStock
        ]);

        $this->ghiNhatKy(
            $request,
            'Điều chỉnh tồn kho',
            'Quản lý biến thể',
            "{$food->name} - {$variant->value}: {$newStock}"
        );

        return back()->with(
            'success',
            'Đã cập nhật tồn kho.'
        );
    }

    public function destroy(
        Request $request,
        Doan $food,
        BienTheDoAn $variant
    ) {
        abort_if(
            $variant->food_id != $food->id,
            404
        );

        $name = $variant->value;

        $variant->delete();

        $food->update([
            'price' => $food->variants()->min('price')
        ]);

        $this->ghiNhatKy(
            $request,
            'Xóa biến thể',
            'Quản lý biến thể',
            "Xóa {$food->name} - {$name}"
        );

        return back()->with(
            'success',
            'Đã xóa biến thể.'
        );
    }
}
