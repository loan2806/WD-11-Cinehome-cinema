<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\GheNgoi;
use App\Models\SuatChieu;
use App\Models\VeXemPhim;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BanVeController extends Controller
{
    /**
     * Trang đầu tiên của bán vé tại quầy.
     * Nhân viên sẽ chọn một suất chiếu trước.
     */
    public function index()
    {
        $showtimes = SuatChieu::with(['phim', 'rapChieuPhim', 'phongChieu'])
            ->where('thoi_gian_chieu', '>=', now())
            ->orderBy('thoi_gian_chieu')
            ->get();

        return view('staff.ban-ve.index', compact('showtimes'));
    }

    /**
     * Sau khi chọn suất chiếu, hệ thống hiển thị sơ đồ ghế.
     * Ghế đã bán sẽ bị khóa, ghế bảo trì cũng không chọn được.
     */
    public function show(SuatChieu $suatChieu)
    {
        $suatChieu->load(['phim', 'rapChieuPhim', 'phongChieu']);

        $soldSeatCodes = VeXemPhim::where('suat_chieu_id', $suatChieu->id)
            ->whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung'])
            ->pluck('ma_ghe')
            ->flatMap(function ($seatCodes) {
                return collect(explode(',', $seatCodes))
                    ->map(fn($seatCode) => strtoupper(trim($seatCode)))
                    ->filter();
            })
            ->values();

        $seatsByRow = collect();

        if ($suatChieu->phong_chieu_id) {
            $seatsByRow = GheNgoi::with(['hangGhe', 'loaiGhe'])
                ->where('phong_chieu_id', $suatChieu->phong_chieu_id)
                ->orderBy('hang_ghe_id')
                ->orderBy('cot')
                ->get()
                ->groupBy(fn($seat) => $seat->hangGhe->ten_hang ?? 'Khác');
        }

        return view('staff.ban-ve.show', compact(
            'suatChieu',
            'soldSeatCodes',
            'seatsByRow'
        ));
    }

    /**
     * Tạo vé tại quầy sau khi nhân viên chọn ghế.
     * Bản này hỗ trợ chọn nhiều ghế trong cùng một lần bán.
     */
    public function store(Request $request, SuatChieu $suatChieu)
    {
        $request->validate([
            'seats' => 'required|array|min:1',
            'seats.*' => 'required|string|max:20',
        ], [
            'seats.required' => 'Vui lòng chọn ít nhất một ghế.',
            'seats.min' => 'Vui lòng chọn ít nhất một ghế.',
        ]);

        $suatChieu->load(['phim', 'rapChieuPhim', 'phongChieu']);

        $selectedSeats = collect($request->seats)
            ->map(fn($seat) => strtoupper(trim($seat)))
            ->unique()
            ->values();

        $soldSeats = VeXemPhim::where('suat_chieu_id', $suatChieu->id)
            ->whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung'])
            ->pluck('ma_ghe')
            ->flatMap(function ($seatCodes) {
                return collect(explode(',', $seatCodes))
                    ->map(fn($seatCode) => strtoupper(trim($seatCode)))
                    ->filter();
            })
            ->intersect($selectedSeats)
            ->values();

        if ($soldSeats->isNotEmpty()) {
            return back()
                ->withInput()
                ->with('error', 'Ghế ' . $soldSeats->implode(', ') . ' đã được bán. Vui lòng chọn ghế khác.');
        }

        $tongTien = $selectedSeats->count() * (float) $suatChieu->gia_ve;

        $ve = VeXemPhim::create([
            'nguoi_dung_id' => null,
            'nhan_vien_id' => auth()->id(),
            'suat_chieu_id' => $suatChieu->id,

            'ma_ve' => 'OFF-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(5)),

            'ten_phim' => $suatChieu->phim->ten_phim ?? 'Không rõ phim',
            'ten_rap' => $suatChieu->rapChieuPhim->ten_rap ?? 'Không rõ rạp',
            'ten_phong' => $suatChieu->phongChieu->ten_phong ?? 'Phòng chiếu',

            // Lưu nhiều ghế dạng A1,A2,A3 để phù hợp bảng hiện tại
            'ma_ghe' => $selectedSeats->implode(','),

            'thoi_gian_chieu' => $suatChieu->thoi_gian_chieu,
            'tong_tien' => $tongTien,
            'tien_hoan' => 0,
            'loai_ve' => 'tai_quay',
            'trang_thai' => 'da_thanh_toan',
        ]);

        return redirect()
            ->route('staff.lich-su-ve.index')
            ->with('success', 'Bán vé thành công. Mã vé: ' . $ve->ma_ve);
    }
}
