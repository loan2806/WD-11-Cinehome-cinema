<?php

namespace App\Console\Commands;

use App\Models\NguoiDung;
use App\Models\NguoiDungVoucher;
use App\Models\Voucher;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TangVoucherSinhNhat extends Command
{
    protected $signature = 'voucher:tang-sinh-nhat';

    protected $description = 'Tự động tặng voucher sinh nhật theo hạng thành viên';

    public function handle(): int
    {
        $homNay = now();
        $namHienTai = $homNay->year;

        $khachHangs = NguoiDung::with('thanhVien')
            ->where('vai_tro', 'khach_hang')
            ->where('trang_thai_hoat_dong', true)
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

            $hang = $khachHang->thanhVien->hang_thanh_vien ?? 'member';

            $giaTriGiam = match ($hang) {
                'silver' => 100000,
                'gold' => 150000,
                'platinum' => 200000,
                default => 50000,
            };

            $voucherSinhNhat = Voucher::firstOrCreate(
                [
                    'ma_voucher' => 'BIRTHDAY-' . strtoupper($hang),
                ],
                [
                    'ten_voucher' => 'Voucher sinh nhật hạng ' . strtoupper($hang),
                    'gia_tri_giam' => $giaTriGiam,
                    'diem_can_doi' => 0,
                    'ngay_het_han' => now()->addYears(10),
                    'trang_thai' => true,
                    'loai_voucher' => 'sinh_nhat',
                ]
            );

            NguoiDungVoucher::create([
                'nguoi_dung_id' => $khachHang->id,
                'voucher_id' => $voucherSinhNhat->id,
                'ma_voucher_ca_nhan' => strtoupper('BIRTHDAY-' . $hang . '-' . Str::random(6)),
                'loai_cap_phat' => 'sinh_nhat',
                'nam_ap_dung' => $namHienTai,
                'da_su_dung' => false,
                'ngay_nhan' => now(),
                'ngay_het_han' => now()->addDays(30),
            ]);
        }

        $this->info('Đã xử lý tặng voucher sinh nhật theo hạng thành viên.');
        return self::SUCCESS;
    }
}