<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;

class StaffDashboardController extends Controller
{
    public function index()
    {
        $totalMovies = 0;
        $totalCinemas = 0;
        $todayShowtimes = 0;
        $upcomingShowtimes = [];

        return view('staff.dashboard', compact(
            'totalMovies',
            'totalCinemas',
            'todayShowtimes',
            'upcomingShowtimes'
        ));
    }
}