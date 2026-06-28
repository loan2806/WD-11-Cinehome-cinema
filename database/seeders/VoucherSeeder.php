<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Voucher;

class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        $vouchers = [
            [
                'ma_voucher' => 'VE10K',
                'ten_voucher' => 'Giảm 10.000 VNĐ tiền vé',
                'loai_voucher' => 'giam_gia_ve',
                'gia_tri_giam' => 10000,
                'diem_can_doi' => 100,
                'ngay_het_han' => now()->addYear(),
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ma_voucher' => 'FOOD20K',
                'ten_voucher' => 'Giảm 20.000 VNĐ combo bắp nước',
                'loai_voucher' => 'giam_gia_do_an',
                'gia_tri_giam' => 20000,
                'diem_can_doi' => 150,
                'ngay_het_han' => now()->addYear(),
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ma_voucher' => 'VIP30K',
                'ten_voucher' => 'Giảm 30.000 VNĐ ghế VIP',
                'loai_voucher' => 'giam_gia_ghe_vip',
                'gia_tri_giam' => 30000,
                'diem_can_doi' => 250,
                'ngay_het_han' => now()->addYear(),
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ma_voucher' => 'BIRTHDAY50K',
                'ten_voucher' => 'Voucher sinh nhật 50.000 VNĐ',
                'loai_voucher' => 'sinh_nhat',
                'gia_tri_giam' => 50000,
                'diem_can_doi' => 0,
                'ngay_het_han' => now()->addYear(),
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($vouchers as $voucher) {
            Voucher::updateOrCreate(
                ['ma_voucher' => $voucher['ma_voucher']],
                collect($voucher)->except(['created_at'])->all()
            );
        }
    }
}
