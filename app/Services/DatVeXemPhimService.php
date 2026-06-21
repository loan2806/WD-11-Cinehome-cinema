<?php

namespace App\Services;

use App\Models\GheNgoi;
use App\Models\SuatChieu;
use App\Models\SystemSetting;
use App\Models\VeXemPhim;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DatVeXemPhimService
{
    private const HANG_GHE_MAC_DINH = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
    private const SO_COT_MAC_DINH = 10;
    private const SO_GHE_TOI_DA = 8;

    public function duLieuChonGhe(SuatChieu $suatChieu): array
    {
        $suatChieu->loadMissing(['phim', 'rapChieuPhim', 'phongChieu']);

        $gheDaDat = $this->gheDaDat($suatChieu);
        $gheTheoHang = $this->gheTheoHang($suatChieu, $gheDaDat);

        return [
            'suatChieu' => $suatChieu,
            'gheTheoHang' => $gheTheoHang,
            'gheDaDat' => $gheDaDat,
            'coSoDoGheThat' => $gheTheoHang->flatten(1)->contains(fn ($seat) => $seat['nguon'] === 'database'),
            'phuongThucThanhToan' => $this->phuongThucThanhToan(),
        ];
    }

    public function datVeTrucTuyen(SuatChieu $suatChieu, int $nguoiDungId, string $rawSeats, string $paymentMethod): VeXemPhim
    {
        if (! SystemSetting::getBoolean('booking_enabled', true)) {
            throw ValidationException::withMessages([
                'ghe_duoc_chon' => 'Hệ thống đang tạm khóa chức năng đặt vé.',
            ]);
        }

        if (! array_key_exists($paymentMethod, $this->phuongThucThanhToan())) {
            throw ValidationException::withMessages([
                'payment_method' => 'Phương thức thanh toán không hợp lệ hoặc đang tắt.',
            ]);
        }

        return DB::transaction(function () use ($suatChieu, $nguoiDungId, $rawSeats, $paymentMethod) {
            $lockedShowtime = SuatChieu::query()
                ->whereKey($suatChieu->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedShowtime->load(['phim', 'rapChieuPhim', 'phongChieu']);

            $this->kiemTraSuatChieuCoTheDat($lockedShowtime);

            $maGheDuocChon = $this->chuanHoaDanhSachGhe($rawSeats);
            $chiTietGhe = $this->kiemTraGheHopLe($lockedShowtime, $maGheDuocChon);
            $gheBiTrung = collect($this->gheDaDat($lockedShowtime))->intersect($maGheDuocChon);

            if ($gheBiTrung->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'ghe_duoc_chon' => 'Ghế ' . $gheBiTrung->implode(', ') . ' đã có người đặt. Vui lòng chọn ghế khác.',
                ]);
            }

            $tongTien = $chiTietGhe->sum('gia');

            return VeXemPhim::create([
                'nguoi_dung_id' => $nguoiDungId,
                'nhan_vien_id' => null,
                'suat_chieu_id' => $lockedShowtime->id,
                'ma_ve' => $this->taoMaVe(),
                'ten_phim' => $lockedShowtime->phim->ten_phim,
                'ten_rap' => $lockedShowtime->rapChieuPhim->ten_rap,
                'ten_phong' => $lockedShowtime->phongChieu->ten_phong ?? 'Phòng chiếu',
                'ma_ghe' => $maGheDuocChon->implode(','),
                'thoi_gian_chieu' => $lockedShowtime->thoi_gian_chieu,
                'tong_tien' => $tongTien,
                'tien_hoan' => 0,
                'loai_ve' => 'truc_tuyen',
                'trang_thai' => 'da_thanh_toan',
            ]);
        });
    }

    public function gheDaDat(SuatChieu $suatChieu): array
    {
        $suatChieu->loadMissing(['phim', 'rapChieuPhim']);

        return VeXemPhim::query()
            ->whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung'])
            ->where(function ($query) use ($suatChieu) {
                $query->where('suat_chieu_id', $suatChieu->id)
                    ->orWhere(function ($fallback) use ($suatChieu) {
                        $fallback
                            ->whereNull('suat_chieu_id')
                            ->where('ten_phim', $suatChieu->phim->ten_phim)
                            ->where('ten_rap', $suatChieu->rapChieuPhim->ten_rap)
                            ->where('thoi_gian_chieu', $suatChieu->thoi_gian_chieu->format('Y-m-d H:i:s'));
                    });
            })
            ->pluck('ma_ghe')
            ->flatMap(fn ($seats) => explode(',', (string) $seats))
            ->map(fn ($seat) => strtoupper(trim($seat)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function phuongThucThanhToan(): array
    {
        $methods = [];

        if (SystemSetting::getBoolean('payment_demo_enabled', true)) {
            $methods['demo_online'] = 'Thanh toán online giả lập';
        }

        if (SystemSetting::getBoolean('payment_cash_enabled', true)) {
            $methods['cash'] = 'Thanh toán tại quầy';
        }

        if (SystemSetting::getBoolean('payment_vnpay_enabled', false)) {
            $methods['vnpay'] = 'VNPAY';
        }

        if (SystemSetting::getBoolean('payment_momo_enabled', false)) {
            $methods['momo'] = 'MoMo';
        }

        return $methods;
    }

    private function gheTheoHang(SuatChieu $suatChieu, array $gheDaDat): Collection
    {
        if ($suatChieu->phong_chieu_id) {
            $gheTrongPhong = GheNgoi::query()
                ->with(['hangGhe', 'loaiGhe'])
                ->where('phong_chieu_id', $suatChieu->phong_chieu_id)
                ->orderBy('hang_ghe_id')
                ->orderBy('cot')
                ->get();

            if ($gheTrongPhong->isNotEmpty()) {
                return $gheTrongPhong
                    ->map(fn (GheNgoi $ghe) => $this->payloadGhe($ghe, $suatChieu, $gheDaDat))
                    ->groupBy('hang')
                    ->map(fn ($seats) => $seats->sortBy('cot')->values());
            }
        }

        return collect(self::HANG_GHE_MAC_DINH)
            ->mapWithKeys(function (string $hang) use ($suatChieu, $gheDaDat) {
                $seats = collect(range(1, self::SO_COT_MAC_DINH))->map(function (int $cot) use ($hang, $suatChieu, $gheDaDat) {
                    $maGhe = $hang . $cot;

                    return [
                        'nguon' => 'fallback',
                        'ma_ghe' => $maGhe,
                        'hang' => $hang,
                        'cot' => $cot,
                        'loai_ghe' => 'Thường',
                        'mau_sac' => '#2a2a2a',
                        'phu_thu' => 0,
                        'gia' => (float) $suatChieu->gia_ve,
                        'da_dat' => in_array($maGhe, $gheDaDat, true),
                        'bao_tri' => false,
                        'chon_duoc' => ! in_array($maGhe, $gheDaDat, true),
                    ];
                });

                return [$hang => $seats];
            });
    }

    private function payloadGhe(GheNgoi $ghe, SuatChieu $suatChieu, array $gheDaDat): array
    {
        $phuThu = (float) ($ghe->loaiGhe?->phu_thu ?? 0);
        $daDat = in_array(strtoupper($ghe->ma_ghe), $gheDaDat, true);
        $baoTri = $ghe->trang_thai !== 'hoat_dong';

        return [
            'nguon' => 'database',
            'ma_ghe' => strtoupper($ghe->ma_ghe),
            'hang' => $ghe->hangGhe->ten_hang ?? strtoupper(substr($ghe->ma_ghe, 0, 1)),
            'cot' => (int) $ghe->cot,
            'loai_ghe' => $ghe->loaiGhe->ten_loai ?? 'Thường',
            'mau_sac' => $ghe->loaiGhe->mau_sac ?? '#2a2a2a',
            'phu_thu' => $phuThu,
            'gia' => (float) $suatChieu->gia_ve + $phuThu,
            'da_dat' => $daDat,
            'bao_tri' => $baoTri,
            'chon_duoc' => ! $daDat && ! $baoTri,
        ];
    }

    private function kiemTraSuatChieuCoTheDat(SuatChieu $suatChieu): void
    {
        if ($suatChieu->thoi_gian_chieu->lt(now('Asia/Ho_Chi_Minh'))) {
            throw ValidationException::withMessages([
                'ghe_duoc_chon' => 'Suất chiếu này đã qua giờ đặt vé.',
            ]);
        }

        if (in_array($suatChieu->trang_thai, ['da_chieu', 'huy'], true)) {
            throw ValidationException::withMessages([
                'ghe_duoc_chon' => 'Suất chiếu này không còn nhận đặt vé.',
            ]);
        }

        if ($suatChieu->phongChieu && $suatChieu->phongChieu->trang_thai !== 'hoat_dong') {
            throw ValidationException::withMessages([
                'ghe_duoc_chon' => 'Phòng chiếu đang bảo trì hoặc ngừng hoạt động.',
            ]);
        }
    }

    private function chuanHoaDanhSachGhe(string $rawSeats): Collection
    {
        $seats = collect(explode(',', $rawSeats))
            ->map(fn ($seat) => strtoupper(trim($seat)))
            ->filter()
            ->unique()
            ->values();

        if ($seats->isEmpty()) {
            throw ValidationException::withMessages([
                'ghe_duoc_chon' => 'Vui lòng chọn ít nhất một ghế.',
            ]);
        }

        if ($seats->count() > self::SO_GHE_TOI_DA) {
            throw ValidationException::withMessages([
                'ghe_duoc_chon' => 'Mỗi lần đặt tối đa ' . self::SO_GHE_TOI_DA . ' ghế.',
            ]);
        }

        return $seats;
    }

    private function kiemTraGheHopLe(SuatChieu $suatChieu, Collection $maGheDuocChon): Collection
    {
        $gheTheoHang = $this->gheTheoHang($suatChieu, []);
        $tatCaGhe = $gheTheoHang->flatten(1)->keyBy('ma_ghe');

        $gheKhongTonTai = $maGheDuocChon->diff($tatCaGhe->keys());

        if ($gheKhongTonTai->isNotEmpty()) {
            throw ValidationException::withMessages([
                'ghe_duoc_chon' => 'Ghế ' . $gheKhongTonTai->implode(', ') . ' không tồn tại trong phòng chiếu.',
            ]);
        }

        $gheKhongChonDuoc = $maGheDuocChon
            ->map(fn ($maGhe) => $tatCaGhe[$maGhe])
            ->filter(fn ($seat) => ! $seat['chon_duoc']);

        if ($gheKhongChonDuoc->isNotEmpty()) {
            throw ValidationException::withMessages([
                'ghe_duoc_chon' => 'Có ghế đang bảo trì hoặc không thể chọn.',
            ]);
        }

        return $maGheDuocChon->map(fn ($maGhe) => $tatCaGhe[$maGhe])->values();
    }

    private function taoMaVe(): string
    {
        do {
            $maVe = 'VE-' . Carbon::now('Asia/Ho_Chi_Minh')->format('ymd') . '-' . Str::upper(Str::random(6));
        } while (VeXemPhim::where('ma_ve', $maVe)->exists());

        return $maVe;
    }
}
