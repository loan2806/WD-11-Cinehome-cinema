<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ChamCong;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ChamCongController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();
        
        // Lấy biến lọc
        $thang = $request->input('thang', date('m'));
        $nam = $request->input('nam', date('Y'));
        
        // Query cơ bản
        $query = ChamCong::where('nguoi_dung_id', $user->id);
        
        // Lọc theo tháng/năm
        $query->whereMonth('ngay', $thang)->whereYear('ngay', $nam);
        
        // Lấy dữ liệu phân trang
        $chamCongs = $query->orderBy('ngay', 'desc')->paginate(15);
        
        // Lấy chấm công hôm nay
        $chamCongHomNay = ChamCong::where('nguoi_dung_id', $user->id)
            ->whereDate('ngay', $today)
            ->first();

        return view('staff.cham-congs.index', compact(
            'chamCongs', 
            'chamCongHomNay',
            'today',
            'thang',
            'nam'
        ));
    }

    public function checkIn(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();

        $existing = ChamCong::where('nguoi_dung_id', $user->id)
            ->whereDate('ngay', $today)
            ->first();

        if ($existing) {
            return back()->with('error', 'Bạn đã check-in hôm nay rồi!');
        }

        ChamCong::create([
            'nguoi_dung_id' => $user->id,
            'ngay' => $today,
            'loai_cham_cong' => 'di_lam',
            'gio_vao' => Carbon::now()->format('H:i:s'),
            'trang_thai' => 'cho_xac_nhan',
        ]);

        return back()->with('success', 'Check-in thành công!');
    }

    public function checkOut(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();

        $chamCong = ChamCong::where('nguoi_dung_id', $user->id)
            ->whereDate('ngay', $today)
            ->where('loai_cham_cong', 'di_lam')
            ->first();

        if (!$chamCong) {
            return back()->with('error', 'Bạn chưa check-in hôm nay!');
        }

        if ($chamCong->gio_ra) {
            return back()->with('error', 'Bạn đã check-out hôm nay rồi!');
        }

        $chamCong->update([
            'gio_ra' => Carbon::now()->format('H:i:s'),
            'so_gio_tang_ca' => $request->input('so_gio_tang_ca', 0),
            'ghi_chu' => $request->input('ghi_chu'),
        ]);

        return back()->with('success', 'Check-out thành công!');
    }
}
