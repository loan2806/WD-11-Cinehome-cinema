<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Voucher;

class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        $vouchers = [
            // =========================
            // VOUCHER THÔNG THƯỜNG CHO USER
            // =========================
            [
                'ma_voucher' => 'VE10K',
                'ten_voucher' => 'Giảm 10.000 VNĐ tiền vé',
                'loai_voucher' => 'giam_gia_ve',
                'kieu_giam' => 'tien',
                'gia_tri_giam' => 10000,
                'diem_can_doi' => 100,
                'ngay_het_han' => now()->addYear(),
                'trang_thai' => true,
            ],
            [
                'ma_voucher' => 'FOOD20K',
                'ten_voucher' => 'Giảm 20.000 VNĐ combo bắp nước',
                'loai_voucher' => 'giam_gia_do_an',
                'kieu_giam' => 'tien',
                'gia_tri_giam' => 20000,
                'diem_can_doi' => 150,
                'ngay_het_han' => now()->addYear(),
                'trang_thai' => true,
            ],
            [
                'ma_voucher' => 'VIP30K',
                'ten_voucher' => 'Giảm 30.000 VNĐ ghế VIP',
                'loai_voucher' => 'giam_gia_ghe_vip',
                'kieu_giam' => 'tien',
                'gia_tri_giam' => 30000,
                'diem_can_doi' => 250,
                'ngay_het_han' => now()->addYear(),
                'trang_thai' => true,
            ],
            [
                'ma_voucher' => 'BIRTHDAY50K',
                'ten_voucher' => 'Voucher sinh nhật 50.000 VNĐ',
                'loai_voucher' => 'sinh_nhat',
                'kieu_giam' => 'tien',
                'gia_tri_giam' => 50000,
                'diem_can_doi' => 0,
                'ngay_het_han' => now()->addYear(),
                'trang_thai' => true,
            ],

            // =========================
            // VOUCHER ĐẶC BIỆT CHO STAFF
            // =========================
            [
                'ma_voucher' => 'STAFF-VIP50K',
                'ten_voucher' => 'Khách VIP - Giảm 50.000 VNĐ',
                'loai_voucher' => 'staff_dac_biet',
                'kieu_giam' => 'tien',
                'gia_tri_giam' => 50000,
                'diem_can_doi' => 0,
                'ngay_het_han' => now()->addYear(),
                'trang_thai' => true,
            ],
            [
                'ma_voucher' => 'STAFF-BIRTHDAY20',
                'ten_voucher' => 'Khách sinh nhật - Giảm 20%',
                'loai_voucher' => 'staff_dac_biet',
                'kieu_giam' => 'phan_tram',
                'gia_tri_giam' => 20,
                'diem_can_doi' => 0,
                'ngay_het_han' => now()->addYear(),
                'trang_thai' => true,
            ],
            [
                'ma_voucher' => 'STAFF-LOYALTY10',
                'ten_voucher' => 'Khách hàng thân thiết - Giảm 10%',
                'loai_voucher' => 'staff_dac_biet',
                'kieu_giam' => 'phan_tram',
                'gia_tri_giam' => 10,
                'diem_can_doi' => 0,
                'ngay_het_han' => now()->addYear(),
                'trang_thai' => true,
            ],
            [
                'ma_voucher' => 'STAFF-PARTNER50K',
                'ten_voucher' => 'Khách đối tác - Giảm 50.000 VNĐ',
                'loai_voucher' => 'staff_dac_biet',
                'kieu_giam' => 'tien',
                'gia_tri_giam' => 50000,
                'diem_can_doi' => 0,
                'ngay_het_han' => now()->addYear(),
                'trang_thai' => true,
            ],
            [
                'ma_voucher' => 'STAFF-COMPENSATE100K',
                'ten_voucher' => 'Khách cần hỗ trợ đặc biệt - Giảm 100.000 VNĐ',
                'loai_voucher' => 'staff_dac_biet',
                'kieu_giam' => 'tien',
                'gia_tri_giam' => 100000,
                'diem_can_doi' => 0,
                'ngay_het_han' => now()->addYear(),
                'trang_thai' => true,
            ],
            [
                'ma_voucher' => 'STAFF-SPECIAL15',
                'ten_voucher' => 'Trường hợp đặc biệt - Giảm 15%',
                'loai_voucher' => 'staff_dac_biet',
                'kieu_giam' => 'phan_tram',
                'gia_tri_giam' => 15,
                'diem_can_doi' => 0,
                'ngay_het_han' => now()->addYear(),
                'trang_thai' => true,
            ],
        ];

        foreach ($vouchers as $voucher) {
            Voucher::updateOrCreate(
                ['ma_voucher' => $voucher['ma_voucher']],
                $voucher
            );
        }
    }
}
