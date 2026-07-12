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

        $thang = $request->input('thang', date('m'));
        $nam = $request->input('nam', date('Y'));

        // Lấy lịch sử chấm công của nhân viên trong tháng
        $chamCongs = ChamCong::where('nguoi_dung_id', $user->id)
            ->whereMonth('ngay', $thang)
            ->whereYear('ngay', $nam)
            ->orderBy('ngay', 'desc')
            ->get();

        // Lấy thông tin chấm công hôm nay
        $today = Carbon::today()->format('Y-m-d');
        $chamCongHomNay = ChamCong::where('nguoi_dung_id', $user->id)
            ->where('ngay', $today)
            ->first();

        return view('staff.cham-congs.index', compact('chamCongs', 'chamCongHomNay', 'thang', 'nam', 'today'));
    }

    public function checkIn(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now();

        $existing = ChamCong::where('nguoi_dung_id', $user->id)
            ->where('ngay', $today)
            ->first();

        if ($existing) {
            return back()->with('error', 'Bạn đã chấm công vào ca hôm nay rồi!');
        }

        $gioVaoChuan = Carbon::today()->setTimeFromTimeString('08:00:00');
        
        $data = [
            'nguoi_dung_id' => $user->id,
            'ngay' => $today,
            'gio_vao' => $now->format('H:i:s'),
            'di_muon' => $now > (clone $gioVaoChuan)->addMinutes(5),
            'nghi_phep' => false,
            'nghi_khong_phep' => false,
        ];

        ChamCong::create($data);

        return back()->with('success', 'Chấm công vào ca thành công!');
    }

    public function checkOut(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now();

        $chamCong = ChamCong::where('nguoi_dung_id', $user->id)
            ->where('ngay', $today)
            ->first();

        if (!$chamCong) {
            return back()->with('error', 'Bạn chưa chấm công vào ca hôm nay!');
        }

        if ($chamCong->gio_ra) {
            return back()->with('error', 'Bạn đã chấm công ra ca hôm nay rồi!');
        }

        $gioRaChuanFull = Carbon::today()->setTimeFromTimeString('17:00:00');
        $timeVaoFull = Carbon::today()->setTimeFromTimeString($chamCong->gio_vao);
        $timeRaFull = $now;

        $data = [
            'gio_ra' => $timeRaFull->format('H:i:s'),
            've_som' => $timeRaFull < $gioRaChuanFull,
        ];

        // Tính giờ làm thực tế (trừ 1 tiếng nghỉ trưa từ 12:00 - 13:00)
        $phutLam = $timeVaoFull->diffInMinutes($timeRaFull, true);
        
        $lunchStartToday = Carbon::today()->setTimeFromTimeString('12:00:00');
        $lunchEndToday = Carbon::today()->setTimeFromTimeString('13:00:00');
        
        if ($timeVaoFull < $lunchStartToday && $timeRaFull > $lunchEndToday) {
            $phutLam -= 60;
        }

        $soGioLam = round(max(0, $phutLam) / 60, 2);
        $data['so_gio_lam'] = max(0, min(8, $soGioLam)); // Tối đa 8 tiếng/ngày chuẩn

        // Tính giờ tăng ca tự động (sau 17h)
        if ($timeRaFull > $gioRaChuanFull) {
            $phutTangCa = $gioRaChuanFull->diffInMinutes($timeRaFull, true);
            $data['so_gio_tang_ca'] = round($phutTangCa / 60, 2);
        } else {
            $data['so_gio_tang_ca'] = 0.00;
        }

        $chamCong->update($data);

        return back()->with('success', 'Chấm công ra ca thành công!');
    }
}
