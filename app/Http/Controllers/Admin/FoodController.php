<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CapnhatFoodRequest;
use App\Http\Requests\Admin\ThemmoiFoodRequest;
use App\Models\Food;
use App\Models\FoodCategory;
use App\Models\FoodVariant;
use App\Services\AdminNotificationService;
use App\Traits\Loggable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ComboItem;

class FoodController extends Controller
{
    use Loggable;

    public function type()
    {
        return view('admin.foods.type');
    }

    public function index(Request $request)
    {
        $query = Food::with(['variants', 'category'])
            ->withCount('invoiceItems');

        $foods = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        if ($request->filled('q')) {

            $keyword = trim($request->q);

            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('sku', 'like', "%{$keyword}%")
                    ->orWhereHas('category', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {

            match ($request->status) {

                'active' => $query->where('is_active', true),

                'inactive' => $query->where('is_active', false),

                default => null,
            };
        }


        $categories = FoodCategory::orderBy('name')->get();

        $summary = [
            'total' => Food::count(),
            'active' => Food::where('is_active', true)->count(),
            'inactive' => Food::where('is_active', false)->count(),
        ];

        return view(
            'admin.foods.index',
            compact(
                'foods',
                'categories',
                'summary'
            )
        );
    }

    public function create(Request $request)
    {

        $categories = FoodCategory::orderBy('name')->get();

        // Lấy tất cả món KHÔNG phải Combo

        $variants = FoodVariant::with('food')
            ->whereHas('food', function ($q) {
                $q->whereHas('category', function ($q) {
                    $q->where('name', 'not like', '%Combo%');
                });
            })
            ->get();

        return view('admin.foods.create', compact(
            'categories',
            'variants',
        ));
    }

    public function show(Food $food)
    {
        $food->load('variants');

        return view('admin.foods.show', compact('food'));
    }

    public function edit(Food $food)
    {
        $food->load('category', 'variants', 'comboItems');

        $categories = FoodCategory::orderBy('name')->get();

        $variants = FoodVariant::with('food')
            ->whereHas('food', function ($q) {
                $q->where('category_id', '!=', FoodCategory::where('name', 'Combo')->value('id'));
            })
            ->get();

        return view('admin.foods.edit', compact(
            'food',
            'categories',
            'variants'
        ));
    }

    public function store(ThemmoiFoodRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('foods', 'public');
        }
        $category = FoodCategory::find($data['category_id']);

        if ($category && str_contains(strtolower($category->name), 'combo')) {

            $variantIds = collect($request->input('combo_items', []))
                ->pluck('variant_id')
                ->filter();

            if ($variantIds->count() != $variantIds->unique()->count()) {

                return back()
                    ->withInput()
                    ->with('error', 'Không được chọn trùng biến thể trong cùng một combo.');
            }
        }

        $food = Food::create($data);

        $isCombo = str_contains(
            strtolower($food->category->name),
            'combo'
        );

        if ($isCombo) {



            foreach ($request->input('combo_items', []) as $item) {

                if (empty($item['variant_id'])) {
                    continue;
                }

                ComboItem::create([
                    'combo_food_id'   => $food->id,
                    'food_variant_id' => $item['variant_id'],
                    'quantity'        => $item['quantity'] ?? 1,
                ]);
            }
        } else {

            $variants = collect($request->input('variants', []))
                ->map(function ($variant) {
                    return [
                        'value' => trim($variant['value'] ?? ''),
                        'price' => (float) ($variant['price'] ?? 0),
                        'stock_quantity' => (int) ($variant['stock_quantity'] ?? 0),
                        'is_active' => true,
                    ];
                })
                ->filter(function ($variant) {
                    return $variant['value'] !== '';
                })
                ->values()
                ->all();

            if (! empty($variants)) {
                $food->variants()->createMany($variants);
            }
        }

        AdminNotificationService::push(
            'Thêm món mới',
            "Đã thêm {$food->name}",
            'Success'
        );

        $this->ghiNhatKy(
            $request,
            'Thêm món',
            'Quản lý đồ ăn',
            "Đã thêm {$food->name}"
        );

        return redirect()
            ->route('admin.foods.index')
            ->with(
                'success',
                'Thêm món thành công.'
            );
    }

    public function update(CapnhatFoodRequest $request, Food $food)
    {
        $data = $request->validated();

        // Upload ảnh mới
        if ($request->hasFile('image')) {

            if ($food->image && Storage::disk('public')->exists($food->image)) {
                Storage::disk('public')->delete($food->image);
            }

            $data['image'] = $request->file('image')->store('foods', 'public');
        }



        $food->update($data);

        // Nếu là combo thì cập nhật thành phần
        $food->load('category');

        if (str_contains(strtolower($food->category->name), 'combo')) {

            $errors = [];
            $seen = [];

            foreach ($request->input('combo_items', []) as $index => $item) {

                $variantId = $item['variant_id'] ?? null;

                if (!$variantId) continue;

                if (isset($seen[$variantId])) {

                    $prevIndex = $seen[$variantId];

                    $errors["combo_items.$index.variant_id"] = 'Biến thể bị trùng trong combo.';
                    $errors["combo_items.$prevIndex.variant_id"] = 'Biến thể bị trùng trong combo.';
                } else {
                    $seen[$variantId] = $index;
                }
            }

            if (!empty($errors)) {
                return back()
                    ->withInput()
                    ->withErrors($errors);
            }

            $food->comboItems()->delete();

            foreach ($request->input('combo_items', []) as $item) {

                if (empty($item['variant_id'])) {
                    continue;
                }

                $food->comboItems()->create([
                    'food_variant_id' => $item['variant_id'],
                    'quantity'        => $item['quantity'] ?? 1,
                ]);
            }
        }

        $this->ghiNhatKy(
            $request,
            'Cập nhật món',
            'Quản lý đồ ăn',
            "Cập nhật {$food->name}"
        );

        return redirect()
            ->route('admin.foods.index')
            ->with('success', 'Cập nhật thành công.');
    }

    public function toggleStatus(
        Request $request,
        Food $food
    ) {
        $food->update([
            'is_active' => ! $food->is_active,
        ]);

        $this->ghiNhatKy(
            $request,
            'Đổi trạng thái',
            'Quản lý đồ ăn',
            ($food->is_active ? 'Bật ' : 'Ẩn ') . $food->name
        );

        return back()->with(
            'success',
            $food->is_active
                ? 'Đã bật món.'
                : 'Đã tạm ẩn món.'
        );
    }
    public function destroy(
        Request $request,
        Food $food
    ) {
        if ($food->invoiceItems()->exists()) {

            return back()->with(
                'error',
                'Không thể xóa món đã phát sinh hóa đơn.'
            );
        }

        $name = $food->name;

        $food->delete();

        $this->ghiNhatKy(
            $request,
            'Xóa món',
            'Quản lý đồ ăn',
            "Đã xóa {$name}"
        );

        return back()->with(
            'success',
            'Đã xóa món thành công.'
        );
    }
}
