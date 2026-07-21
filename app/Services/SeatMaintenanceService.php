<?php

namespace App\Services;

use App\Models\GheNgoi;
use App\Models\LichBaoTriGheNgoi;
use App\Models\SuatChieu;
use App\Models\VeXemPhim;
use Illuminate\Support\Facades\DB;

class SeatMaintenanceService
{
    /**
     * Kiểm tra ghế có vé ở suất chiếu tương lai không.
     * Nếu có trả mảng thông tin vé bị ảnh hưởng.
     */
    public function getFutureConflicts(GheNgoi $ghe, ?\DateTimeInterface $before = null): array
    {
        $maGhe = $ghe->ma_ghe;
        $phongChieu = $ghe->phongChieu;
        if (!$phongChieu) {
            return [];
        }

        $now = $before ? \Carbon\Carbon::instance($before) : now();

        $veQuery = VeXemPhim::query()
            ->whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung'])
            ->where('ten_phong', $phongChieu->ten_phong)
            ->when(!empty($phongChieu->rapChieuPhim?->ten_rap), function ($q) use ($phongChieu) {
                $q->where('ten_rap', $phongChieu->rapChieuPhim->ten_rap);
            });

        // Ưu tiên lọc theo suat_chieu_id nếu DB đang có khóa ngoại thật.
        // Nếu cột không tồn tại hoặc NULL thì fallback sang thoi_gian_chieu.
        if (method_exists(VeXemPhim::query()->getModel(), 'suatChieu')) {
            $veQuery->whereHas('suatChieu', function ($q) use ($now) {
                $q->where('thoi_gian_chieu', '>=', $now);
            });
        } else {
            $veQuery->where('thoi_gian_chieu', '>=', $now);
        }

        $ves = $veQuery->get(['id', 'ma_ve', 'ma_ghe', 'thoi_gian_chieu', 'suat_chieu_id']);

        $conflicted = [];
        foreach ($ves as $ve) {
            $seats = array_filter(array_map('trim', explode(',', (string) $ve->ma_ghe)));
            if (!in_array($maGhe, $seats, true)) {
                continue;
            }

            $showtime = null;
            if ($ve->suat_chieu_id) {
                $showtime = SuatChieu::find($ve->suat_chieu_id);
            }

            $conflicted[] = [
                've_id' => $ve->id,
                'ma_ve' => $ve->ma_ve,
                'ma_ghe' => $maGhe,
                'thoi_gian_chieu' => $showtime?->thoi_gian_chieu ?? $ve->thoi_gian_chieu,
                'suat_chieu_id' => $ve->suat_chieu_id,
            ];
        }

        return $conflicted;
    }

    /**
     * Kiểm tra xem có thể bảo trì ngay không.
     */
    public function canMaintainNow(GheNgoi $ghe): array
    {
        if ($ghe->trang_thai === 'bao_tri') {
            return ['can' => true, 'conflicts' => []];
        }

        $conflicts = $this->getFutureConflicts($ghe);

        return [
            'can' => empty($conflicts),
            'conflicts' => $conflicts,
        ];
    }

    /**
     * Lấy ghế couple liên quan (đồng thời cập nhật nếu cần).
     */
    public function getCoupleSiblings(GheNgoi $ghe): array
    {
        if (empty($ghe->couple_group_id)) {
            return [];
        }

        return GheNgoi::where('couple_group_id', $ghe->couple_group_id)
            ->where('id', '!=', $ghe->id)
            ->get()
            ->all();
    }

    /**
     * Bảo trì ngay.
     */
    public function maintainNow(GheNgoi $ghe, ?int $nguoiThucHienId, ?string $lyDo = null): LichBaoTriGheNgoi
    {
        $beforeStatus = $ghe->trang_thai;

        return DB::transaction(function () use ($ghe, $nguoiThucHienId, $lyDo, $beforeStatus) {
            $this->applyCoupleMaintenance($ghe);

            $ghe->update(['trang_thai' => 'bao_tri']);

            return LichBaoTriGheNgoi::create([
                'ghe_ngoi_id' => $ghe->id,
                'phong_chieu_id' => $ghe->phong_chieu_id,
                'nguoi_dung_id' => $nguoiThucHienId,
                'thoi_gian_bat_dau' => now(),
                'ly_do' => $lyDo,
                'trang_thai_truoc' => $beforeStatus,
                'trang_thai_sau' => 'bao_tri',
                'trang_thai' => 'dang_thuc_hien',
            ]);
        });
    }

    /**
     * Lên lịch bảo trì.
     */
    public function scheduleMaintenance(GheNgoi $ghe, \DateTimeInterface $thoiGianBatDau, ?\DateTimeInterface $thoiGianKetThuc, ?int $nguoiThucHienId, ?string $lyDo = null): LichBaoTriGheNgoi
    {
        $beforeStatus = $ghe->trang_thai;

        return DB::transaction(function () use ($ghe, $thoiGianBatDau, $thoiGianKetThuc, $nguoiThucHienId, $lyDo, $beforeStatus) {
            $this->applyCoupleMaintenance($ghe);

            return LichBaoTriGheNgoi::create([
                'ghe_ngoi_id' => $ghe->id,
                'phong_chieu_id' => $ghe->phong_chieu_id,
                'nguoi_dung_id' => $nguoiThucHienId,
                'thoi_gian_bat_dau' => $thoiGianBatDau,
                'thoi_gian_ket_thuc' => $thoiGianKetThuc,
                'ly_do' => $lyDo,
                'trang_thai_truoc' => $beforeStatus,
                'trang_thai_sau' => 'bao_tri',
                'trang_thai' => 'cho_thuc_hien',
            ]);
        });
    }

    /**
     * Kích hoạt lại sau bảo trì.
     */
    public function completeMaintenance(LichBaoTriGheNgoi $lich, ?int $nguoiThucHienId, ?string $ghiChu = null): GheNgoi
    {
        return DB::transaction(function () use ($lich, $nguoiThucHienId, $ghiChu) {
            $ghe = GheNgoi::where('id', $lich->ghe_ngoi_id)->lockForUpdate()->firstOrFail();

            $beforeStatus = $ghe->trang_thai;

            $this->applyCoupleActivation($ghe);

            $ghe->update(['trang_thai' => 'hoat_dong']);

            $lich->update([
                'trang_thai' => 'da_hoan_thanh',
                'thoi_gian_ket_thuc' => $lich->thoi_gian_ket_thuc ?? now(),
                'ghi_chu' => $ghiChu,
            ]);

            // Đồng bộ các lịch chờ liên quan của cùng ghế về đã hủy
            LichBaoTriGheNgoi::where('ghe_ngoi_id', $ghe->id)
                ->where('id', '!=', $lich->id)
                ->whereIn('trang_thai', ['cho_thuc_hien', 'dang_thuc_hien'])
                ->update(['trang_thai' => 'da_huy', 'ghi_chu' => ($ghiChu ? $ghiChu . ' ' : '') . 'Đã kích hoạt lại sớm.']);

            return $ghe->fresh();
        });
    }

    /**
     * Kích hoạt lại ghế đơn lẻ (không cần lịch bảo trì).
     */
    public function activateSeat(GheNgoi $ghe, ?int $nguoiThucHienId = null): GheNgoi
    {
        $ghe->update(['trang_thai' => 'hoat_dong']);
        return $ghe->fresh();
    }

    /**
     * Áp dụng bảo trì cho ghế couple liên quan.
     */
    protected function applyCoupleMaintenance(GheNgoi $ghe): void
    {
        if (empty($ghe->couple_group_id)) {
            return;
        }

        GheNgoi::where('couple_group_id', $ghe->couple_group_id)
            ->where('id', '!=', $ghe->id)
            ->where('trang_thai', 'hoat_dong')
            ->update(['trang_thai' => 'bao_tri']);
    }

    /**
     * Áp dụng kích hoạt cho ghế couple liên quan.
     */
    protected function applyCoupleActivation(GheNgoi $ghe): void
    {
        if (empty($ghe->couple_group_id)) {
            return;
        }

        GheNgoi::where('couple_group_id', $ghe->couple_group_id)
            ->where('id', '!=', $ghe->id)
            ->where('trang_thai', 'bao_tri')
            ->update(['trang_thai' => 'hoat_dong']);
    }
}
