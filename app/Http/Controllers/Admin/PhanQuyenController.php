<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

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

        $danhSachVaiTro = config('phan_quyen.vai_tro', []);
        
        // Lấy ma trận quyền từ Cache
        $maTranQuyen = Cache::get('ma_tran_phan_quyen_he_thong', []);

        // Thu thập toàn bộ các mã quyền đang khai báo trong Config
        $tatCaMaQuyen = [];
        foreach (config('phan_quyen.nhom_quyen', []) as $nhom) {
            foreach ($nhom['danh_sach_quyen'] as $maQuyen => $tenQuyen) {
                $tatCaMaQuyen[] = $maQuyen;
            }
        }

        // Nếu Cache rỗng, khởi tạo mặc định cho super_admin sở hữu tất cả các quyền
        if (empty($maTranQuyen)) {
            $maTranQuyen['super_admin'] = $tatCaMaQuyen;
        } else {
            // Đảm bảo Super Admin luôn sở hữu 100% tất cả các quyền kể cả quyền mới tạo
            $maTranQuyen['super_admin'] = array_values(array_unique(array_merge($maTranQuyen['super_admin'] ?? [], $tatCaMaQuyen)));
        }

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

        // Thu thập toàn bộ mã quyền có trong hệ thống
        $tatCaMaQuyen = [];
        foreach (config('phan_quyen.nhom_quyen', []) as $nhom) {
            foreach ($nhom['danh_sach_quyen'] as $maQuyen => $tenQuyen) {
                $tatCaMaQuyen[] = $maQuyen;
            }
        }

        // Mặc định ép Quản Trị Viên (Super-admin) sở hữu 100% tất cả các quyền
        $danhSachQuyenGuiLen['super_admin'] = $tatCaMaQuyen;

        // 🌟 1. Cập nhật và ghi đè vĩnh viễn vào Cache hệ thống
        Cache::forget('ma_tran_phan_quyen_he_thong');
        Cache::forever('ma_tran_phan_quyen_he_thong', $danhSachQuyenGuiLen);

        // 🌟 2. Đồng bộ trực tiếp vào Cơ sở dữ liệu Spatie Permission
        try {
            // Xóa bộ nhớ tạm Spatie
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            // Đảm bảo tất cả Permission Keys trong config đều đã tồn tại trong DB
            foreach ($tatCaMaQuyen as $maQuyen) {
                Permission::firstOrCreate(
                    ['name' => $maQuyen, 'guard_name' => 'web'],
                    ['description' => $maQuyen]
                );
            }

            // Cập nhật quyền cho từng vai trò tương ứng trong DB
            $danhSachVaiTro = config('phan_quyen.vai_tro', []);
            foreach ($danhSachVaiTro as $khoaVaiTro => $tenVaiTro) {
                $role = Role::firstOrCreate(['name' => $khoaVaiTro, 'guard_name' => 'web']);
                
                // Lấy danh sách quyền được tích chọn từ màn hình ma trận
                $quyenCuaVaiTro = $danhSachQuyenGuiLen[$khoaVaiTro] ?? [];
                
                // Đồng bộ danh sách quyền vào vai trò
                $role->syncPermissions($quyenCuaVaiTro);
            }
        } catch (\Throwable $e) {
            logger()->error('Cảnh báo đồng bộ Spatie DB: ' . $e->getMessage());
        }

        return back()->with('success', 'Đã cập nhật và áp dụng ngay lập tức ma trận phân quyền hệ thống!');
    }
}