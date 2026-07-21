<?php

namespace App\Console\Commands;

use App\Models\GheNgoi;
use App\Models\LichBaoTriGheNgoi;
use Illuminate\Console\Command;

class KichHoatGheHetHanBaoTri extends Command
{
    protected $signature = 'ghe:kich-hoat-het-han';

    protected $description = 'Tự động kích hoạt ghế hết hạn bảo trì có thời hạn';

    public function handle(): int
    {
        $now = now();

        // 1. Kích hoạt các ghế bảo trì có thời hạn đã hết hạn
        // Tìm các lịch bảo trì đang 'dang_thuc_hien' hoặc 'cho_thuc_hien' mà thời_gian_ket_thuc <= hiện tại
        $lichsCanKichHoat = LichBaoTriGheNgoi::whereIn('trang_thai', ['dang_thuc_hien', 'cho_thuc_hien'])
            ->whereNotNull('thoi_gian_ket_thuc')
            ->where('thoi_gian_ket_thuc', '<=', $now)
            ->with('gheNgoi')
            ->get();

        $soKichHoat = 0;
        $soLichCanKichHoat = 0;

        foreach ($lichsCanKichHoat as $lich) {
            $ghe = $lich->gheNgoi;
            if (!$ghe || $ghe->trang_thai !== 'bao_tri') {
                continue;
            }

            $soLichCanKichHoat++;

            // Kiểm tra xem ghế có vé đang sử dụng ở thời điểm hiện tại không
            $hasCurrentBooking = $this->hasCurrentBooking($ghe);
            if ($hasCurrentBooking) {
                $this->warn("Ghế {$ghe->ma_ghe}: Có vé đang sử dụng, bỏ qua kích hoạt tự động.");
                continue;
            }

            // Kích hoạt ghế
            $ghe->update(['trang_thai' => 'hoat_dong']);

            // Cập nhật trạng thái lịch bảo trì
            $lich->update([
                'trang_thai' => 'da_hoan_thanh',
            ]);

            // Hủy các lịch chờ khác của cùng ghế
            LichBaoTriGheNgoi::where('ghe_ngoi_id', $ghe->id)
                ->where('id', '!=', $lich->id)
                ->whereIn('trang_thai', ['cho_thuc_hien', 'dang_thuc_hien'])
                ->update(['trang_thai' => 'da_huy', 'ghi_chu' => 'Hệ thống tự động hủy khi kích hoạt ghế.']);

            // Kích hoạt ghế couple liên quan (nếu có)
            $this->activateCoupleSiblings($ghe);

            $soKichHoat++;
            $this->info("Đã kích hoạt ghế {$ghe->ma_ghe} - Lý do: {$lich->ly_do}");
        }

        // 2. Kích hoạt các ghế bảo trì có lịch 'cho_thuc_hien' mà thời_gian_bat_dau <= hiện tại
        $currentTime = now();
        $lichsBatDau = LichBaoTriGheNgoi::where('trang_thai', 'cho_thuc_hien')
            ->where('thoi_gian_bat_dau', '<=', $currentTime)
            ->where(function ($query) use ($currentTime) {
                $query->whereNull('thoi_gian_ket_thuc')
                    ->orWhere('thoi_gian_ket_thuc', '>', $currentTime);
            })
            ->with('gheNgoi')
            ->get();

        foreach ($lichsBatDau as $lich) {
            $ghe = $lich->gheNgoi;
            if (!$ghe) {
                continue;
            }

            // Kiểm tra vé xung đột
            $hasConflict = $this->hasFutureConflict($ghe, $lich->thoi_gian_bat_dau);
            if ($hasConflict) {
                $this->warn("Ghế {$ghe->ma_ghe}: Có vé xung đột, chuyển trạng thái lịch sang 'da_huy'.");
                $lich->update(['trang_thai' => 'da_huy', 'ghi_chu' => 'Hệ thống tự động hủy do có vé xung đột.']);
                continue;
            }

            // Chuyển sang đang thực hiện
            $ghe->update(['trang_thai' => 'bao_tri']);
            $lich->update(['trang_thai' => 'dang_thuc_hien']);

            // Kích hoạt ghế couple liên quan (nếu có)
            $this->activateCoupleSiblings($ghe);

            $this->info("Đã bắt đầu bảo trì ghế {$ghe->ma_ghe} - Lý do: {$lich->ly_do}");
        }

        $this->info("Hoàn tất: Đã kích hoạt {$soKichHoat}/{$soLichCanKichHoat} ghế.");
        return self::SUCCESS;
    }

    /**
     * Kiểm tra ghế có vé đang sử dụng tại thời điểm hiện tại không
     */
    protected function hasCurrentBooking(GheNgoi $ghe): bool
    {
        $now = now();
        $phongChieu = $ghe->phongChieu;

        if (!$phongChieu) {
            return false;
        }

        return \App\Models\VeXemPhim::whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung'])
            ->where('ten_phong', $phongChieu->ten_phong)
            ->whereNotNull('ma_ghe')
            ->where('ma_ghe', '!=', '')
            ->get()
            ->filter(function ($ve) use ($ghe, $now) {
                $seats = array_filter(array_map('trim', explode(',', (string) $ve->ma_ghe)));
                return in_array($ghe->ma_ghe, $seats, true);
            })
            ->isNotEmpty();
    }

    /**
     * Kiểm tra ghế có vé xung đột trong khoảng thời gian không
     */
    protected function hasFutureConflict(GheNgoi $ghe, $startTime): bool
    {
        $phongChieu = $ghe->phongChieu;
        if (!$phongChieu) {
            return false;
        }

        $conflicts = \App\Models\VeXemPhim::whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung'])
            ->where('ten_phong', $phongChieu->ten_phong)
            ->where('thoi_gian_chieu', '>=', $startTime)
            ->whereNotNull('ma_ghe')
            ->where('ma_ghe', '!=', '')
            ->get()
            ->filter(function ($ve) use ($ghe) {
                $seats = array_filter(array_map('trim', explode(',', (string) $ve->ma_ghe)));
                return in_array($ghe->ma_ghe, $seats, true);
            });

        return $conflicts->isNotEmpty();
    }

    /**
     * Kích hoạt các ghế couple liên quan
     */
    protected function activateCoupleSiblings(GheNgoi $ghe): void
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
