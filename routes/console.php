<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\NhatKyHoatDongHeThong;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('nhatky:clear', function () {
    $deleted = NhatKyHoatDongHeThong::where('created_at', '<', now()->subDays(30))->delete();
    $this->info("Đã xóa {$deleted} bản ghi nhật ký cũ.");
})->purpose('Xóa các bản ghi nhật ký hoạt động cũ hơn 30 ngày');

Schedule::command('voucher:tang-sinh-nhat')
    ->dailyAt('00:01');

Schedule::command('nhat-ky:xoa-cu')
    ->dailyAt('02:00');