<?php

namespace App\Http\Controllers\DatVe;

use App\Http\Controllers\Controller;
use App\Models\RapChieuPhim;
use App\Models\SuatChieu;
use App\Services\DatVeXemPhimService;
use Illuminate\Support\Facades\Auth;
use App\Models\NguoiDungVoucher;

class DatVeController extends Controller
{
    public function chonRap()
    {
        $danhSachRap = RapChieuPhim::whereHas('suatChieus', function ($query) {
            $query->where('thoi_gian_chieu', '>=', now('Asia/Ho_Chi_Minh'));
        })
            ->orderBy('ten_rap')
            ->get();

        return view('dat_ve.chon_rap', compact('danhSachRap'));
    }

    public function chonPhim($rap_id)
    {
        $rap = RapChieuPhim::findOrFail($rap_id);

        $danhSachSuatChieu = SuatChieu::with(['phim', 'rapChieuPhim'])
            ->where('rap_chieu_phim_id', $rap->id)
            ->where('thoi_gian_chieu', '>=', now('Asia/Ho_Chi_Minh'))
            ->orderBy('thoi_gian_chieu')
            ->get();

        $suatChieuTheoPhim = $danhSachSuatChieu->groupBy('phim_id');

        return view('dat_ve.chon_phim', compact('rap', 'suatChieuTheoPhim'));
    }

    public function chonGhe($suat_chieu_id, DatVeXemPhimService $datVeXemPhimService)
    {
        $suatChieu = SuatChieu::with(['phim', 'rapChieuPhim', 'phongChieu'])
            ->findOrFail($suat_chieu_id);

        abort_if($suatChieu->thoi_gian_chieu->lt(now('Asia/Ho_Chi_Minh')), 404);

        $duLieuChonGhe = $datVeXemPhimService->duLieuChonGhe($suatChieu);

        // Lấy voucher chưa sử dụng của khách đang đăng nhập
        $duLieuChonGhe['vouchers'] = Auth::check()
            ? NguoiDungVoucher::with('voucher')
            ->where('nguoi_dung_id', Auth::id())
            ->where('da_su_dung', false)
            ->get()
            : collect();

        return view('dat_ve.chon_ghe', $duLieuChonGhe);
    }
}
