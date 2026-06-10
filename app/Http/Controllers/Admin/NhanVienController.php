<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class NhanVienController extends Controller
{
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

        NguoiDung::create([
            'ho_ten' => $request->ho_ten,
            'email' => $request->email,
            'mat_khau' => Hash::make($request->mat_khau),
            'vai_tro' => 'nhan_vien',
            'trang_thai_hoat_dong' => true,
        ]);

        return redirect()
            ->route('admin.nhanviens.index')
            ->with('success', 'Thêm nhân viên thành công');
    }

    public function edit(NguoiDung $nhanvien)
    {
        return view(
            'admin.nhanviens.edit',
            compact('nhanvien')
        );
    }

    public function update(
        Request $request,
        NguoiDung $nhanvien
    ) {
        $request->validate([
            'ho_ten' => 'required|max:255',
            'email' => 'required|email|unique:nguoi_dungs,email,' . $nhanvien->id,
        ]);

        $nhanvien->update([
            'ho_ten' => $request->ho_ten,
            'email' => $request->email,
        ]);

        return redirect()
            ->route('admin.nhanviens.index')
            ->with('success', 'Cập nhật thành công');
    }

    public function destroy(NguoiDung $nhanvien)
    {
        $nhanvien->delete();

        return back()
            ->with('success', 'Đã xóa');
    }

    public function toggleStatus(NguoiDung $nhanvien)
    {
        $nhanvien->update([
            'trang_thai_hoat_dong'
            => !$nhanvien->trang_thai_hoat_dong
        ]);

        return back();
    }
}
