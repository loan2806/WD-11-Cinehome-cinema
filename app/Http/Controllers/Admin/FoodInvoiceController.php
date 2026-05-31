<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\FoodInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FoodInvoiceController extends Controller
{
    public function index()
    {
        $invoices = FoodInvoice::with('items', 'user')->latest()->paginate(10);

        return view('admin.food-invoices.index', compact('invoices'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'payment_status' => ['required', 'in:pending,paid,cancelled'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.food_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $subtotal = collect($data['items'])->sum(fn ($item) => $item['quantity'] * $item['unit_price']);
        $discount = $data['discount'] ?? 0;

        $invoice = FoodInvoice::create([
            'invoice_code' => 'FD-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
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
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'module' => 'food_invoices',
            'description' => 'Tao hoa don do an ' . $invoice->invoice_code,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Da tao hoa don do an.');
    }

    public function destroy(Request $request, FoodInvoice $foodInvoice)
    {
        $code = $foodInvoice->invoice_code;
        $foodInvoice->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'module' => 'food_invoices',
            'description' => 'Xoa hoa don do an ' . $code,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Da xoa hoa don do an.');
    }
}
