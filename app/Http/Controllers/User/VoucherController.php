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
     * Hiển thị danh sách voucher có thể đổi bằng điểm.
     *
     * Lưu ý:
     * - Không hiển thị voucher sinh nhật.
     * - Voucher sinh nhật chỉ được hệ thống tự động cấp
     *   khi đến đúng ngày sinh nhật của khách hàng.
     */
    public function index()
    {
        $vouchers = Voucher::where('trang_thai', true)
            ->where('loai_voucher', '!=', 'sinh_nhat')
            ->whereDate('ngay_het_han', '>=', today())
            ->orderBy('diem_can_doi')
            ->get();

        $thanhVien = Auth::user()->thanhVien;

        return view('user.voucher.index', compact(
            'vouchers',
            'thanhVien'
        ));
    }

    /**
     * Đổi điểm lấy voucher.
     *
     * Chỉ cho phép đổi các voucher thông thường.
     * Không cho đổi voucher sinh nhật bằng điểm.
     */
    public function exchange(Voucher $voucher)
    {
        $thanhVien = Auth::user()->thanhVien;

        if (!$thanhVien) {
            return back()->with('error', 'Bạn chưa có thẻ thành viên.');
        }

        /**
         * Chặn tuyệt đối voucher sinh nhật.
         * Tránh trường hợp user tự submit form hoặc sửa URL để đổi voucher sinh nhật.
         */
        if ($voucher->loai_voucher === 'sinh_nhat') {
            return back()->with('error', 'Voucher sinh nhật chỉ được hệ thống tự động tặng vào đúng ngày sinh nhật.');
        }

        if (! $voucher->trang_thai || $voucher->ngay_het_han->lt(today())) {
            return back()->with('error', 'Voucher này đã tắt hoặc hết hạn.');
        }

        if ($thanhVien->diem_hien_tai < $voucher->diem_can_doi) {
            return back()->with('error', 'Điểm hiện tại không đủ để đổi voucher.');
        }

        // Trừ điểm hiện tại khi đổi voucher.
        // Không trừ tổng điểm tích lũy để không làm tụt hạng thành viên.
        $thanhVien->truDiem(
            $voucher->diem_can_doi,
            null,
            'Đổi voucher ' . $voucher->ten_voucher
        );

        // Tạo voucher cá nhân sau khi đổi điểm thành công.
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
     * Hiển thị danh sách voucher cá nhân của khách hàng.
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
