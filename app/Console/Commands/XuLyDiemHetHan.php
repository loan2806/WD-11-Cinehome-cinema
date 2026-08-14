<?php

namespace App\Console\Commands;

use App\Models\LichSuDiem;
use App\Models\ThanhVien;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class XuLyDiemHetHan extends Command
{
    protected $signature = 'diem:het-han';

    protected $description = 'Xử lý các điểm đã hết hạn sau 45 ngày';

    public function handle(): int
    {
        $now = now();

        // Lấy các khoản điểm đã hết hạn nhưng vẫn còn điểm
        $lichSuDiems = LichSuDiem::where('loai_giao_dich', 'cong_diem')
            ->whereNotNull('ngay_het_han')
            ->where('ngay_het_han', '<=', $now)
            ->where('diem_con_lai', '>', 0)
            ->orderBy('ngay_het_han')
            ->get();

        $tongDiemHetHan = 0;

        foreach ($lichSuDiems as $lichSuDiem) {
            DB::transaction(function () use ($lichSuDiem, &$tongDiemHetHan) {

                $diemHetHan = (int) $lichSuDiem->diem_con_lai;

                if ($diemHetHan <= 0) {
                    return;
                }

                $thanhVien = ThanhVien::find($lichSuDiem->thanh_vien_id);

                if (!$thanhVien) {
                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | TRỪ ĐIỂM HIỆN TẠI
                |--------------------------------------------------------------------------
                |
                | Điểm hết hạn chỉ được trừ khỏi điểm có thể sử dụng.
                | Không được trừ tổng điểm tích lũy.
                |
                */

                $diemHienTaiMoi = max(
                    0,
                    $thanhVien->diem_hien_tai - $diemHetHan
                );

                $thanhVien->update([
                    'diem_hien_tai' => $diemHienTaiMoi,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Đánh dấu khoản điểm này đã hết hạn
                |--------------------------------------------------------------------------
                */

                $lichSuDiem->update([
                    'diem_con_lai' => 0,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Ghi lịch sử hết hạn
                |--------------------------------------------------------------------------
                */

                $thanhVien->lichSuDiems()->create([
                    've_xem_phim_id' => null,
                    'loai_giao_dich' => 'dieu_chinh',
                    'so_diem' => -$diemHetHan,
                    'diem_con_lai' => 0,
                    'ngay_het_han' => null,
                    'noi_dung' => 'Điểm hết hạn sau 45 ngày.',
                ]);

                $tongDiemHetHan += $diemHetHan;
            });
        }

        $this->info(
            "Đã xử lý {$tongDiemHetHan} điểm hết hạn."
        );

        return self::SUCCESS;
    }
}