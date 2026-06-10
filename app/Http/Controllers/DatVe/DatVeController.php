<?php

namespace App\Http\Controllers\DatVe;

use App\Http\Controllers\Controller;
use App\Models\RapChieuPhim;
use App\Models\SuatChieu;
use App\Models\VeXemPhim;

class DatVeController extends Controller
{
    private const HANG_GHE = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
    private const SO_COT = 10;

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

    public function chonGhe($suat_chieu_id)
    {
        $suatChieu = SuatChieu::with(['phim', 'rapChieuPhim'])->findOrFail($suat_chieu_id);

        abort_if($suatChieu->thoi_gian_chieu->lt(now('Asia/Ho_Chi_Minh')), 404);

        return view('dat_ve.chon_ghe', [
            'suatChieu' => $suatChieu,
            'gheDaDat' => $this->gheDaDat($suatChieu),
            'hangGhe' => self::HANG_GHE,
            'soCot' => self::SO_COT,
        ]);
    }

    private function gheDaDat(SuatChieu $suatChieu): array
    {
        return VeXemPhim::query()
            ->where('ten_phim', $suatChieu->phim->ten_phim)
            ->where('ten_rap', $suatChieu->rapChieuPhim->ten_rap)
            ->where('thoi_gian_chieu', $suatChieu->thoi_gian_chieu->format('Y-m-d H:i:s'))
            ->where('trang_thai', '!=', 'da_huy')
            ->pluck('ma_ghe')
            ->flatMap(fn ($seats) => explode(',', (string) $seats))
            ->map(fn ($seat) => strtoupper(trim($seat)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
