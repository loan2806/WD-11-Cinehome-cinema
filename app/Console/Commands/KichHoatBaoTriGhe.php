<?php

namespace App\Console\Commands;

use App\Models\GheNgoi;
use App\Models\LichBaoTriGheNgoi;
use Illuminate\Console\Command;
use Carbon\Carbon;

class KichHoatBaoTriGhe extends Command
{
    protected $signature = 'baotri:activate';
    protected $description = 'Kích hoạt bảo trì ghế khi đến giờ';

    public function handle()
    {
        $now = Carbon::now();
        
        // Tìm các lịch bảo trì chưa kích hoạt và đã đến giờ
        $schedules = LichBaoTriGheNgoi::where('trang_thai', 'cho_thuc_hien')
            ->where('thoi_gian_bat_dau', '<=', $now)
            ->with('gheNgoi')
            ->get();

        $this->info("Tìm thấy {$schedules->count()} lịch cần kích hoạt.");

        foreach ($schedules as $schedule) {
            $seat = $schedule->gheNgoi;
            if (!$seat) {
                $this->warn("Ghế không tồn tại cho lịch #{$schedule->id}");
                continue;
            }

            // Cập nhật ghế thành bảo trì
            $seat->update(['trang_thai' => 'bao_tri']);

            // Cập nhật lịch
            $schedule->update([
                'trang_thai' => 'dang_thuc_hien',
                'thoi_gian_bat_dau' => $now,
            ]);

            $this->info("Đã kích hoạt bảo trì ghế {$seat->ma_ghe} (ID: {$seat->id})");
        }

        // Hoàn thành bảo trì đã hết hạn
        $expired = LichBaoTriGheNgoi::where('trang_thai', 'dang_thuc_hien')
            ->whereNotNull('thoi_gian_ket_thuc')
            ->where('thoi_gian_ket_thuc', '<=', $now)
            ->with('gheNgoi')
            ->get();

        foreach ($expired as $schedule) {
            $seat = $schedule->gheNgoi;
            if (!$seat) continue;

            // Khôi phục trạng thái cũ
            $seat->update(['trang_thai' => $schedule->trang_thai_truoc ?? 'hoat_dong']);

            $schedule->update(['trang_thai' => 'da_hoan_thanh']);

            $this->info("Đã hoàn thành bảo trì ghế {$seat->ma_ghe} (ID: {$seat->id})");
        }

        $this->info('Hoàn tất.');
        return Command::SUCCESS;
    }
}
