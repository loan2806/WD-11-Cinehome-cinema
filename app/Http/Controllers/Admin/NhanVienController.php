<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Traits\Loggable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class NhanVienController extends Controller
{
    use Loggable;
    public function index(Request $request)
    {
        $query = NguoiDung::query()
            ->where('vai_tro', 'nhan_vien');

        if ($request->keyword) {
            $query->where(function ($q) use ($request) {
                $q->where('ho_ten', 'like', '%' . $request->keyword . '%')
                    ->orWhere('email', 'like', '%' . $request->keyword . '%');
            });
        }

        $nhanViens = $query
            ->latest()
            ->paginate(10);

        return view(
            'admin.nhanviens.index',
            compact('nhanViens')
        );
    }

    public function create()
    {
        return view('admin.nhanviens.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'ho_ten' => 'required|max:255',
            'email' => 'required|email|unique:nguoi_dungs,email',
            'mat_khau' => 'required|min:6',
        ]);

        $email = strtolower(trim($request->email));

        NguoiDung::create([
            'ho_ten' => trim($request->ho_ten),
            'email' => $email,
            'mat_khau' => Hash::make($request->mat_khau),
            'vai_tro' => 'nhan_vien',
            'trang_thai_hoat_dong' => true,
        ]);

        $this->ghiNhatKy($request, 'Thêm nhân viên', 'Quản lý nhân viên', "Thêm nhân viên: {$request->ho_ten}");

        return redirect()
            ->route('admin.nhanviens.index')
            ->with('success', 'Thêm nhân viên thành công');
    }

    public function edit(NguoiDung $nhanvien)
    {
        if ($nhanvien->vai_tro !== 'nhan_vien') {
            abort(404);
        }
        return view(
            'admin.nhanviens.edit',
            compact('nhanvien')
        );
    }

    public function update(
        Request $request,
        NguoiDung $nhanvien
    ) {
        if ($nhanvien->vai_tro !== 'nhan_vien') {
            abort(404);
        }
        $request->validate([
            'ho_ten' => 'required|max:255',
            'email' => 'required|email|unique:nguoi_dungs,email,' . $nhanvien->id,
        ]);

        $nhanvien->update([
            'ho_ten' => $request->ho_ten,
            'email' => strtolower(trim($request->email)),
        ]);

        $this->ghiNhatKy($request, 'Cập nhật nhân viên', 'Quản lý nhân viên', "Cập nhật nhân viên: {$nhanvien->ho_ten}");

        return redirect()
            ->route('admin.nhanviens.index')
            ->with('success', 'Cập nhật thành công');
    }

    public function destroy(NguoiDung $nhanvien)
    {
        if ($nhanvien->vai_tro !== 'nhan_vien') {
            abort(404);
        }

        if ($nhanvien->id == Auth::id()) {
            return back()
                ->with('error', 'Không thể xóa chính tài khoản của bạn');
        }

        $tenNhanVien = $nhanvien->ho_ten;
        $nhanvien->delete();

        $this->ghiNhatKy($request, 'Xóa nhân viên', 'Quản lý nhân viên', "Xóa nhân viên: {$tenNhanVien}");

        return back()
            ->with('success', 'Đã xóa nhân viên');
    }

    public function toggleStatus(NguoiDung $nhanvien)
    {
        if ($nhanvien->vai_tro !== 'nhan_vien') {
            abort(404);
        }

        if ($nhanvien->id == Auth::id()) {
            return back()
                ->with('error', 'Không thể khóa chính tài khoản của bạn');
        }

        $nhanvien->update([
            'trang_thai_hoat_dong'
            => !$nhanvien->trang_thai_hoat_dong
        ]);

        $trangThai = $nhanvien->trang_thai_hoat_dong ? 'kích hoạt' : 'khóa';
        $this->ghiNhatKy($request, 'Đổi trạng thái nhân viên', 'Quản lý nhân viên', "Đổi trạng thái nhân viên {$nhanvien->ho_ten} sang {$trangThai}");

        return back()
            ->with('success', 'Cập nhật trạng thái thành công');
    }
}
