<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Food;
use App\Models\FoodInvoice;
use App\Models\FoodVariant;
use App\Traits\Loggable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FoodInvoiceController extends Controller
{
    use Loggable;

    public function index(Request $request)
    {
        $query = FoodInvoice::with('items', 'user')->latest();

        if ($request->filled('q')) {
            $keyword = trim($request->q);

            $query->where(function ($q) use ($keyword) {
                $q->where('invoice_code', 'like', "%{$keyword}%")
                    ->orWhere('customer_name', 'like', "%{$keyword}%")
                    ->orWhere('customer_phone', 'like', "%{$keyword}%")
                    ->orWhereHas('items', fn ($itemQuery) => $itemQuery->where('food_name', 'like', "%{$keyword}%"));
            });
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->date('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->date('to'));
        }

        $summaryQuery = clone $query;

        $summaryInvoices = $summaryQuery->get();
        $summary = [
            'count' => $summaryInvoices->count(),
            'paid_count' => $summaryInvoices->where('payment_status', 'paid')->count(),
            'pending_count' => $summaryInvoices->where('payment_status', 'pending')->count(),
            'paid_total' => $summaryInvoices->where('payment_status', 'paid')->sum('total'),
        ];

        $invoices = $query->paginate(10)->withQueryString();

        $foodsForSale = Food::active()
            ->with([
                'category',
                'variants' => fn ($variantQuery) => $variantQuery
                    ->where('is_active', true)
                    ->orderBy('price')
                    ->orderBy('id'),
                'comboItems.variant',
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $quickFoods = $foodsForSale
            ->filter(fn (Food $food) => $food->stock_quantity > 0)
            ->values();

        $lowStockFoods = $foodsForSale
            ->filter(fn (Food $food) => $food->stock_quantity > 0 && $food->stock_quantity <= $food->min_stock_quantity)
            ->sortBy('stock_quantity')
            ->take(5)
            ->values();

        return view('admin.food-invoices.index', compact('invoices', 'summary', 'quickFoods', 'lowStockFoods'));
    }

    public function store(Request $request)
    {
        $items = collect($request->input('items', []))
            ->filter(fn ($item) => filled($item['food_name'] ?? null) || filled($item['unit_price'] ?? null))
            ->values()
            ->all();

        $request->merge(['items' => $items]);

        $data = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'payment_status' => ['required', 'in:pending,paid,cancelled'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.food_id' => ['nullable', 'integer', 'exists:foods,id'],
            'items.*.food_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ], [
            'items.required' => 'Vui lòng nhập ít nhất một món.',
            'items.min' => 'Vui lòng nhập ít nhất một món.',
            'items.*.food_name.required' => 'Vui lòng nhập tên món.',
            'items.*.quantity.required' => 'Vui lòng nhập số lượng.',
            'items.*.unit_price.required' => 'Vui lòng nhập đơn giá.',
        ]);

        $discount = (float) ($data['discount'] ?? 0);

        $items = $this->normalizeItems($data['items']);
        $subtotal = collect($items)->sum(fn ($item) => (int) $item['quantity'] * (float) $item['unit_price']);

        if ($discount > $subtotal) {
            throw ValidationException::withMessages([
                'discount' => 'Giảm giá không được lớn hơn tạm tính.',
            ]);
        }

        $invoice = DB::transaction(function () use ($data, $items, $subtotal, $discount) {
            $inventoryDeducted = $data['payment_status'] !== 'cancelled';

            if ($inventoryDeducted) {
                $this->deductInventory($items);
            }

            $invoice = FoodInvoice::create([
                'invoice_code' => 'FD-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                'user_id' => Auth::id(),
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => max($subtotal - $discount, 0),
                'payment_status' => $data['payment_status'],
                'inventory_deducted' => $inventoryDeducted,
                'payment_method' => $data['payment_method'] ?? null,
                'note' => $data['note'] ?? null,
            ]);

            foreach ($items as $item) {
                $invoice->items()->create([
                    'food_id' => $item['food_id'],
                    'food_name' => $item['food_name'],
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => (float) $item['unit_price'],
                    'total_price' => (int) $item['quantity'] * (float) $item['unit_price'],
                ]);
            }

            return $invoice;
        });

        $this->ghiNhatKy($request, 'Tạo hóa đơn đồ ăn', 'Quản lý hóa đơn đồ ăn', "Tạo hóa đơn: {$invoice->invoice_code}");

        return redirect()
            ->route('admin.food-invoices.index')
            ->with('success', 'Đã tạo hóa đơn đồ ăn.');
    }

    public function updateStatus(Request $request, FoodInvoice $foodInvoice)
    {
        $data = $request->validate([
            'payment_status' => ['required', 'in:pending,paid,cancelled'],
        ]);

        $oldStatus = $foodInvoice->payment_status;

        DB::transaction(function () use ($foodInvoice, $data) {
            $foodInvoice->load('items');
            $newStatus = $data['payment_status'];

            if (! $foodInvoice->inventory_deducted && $newStatus !== 'cancelled') {
                $this->deductInventory($foodInvoice->items);
                $foodInvoice->inventory_deducted = true;
            }

            if ($foodInvoice->inventory_deducted && $newStatus === 'cancelled') {
                $this->restoreInventory($foodInvoice->items);
                $foodInvoice->inventory_deducted = false;
            }

            $foodInvoice->payment_status = $newStatus;
            $foodInvoice->save();
        });

        $this->ghiNhatKy(
            $request,
            'Cập nhật trạng thái hóa đơn đồ ăn',
            'Quản lý hóa đơn đồ ăn',
            "Cập nhật {$foodInvoice->invoice_code}: {$oldStatus} -> {$foodInvoice->payment_status}"
        );

        return back()->with('success', 'Đã cập nhật trạng thái hóa đơn.');
    }

    public function destroy(Request $request, FoodInvoice $foodInvoice)
    {
        $code = $foodInvoice->invoice_code;

        DB::transaction(function () use ($foodInvoice) {
            $foodInvoice->load('items');

            if ($foodInvoice->inventory_deducted) {
                $this->restoreInventory($foodInvoice->items);
            }

            $foodInvoice->items()->delete();
            $foodInvoice->delete();
        });

        $this->ghiNhatKy($request, 'Xóa hóa đơn đồ ăn', 'Quản lý hóa đơn đồ ăn', "Xóa hóa đơn: {$code}");

        return back()->with('success', 'Đã xóa hóa đơn đồ ăn.');
    }

    private function normalizeItems(array $items): array
    {
        $foodIds = collect($items)
            ->pluck('food_id')
            ->filter()
            ->unique()
            ->values();

        $foods = Food::with([
            'category',
            'variants' => fn ($variantQuery) => $variantQuery
                ->where('is_active', true)
                ->orderBy('price')
                ->orderBy('id'),
            'comboItems.variant',
        ])
            ->whereIn('id', $foodIds)
            ->get()
            ->keyBy('id');

        return collect($items)
            ->map(function ($item) use ($foods) {
                $food = filled($item['food_id'] ?? null) ? $foods->get((int) $item['food_id']) : null;

                if ($food && ! $food->is_active) {
                    throw ValidationException::withMessages([
                        'items' => "Món {$food->name} đang tạm ẩn, không thể bán mới.",
                    ]);
                }

                return [
                    'food_id' => $food?->id,
                    'food_name' => $food?->name ?? trim($item['food_name']),
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => $food ? (float) $food->price : (float) $item['unit_price'],
                ];
            })
            ->values()
            ->all();
    }

    private function deductInventory($items): void
    {
        $quantities = collect($items)
            ->filter(fn ($item) => filled(data_get($item, 'food_id')))
            ->groupBy(fn ($item) => (int) data_get($item, 'food_id'))
            ->map(fn ($group) => $group->sum(fn ($item) => (int) data_get($item, 'quantity')));

        foreach ($quantities as $foodId => $quantity) {
            $food = Food::with(['category', 'comboItems.variant'])->lockForUpdate()->find($foodId);

            if (! $food) {
                continue;
            }

            if ($food->isCombo()) {
                $this->deductComboInventory($food, $quantity);
                continue;
            }

            $variant = $this->saleVariantForUpdate($food);

            if (! $variant || $variant->stock_quantity < $quantity) {
                $available = (int) ($variant?->stock_quantity ?? 0);

                throw ValidationException::withMessages([
                    'items' => "Món {$food->name} chỉ còn {$available}, không đủ để lưu hóa đơn {$quantity} phần.",
                ]);
            }

            $variant->decrement('stock_quantity', $quantity);
        }
    }

    private function restoreInventory($items): void
    {
        $quantities = collect($items)
            ->filter(fn ($item) => filled(data_get($item, 'food_id')))
            ->groupBy(fn ($item) => (int) data_get($item, 'food_id'))
            ->map(fn ($group) => $group->sum(fn ($item) => (int) data_get($item, 'quantity')));

        foreach ($quantities as $foodId => $quantity) {
            $food = Food::with(['category', 'comboItems.variant'])->find($foodId);

            if (! $food) {
                continue;
            }

            if ($food->isCombo()) {
                $this->restoreComboInventory($food, $quantity);
                continue;
            }

            $variant = $this->saleVariantForUpdate($food);

            if ($variant) {
                $variant->increment('stock_quantity', $quantity);
            }
        }
    }

    private function saleVariantForUpdate(Food $food): ?FoodVariant
    {
        return $food->variants()
            ->where('is_active', true)
            ->orderBy('price')
            ->orderBy('id')
            ->lockForUpdate()
            ->first();
    }

    private function deductComboInventory(Food $food, int $quantity): void
    {
        if ($food->comboItems->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => "Combo {$food->name} chưa có thành phần, không thể bán.",
            ]);
        }

        foreach ($food->comboItems as $comboItem) {
            $needed = (int) $comboItem->quantity * $quantity;
            $variant = FoodVariant::lockForUpdate()->find($comboItem->food_variant_id);

            if (! $variant || $variant->stock_quantity < $needed) {
                $available = (int) ($variant?->stock_quantity ?? 0);

                throw ValidationException::withMessages([
                    'items' => "Combo {$food->name} không đủ kho thành phần, cần {$needed} nhưng chỉ còn {$available}.",
                ]);
            }

            $variant->decrement('stock_quantity', $needed);
        }
    }

    private function restoreComboInventory(Food $food, int $quantity): void
    {
        foreach ($food->comboItems as $comboItem) {
            FoodVariant::whereKey($comboItem->food_variant_id)
                ->increment('stock_quantity', (int) $comboItem->quantity * $quantity);
        }
    }
}
