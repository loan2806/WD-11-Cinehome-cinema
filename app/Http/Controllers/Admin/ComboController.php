<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreComboRequest;
use App\Http\Requests\Admin\UpdateComboRequest;
use App\Models\BienTheDoAn;
use App\Models\ChiTietCombo;
use App\Models\DanhMucDoAn;
use App\Models\Doan;
use App\Services\AdminNotificationService;
use App\Traits\Loggable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ComboController extends Controller
{
    use Loggable;

    public function index(Request $request)
    {
        $query = Doan::with(['comboItems.variant.doAn.category', 'category'])
            ->whereHas('category', function ($q) {
                $q->where('is_combo', true);
            })
            ->withCount('invoiceItems');

        if ($request->filled('search')) {
            $keyword = trim($request->search);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'active' => $query->where('is_active', true),
                'inactive' => $query->where('is_active', false),
                'low' => $query->whereHas('comboItems.variant', function ($variantQuery) {
                    $variantQuery->where('stock_quantity', '<=', 10);
                }),
                default => null,
            };
        }

        $combos = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.foods.combos.index', compact('combos'));
    }

    public function create()
    {
        $comboCategory = DanhMucDoAn::where('is_combo', true)->first();

        $variants = BienTheDoAn::with('doAn')
            ->whereHas('doAn.category', function ($q) {
                $q->where('is_combo', false);
            })
            ->get();

        return view('admin.foods.combos.create', compact('comboCategory', 'variants'));
    }

    public function edit(Doan $food)
    {
        $food->load(['category', 'comboItems.variant.doAn.category']);

        $variants = BienTheDoAn::with('doAn')
            ->whereHas('doAn.category', function ($q) {
                $q->where('is_combo', false);
            })
            ->get();

        return view('admin.foods.combos.edit', compact('food', 'variants'));
    }

    public function store(StoreComboRequest $request)
    {
        $data = $request->validated();

        $category = DanhMucDoAn::find($data['category_id']);

        if (! $category || ! $category->is_combo) {
            return back()->withInput()->with('error', 'Danh mục combo không hợp lệ.');
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('foods', 'public');
        }

        $food = Doan::create($data);

        foreach ($request->input('combo_items', []) as $item) {
            if (empty($item['variant_id'])) {
                continue;
            }

            ChiTietCombo::create([
                'combo_food_id' => $food->id,
                'food_variant_id' => $item['variant_id'],
                'quantity' => $item['quantity'] ?? 1,
            ]);
        }

        AdminNotificationService::push(
            'Thêm combo mới',
            "Đã thêm combo {$food->name}",
            'Success'
        );

        $this->ghiNhatKy(
            $request,
            'Thêm combo',
            'Quản lý combo',
            "Đã thêm combo {$food->name}"
        );

        return redirect()
            ->route('admin.foods.combos.index')
            ->with('success', 'Thêm combo thành công.');
    }

    public function update(UpdateComboRequest $request, Doan $food)
    {
        if (! $food->category?->is_combo) {
            return back()->with('error', 'Sản phẩm không phải combo.');
        }

        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($food->image && Storage::disk('public')->exists($food->image)) {
                Storage::disk('public')->delete($food->image);
            }
            $data['image'] = $request->file('image')->store('foods', 'public');
        }

        DB::transaction(function () use ($request, $food, $data) {
            $food->update($data);

            $food->comboItems()->delete();

            foreach ($request->input('combo_items', []) as $item) {
                if (empty($item['variant_id'])) {
                    continue;
                }

                $food->comboItems()->create([
                    'food_variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'] ?? 1,
                ]);
            }
        });

        AdminNotificationService::push(
            'Cập nhật combo',
            "Đã cập nhật combo {$food->name}",
            'Success'
        );

        $this->ghiNhatKy(
            $request,
            'Cập nhật combo',
            'Quản lý combo',
            "Cập nhật combo {$food->name}"
        );

        return redirect()
            ->route('admin.foods.combos.index')
            ->with('success', 'Cập nhật combo thành công.');
    }

    public function destroy(Doan $food)
    {
        if (! $food->category?->is_combo) {
            return back()->with('error', 'Sản phẩm không phải combo.');
        }

        $food->delete();

        return back()->with('success', 'Combo đã được xóa.');
    }
}
