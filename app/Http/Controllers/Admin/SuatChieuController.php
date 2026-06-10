<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NhatKyHoatDongHeThong;
use App\Models\Phims;
use App\Models\RapChieuPhim;
use App\Models\SuatChieu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SuatChieuController extends Controller
{
    private const THOI_GIAN_DON_PHONG = 15;

    public function index()
    {
        $suatChieus = SuatChieu::with(['phim', 'rapChieuPhim'])
            ->orderByDesc('thoi_gian_chieu')
            ->paginate(15);

        return view('admin.suat-chieu.index', compact('suatChieus'));
    }

    public function create()
    {
        $phims = Phims::orderBy('ten_phim')->get();
        $raps = RapChieuPhim::orderBy('ten_rap')->get();

        return view('admin.suat-chieu.create', compact('phims', 'raps'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'phim_id' => ['required', 'exists:phims,id'],
            'rap_chieu_phim_id' => ['required', 'exists:rap_chieu_phims,id'],
            'ngay_chieu' => ['required', 'date', 'after_or_equal:today'],
            'gio_chieu' => ['required', 'date_format:H:i'],
            'gia_ve' => ['required', 'integer', 'min:1000', 'max:500000'],
        ], [
            'ngay_chieu.after_or_equal' => 'Ngay chieu khong duoc nam trong qua khu.',
            'gio_chieu.date_format' => 'Gio chieu khong dung dinh dang.',
        ]);

        $phim = Phims::findOrFail($data['phim_id']);
        $rap = RapChieuPhim::findOrFail($data['rap_chieu_phim_id']);
        $batDau = Carbon::createFromFormat(
            'Y-m-d H:i',
            $data['ngay_chieu'] . ' ' . $data['gio_chieu'],
            'Asia/Ho_Chi_Minh'
        );

        if ($batDau->lte(now('Asia/Ho_Chi_Minh'))) {
            throw ValidationException::withMessages([
                'gio_chieu' => 'Suat chieu phai lon hon thoi diem hien tai.',
            ]);
        }

        if ($phim->ngay_khoi_chieu && $batDau->toDateString() < $phim->ngay_khoi_chieu->toDateString()) {
            throw ValidationException::withMessages([
                'ngay_chieu' => 'Khong the tao suat chieu truoc ngay khoi chieu cua phim.',
            ]);
        }

        $ketThucMoi = $batDau->copy()->addMinutes(max((int) $phim->thoi_luong, 1) + self::THOI_GIAN_DON_PHONG);
        $suatTrung = $this->timSuatChieuBiTrung((int) $data['rap_chieu_phim_id'], $batDau, $ketThucMoi);

        if ($suatTrung) {
            throw ValidationException::withMessages([
                'gio_chieu' => 'Rap nay da co suat "' . ($suatTrung->phim?->ten_phim ?? 'Phim da xoa') . '" luc '
                    . $suatTrung->thoi_gian_chieu->format('H:i')
                    . '. Can cach nhau toi thieu ' . self::THOI_GIAN_DON_PHONG . ' phut.',
            ]);
        }

        $suatChieu = SuatChieu::create([
            'phim_id' => $phim->id,
            'rap_chieu_phim_id' => $rap->id,
            'thoi_gian_chieu' => $batDau,
            'gia_ve' => $data['gia_ve'],
        ]);

        NhatKyHoatDongHeThong::create([
            'nguoi_dung_id' => $request->user()?->id,
            'hanh_dong' => 'tao_suat_chieu',
            'chuc_nang' => 'suat_chieu',
            'mo_ta' => 'Tao suat chieu ' . $phim->ten_phim . ' tai ' . $rap->ten_rap,
            'dia_chi_ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'thuoc_tinh' => ['suat_chieu_id' => $suatChieu->id],
        ]);

        return redirect()
            ->route('admin.suat-chieu.index')
            ->with('success', 'Da tao suat chieu moi.');
    }

    private function timSuatChieuBiTrung(int $rapId, Carbon $batDauMoi, Carbon $ketThucMoi): ?SuatChieu
    {
        return SuatChieu::with('phim')
            ->where('rap_chieu_phim_id', $rapId)
            ->whereDate('thoi_gian_chieu', $batDauMoi->toDateString())
            ->get()
            ->first(function (SuatChieu $suatChieu) use ($batDauMoi, $ketThucMoi) {
                $batDauCu = $suatChieu->thoi_gian_chieu;
                $ketThucCu = $batDauCu->copy()->addMinutes(max((int) $suatChieu->phim?->thoi_luong, 1) + self::THOI_GIAN_DON_PHONG);

                return $batDauMoi->lt($ketThucCu) && $ketThucMoi->gt($batDauCu);
            });
    }
}
