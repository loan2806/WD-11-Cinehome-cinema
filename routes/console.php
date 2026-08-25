<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\NhatKyHoatDongHeThong;
use App\Models\LichSuDiem;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Xóa nhật ký hoạt động cũ
|--------------------------------------------------------------------------
*/

Artisan::command('nhatky:clear', function () {
    $deleted = NhatKyHoatDongHeThong::where(
        'created_at',
        '<',
        now()->subDays(30)
    )->delete();

    $this->info("Đã xóa {$deleted} bản ghi nhật ký cũ.");
})->purpose('Xóa các bản ghi nhật ký hoạt động cũ hơn 30 ngày');

/*
|--------------------------------------------------------------------------
| Tự động xử lý điểm hết hạn
|--------------------------------------------------------------------------
*/

Artisan::command('diem:het-han', function () {
    $cacKhoanDiem = LichSuDiem::with('thanhVien')
        ->where('loai_giao_dich', 'cong_diem')
        ->where('diem_con_lai', '>', 0)
        ->whereNotNull('ngay_het_han')
        ->where('ngay_het_han', '<=', now())
        ->get();

    $tongDiemHetHan = 0;
    $soKhoanDiem = 0;

    foreach ($cacKhoanDiem as $lichSuDiem) {
        $thanhVien = $lichSuDiem->thanhVien;

        if (!$thanhVien) {
            continue;
        }

        $soDiemHetHan = (int) $lichSuDiem->diem_con_lai;

        if ($soDiemHetHan <= 0) {
            continue;
        }

        $soDiemTru = min(
            $soDiemHetHan,
            (int) $thanhVien->diem_hien_tai
        );

        if ($soDiemTru > 0) {
            $thanhVien->decrement('diem_hien_tai', $soDiemTru);
            $tongDiemHetHan += $soDiemTru;
        }

        $lichSuDiem->update([
            'diem_con_lai' => 0,
        ]);

        $thanhVien->refresh();
        $thanhVien->capNhatHangThanhVien();

        $soKhoanDiem++;
    }

    $this->info(
        "Đã xử lý {$soKhoanDiem} khoản điểm hết hạn, tổng cộng {$tongDiemHetHan} điểm."
    );
})->purpose('Tự động xử lý các khoản điểm thành viên đã hết hạn');

/*
|--------------------------------------------------------------------------
| Tự động xóa vĩnh viễn tài khoản trong thùng rác sau 14 ngày
|--------------------------------------------------------------------------
*/

Artisan::command('user:clean-trashed', function () {
    $userModel = config('auth.providers.users.model', \App\Models\User::class);
    
    $deletedCount = $userModel::onlyTrashed()
        ->where('deleted_at', '<=', now()->subDays(14))
        ->forceDelete();

    $this->info("Đã xóa vĩnh viễn {$deletedCount} tài khoản quá hạn 14 ngày chờ.");
})->purpose('Xóa vĩnh viễn các tài khoản đã bị soft delete quá 14 ngày');

/*
|--------------------------------------------------------------------------
| Lịch chạy tự động
|--------------------------------------------------------------------------
*/

Schedule::command('voucher:tang-sinh-nhat')
    ->dailyAt('00:01');

Schedule::command('diem:het-han')
    ->dailyAt('00:05');

Schedule::command('nhatky:clear')
    ->dailyAt('02:00');

Schedule::command('user:clean-trashed')
    ->dailyAt('03:00');

/*
|--------------------------------------------------------------------------
| Tự động hết hạn VietQR tại quầy
|--------------------------------------------------------------------------
| Command này đã có trong App\Console\Commands\HetHanVietQrStaff:
| staff:vietqr-het-han
*/
Schedule::command('staff:vietqr-het-han')
    ->everyMinute()
    ->withoutOverlapping();