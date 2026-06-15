<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PhanQuyenController extends Controller
{
    /**
     * Hiển thị danh sách các vai trò và bảng ma trận tích chọn quyền động
     */
    public function index(Request $request)
    {
        // Lấy tất cả vai trò kèm theo danh sách quyền hiện tại của chúng
        $roles = Role::with('permissions')->get();
        
        // Lấy toàn bộ danh sách các quyền hạn được định nghĩa trong hệ thống
        $permissions = Permission::all();

        // Kiểm tra xem Admin có đang bấm chọn xem một vai trò cụ thể nào không
        $selectedRole = null;
        if ($request->has('role_id')) {
            $selectedRole = Role::find($request->role_id);
        }

        // Nếu không lựa chọn vai trò cụ thể, mặc định hiển thị vai trò đầu tiên trong danh sách
        if (!$selectedRole) {
            $selectedRole = Role::first();
        }

        return view('admin.phan-quyen.index', compact('roles', 'permissions', 'selectedRole'));
    }

    /**
     * Xử lý lưu và cập nhật ma trận tích chọn quyền động của Vai trò
     * Đã tích hợp lệnh xóa bộ nhớ đệm tối cao chống leak quyền
     */
    public function updateMatrix(Request $request, $id)
    {
        // Tìm vai trò cần cập nhật quyền hạn
        $role = Role::findOrFail($id);
        
        // Nhận danh sách mảng các quyền được người dùng tích chọn từ form
        $activePermissions = $request->input('permissions', []);

        // Đồng bộ hóa danh sách quyền mới cho vai trò
        $role->syncPermissions($activePermissions);

        // BIỆN PHÁP BẢO MẬT: Ép buộc Spatie xóa sạch bộ nhớ đệm cũ để kích hoạt quyền mới ngay lập tức
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.phan-quyen.index', ['role_id' => $role->id])
                         ->with('success', 'Cập nhật ma trận phân quyền động cho vai trò thành công!');
    }
}