<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PhanQuyenController extends Controller
{
    /**
     * Hiển thị Ma trận phân quyền
     */
    public function maTran()
    {
        // 🌟 BẢO VỆ TỨC THÌ: Nếu quyền xem Ma trận bị tước, lập tức chặn lại
        if (! \coQuyen('phan_quyen.ma_tran')) {
            return redirect()->route('admin.dashboard')->with('error', 'Tài khoản của bạn không có quyền truy cập Ma trận phân quyền!');
        }

        $danhSachVaiTro = config('phan_quyen.vai_tro');
        $maTranQuyen = Cache::get('ma_tran_phan_quyen_he_thong', []);

        return view('admin.phan_quyen.ma_tran', compact('danhSachVaiTro', 'maTranQuyen'));
    }

    /**
     * Cập nhật Ma trận phân quyền
     */
    public function capNhat(Request $request)
    {
        // 🌟 BẢO VỆ TỨC THÌ: Kiểm tra ngay tại thời điểm bấm gửi Form
        if (! \coQuyen('phan_quyen.ma_tran')) {
            return redirect()->route('admin.dashboard')->with('error', 'Tài khoản của bạn vừa bị thu hồi quyền thao tác trên Ma trận phân quyền!');
        }

        $danhSachQuyenGuiLen = $request->input('danh_sach_quyen', []);

        // Mặc định ép Quản Trị Viên (Super-admin) sở hữu 100% tất cả các quyền
        $tatCaMaQuyen = [];
        foreach (config('phan_quyen.nhom_quyen') as $nhom) {
            foreach ($nhom['danh_sach_quyen'] as $maQuyen => $tenQuyen) {
                $tatCaMaQuyen[] = $maQuyen;
            }
        }
        $danhSachQuyenGuiLen['super_admin'] = $tatCaMaQuyen;

        // 🌟 Xóa cache cũ và ghi đè cache mới vĩnh viễn để cập nhật phân quyền tức thì
        Cache::forget('ma_tran_phan_quyen_he_thong');
        Cache::forever('ma_tran_phan_quyen_he_thong', $danhSachQuyenGuiLen);

        return back()->with('success', 'Đã cập nhật và áp dụng ngay lập tức ma trận phân quyền hệ thống!');
    }
}
