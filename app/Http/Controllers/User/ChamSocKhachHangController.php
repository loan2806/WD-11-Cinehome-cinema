<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\NguoiDungVoucher;
use App\Models\Voucher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ChamSocKhachHangController extends Controller
{
    /**
     * Nhận voucher sinh nhật.
     * Mỗi tài khoản chỉ được nhận 1 voucher sinh nhật trong 1 năm.
     */
    public function nhanVoucherSinhNhat()
    {
        $nguoiDung = Auth::user();

        if (!$nguoiDung->ngay_sinh) {
            return back()->with('error', 'Bạn cần cập nhật ngày sinh trước khi nhận voucher sinh nhật.');
        }

        $homNay = now();

        if (
            $nguoiDung->ngay_sinh->format('m-d') !== $homNay->format('m-d')
        ) {
            return back()->with('error', 'Hôm nay chưa phải ngày sinh nhật của bạn.');
        }

        $daNhanNamNay = NguoiDungVoucher::where('nguoi_dung_id', $nguoiDung->id)
            ->where('loai_cap_phat', 'sinh_nhat')
            ->where('nam_ap_dung', $homNay->year)
            ->exists();

        if ($daNhanNamNay) {
            return back()->with('error', 'Bạn đã nhận voucher sinh nhật trong năm nay.');
        }

        $voucherSinhNhat = Voucher::where('loai_voucher', 'sinh_nhat')
            ->where('trang_thai', true)
            ->first();

        if (!$voucherSinhNhat) {
            return back()->with('error', 'Hiện chưa có voucher sinh nhật khả dụng.');
        }

        NguoiDungVoucher::create([
            'nguoi_dung_id' => $nguoiDung->id,
            'voucher_id' => $voucherSinhNhat->id,
            'ma_voucher_ca_nhan' => strtoupper('BIRTHDAY-' . Str::random(6)),
            'loai_cap_phat' => 'sinh_nhat',
            'nam_ap_dung' => $homNay->year,
            'da_su_dung' => false,
            'ngay_nhan' => now(),
            'ngay_het_han' => now()->addDays(30),
        ]);

        return back()->with('success', 'Chúc mừng sinh nhật! Bạn đã nhận voucher 50.000đ.');
    }
}