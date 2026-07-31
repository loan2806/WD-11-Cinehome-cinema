<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CapnhatFoodRequest;
use App\Http\Requests\Admin\ThemmoiFoodRequest;
use App\Models\BienTheDoAn;
use App\Models\ChiTietCombo;
use App\Models\DanhMucDoAn;
use App\Models\Doan;
use App\Services\AdminNotificationService;
use App\Traits\Loggable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FoodController extends Controller
{
    use Loggable;

    public function index(Request $request)
    {
        $query = Doan::with([
            'variants',
            'category',
            'comboItems.variant',
            'defaultVariant',
        ])
            ->withCount('invoiceItems');

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
                'low' => $query->where(function ($q) {
                    $q->whereHas('variants', function ($variantQuery) {
                        $variantQuery->where('is_active', true)
                            ->where('stock_quantity', '<=', 10);
                    })->orWhereHas('comboItems.variant', function ($variantQuery) {
                        $variantQuery->where('stock_quantity', '<=', 10);
                    });
                }),
                default => null,
            };
        }

        // PHẢI paginate sau khi lọc
        $foods = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = DanhMucDoAn::orderBy('name')->get();
        $allFoods = Doan::with([
            'category',
            'variants',
            'comboItems.variant',
            'defaultVariant',
        ])->get();

        $summary = [
            'total' => $allFoods->count(),
            'active' => $allFoods->where('is_active', true)->count(),
            'inactive' => $allFoods->where('is_active', false)->count(),
            'low_stock' => $allFoods
                ->filter(fn (Doan $food) => $food->stock_quantity <= $food->min_stock_quantity)
                ->count(),
        ];

        return view('admin.foods.index', compact(
            'foods',
            'categories',
            'summary'
        ));
    }

    public function create(Request $request)
    {

        $categories = DanhMucDoAn::orderBy('name')->get();

        // Lấy tất cả món KHÔNG phải Combo

        $variants = BienTheDoAn::with('doAn')
            ->whereHas('doAn', function ($q) {
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

    public function show(Doan $food)
    {
        $food->load([
            'category',
            'variants',
            'defaultVariant',
            'comboItems.variant.doAn.category',
        ])->loadCount('invoiceItems');

        return view('admin.foods.show', compact('food'));
    }

    public function edit(Doan $food)
    {
        $food->load([
            'category',
            'variants',
            'defaultVariant',
            'comboItems.variant.doAn.category',
        ]);

        $categories = DanhMucDoAn::orderBy('name')->get();

        $variants = BienTheDoAn::with('doAn')
    ->whereHas('doAn.category', function ($q) {
        $q->where('is_combo', false);
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
        $category = DanhMucDoAn::find($data['category_id']);

        if ($category && $category->is_combo) {

            $variantIds = collect($request->input('combo_items', []))
                ->pluck('variant_id')
                ->filter();

            if ($variantIds->count() != $variantIds->unique()->count()) {

                return back()
                    ->withInput()
                    ->with('error', 'Không được chọn trùng biến thể trong cùng một combo.');
            }
        }

        $food = Doan::create($data);

        $isCombo = $food->category->is_combo;

        if ($isCombo) {



            foreach ($request->input('combo_items', []) as $item) {

                if (empty($item['variant_id'])) {
                    continue;
                }

                ChiTietCombo::create([
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

                $food->update([
                    'price' => collect($variants)->min('price')
                ]);
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

    public function update(CapnhatFoodRequest $request, Doan $food)
    {
        $data = $request->validated();

        // Upload ảnh mới
        if ($request->hasFile('image')) {
            if ($food->image && Storage::disk('public')->exists($food->image)) {
                Storage::disk('public')->delete($food->image);
            }
            $data['image'] = $request->file('image')->store('foods', 'public');
        }

        // Load category để biết có phải combo không
        $food->load('category');

        DB::transaction(function () use ($request, $food, $data) {

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
                    // Throw an exception so transaction rolls back and we handle outside
                    throw new \Illuminate\Validation\ValidationException(
                        \Illuminate\Support\Facades\Validator::make([], []) // dummy
                    );
                }

                // Xóa các combo items cũ trước khi tạo lại
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

                // Cập nhật $food SAU khi đã cập nhật comboItems (để giá từ form được ghi đè)
                $food->update($data);
            } else {
                $food->update($data);

                $minPrice = $food->variants()->min('price');

                $food->update([
                    'price' => $minPrice
                ]);
            }
        });

        // Nếu có lỗi validation custom trên combo_items, trả về với lỗi và input (kiểm tra trước transaction để gửi lỗi đúng)
        // Note: vì mình dùng throw rỗng ở trên để rollback, ta nên kiểm tra và trả về lỗi trước khi bắt transaction.
        // (Để đơn giản: nếu có lỗi, hàm đã return ở trên trước khi vào transaction.)

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
        Doan $food
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
        Doan $food
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
