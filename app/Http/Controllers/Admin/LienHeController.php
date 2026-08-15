<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\LienHePhanHoiMail;
use App\Mail\VoucherUuDaiMail;
use App\Models\LienHe;
use App\Models\NguoiDungVoucher;
use App\Models\Voucher;
use App\Traits\Loggable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LienHeController extends Controller
{
    use Loggable;

    /**
     * Chỉ những liên hệ thuộc các chủ đề lỗi thực sự mới được tặng voucher.
     * "Góp ý" là phản hồi tích cực/đề xuất, không phải sự cố nên không tặng.
     */
    private const CHU_DE_DUOC_TANG_VOUCHER = ['Lỗi đặt vé', 'Lỗi thanh toán', 'Lỗi tài khoản', 'Khác'];

    public function index(Request $request)
    {
        if (! \coQuyen('lien_he.xem')) {
            return redirect()->route('admin.dashboard')->with('error', 'Tài khoản của bạn không có quyền xem Danh sách liên hệ!');
        }

        $query = LienHe::with('nguoiDung')->latest();

        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->string('trang_thai'));
        }

        if ($request->filled('tim_kiem')) {
            $tuKhoa = $request->string('tim_kiem');
            $query->where(function ($q) use ($tuKhoa) {
                $q->where('ho_ten', 'like', "%{$tuKhoa}%")
                    ->orWhere('email', 'like', "%{$tuKhoa}%")
                    ->orWhere('so_dien_thoai', 'like', "%{$tuKhoa}%");
            });
        }

        $lienHes = $query->paginate(10)->withQueryString();

        $thongKe = [
            'tong' => LienHe::count(),
            'cho_xu_ly' => LienHe::where('trang_thai', 'cho_xu_ly')->count(),
            'dang_xu_ly' => LienHe::where('trang_thai', 'dang_xu_ly')->count(),
            'da_xu_ly' => LienHe::where('trang_thai', 'da_xu_ly')->count(),
        ];

        $soChoXuLy = $thongKe['cho_xu_ly'];

        return view('admin.lien-he.index', compact('lienHes', 'soChoXuLy', 'thongKe'));
    }

    public function show(LienHe $lienHe)
    {
        if (! \coQuyen('lien_he.xem')) {
            return redirect()->route('admin.dashboard')->with('error', 'Tài khoản của bạn không có quyền xem Chi tiết liên hệ!');
        }

        $lienHe->load(['nguoiDung', 'nguoiXuLy']);

        $activeVouchers = Voucher::where('trang_thai', true)
            ->whereDate('ngay_het_han', '>=', today())
            ->orderBy('ten_voucher')
            ->get();

        $duocTangVoucher = in_array($lienHe->chu_de, self::CHU_DE_DUOC_TANG_VOUCHER, true);

        return view('admin.lien-he.show', compact('lienHe', 'activeVouchers', 'duocTangVoucher'));
    }

    public function update(Request $request, LienHe $lienHe)
    {
        if (! \coQuyen('lien_he.cap_nhat')) {
            return back()->with('error', 'Tài khoản của bạn không có quyền cập nhật trạng thái liên hệ!');
        }

        $data = $request->validate([
            'trang_thai' => ['required', 'in:cho_xu_ly,dang_xu_ly,da_xu_ly'],
            'phan_hoi' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['nguoi_xu_ly_id'] = auth()->id();

        if ($data['trang_thai'] === 'da_xu_ly') {
            $data['thoi_gian_xu_ly'] = now();
        }

        $phanHoiCu = $lienHe->phan_hoi;

        $lienHe->update($data);

        $daGuiEmail = false;

        if (!empty($data['phan_hoi']) && $data['phan_hoi'] !== $phanHoiCu) {
            Mail::to($lienHe->email)->send(new LienHePhanHoiMail($lienHe));
            $daGuiEmail = true;
        }

        $this->ghiNhatKy(
            $request,
            'Cập nhật liên hệ khách hàng',
            'Quản lý liên hệ',
            "Cập nhật liên hệ #{$lienHe->id} sang {$data['trang_thai']}"
        );

        return back()->with(
            'success',
            $daGuiEmail
                ? 'Đã cập nhật liên hệ và gửi email phản hồi tới khách hàng.'
                : 'Đã cập nhật liên hệ.'
        );
    }

    public function tangVoucher(Request $request, LienHe $lienHe)
    {
        if (! \coQuyen('lien_he.cap_nhat')) {
            return back()->with('error', 'Tài khoản của bạn không có quyền tặng voucher!');
        }

        if (!$lienHe->nguoi_dung_id) {
            return back()->with('error', 'Khách hàng này chưa đăng nhập tài khoản nên không thể tặng voucher trực tiếp.');
        }

        if (!in_array($lienHe->chu_de, self::CHU_DE_DUOC_TANG_VOUCHER, true)) {
            return back()->with('error', 'Chỉ có thể tặng voucher cho liên hệ thuộc nhóm lỗi (đặt vé, thanh toán, tài khoản, khác). "Góp ý" không được tặng voucher.');
        }

        $data = $request->validate([
            'voucher_id' => ['required', 'exists:vouchers,id'],
        ]);

        $voucher = Voucher::findOrFail($data['voucher_id']);

        if (!$voucher->trang_thai || $voucher->ngay_het_han->lt(today())) {
            throw ValidationException::withMessages([
                'voucher_id' => 'Voucher này đang tắt hoặc đã hết hạn, không thể tặng cho khách.',
            ]);
        }

        $nguoiDungVoucher = DB::transaction(function () use ($voucher, $lienHe) {
            return NguoiDungVoucher::create([
                'nguoi_dung_id' => $lienHe->nguoi_dung_id,
                'voucher_id' => $voucher->id,
                'ma_voucher_ca_nhan' => $this->maVoucherCaNhan($voucher),
                'loai_cap_phat' => 'admin_tang',
                'nam_ap_dung' => now()->year,
                'da_su_dung' => false,
                'ngay_nhan' => now(),
                'ngay_het_han' => $voucher->ngay_het_han->copy()->endOfDay(),
            ]);
        });

        $lyDoNhan = $this->lyDoTangVoucher($lienHe);

        Mail::to($lienHe->email)->send(
            new VoucherUuDaiMail($lienHe, $nguoiDungVoucher, $lyDoNhan)
        );

        $this->ghiNhatKy(
            $request,
            'Tặng voucher từ liên hệ khách hàng',
            'Quản lý liên hệ',
            "Tặng voucher {$voucher->ma_voucher} cho {$lienHe->ho_ten} (liên hệ #{$lienHe->id}) - Lý do: {$lyDoNhan}"
        );

        return back()->with(
            'success',
            "Đã tặng voucher {$nguoiDungVoucher->ma_voucher_ca_nhan} cho khách hàng và gửi email thông báo."
        );
    }

    /**
     * Tạo nội dung lý do nhận voucher dựa theo chủ đề liên hệ.
     */
    private function lyDoTangVoucher(LienHe $lienHe): string
    {
        return match ($lienHe->chu_de) {
            'Lỗi đặt vé' => 'CineHome gửi tặng voucher để hỗ trợ và xin lỗi bạn về sự cố trong quá trình đặt vé.',
            'Lỗi thanh toán' => 'CineHome gửi tặng voucher để hỗ trợ và xin lỗi bạn về sự cố trong quá trình thanh toán.',
            'Lỗi tài khoản' => 'CineHome gửi tặng voucher để hỗ trợ và xin lỗi bạn về sự cố liên quan đến tài khoản.',
            'Khác' => 'CineHome gửi tặng voucher để hỗ trợ và cảm ơn bạn đã phản hồi về vấn đề gặp phải trong quá trình sử dụng dịch vụ.',
            default => 'CineHome gửi tặng voucher để cảm ơn bạn đã phản hồi và giúp chúng tôi cải thiện chất lượng dịch vụ.',
        };
    }

    private function maVoucherCaNhan(Voucher $voucher): string
    {
        do {
            $code = Str::upper($voucher->ma_voucher . '-' . Str::random(8));
        } while (NguoiDungVoucher::where('ma_voucher_ca_nhan', $code)->exists());

        return $code;
    }

    public function destroy(Request $request, LienHe $lienHe)
    {
        if (! \coQuyen('lien_he.xoa')) {
            return back()->with('error', 'Tài khoản của bạn không có quyền xóa liên hệ!');
        }

        $id = $lienHe->id;
        $lienHe->delete();

        $this->ghiNhatKy(
            $request,
            'Xóa liên hệ khách hàng',
            'Quản lý liên hệ',
            "Xóa liên hệ #{$id}"
        );

        return redirect()
            ->route('admin.lien-he.index')
            ->with('success', 'Đã xóa liên hệ.');
    }
}