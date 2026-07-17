<?php

namespace App\Http\Controllers\Admin;

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

        $ticketQuery = VeXemPhim::query()
            ->whereBetween('created_at', [$from->copy(), $to->copy()]);

        $foodInvoiceQuery = FoodInvoice::query()
            ->whereBetween('created_at', [$from->copy(), $to->copy()]);

        $paidTicketQuery = (clone $ticketQuery)
            ->whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung']);

        $paidFoodInvoiceQuery = (clone $foodInvoiceQuery)
            ->where('payment_status', 'paid');

        $summary = [
            'ticket_revenue' => (clone $paidTicketQuery)->sum('tong_tien'),
            'food_revenue' => (clone $paidFoodInvoiceQuery)->sum('total'),
            'tickets_sold' => (clone $paidTicketQuery)->count(),
            'food_invoices' => (clone $paidFoodInvoiceQuery)->count(),
        ];
        $summary['total_revenue'] = $summary['ticket_revenue'] + $summary['food_revenue'];

        $tickets = (clone $ticketQuery)
            ->latest()
            ->paginate(8, ['*'], 'tickets_page')
            ->withQueryString();

        $foodInvoices = (clone $foodInvoiceQuery)
            ->latest()
            ->paginate(8, ['*'], 'food_page')
            ->withQueryString();

        return view('admin.revenue-reports.index', compact('from', 'to', 'summary', 'tickets', 'foodInvoices'));
    }
}
