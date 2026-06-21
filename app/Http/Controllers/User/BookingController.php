<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Phims;
use App\Models\RapChieuPhim;
use App\Models\SuatChieu;
use App\Services\DatVeXemPhimService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index(Phims $movie)
    {
        return redirect()->route('user.phims.show', $movie);
    }

    public function showtimes(Phims $movie, RapChieuPhim $cinema)
    {
        $suatChieuTheoPhim = SuatChieu::with(['phim', 'rapChieuPhim'])
            ->where('phim_id', $movie->id)
            ->where('rap_chieu_phim_id', $cinema->id)
            ->where('thoi_gian_chieu', '>=', now('Asia/Ho_Chi_Minh'))
            ->orderBy('thoi_gian_chieu')
            ->get()
            ->groupBy('phim_id');

        return view('dat_ve.chon_phim', [
            'rap' => $cinema,
            'suatChieuTheoPhim' => $suatChieuTheoPhim,
        ]);
    }

    public function selectSeats(SuatChieu $showtime, DatVeXemPhimService $datVeXemPhimService)
    {
        abort_if($showtime->thoi_gian_chieu->lt(now('Asia/Ho_Chi_Minh')), 404);

        return view('dat_ve.chon_ghe', $datVeXemPhimService->duLieuChonGhe($showtime));
    }

    public function store(Request $request, SuatChieu $showtime, DatVeXemPhimService $datVeXemPhimService)
    {
        $data = $request->validate([
            'ghe_duoc_chon' => ['required', 'string'],
            'payment_method' => ['required', 'string'],
        ], [
            'ghe_duoc_chon.required' => 'Vui lòng chọn ít nhất một ghế.',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
        ]);

        $veXemPhim = $datVeXemPhimService->datVeTrucTuyen(
            $showtime,
            Auth::id(),
            $data['ghe_duoc_chon'],
            $data['payment_method']
        );

        return redirect()
            ->route('user.ve_xem_phim.show', $veXemPhim)
            ->with('success', 'Đặt vé thành công. Mã vé của bạn là ' . $veXemPhim->ma_ve . '.');
    }
}