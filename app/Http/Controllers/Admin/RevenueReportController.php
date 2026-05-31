<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FoodInvoice;
use App\Models\Ticket;
use Illuminate\Http\Request;

class RevenueReportController extends Controller
{
    public function index(Request $request)
    {
        $from = ($request->date('from') ?? now()->startOfMonth())->startOfDay();
        $to = ($request->date('to') ?? now())->endOfDay();

        $tickets = Ticket::query()
            ->whereBetween('created_at', [$from->copy(), $to->copy()])
            ->get();

        $foodInvoices = FoodInvoice::query()
            ->whereBetween('created_at', [$from->copy(), $to->copy()])
            ->get();

        $summary = [
            'ticket_revenue' => $tickets->where('status', 'paid')->sum('total_price'),
            'food_revenue' => $foodInvoices->where('payment_status', 'paid')->sum('total'),
            'tickets_sold' => $tickets->where('status', 'paid')->count(),
            'food_invoices' => $foodInvoices->where('payment_status', 'paid')->count(),
        ];
        $summary['total_revenue'] = $summary['ticket_revenue'] + $summary['food_revenue'];

        return view('admin.revenue-reports.index', compact('from', 'to', 'summary', 'tickets', 'foodInvoices'));
    }
}
