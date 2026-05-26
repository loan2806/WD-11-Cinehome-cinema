<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\FoodOrder;
use Illuminate\Http\Request;

class FoodOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = FoodOrder::with(['user', 'ticket'])
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $keyword = '%' . $request->keyword . '%';

                $query->where(function ($inner) use ($keyword) {
                    $inner->where('invoice_code', 'like', $keyword)
                        ->orWhere('customer_name', 'like', $keyword)
                        ->orWhere('customer_phone', 'like', $keyword);
                });
            })
            ->when($request->filled('payment_status'), fn ($query) => $query->where('payment_status', $request->payment_status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.food-orders.index', compact('orders'));
    }

    public function show(FoodOrder $foodOrder)
    {
        $foodOrder->load(['items', 'user', 'ticket']);

        return view('admin.food-orders.show', compact('foodOrder'));
    }

    public function updateStatus(Request $request, FoodOrder $foodOrder)
    {
        $data = $request->validate([
            'fulfillment_status' => ['required', 'in:waiting,preparing,completed,cancelled'],
        ]);

        $foodOrder->update($data);

        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'update_food_order_status',
            'module' => 'food_orders',
            'description' => 'Cap nhat trang thai hoa don ' . $foodOrder->invoice_code,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'properties' => $data,
        ]);

        return back()->with('success', 'Da cap nhat trang thai hoa don do an.');
    }
}
