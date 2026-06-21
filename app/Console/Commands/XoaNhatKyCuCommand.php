<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NhatKyHoatDongHeThong;

class XoaNhatKyCuCommand extends Command
{
    protected $signature = 'nhatky:clear';
    protected $description = 'Xóa các bản ghi nhật ký hoạt động cũ hơn 30 ngày';

    public function handle(): void
    {
        $deleted = NhatKyHoatDongHeThong::where('created_at', '<', now()->subDays(30))->delete();

        $this->info("Đã xóa {$deleted} bản ghi nhật ký cũ.");
    }
}
