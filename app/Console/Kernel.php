<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\XoaNhatKyCuCommand;
use App\Console\Commands\TangVoucherSinhNhat;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Xóa nhật ký cũ lúc 2h sáng
        $schedule->command(XoaNhatKyCuCommand::class)
            ->dailyAt('02:00');

        // Kiểm tra sinh nhật khách hàng và tặng voucher lúc 00:01 mỗi ngày
        $schedule->command(TangVoucherSinhNhat::class)
            ->dailyAt('00:01');
    }
}