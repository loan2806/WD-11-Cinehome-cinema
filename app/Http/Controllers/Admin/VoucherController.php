<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\VoucherDuocTangMail;
use App\Models\NguoiDung;
use App\Models\NguoiDungVoucher;
use App\Models\ThongBaoCaNhan;
use App\Models\Voucher;
use App\Traits\Loggable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VoucherController extends Controller
{
    use Loggable;

    private const VOUCHER_TYPES = [
        'giam_gia_ve' => 'Giảm giá vé',
        'giam_gia_do_an' => 'Giảm đồ ăn',
        'giam_gia_ghe_vip' => 'Giảm ghế VIP',
        'sinh_nhat' => 'Sinh nhật',
        'khach_hang_than_thiet' => 'Khách hàng thân thiết',
    ];

    public function index(Request $request)
    {
        $query = Voucher::withCount([
            'nguoiDungVouchers',
            'nguoiDungVouchers as used_count' => fn ($q) => $q->where('da_su_dung', true),
        ]);

        if ($request->filled('q')) {
            $keyword = trim($request->q);

            $query->where(function ($q) use ($keyword) {
                $q->where('ma_voucher', 'like', "%{$keyword}%")
                    ->orWhere('ten_voucher', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('loai_voucher')) {
            $query->where('loai_voucher', $request->loai_voucher);
        }

        if ($request->filled('trang_thai')) {
            match ($request->trang_thai) {
                'active' => $query->where('trang_thai', true)->whereDate('ngay_het_han', '>=', today()),
                'inactive' => $query->where('trang_thai', false),
                'expired' => $query->whereDate('ngay_het_han', '<', today()),
                default => null,
            };
        }

        $vouchers = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $issuedQuery = NguoiDungVoucher::with(['voucher', 'nguoiDung'])
            ->latest();

        if ($request->filled('issued_q')) {
            $keyword = trim($request->issued_q);

            $issuedQuery->where(function ($q) use ($keyword) {
                $q->where('ma_voucher_ca_nhan', 'like', "%{$keyword}%")
                    ->orWhereHas('voucher', fn ($voucherQuery) => $voucherQuery
                        ->where('ma_voucher', 'like', "%{$keyword}%")
                        ->orWhere('ten_voucher', 'like', "%{$keyword}%"))
                    ->orWhereHas('nguoiDung', fn ($userQuery) => $userQuery
                        ->where('ho_ten', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%")
                        ->orWhere('so_dien_thoai', 'like', "%{$keyword}%"));
            });
        }

        if ($request->filled('issued_status')) {
            match ($request->issued_status) {
                'used' => $issuedQuery->where('da_su_dung', true),
                'unused' => $issuedQuery->where('da_su_dung', false)
                    ->where(function ($q) {
                        $q->whereNull('ngay_het_han')->orWhere('ngay_het_han', '>=', now());
                    }),
                'expired' => $issuedQuery->where('da_su_dung', false)
                    ->whereNotNull('ngay_het_han')
                    ->where('ngay_het_han', '<', now()),
                default => null,
            };
        }

        $issuedVouchers = $issuedQuery
            ->paginate(8, ['*'], 'issued_page')
            ->withQueryString();

        $customers = NguoiDung::where('vai_tro', 'khach_hang')
            ->where('trang_thai_hoat_dong', true)
            ->orderBy('ho_ten')
            ->limit(200)
            ->get(['id', 'ho_ten', 'email', 'so_dien_thoai']);

        $activeVouchers = Voucher::where('trang_thai', true)
            ->whereDate('ngay_het_han', '>=', today())
            ->orderBy('ten_voucher')
            ->get();

        $summary = [
            'total' => Voucher::count(),
            'active' => Voucher::where('trang_thai', true)
                ->whereDate('ngay_het_han', '>=', today())
                ->count(),
            'expired' => Voucher::whereDate('ngay_het_han', '<', today())->count(),
            'issued' => NguoiDungVoucher::count(),
            'used' => NguoiDungVoucher::where('da_su_dung', true)->count(),
        ];

        return view('admin.vouchers.index', [
            'vouchers' => $vouchers,
            'issuedVouchers' => $issuedVouchers,
            'customers' => $customers,
            'activeVouchers' => $activeVouchers,
            'summary' => $summary,
            'voucherTypeLabels' => self::VOUCHER_TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $voucher = Voucher::create($data);

        $this->ghiNhatKy(
            $request,
            'Tạo voucher',
            'Khuyến mãi & Voucher',
            "Tạo voucher {$voucher->ma_voucher}"
        );

        return redirect()
            ->route('admin.vouchers.index')
            ->with('success', 'Đã tạo voucher khuyến mãi.');
    }

    public function update(Request $request, Voucher $voucher)
    {
        $data = $this->validatedData($request, $voucher);

        $voucher->update($data);

        $this->ghiNhatKy(
            $request,
            'Cập nhật voucher',
            'Khuyến mãi & Voucher',
            "Cập nhật voucher {$voucher->ma_voucher}"
        );

        return redirect()
            ->route('admin.vouchers.index', $request->query())
            ->with('success', 'Đã cập nhật voucher.');
    }

    public function toggleStatus(Request $request, Voucher $voucher)
    {
        $voucher->update([
            'trang_thai' => ! $voucher->trang_thai,
        ]);

        $this->ghiNhatKy(
            $request,
            'Bật/tắt voucher',
            'Khuyến mãi & Voucher',
            ($voucher->trang_thai ? 'Bật ' : 'Tắt ') . $voucher->ma_voucher
        );

        return back()->with(
            'success',
            $voucher->trang_thai ? 'Đã bật voucher.' : 'Đã tắt voucher.'
        );
    }

    /**
     * Admin cấp voucher trực tiếp cho khách:
     * - Tạo voucher cá nhân
     * - Tạo thông báo trên chuông User
     * - Gửi Gmail thông báo cho khách
     */
    public function issue(Request $request)
    {
        $data = $request->validate([
            'voucher_id' => ['required', 'exists:vouchers,id'],
            'nguoi_dung_id' => [
                'required',
                Rule::exists('nguoi_dungs', 'id')->where(fn ($q) => $q
                    ->where('vai_tro', 'khach_hang')
                    ->where('trang_thai_hoat_dong', true)),
            ],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'loai_cap_phat' => ['required', 'in:admin_tang,khach_hang_than_thiet,khac'],
            'ly_do_khac' => ['nullable', 'required_if:loai_cap_phat,khac', 'string', 'max:255'],
            'ngay_het_han' => ['nullable', 'date', 'after_or_equal:today'],
        ], [
            'ly_do_khac.required_if' => 'Vui lòng nhập lý do cấp.',
        ]);

        $voucher = Voucher::findOrFail($data['voucher_id']);

        if (! $voucher->trang_thai || $voucher->ngay_het_han->lt(today())) {
            throw ValidationException::withMessages([
                'voucher_id' => 'Voucher mẫu đang tắt hoặc đã hết hạn, không thể cấp cho khách.',
            ]);
        }

        $customer = NguoiDung::where('vai_tro', 'khach_hang')
            ->where('trang_thai_hoat_dong', true)
            ->findOrFail($data['nguoi_dung_id']);

        $expiresAt = filled($data['ngay_het_han'] ?? null)
            ? Carbon::parse($data['ngay_het_han'])->endOfDay()
            : $voucher->ngay_het_han->copy()->endOfDay();

        if ($expiresAt->gt($voucher->ngay_het_han->copy()->endOfDay())) {
            throw ValidationException::withMessages([
                'ngay_het_han' => 'Hạn dùng cấp cho khách không được vượt quá hạn của voucher mẫu.',
            ]);
        }

        $issuedVouchers = DB::transaction(function () use ($voucher, $customer, $data, $expiresAt) {
            $issued = collect();

            for ($i = 0; $i < (int) $data['quantity']; $i++) {
                $issued->push(
                    NguoiDungVoucher::create([
                        'nguoi_dung_id' => $customer->id,
                        'voucher_id' => $voucher->id,
                        'ma_voucher_ca_nhan' => $this->personalCode($voucher),
                        'loai_cap_phat' => $data['loai_cap_phat'],
                        'ly_do_khac' => $data['loai_cap_phat'] === 'khac'
                            ? ($data['ly_do_khac'] ?? null)
                            : null,
                        'nam_ap_dung' => now()->year,
                        'da_su_dung' => false,
                        'ngay_nhan' => now(),
                        'ngay_het_han' => $expiresAt,
                    ])
                );
            }

            ThongBaoCaNhan::create([
                'nguoi_dung_id' => $customer->id,
                'tieu_de' => 'Bạn vừa nhận được voucher ưu đãi',
                'noi_dung' => sprintf(
                    'CineHome vừa tặng bạn %d voucher "%s" trị giá giảm %sđ. Hạn dùng đến %s.',
                    $issued->count(),
                    $voucher->ten_voucher,
                    number_format((float) $voucher->gia_tri_giam, 0, ',', '.'),
                    $expiresAt->format('d/m/Y')
                ),
                'loai_thong_bao' => 'voucher',
                'duong_dan' => route('user.voucher.my'),
                'da_doc' => false,
                'doc_luc' => null,
            ]);

            return $issued;
        });

        $lyDoNhan = match ($data['loai_cap_phat']) {
            'admin_tang' => 'Admin tặng voucher ưu đãi.',
            'khach_hang_than_thiet' => 'Ưu đãi dành cho khách hàng thân thiết.',
            'khac' => trim((string) ($data['ly_do_khac'] ?? '')),
            default => 'Voucher ưu đãi từ CineHome.',
        };

        $lyDoText = $data['loai_cap_phat'] === 'khac'
            ? " — Lý do: {$data['ly_do_khac']}"
            : '';

        $this->ghiNhatKy(
            $request,
            'Cấp voucher cho khách',
            'Khuyến mãi & Voucher',
            "Cấp {$data['quantity']} voucher {$voucher->ma_voucher} cho {$customer->ho_ten}{$lyDoText}"
        );

        try {
            Mail::to($customer->email)
                ->send(new VoucherDuocTangMail(
                    $customer,
                    $voucher,
                    $issuedVouchers,
                    $lyDoNhan
                ));
        } catch (\Throwable $e) {
            Log::error('Gửi email tặng voucher thất bại', [
                'nguoi_dung_id' => $customer->id,
                'email' => $customer->email,
                'voucher_id' => $voucher->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->with('success', 'Đã cấp voucher và tạo thông báo cho khách hàng.')
                ->with('warning', 'Voucher đã được cấp nhưng email thông báo gửi thất bại. Kiểm tra cấu hình Gmail/log hệ thống.');
        }

        return back()->with(
            'success',
            "Đã cấp {$issuedVouchers->count()} voucher cho {$customer->ho_ten}, tạo thông báo và gửi Gmail thành công."
        );
    }

    public function destroyIssued(Request $request, NguoiDungVoucher $nguoiDungVoucher)
    {
        if ($nguoiDungVoucher->da_su_dung) {
            return back()->with('error', 'Không thể thu hồi voucher đã sử dụng.');
        }

        $code = $nguoiDungVoucher->ma_voucher_ca_nhan;
        $nguoiDungVoucher->delete();

        $this->ghiNhatKy(
            $request,
            'Thu hồi voucher cá nhân',
            'Khuyến mãi & Voucher',
            "Thu hồi voucher {$code}"
        );

        return back()->with('success', 'Đã thu hồi voucher cá nhân.');
    }

    public function destroy(Request $request, Voucher $voucher)
    {
        if ($voucher->nguoiDungVouchers()->exists()) {
            return back()->with(
                'error',
                'Không thể xóa voucher đã cấp cho khách. Hãy tắt voucher để ngừng sử dụng.'
            );
        }

        $code = $voucher->ma_voucher;
        $voucher->delete();

        $this->ghiNhatKy(
            $request,
            'Xóa voucher',
            'Khuyến mãi & Voucher',
            "Xóa voucher {$code}"
        );

        return back()->with('success', 'Đã xóa voucher.');
    }

    private function validatedData(Request $request, ?Voucher $voucher = null): array
    {
        $data = $request->validate([
            'ma_voucher' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9_-]+$/i',
                Rule::unique('vouchers', 'ma_voucher')->ignore($voucher?->id),
            ],
            'ten_voucher' => ['required', 'string', 'max:255'],
            'loai_voucher' => ['required', Rule::in(array_keys(self::VOUCHER_TYPES))],
            'gia_tri_giam' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'diem_can_doi' => ['required', 'integer', 'min:0', 'max:999999'],
            'ngay_het_han' => ['required', 'date'],
            'trang_thai' => ['nullable', 'boolean'],
        ], [
            'ma_voucher.regex' => 'Mã voucher chỉ gồm chữ, số, dấu gạch ngang hoặc gạch dưới.',
        ]);

        $data['ma_voucher'] = Str::upper(trim($data['ma_voucher']));
        $data['ten_voucher'] = trim($data['ten_voucher']);
        $data['trang_thai'] = $request->boolean('trang_thai');

        return $data;
    }

    private function personalCode(Voucher $voucher): string
    {
        do {
            $code = Str::upper($voucher->ma_voucher . '-' . Str::random(8));
        } while (NguoiDungVoucher::where('ma_voucher_ca_nhan', $code)->exists());

        return $code;
    }
}