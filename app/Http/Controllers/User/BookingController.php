<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Phims;
use App\Models\RapChieuPhim;
use App\Models\SuatChieu;
use App\Services\DatVeXemPhimService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\ThanhVien;
use App\Models\NguoiDungVoucher;

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

        $vouchers = [];

        if (Auth::check()) {
            $vouchers = NguoiDungVoucher::with('voucher')
                ->where('nguoi_dung_id', Auth::id())
                ->where('da_su_dung', false)
                ->get();
        }

        return view('dat_ve.chon_ghe', [
            'suatChieu' => $showtime,
            'gheDaDat' => $this->gheDaDat($showtime),
            'hangGhe' => self::HANG_GHE,
            'soCot' => self::SO_COT,
            'vouchers' => $vouchers,
        ]);
    }

    public function store(Request $request, SuatChieu $showtime, DatVeXemPhimService $datVeXemPhimService)
    {
        $data = $request->validate([
            'ghe_duoc_chon' => ['required', 'string'],
            'voucher_id' => ['nullable', 'exists:nguoi_dung_vouchers,id'],
        ]);

        $gheDuocChon = collect(explode(',', $data['ghe_duoc_chon']))
            ->map(fn($seat) => strtoupper(trim($seat)))
            ->filter()
            ->unique()
            ->values();

        if ($gheDuocChon->isEmpty()) {
            throw ValidationException::withMessages([
                'ghe_duoc_chon' => 'Vui long chon it nhat mot ghe.',
            ]);
        }

        $gheHopLe = collect(self::HANG_GHE)
            ->flatMap(fn($hang) => collect(range(1, self::SO_COT))->map(fn($cot) => $hang . $cot));

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

        // Mặc định không giảm giá
        $giamGia = 0;

        // Nếu khách chọn voucher
        if (!empty($data['voucher_id'])) {

            $voucherCaNhan = \App\Models\NguoiDungVoucher::with('voucher')
                ->where('id', $data['voucher_id'])
                ->where('nguoi_dung_id', Auth::id())
                ->where('da_su_dung', false)
                ->first();

            if ($voucherCaNhan) {

                $giamGia = $voucherCaNhan->voucher->gia_tri_giam;

                // Đánh dấu voucher đã dùng
                $voucherCaNhan->update([
                    'da_su_dung' => true,
                    'ngay_su_dung' => now(),
                ]);
            }
        }

        $veXemPhim = VeXemPhim::create([
            'nguoi_dung_id' => Auth::id(),
            'suat_chieu_id' => $showtime->id,
            'ma_ve' => $this->taoMaVe(),
            'ten_phim' => $showtime->phim->ten_phim,
            'ten_rap' => $showtime->rapChieuPhim->ten_rap,
            'ten_phong' => 'Phong 1',
            'ma_ghe' => $gheDuocChon->join(', '),
            'thoi_gian_chieu' => $showtime->thoi_gian_chieu,
            'tong_tien' => max(($gheDuocChon->count() * (float) $showtime->gia_ve) - $giamGia, 0),
            'loai_ve' => 'truc_tuyen',
            'trang_thai' => 'da_thanh_toan',
            'voucher_id' => ['nullable', 'exists:nguoi_dung_vouchers,id'],
        ]);

        // Cộng điểm thành viên sau khi đặt vé thành công
        $this->congDiemThanhVien($veXemPhim);

        return redirect()
            ->route('user.ve_xem_phim.show', $veXemPhim)
            ->with('success', 'Đặt vé thành công. Mã vé của bạn là ' . $veXemPhim->ma_ve . '.');
    }

    private function gheDaDat(SuatChieu $suatChieu): array
    {
        return VeXemPhim::query()
            ->where('ten_phim', $suatChieu->phim->ten_phim)
            ->where('ten_rap', $suatChieu->rapChieuPhim->ten_rap)
            ->where('thoi_gian_chieu', $suatChieu->thoi_gian_chieu->format('Y-m-d H:i:s'))
            ->where('trang_thai', '!=', 'da_huy')
            ->pluck('ma_ghe')
            ->flatMap(fn($seats) => explode(',', (string) $seats))
            ->map(fn($seat) => strtoupper(trim($seat)))
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

    /**
     * Cộng điểm cho khách hàng sau khi đặt vé thành công.
     * Quy tắc hiện tại: 10.000 VNĐ = 1 điểm.
     */
    private function congDiemThanhVien(VeXemPhim $veXemPhim): void
    {
        if (!$veXemPhim->nguoi_dung_id || $veXemPhim->trang_thai !== 'da_thanh_toan') {
            return;
        }

        $thanhVien = ThanhVien::firstOrCreate(
            [
                'nguoi_dung_id' => $veXemPhim->nguoi_dung_id,
            ],
            [
                'ma_thanh_vien' => 'TV' . str_pad($veXemPhim->nguoi_dung_id, 6, '0', STR_PAD_LEFT),
                'hang_thanh_vien' => 'member',
                'diem_hien_tai' => 0,
                'tong_diem_tich_luy' => 0,
                'ngay_tham_gia' => now(),
            ]
        );

        // Điểm gốc: 10.000 VNĐ = 1 điểm
        $diemGoc = (int) floor((float) $veXemPhim->tong_tien / 10000);

        // Nhân điểm theo hạng thành viên hiện tại
        $diemCong = (int) floor($diemGoc * $thanhVien->heSoTichDiem());

        $thanhVien->congDiem(
            $diemCong,
            $veXemPhim,
            'Cộng ' . $diemCong . ' điểm khi mua vé phim ' . $veXemPhim->ten_phim .
                ' (hệ số hạng ' . strtoupper($thanhVien->hang_thanh_vien) . ')'
        );
    }
}
