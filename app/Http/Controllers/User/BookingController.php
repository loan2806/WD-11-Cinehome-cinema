<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Phims;
use App\Models\RapChieuPhim;
use App\Models\SuatChieu;
use App\Models\VeXemPhim;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    private const HANG_GHE = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
    private const SO_COT = 10;

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

    public function selectSeats(SuatChieu $showtime)
    {
        $showtime->load(['phim', 'rapChieuPhim']);

        abort_if($showtime->thoi_gian_chieu->lt(now('Asia/Ho_Chi_Minh')), 404);

        $gheDaDat = $this->gheDaDat($showtime);

        $gheBaoTri = [];
        if ($showtime->phong_chieu_id) {
            $gheBaoTri = GheNgoi::where('phong_chieu_id', $showtime->phong_chieu_id)
                ->get(['ma_ghe', 'trang_thai'])
                ->filter(fn ($ghe) => $ghe->isEffectivelyUnderMaintenance())
                ->pluck('ma_ghe')
                ->map(fn ($code) => strtoupper(trim($code)))
                ->values()
                ->all();
        }

        return view('dat_ve.chon_ghe', [
            'suatChieu' => $showtime,
            'gheDaDat' => $gheDaDat,
            'gheBaoTri' => $gheBaoTri,
            'hangGhe' => self::HANG_GHE,
            'soCot' => self::SO_COT,
        ]);
    }

    public function store(Request $request, SuatChieu $showtime)
    {
        $showtime->load(['phim', 'rapChieuPhim']);

        if ($showtime->thoi_gian_chieu->lt(now('Asia/Ho_Chi_Minh'))) {
            throw ValidationException::withMessages([
                'ghe_duoc_chon' => 'Suat chieu nay da qua gio dat ve.',
            ]);
        }

        $data = $request->validate([
            'ghe_duoc_chon' => ['required', 'string'],
        ], [
            'ghe_duoc_chon.required' => 'Vui long chon it nhat mot ghe.',
        ]);

        $gheDuocChon = collect(explode(',', $data['ghe_duoc_chon']))
            ->map(fn ($seat) => strtoupper(trim($seat)))
            ->filter()
            ->unique()
            ->values();

        if ($gheDuocChon->isEmpty()) {
            throw ValidationException::withMessages([
                'ghe_duoc_chon' => 'Vui long chon it nhat mot ghe.',
            ]);
        }

        $showSeats = GheNgoi::where('phong_chieu_id', $showtime->phong_chieu_id)
            ->whereIn('ma_ghe', $gheDuocChon->toArray())
            ->get(['ma_ghe', 'trang_thai']);

        $maintenanceSeats = $showSeats
            ->filter(fn ($seat) => $seat->isEffectivelyUnderMaintenance())
            ->pluck('ma_ghe')
            ->values()
            ->all();

        if (!empty($maintenanceSeats)) {
            throw ValidationException::withMessages([
                'ghe_duoc_chon' => 'Ghế ' . implode(', ', $maintenanceSeats) . ' đang bảo trì, vui lòng chọn ghế khác.',
            ]);
        }

        $gheHopLe = collect(self::HANG_GHE)
            ->flatMap(fn ($hang) => collect(range(1, self::SO_COT))->map(fn ($cot) => $hang . $cot));

        if ($gheDuocChon->diff($gheHopLe)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'ghe_duoc_chon' => 'Danh sach ghe khong hop le.',
            ]);
        }

        $gheDaDat = collect($this->gheDaDat($showtime));
        $gheBiTrung = $gheDuocChon->intersect($gheDaDat);

        if ($gheBiTrung->isNotEmpty()) {
            throw ValidationException::withMessages([
                'ghe_duoc_chon' => 'Ghe ' . $gheBiTrung->join(', ') . ' da co nguoi dat.',
            ]);
        }

        $veXemPhim = VeXemPhim::create([
            'nguoi_dung_id' => Auth::id(),
            'ma_ve' => $this->taoMaVe(),
            'ten_phim' => $showtime->phim->ten_phim,
            'ten_rap' => $showtime->rapChieuPhim->ten_rap,
            'ten_phong' => 'Phong 1',
            'ma_ghe' => $gheDuocChon->join(', '),
            'thoi_gian_chieu' => $showtime->thoi_gian_chieu,
            'tong_tien' => $gheDuocChon->count() * (float) $showtime->gia_ve,
            'loai_ve' => 'truc_tuyen',
            'trang_thai' => 'da_thanh_toan',
        ]);

        return redirect()
            ->route('user.ve_xem_phim.show', $veXemPhim)
            ->with('success', 'Dat ve thanh cong.');
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

    private function taoMaVe(): string
    {
        do {
            $maVe = 'VE' . Carbon::now('Asia/Ho_Chi_Minh')->format('ymd') . Str::upper(Str::random(6));
        } while (VeXemPhim::where('ma_ve', $maVe)->exists());

        return $maVe;
    }
}
