<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NhanVienController extends Controller
{
    public function index()
    {
        // Kiểm tra quyền xem
        if (! \coQuyen('nhan_vien.xem')) {
            return redirect()->route('admin.dashboard')->with('error', 'Bạn không có quyền truy cập Danh sách nhân viên!');
        }

        // Code xử lý lấy danh sách nhân viên...
        return view('admin.nhanvien.index');
    }

    public function store(Request $request)
    {
        // Kiểm tra quyền thêm/sửa/xóa
        if (! \coQuyen('nhan_vien.quan_ly')) {
            return redirect()->route('admin.dashboard')->with('error', 'Tài khoản của bạn không có quyền thực hiện thao tác này!');
        }

        // Code xử lý thêm nhân viên...
    }

    public function toggleStatus($id)
    {
        if (! \coQuyen('nhan_vien.quan_ly')) {
            return redirect()->route('admin.dashboard')->with('error', 'Tài khoản của bạn không có quyền khóa/mở khóa nhân viên!');
        }

        // Code xử lý đổi trạng thái...
    }
}
