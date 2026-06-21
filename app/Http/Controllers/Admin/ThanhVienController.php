<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThanhVien;
use Illuminate\Http\Request;

class ThanhVienController extends Controller
{
    /**
     * Hiển thị danh sách thẻ thành viên trong Admin.
     * Có tìm kiếm, lọc hạng và thống kê nhanh.
     */
    public function index(Request $request)
    {
        $query = ThanhVien::with('nguoiDung')
            ->withCount('lichSuDiems');

        // Tìm kiếm theo mã thành viên, tên khách hoặc email
        if ($request->filled('tim_kiem')) {
            $keyword = $request->tim_kiem;

            $query->where(function ($q) use ($keyword) {
                $q->where('ma_thanh_vien', 'like', "%{$keyword}%")
                    ->orWhereHas('nguoiDung', function ($userQuery) use ($keyword) {
                        $userQuery->where('ho_ten', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%");
                    });
            });
        }

        // Lọc theo hạng thành viên
        if ($request->filled('hang_thanh_vien')) {
            $query->where('hang_thanh_vien', $request->hang_thanh_vien);
        }

        $thanhViens = $query
            ->orderByDesc('tong_diem_tich_luy')
            ->paginate(10)
            ->withQueryString();

        // Thống kê nhanh số lượng thành viên theo từng hạng
        $tongThanhVien = ThanhVien::count();
        $tongMember = ThanhVien::where('hang_thanh_vien', 'member')->count();
        $tongSilver = ThanhVien::where('hang_thanh_vien', 'silver')->count();
        $tongGold = ThanhVien::where('hang_thanh_vien', 'gold')->count();
        $tongPlatinum = ThanhVien::where('hang_thanh_vien', 'platinum')->count();

        return view('admin.thanh_vien.index', compact(
            'thanhViens',
            'tongThanhVien',
            'tongMember',
            'tongSilver',
            'tongGold',
            'tongPlatinum'
        ));
    }

    /**
     * Hiển thị chi tiết một thẻ thành viên.
     * Bao gồm thông tin khách hàng, điểm, vé đã mua, voucher và lịch sử điểm.
     */
    public function show(ThanhVien $thanhVien)
    {
        $thanhVien->load([
            'nguoiDung.veXemPhims',
            'nguoiDung.vouchersCaNhan.voucher',
            'lichSuDiems.veXemPhim',
        ]);

        $nguoiDung = $thanhVien->nguoiDung;

        // Tổng số vé khách đã mua
        $tongVe = $nguoiDung?->veXemPhims()->count() ?? 0;

        // Tổng chi tiêu chỉ tính vé chưa bị hủy
        $tongChiTieu = $nguoiDung?->veXemPhims()
            ->where('trang_thai', '!=', 'da_huy')
            ->sum('tong_tien') ?? 0;

        // Lịch sử điểm mới nhất
        $lichSuDiems = $thanhVien->lichSuDiems()
            ->with('veXemPhim')
            ->latest()
            ->paginate(10);

        // Danh sách voucher khách hàng đang sở hữu
        $vouchers = $nguoiDung?->vouchersCaNhan()
            ->with('voucher')
            ->latest()
            ->get() ?? collect();

        return view('admin.thanh_vien.show', compact(
            'thanhVien',
            'nguoiDung',
            'tongVe',
            'tongChiTieu',
            'lichSuDiems',
            'vouchers'
        ));
    }

    /**
     * Admin tặng điểm thủ công cho thành viên.
     *
     * Nếu tinh_vao_hang = 1:
     * - Cộng điểm hiện tại.
     * - Cộng tổng điểm tích lũy.
     * - Có thể làm khách lên hạng.
     *
     * Nếu tinh_vao_hang = 0:
     * - Chỉ cộng điểm hiện tại.
     * - Không cộng tổng điểm tích lũy.
     * - Không làm khách lên hạng.
     */
    public function tangDiem(Request $request, ThanhVien $thanhVien)
    {
        $data = $request->validate([
            'so_diem' => ['required', 'integer', 'min:1', 'max:10000'],
            'noi_dung' => ['required', 'string', 'max:255'],
            'tinh_vao_hang' => ['required', 'boolean'],
        ]);

        if ((int) $data['tinh_vao_hang'] === 1) {
            $thanhVien->congDiem(
                $data['so_diem'],
                null,
                'Admin tặng điểm có xét hạng: ' . $data['noi_dung']
            );
        } else {
            $thanhVien->congDiemKhongXetHang(
                $data['so_diem'],
                'Admin tặng điểm không xét hạng: ' . $data['noi_dung']
            );
        }

        return back()->with('success', 'Đã tặng điểm cho thành viên thành công.');
    }

    /**
     * Admin thu hồi điểm thủ công cho thành viên.
     *
     * Dùng cho:
     * - Thu hồi điểm tặng nhầm.
     * - Thu hồi điểm cộng sai.
     * - Xử lý gian lận điểm.
     *
     * Lưu ý:
     * Dùng hàm thuHoiDiem() để trừ cả:
     * - Điểm hiện tại.
     * - Tổng điểm tích lũy.
     *
     * Nhờ đó hạng thành viên sẽ tự quay về đúng nếu tổng điểm bị giảm.
     */
    public function truDiem(Request $request, ThanhVien $thanhVien)
    {
        $data = $request->validate([
            'so_diem' => ['required', 'integer', 'min:1', 'max:10000'],
            'noi_dung' => ['required', 'string', 'max:255'],
        ]);

        $thanhVien->thuHoiDiem(
            $data['so_diem'],
            'Admin thu hồi điểm: ' . $data['noi_dung']
        );

        return back()->with('success', 'Đã thu hồi điểm thành viên thành công.');
    }
}
