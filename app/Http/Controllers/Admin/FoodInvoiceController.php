<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Food;
use App\Models\FoodInvoice;
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

        $quickFoods = Food::active()
            ->where('stock_quantity', '>', 0)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $lowStockFoods = Food::active()
            ->whereColumn('stock_quantity', '<=', 'min_stock_quantity')
            ->orderBy('stock_quantity')
            ->take(5)
            ->get();

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
            if ($data['payment_status'] === 'paid') {
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

        DB::transaction(function () use ($foodInvoice, $oldStatus, $data) {
            $foodInvoice->load('items');

            if ($oldStatus !== 'paid' && $data['payment_status'] === 'paid') {
                $this->deductInventory($foodInvoice->items);
            }

            if ($oldStatus === 'paid' && $data['payment_status'] !== 'paid') {
                $this->restoreInventory($foodInvoice->items);
            }

            $foodInvoice->update(['payment_status' => $data['payment_status']]);
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

            if ($foodInvoice->payment_status === 'paid') {
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

        $foods = Food::whereIn('id', $foodIds)->get()->keyBy('id');

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
            $food = Food::lockForUpdate()->find($foodId);

            if (! $food) {
                continue;
            }

            if ($food->stock_quantity < $quantity) {
                throw ValidationException::withMessages([
                    'items' => "Món {$food->name} chỉ còn {$food->stock_quantity}, không đủ để thanh toán {$quantity} phần.",
                ]);
            }

            $food->decrement('stock_quantity', $quantity);
        }
    }

    private function restoreInventory($items): void
    {
        $quantities = collect($items)
            ->filter(fn ($item) => filled(data_get($item, 'food_id')))
            ->groupBy(fn ($item) => (int) data_get($item, 'food_id'))
            ->map(fn ($group) => $group->sum(fn ($item) => (int) data_get($item, 'quantity')));

        foreach ($quantities as $foodId => $quantity) {
            Food::whereKey($foodId)->increment('stock_quantity', $quantity);
        }
    }
}
