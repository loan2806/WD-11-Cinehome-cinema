<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\NguoiDungVoucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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

        $availablePoints = (int) ($thanhVien?->diem_hien_tai ?? 0);
        $affordableCount = $vouchers
            ->filter(fn($voucher) => $availablePoints >= $voucher->diem_can_doi)
            ->count();
        $nextVoucher = $vouchers
            ->filter(fn($voucher) => $availablePoints < $voucher->diem_can_doi)
            ->sortBy('diem_can_doi')
            ->first();
        $pointsNeededForNext = $nextVoucher
            ? max(0, $nextVoucher->diem_can_doi - $availablePoints)
            : 0;

        return view('user.voucher.index', compact(
            'vouchers',
            'thanhVien',
            'availablePoints',
            'affordableCount',
            'nextVoucher',
            'pointsNeededForNext'
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
    public function myVoucher(Request $request)
    {
        $this->capVoucherSinhNhat();
        $allowedStatuses = ['kha_dung', 'da_su_dung', 'het_han'];
        $activeStatus = in_array($request->query('trang_thai'), $allowedStatuses, true)
            ? $request->query('trang_thai')
            : null;

        $baseQuery = Auth::user()
            ->vouchersCaNhan()
            ->with('voucher');

        $voucherStats = [
            'total' => (clone $baseQuery)->count(),
            'available' => (clone $baseQuery)
                ->where('da_su_dung', false)
                ->where(function ($query) {
                    $query->whereNull('ngay_het_han')
                        ->orWhere('ngay_het_han', '>=', now());
                })
                ->count(),
            'used' => (clone $baseQuery)->where('da_su_dung', true)->count(),
            'expired' => (clone $baseQuery)
                ->where('da_su_dung', false)
                ->whereNotNull('ngay_het_han')
                ->where('ngay_het_han', '<', now())
                ->count(),
        ];

        $expiringVoucher = (clone $baseQuery)
            ->where('da_su_dung', false)
            ->whereNotNull('ngay_het_han')
            ->where('ngay_het_han', '>=', now())
            ->orderBy('ngay_het_han')
            ->first();

        $vouchers = (clone $baseQuery)
            ->when($activeStatus === 'kha_dung', function ($query) {
                $query->where('da_su_dung', false)
                    ->where(function ($innerQuery) {
                        $innerQuery->whereNull('ngay_het_han')
                            ->orWhere('ngay_het_han', '>=', now());
                    });
            })
            ->when($activeStatus === 'da_su_dung', fn($query) => $query->where('da_su_dung', true))
            ->when($activeStatus === 'het_han', function ($query) {
                $query->where('da_su_dung', false)
                    ->whereNotNull('ngay_het_han')
                    ->where('ngay_het_han', '<', now());
            })
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view(
            'user.voucher.my-voucher',
            compact('vouchers', 'voucherStats', 'activeStatus', 'expiringVoucher')
        );
    }

    /**
     * Lưu voucher tạm vào session khi nhấn "Sử dụng ngay"
     */
    public function saveTam(Request $request): JsonResponse
    {
        $voucherId = $request->voucher_id;

        if (!$voucherId) {
            return response()->json(['success' => false, 'message' => 'Voucher không hợp lệ']);
        }

        $voucher = Voucher::find($voucherId);

        if (!$voucher) {
            return response()->json(['success' => false, 'message' => 'Voucher không tồn tại']);
        }

        if (!$voucher->trang_thai) {
            return response()->json(['success' => false, 'message' => 'Voucher đã bị tắt']);
        }

        if ($voucher->ngay_het_han && $voucher->ngay_het_han->lt(now())) {
            return response()->json(['success' => false, 'message' => 'Voucher đã hết hạn']);
        }

        // Lưu voucher vào session
        session(['voucher_tam' => $voucherId]);

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu voucher, đang chuyển đến trang đặt vé...'
        ]);
    }

    /**
     * Xóa voucher tạm khỏi session
     */
    public function xoaVoucherTam(): JsonResponse
    {
        session()->forget('voucher_tam');

        return response()->json(['success' => true]);
    }

    private function capVoucherSinhNhat(): void
    {
        $nguoiDung = Auth::user();

        if (
            !$nguoiDung
            || !$nguoiDung->ngay_sinh
            || !$nguoiDung->trang_thai_hoat_dong
        ) {
            return;
        }

        $ngaySinh = Carbon::parse($nguoiDung->ngay_sinh);

        if (
            $ngaySinh->day !== now()->day
            || $ngaySinh->month !== now()->month
        ) {
            return;
        }

        $voucherSinhNhat = Voucher::query()
            ->where('loai_voucher', 'sinh_nhat')
            ->where('trang_thai', true)
            ->whereDate('ngay_het_han', '>=', today())
            ->first();

        if (!$voucherSinhNhat) {
            return;
        }

        $daNhanTrongNam = NguoiDungVoucher::query()
            ->where('nguoi_dung_id', $nguoiDung->id)
            ->where('nam_ap_dung', now()->year)
            ->whereHas('voucher', function ($query) {
                $query->where('loai_voucher', 'sinh_nhat');
            })
            ->exists();

        if ($daNhanTrongNam) {
            return;
        }

        DB::transaction(function () use ($nguoiDung, $voucherSinhNhat) {
            do {
                $maVoucherCaNhan = Str::upper(
                    $voucherSinhNhat->ma_voucher . '-' . Str::random(8)
                );
            } while (
                NguoiDungVoucher::where(
                    'ma_voucher_ca_nhan',
                    $maVoucherCaNhan
                )->exists()
            );

            $hanSuDung = now()->copy()->addDays(7)->endOfDay();
            $hanVoucherMau = Carbon::parse(
                $voucherSinhNhat->ngay_het_han
            )->endOfDay();

            NguoiDungVoucher::create([
                'nguoi_dung_id' => $nguoiDung->id,
                'voucher_id' => $voucherSinhNhat->id,
                'ma_voucher_ca_nhan' => $maVoucherCaNhan,
                'da_su_dung' => false,
                'ngay_nhan' => now(),
                'ngay_su_dung' => null,
                'loai_cap_phat' => 'sinh_nhat',
                'nam_ap_dung' => now()->year,
                'ngay_het_han' => $hanSuDung->lt($hanVoucherMau)
                    ? $hanSuDung
                    : $hanVoucherMau,
            ]);
        });
    }
}
