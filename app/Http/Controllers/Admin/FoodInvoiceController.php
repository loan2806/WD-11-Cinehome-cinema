<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        return view('admin.food-invoices.index', compact('invoices', 'summary'));
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

        $subtotal = collect($data['items'])->sum(fn ($item) => (int) $item['quantity'] * (float) $item['unit_price']);
        $discount = (float) ($data['discount'] ?? 0);

        if ($discount > $subtotal) {
            throw ValidationException::withMessages([
                'discount' => 'Giảm giá không được lớn hơn tạm tính.',
            ]);
        }

        $invoice = DB::transaction(function () use ($data, $subtotal, $discount) {
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

            foreach ($data['items'] as $item) {
                $invoice->items()->create([
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
            ->route('admin.food-invoices.index');
    }

    public function updateStatus(Request $request, FoodInvoice $foodInvoice)
    {
        $data = $request->validate([
            'payment_status' => ['required', 'in:pending,paid,cancelled'],
        ]);

        $oldStatus = $foodInvoice->payment_status;
        $foodInvoice->update(['payment_status' => $data['payment_status']]);

        $this->ghiNhatKy(
            $request,
            'Cập nhật trạng thái hóa đơn đồ ăn',
            'Quản lý hóa đơn đồ ăn',
            "Cập nhật {$foodInvoice->invoice_code}: {$oldStatus} -> {$foodInvoice->payment_status}"
        );

        return back();
    }

    public function destroy(Request $request, FoodInvoice $foodInvoice)
    {
        $code = $foodInvoice->invoice_code;

        DB::transaction(function () use ($foodInvoice) {
            $foodInvoice->items()->delete();
            $foodInvoice->delete();
        });

        $this->ghiNhatKy($request, 'Xóa hóa đơn đồ ăn', 'Quản lý hóa đơn đồ ăn', "Xóa hóa đơn: {$code}");

        return back();
    }
}
