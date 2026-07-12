<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\FoodInvoice;
use App\Models\VeXemPhim;
use Illuminate\Http\Request;

class RevenueReportController extends Controller
{
    public function index(Request $request)
    {
        $from = ($request->date('from') ?? now()->startOfMonth())->startOfDay();
        $to = ($request->date('to') ?? now())->endOfDay();

        $tickets = VeXemPhim::query()
            ->whereBetween('created_at', [$from->copy(), $to->copy()])
            ->get();

        $foodInvoices = FoodInvoice::query()
            ->whereBetween('created_at', [$from->copy(), $to->copy()])
            ->get();

        $paidTickets = $tickets->whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung']);
        $paidFoodInvoices = $foodInvoices->where('payment_status', 'paid');

        $summary = [
            'ticket_revenue' => $paidTickets->sum('tong_tien'),
            'food_revenue' => $paidFoodInvoices->sum('total'),
            'tickets_sold' => $paidTickets->count(),
            'food_invoices' => $paidFoodInvoices->count(),
        ];
        $summary['total_revenue'] = $summary['ticket_revenue'] + $summary['food_revenue'];

        return view('manager.revenue-reports.index', compact('from', 'to', 'summary', 'tickets', 'foodInvoices'));
    }
}
