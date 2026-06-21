<?php

namespace App\Console\Commands;

use App\Models\NguoiDung;
use App\Models\NguoiDungVoucher;
use App\Models\Voucher;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TangVoucherSinhNhat extends Command
{
    /**
     * Lệnh dùng để tự động tặng voucher sinh nhật cho khách hàng.
     */
    protected $signature = 'voucher:tang-sinh-nhat';

    protected $description = 'Tự động tặng voucher sinh nhật cho khách hàng đúng ngày sinh nhật';

    public function handle(): int
    {
        $homNay = now();
        $namHienTai = $homNay->year;

        $voucherSinhNhat = Voucher::where('loai_voucher', 'sinh_nhat')
            ->where('trang_thai', true)
            ->first();

        if (!$voucherSinhNhat) {
            $this->error('Chưa có voucher sinh nhật khả dụng.');
            return self::FAILURE;
        }

        $khachHangs = NguoiDung::where('vai_tro', 'khach_hang')
            ->whereNotNull('ngay_sinh')
            ->whereMonth('ngay_sinh', $homNay->month)
            ->whereDay('ngay_sinh', $homNay->day)
            ->get();

        foreach ($khachHangs as $khachHang) {
            $daTangNamNay = NguoiDungVoucher::where('nguoi_dung_id', $khachHang->id)
                ->where('loai_cap_phat', 'sinh_nhat')
                ->where('nam_ap_dung', $namHienTai)
                ->exists();

            if ($daTangNamNay) {
                continue;
            }

            NguoiDungVoucher::create([
                'nguoi_dung_id' => $khachHang->id,
                'voucher_id' => $voucherSinhNhat->id,
                'ma_voucher_ca_nhan' => strtoupper('BIRTHDAY-' . Str::random(6)),
                'loai_cap_phat' => 'sinh_nhat',
                'nam_ap_dung' => $namHienTai,
                'da_su_dung' => false,
                'ngay_nhan' => now(),
                'ngay_het_han' => now()->addDays(30),
            ]);

            // Nếu dự án bạn đã có bảng notifications riêng thì lát nữa mình sẽ nối vào đây.
        }

        $this->info('Đã xử lý tặng voucher sinh nhật.');
        return self::SUCCESS;
    }
}