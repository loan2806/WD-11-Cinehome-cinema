<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Food;
use App\Services\AdminNotificationService;
use App\Traits\Loggable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FoodController extends Controller
{
    use Loggable;

    public function index(Request $request)
    {
        $query = Food::withCount('invoiceItems');

        if ($request->filled('q')) {
            $keyword = trim($request->q);

            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('sku', 'like', "%{$keyword}%")
                    ->orWhere('category', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'active' => $query->where('is_active', true),
                'inactive' => $query->where('is_active', false),
                'out' => $query->where('stock_quantity', 0),
                'low' => $query->whereColumn('stock_quantity', '<=', 'min_stock_quantity')
                    ->where('stock_quantity', '>', 0),
                default => null,
            };
        }

        $foods = $query
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $categories = Food::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $summary = [
            'total' => Food::count(),
            'active' => Food::where('is_active', true)->count(),
            'low' => Food::whereColumn('stock_quantity', '<=', 'min_stock_quantity')
                ->where('stock_quantity', '>', 0)
                ->count(),
            'out' => Food::where('stock_quantity', 0)->count(),
        ];

        return view('admin.foods.index', compact('foods', 'categories', 'summary'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $food = Food::create($data);

        AdminNotificationService::push(
            'Thêm món mới vào menu',
            "Đã thêm món {$food->name} vào menu quầy",
            'Success'
        );

        $this->ghiNhatKy(
            $request,
            'Thêm món menu & kho',
            'Cấu hình Menu & Kho hàng',
            "Thêm món {$food->name}, tồn kho {$food->stock_quantity}"
        );

        return redirect()
            ->route('admin.foods.index')
            ->with('success', 'Đã thêm món vào menu và kho hàng.');
    }

    public function update(Request $request, Food $food)
    {
        $data = $this->validatedData($request, $food);

        $food->update($data);

        $this->ghiNhatKy(
            $request,
            'Cập nhật món menu & kho',
            'Cấu hình Menu & Kho hàng',
            "Cập nhật món {$food->name}"
        );

        return redirect()
            ->route('admin.foods.index', $request->query())
            ->with('success', 'Đã cập nhật món và thông tin kho.');
    }

    public function updateStock(Request $request, Food $food)
    {
        $data = $request->validate([
            'adjustment' => ['required', 'integer', 'not_in:0', 'min:-999999', 'max:999999'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [
            'adjustment.not_in' => 'Số lượng điều chỉnh phải khác 0.',
        ]);

        $newQuantity = $food->stock_quantity + (int) $data['adjustment'];

        if ($newQuantity < 0) {
            throw ValidationException::withMessages([
                'adjustment' => 'Tồn kho sau điều chỉnh không được nhỏ hơn 0.',
            ]);
        }

        $oldQuantity = $food->stock_quantity;
        $food->update(['stock_quantity' => $newQuantity]);

        $this->ghiNhatKy(
            $request,
            'Điều chỉnh tồn kho đồ ăn',
            'Cấu hình Menu & Kho hàng',
            "Điều chỉnh {$food->name}: {$oldQuantity} -> {$newQuantity}",
            [
                'adjustment' => (int) $data['adjustment'],
                'note' => $data['note'] ?? null,
            ]
        );

        return back()->with('success', 'Đã điều chỉnh tồn kho.');
    }

    public function toggleStatus(Request $request, Food $food)
    {
        $food->update([
            'is_active' => ! $food->is_active,
        ]);

        $this->ghiNhatKy(
            $request,
            'Bật/tắt món trên menu',
            'Cấu hình Menu & Kho hàng',
            ($food->is_active ? 'Bật bán ' : 'Tạm ẩn ') . $food->name
        );

        return back()->with(
            'success',
            $food->is_active ? 'Đã bật món trên menu.' : 'Đã tạm ẩn món khỏi menu.'
        );
    }

    public function destroy(Request $request, Food $food)
    {
        if ($food->invoiceItems()->exists()) {
            return back()->with(
                'error',
                'Không thể xóa món đã phát sinh hóa đơn. Hãy tạm ẩn món để giữ đúng lịch sử bán hàng.'
            );
        }

        $name = $food->name;
        $food->delete();

        $this->ghiNhatKy(
            $request,
            'Xóa món menu & kho',
            'Cấu hình Menu & Kho hàng',
            "Xóa món {$name}"
        );

        return back()->with('success', 'Đã xóa món khỏi menu.');
    }

    private function validatedData(Request $request, ?Food $food = null): array
    {
        $data = $request->validate([
            'sku' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[A-Z0-9_-]+$/i',
                Rule::unique('foods', 'sku')->ignore($food?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'string', 'max:2048'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'description' => ['nullable', 'string', 'max:1000'],
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:999999'],
            'min_stock_quantity' => ['required', 'integer', 'min:0', 'max:999999'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'sku.regex' => 'Mã SKU chỉ gồm chữ, số, dấu gạch ngang hoặc gạch dưới.',
        ]);

        $data['sku'] = filled($data['sku'] ?? null) ? Str::upper(trim($data['sku'])) : null;
        $data['name'] = trim($data['name']);
        $data['category'] = filled($data['category'] ?? null) ? trim($data['category']) : null;
        $data['image'] = filled($data['image'] ?? null) ? trim($data['image']) : null;
        $data['description'] = filled($data['description'] ?? null) ? trim($data['description']) : null;
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
