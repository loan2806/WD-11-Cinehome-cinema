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
     * Hiển thị danh sách voucher User có thể đổi bằng điểm.
     *
     * QUAN TRỌNG:
     * User chỉ nhìn thấy:
     * - doi_tuong_su_dung = user
     * - doi_tuong_su_dung = all
     *
     * Tuyệt đối không hiển thị voucher staff.
     */
    public function index()
    {
        $vouchers = Voucher::query()
            ->forUser()
            ->where('trang_thai', true)
            ->where('loai_voucher', '!=', 'sinh_nhat')
            ->where(function ($query) {
                $query->whereNull('ngay_het_han')
                    ->orWhereDate(
                        'ngay_het_han',
                        '>=',
                        today()
                    );
            })
            ->orderBy('diem_can_doi')
            ->get();

        $thanhVien = Auth::user()->thanhVien;

        $availablePoints =
            (int) ($thanhVien?->diem_hien_tai ?? 0);

        $affordableCount = $vouchers
            ->filter(
                fn ($voucher) =>
                    $availablePoints >=
                    $voucher->diem_can_doi
            )
            ->count();

        $nextVoucher = $vouchers
            ->filter(
                fn ($voucher) =>
                    $availablePoints <
                    $voucher->diem_can_doi
            )
            ->sortBy('diem_can_doi')
            ->first();

        $pointsNeededForNext = $nextVoucher
            ? max(
                0,
                $nextVoucher->diem_can_doi -
                $availablePoints
            )
            : 0;

        return view(
            'user.voucher.index',
            compact(
                'vouchers',
                'thanhVien',
                'availablePoints',
                'affordableCount',
                'nextVoucher',
                'pointsNeededForNext'
            )
        );
    }

    /**
     * Đổi điểm lấy voucher.
     *
     * Backend kiểm tra lại đối tượng sử dụng.
     * Không thể bypass bằng cách sửa URL / POST.
     */
    public function exchange(Voucher $voucher)
    {
        $thanhVien = Auth::user()->thanhVien;

        if (!$thanhVien) {
            return back()->with(
                'error',
                'Bạn chưa có thẻ thành viên.'
            );
        }

        /**
         * Không cho User đổi voucher Staff.
         */
        if (!$voucher->isForUser()) {
            return back()->with(
                'error',
                'Voucher này không dành cho tài khoản User.'
            );
        }

        /**
         * Voucher sinh nhật không được đổi bằng điểm.
         */
        if ($voucher->loai_voucher === 'sinh_nhat') {
            return back()->with(
                'error',
                'Voucher sinh nhật chỉ được hệ thống tự động tặng vào đúng ngày sinh nhật.'
            );
        }

        if (!$voucher->trang_thai) {
            return back()->with(
                'error',
                'Voucher này đã tắt.'
            );
        }

        if (
            $voucher->ngay_het_han &&
            $voucher->ngay_het_han->lt(today())
        ) {
            return back()->with(
                'error',
                'Voucher này đã hết hạn.'
            );
        }

        if (
            $thanhVien->diem_hien_tai <
            $voucher->diem_can_doi
        ) {
            return back()->with(
                'error',
                'Điểm hiện tại không đủ để đổi voucher.'
            );
        }

        DB::transaction(function () use (
            $thanhVien,
            $voucher
        ) {
            $thanhVien->truDiem(
                $voucher->diem_can_doi,
                null,
                'Đổi voucher ' . $voucher->ten_voucher
            );

            do {
                $maVoucherCaNhan = Str::upper(
                    $voucher->ma_voucher .
                    '-' .
                    Str::random(6)
                );
            } while (
                NguoiDungVoucher::where(
                    'ma_voucher_ca_nhan',
                    $maVoucherCaNhan
                )->exists()
            );

            NguoiDungVoucher::create([
                'nguoi_dung_id' => Auth::id(),
                'voucher_id' => $voucher->id,
                'ma_voucher_ca_nhan' =>
                    $maVoucherCaNhan,
                'loai_cap_phat' => 'doi_diem',
                'nam_ap_dung' => now()->year,
                'da_su_dung' => false,
                'ngay_nhan' => now(),
                'ngay_het_han' =>
                    now()->addDays(30),
            ]);
        });

        return back()->with(
            'success',
            'Đổi voucher thành công.'
        );
    }

    /**
     * Hiển thị voucher cá nhân của User.
     */
    public function myVoucher(Request $request)
    {
        $this->capVoucherSinhNhat();

        $allowedStatuses = [
            'kha_dung',
            'da_su_dung',
            'het_han'
        ];

        $activeStatus =
            in_array(
                $request->query('trang_thai'),
                $allowedStatuses,
                true
            )
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
                    $query
                        ->whereNull('ngay_het_han')
                        ->orWhere(
                            'ngay_het_han',
                            '>=',
                            now()
                        );
                })
                ->count(),

            'used' => (clone $baseQuery)
                ->where('da_su_dung', true)
                ->count(),

            'expired' => (clone $baseQuery)
                ->where('da_su_dung', false)
                ->whereNotNull('ngay_het_han')
                ->where(
                    'ngay_het_han',
                    '<',
                    now()
                )
                ->count(),
        ];

        $expiringVoucher = (clone $baseQuery)
            ->where('da_su_dung', false)
            ->whereNotNull('ngay_het_han')
            ->where(
                'ngay_het_han',
                '>=',
                now()
            )
            ->orderBy('ngay_het_han')
            ->first();

        $vouchers = (clone $baseQuery)
            ->when(
                $activeStatus === 'kha_dung',
                function ($query) {
                    $query
                        ->where('da_su_dung', false)
                        ->where(function ($innerQuery) {
                            $innerQuery
                                ->whereNull('ngay_het_han')
                                ->orWhere(
                                    'ngay_het_han',
                                    '>=',
                                    now()
                                );
                        });
                }
            )
            ->when(
                $activeStatus === 'da_su_dung',
                fn ($query) =>
                    $query->where(
                        'da_su_dung',
                        true
                    )
            )
            ->when(
                $activeStatus === 'het_han',
                function ($query) {
                    $query
                        ->where(
                            'da_su_dung',
                            false
                        )
                        ->whereNotNull(
                            'ngay_het_han'
                        )
                        ->where(
                            'ngay_het_han',
                            '<',
                            now()
                        );
                }
            )
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view(
            'user.voucher.my-voucher',
            compact(
                'vouchers',
                'voucherStats',
                'activeStatus',
                'expiringVoucher'
            )
        );
    }

    /**
     * Lưu voucher tạm khi User nhấn "Sử dụng ngay".
     *
     * Đây là một lớp bảo vệ backend quan trọng.
     */
    public function saveTam(Request $request): JsonResponse
    {
        $voucherId = $request->voucher_id;

        if (!$voucherId) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher không hợp lệ.'
            ], 422);
        }

        /**
         * Chỉ tìm voucher dành cho User.
         *
         * Không dùng Voucher::find() vì User có thể gửi
         * ID của voucher Staff bằng DevTools.
         */
        $voucher = Voucher::query()
            ->forUser()
            ->whereKey($voucherId)
            ->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Voucher không tồn tại hoặc không dành cho User.'
            ], 422);
        }

        if (!$voucher->trang_thai) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher đã bị tắt.'
            ], 422);
        }

        if (
            $voucher->ngay_het_han &&
            $voucher->ngay_het_han->lt(now())
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher đã hết hạn.'
            ], 422);
        }

        session([
            'voucher_tam' => $voucher->id
        ]);

        return response()->json([
            'success' => true,
            'message' =>
                'Đã lưu voucher, đang chuyển đến trang đặt vé...'
        ]);
    }

    /**
     * Xóa voucher tạm khỏi session.
     */
    public function xoaVoucherTam(): JsonResponse
    {
        session()->forget('voucher_tam');

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Tự động cấp voucher sinh nhật.
     *
     * Giữ nguyên logic hệ thống hiện tại.
     */
    private function capVoucherSinhNhat(): void
    {
        $nguoiDung = Auth::user();

        if (
            !$nguoiDung ||
            !$nguoiDung->ngay_sinh ||
            !$nguoiDung->trang_thai_hoat_dong
        ) {
            return;
        }

        $ngaySinh =
            Carbon::parse(
                $nguoiDung->ngay_sinh
            );

        if (
            $ngaySinh->day !== now()->day ||
            $ngaySinh->month !== now()->month
        ) {
            return;
        }

        $voucherSinhNhat =
            Voucher::query()
                ->where(
                    'loai_voucher',
                    'sinh_nhat'
                )
                ->forUser()
                ->where(
                    'trang_thai',
                    true
                )
                ->whereDate(
                    'ngay_het_han',
                    '>=',
                    today()
                )
                ->first();

        if (!$voucherSinhNhat) {
            return;
        }

        $daNhanTrongNam =
            NguoiDungVoucher::query()
                ->where(
                    'nguoi_dung_id',
                    $nguoiDung->id
                )
                ->where(
                    'nam_ap_dung',
                    now()->year
                )
                ->whereHas(
                    'voucher',
                    function ($query) {
                        $query
                            ->where(
                                'loai_voucher',
                                'sinh_nhat'
                            )
                            ->forUser();
                    }
                )
                ->exists();

        if ($daNhanTrongNam) {
            return;
        }

        DB::transaction(
            function () use (
                $nguoiDung,
                $voucherSinhNhat
            ) {
                do {
                    $maVoucherCaNhan =
                        Str::upper(
                            $voucherSinhNhat
                                ->ma_voucher .
                            '-' .
                            Str::random(8)
                        );
                } while (
                    NguoiDungVoucher::where(
                        'ma_voucher_ca_nhan',
                        $maVoucherCaNhan
                    )->exists()
                );

                $hanSuDung =
                    now()
                        ->copy()
                        ->addDays(7)
                        ->endOfDay();

                $hanVoucherMau =
                    Carbon::parse(
                        $voucherSinhNhat
                            ->ngay_het_han
                    )->endOfDay();

                if (
                    $hanSuDung->gt(
                        $hanVoucherMau
                    )
                ) {
                    $hanSuDung =
                        $hanVoucherMau;
                }

                NguoiDungVoucher::create([
                    'nguoi_dung_id' =>
                        $nguoiDung->id,

                    'voucher_id' =>
                        $voucherSinhNhat->id,

                    'ma_voucher_ca_nhan' =>
                        $maVoucherCaNhan,

                    'loai_cap_phat' =>
                        'sinh_nhat',

                    'nam_ap_dung' =>
                        now()->year,

                    'da_su_dung' =>
                        false,

                    'ngay_nhan' =>
                        now(),

                    'ngay_het_han' =>
                        $hanSuDung,
                ]);
            }
        );
    }
}