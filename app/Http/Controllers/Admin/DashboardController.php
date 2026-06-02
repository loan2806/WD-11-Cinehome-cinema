<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phims;
use App\Models\Showtime;

class DashboardController extends Controller
{
    public function index()
    {
        // Lấy phim mới nhất
        $latestMovies = Phims::with('showtimes')
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Lấy lịch chiếu gần nhất (từ factory seed)
        $todaySchedules = Showtime::with(['movie', 'cinema'])
            ->whereDate('show_date', now()->toDateString())
            ->orderBy('show_time')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'latestMovies',
            'todaySchedules'
        ));
    }
}
