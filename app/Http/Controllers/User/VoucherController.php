<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\NguoiDungVoucher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VoucherController extends Controller
{
    /**
     * Hiển thị danh sách voucher có thể đổi.
     */
    public function index()
    {
        $vouchers = Voucher::where('trang_thai', true)->get();

        $thanhVien = Auth::user()->thanhVien;

        return view('user.voucher.index', compact(
            'vouchers',
            'thanhVien'
        ));
    }

    /**
     * Đổi điểm lấy voucher.
     */
    public function exchange(Voucher $voucher)
    {
        $thanhVien = Auth::user()->thanhVien;

        if (!$thanhVien) {
            return back()->with('error', 'Bạn chưa có thẻ thành viên.');
        }

        if ($thanhVien->diem_hien_tai < $voucher->diem_can_doi) {
            return back()->with('error', 'Điểm hiện tại không đủ để đổi voucher.');
        }

        // Trừ điểm
        $thanhVien->truDiem(
            $voucher->diem_can_doi,
            null,
            'Đổi voucher ' . $voucher->ten_voucher
        );

        // Tạo voucher cá nhân
        NguoiDungVoucher::create([
            'nguoi_dung_id' => Auth::id(),
            'voucher_id' => $voucher->id,
            'ma_voucher_ca_nhan' => strtoupper($voucher->ma_voucher . '-' . Str::random(6)),
            'loai_cap_phat' => 'doi_diem',
            'nam_ap_dung' => now()->year,
            'da_su_dung' => false,
            'ngay_nhan' => now(),
            'ngay_het_han' => now()->addDays(30),
        ]);

        return back()->with(
            'success',
            'Đổi voucher thành công.'
        );
    }

    /**
     * Voucher của tôi.
     */
    public function myVoucher()
    {
        $vouchers = Auth::user()
            ->vouchersCaNhan()
            ->with('voucher')
            ->latest()
            ->paginate(10);

        return view(
            'user.voucher.my-voucher',
            compact('vouchers')
        );
    }
}
