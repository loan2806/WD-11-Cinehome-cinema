<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FoodOrder;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RevenueReportController extends Controller
{
    public function index(Request $request)
    {
        $from = ($request->date('from') ?: Carbon::now()->subDays(6))->startOfDay();
        $to = ($request->date('to') ?: Carbon::now())->endOfDay();

        $ticketRevenue = Ticket::query()
            ->where('status', 'paid')
            ->whereBetween('created_at', [$from, $to])
            ->sum('total_price');

        $foodRevenue = FoodOrder::query()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$from, $to])
            ->sum('total_amount');

        $ticketCount = Ticket::query()
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $daily = collect();
        $cursor = $from->copy()->startOfDay();
        while ($cursor->lte($to)) {
            $dayStart = $cursor->copy()->startOfDay();
            $dayEnd = $cursor->copy()->endOfDay();

            $daily->push([
                'date' => $cursor->format('d/m'),
                'ticket' => (float) Ticket::query()->where('status', 'paid')->whereBetween('created_at', [$dayStart, $dayEnd])->sum('total_price'),
                'food' => (float) FoodOrder::query()->where('payment_status', 'paid')->whereBetween('created_at', [$dayStart, $dayEnd])->sum('total_amount'),
            ]);

            $cursor->addDay();
        }

        $topMovies = Ticket::query()
            ->selectRaw('movie_title, COUNT(*) as sold_count, SUM(total_price) as revenue')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('movie_title')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        return view('admin.reports.revenue', compact(
            'from',
            'to',
            'ticketRevenue',
            'foodRevenue',
            'ticketCount',
            'daily',
            'topMovies'
        ));
    }
}
