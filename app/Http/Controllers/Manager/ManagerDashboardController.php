<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Phims;
use App\Models\SuatChieu;
use App\Models\PhongChieu;
use App\Models\NguoiDung;
use Illuminate\Support\Facades\Auth;

class ManagerDashboardController extends Controller
{
    public function index()
    {
        $statData = [
            'tong_phim' => Phims::count(),
            'tong_phong' => PhongChieu::count(),
            'suat_chieu_hom_nay' => SuatChieu::whereDate('thoi_gian_chieu', now()->toDateString())->count(),
            'tong_nhan_vien' => NguoiDung::where('vai_tro', 'nhan_vien')->count(),
        ];

        $latestMovies = Phims::latest()->take(5)->get();

        $todaySchedules = SuatChieu::with(['phim', 'phongChieu'])
            ->whereDate('thoi_gian_chieu', now()->toDateString())
            ->orderBy('thoi_gian_chieu', 'asc')
            ->take(10)
            ->get();

        return view('manager.dashboard', compact('statData', 'latestMovies', 'todaySchedules'));
    }
}
