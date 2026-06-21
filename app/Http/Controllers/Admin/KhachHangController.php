<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use Illuminate\Http\Request;

class KhachHangController extends Controller
{
    /**
     * Hiển thị danh sách tài khoản khách hàng.
     * Admin có thể tìm kiếm, lọc trạng thái và xem nhanh thông tin khách.
     */
    public function index(Request $request)
    {
        $query = NguoiDung::query()
            ->where('vai_tro', 'khach_hang')
            ->withCount('veXemPhims')
            ->with('thanhVien');

        // Tìm theo họ tên, email
        // Tìm theo họ tên, email hoặc số điện thoại.
        // Admin chỉ cần nhập SĐT là biết khách hàng nào.
        if ($request->filled('tim_kiem')) {
            $keyword = trim($request->tim_kiem);

            $query->where(function ($q) use ($keyword) {
                $q->where('ho_ten', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('so_dien_thoai', 'like', "%{$keyword}%");
            });
        }

        // Lọc trạng thái hoạt động
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai_hoat_dong', $request->trang_thai);
        }

        $khachHangs = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $tongKhachHang = NguoiDung::where('vai_tro', 'khach_hang')->count();

        $tongDangHoatDong = NguoiDung::where('vai_tro', 'khach_hang')
            ->where('trang_thai_hoat_dong', true)
            ->count();

        $tongBiKhoa = NguoiDung::where('vai_tro', 'khach_hang')
            ->where('trang_thai_hoat_dong', false)
            ->count();

        return view('admin.khach_hang.index', compact(
            'khachHangs',
            'tongKhachHang',
            'tongDangHoatDong',
            'tongBiKhoa'
        ));
    }

    /**
     * Hiển thị chi tiết một tài khoản khách hàng.
     */
    public function show(NguoiDung $khachHang)
    {
        abort_if($khachHang->vai_tro !== 'khach_hang', 404);

        $khachHang->load([
            'thanhVien',
            'veXemPhims',
            'vouchersCaNhan.voucher',
        ]);

        $tongVe = $khachHang->veXemPhims()->count();

        $tongChiTieu = $khachHang->veXemPhims()
            ->where('trang_thai', '!=', 'da_huy')
            ->sum('tong_tien');

        $veGanDay = $khachHang->veXemPhims()
            ->latest()
            ->take(5)
            ->get();

        return view('admin.khach_hang.show', compact(
            'khachHang',
            'tongVe',
            'tongChiTieu',
            'veGanDay'
        ));
    }

    /**
     * Khóa hoặc mở khóa tài khoản khách hàng.
     * Dùng khi khách vi phạm hoặc cần tạm ngừng hoạt động tài khoản.
     */
    public function toggleStatus(NguoiDung $khachHang)
    {
        abort_if($khachHang->vai_tro !== 'khach_hang', 404);

        $khachHang->update([
            'trang_thai_hoat_dong' => !$khachHang->trang_thai_hoat_dong,
        ]);

        return back()->with(
            'success',
            $khachHang->trang_thai_hoat_dong
                ? 'Đã mở khóa tài khoản khách hàng.'
                : 'Đã khóa tài khoản khách hàng.'
        );
    }

    /**
     * Hiển thị form chỉnh sửa thông tin khách hàng.
     * Admin được phép sửa ngày sinh để hỗ trợ khách nhập sai.
     */
    public function edit(NguoiDung $khachHang)
    {
        abort_if($khachHang->vai_tro !== 'khach_hang', 404);

        return view('admin.khach_hang.edit', compact('khachHang'));
    }

    /**
     * Cập nhật thông tin khách hàng.
     * Chỉ cập nhật hồ sơ tài khoản, không sửa điểm/hạng tại đây.
     */
    public function update(Request $request, NguoiDung $khachHang)
    {
        abort_if($khachHang->vai_tro !== 'khach_hang', 404);

        $data = $request->validate([
            'ho_ten' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:nguoi_dungs,email,' . $khachHang->id],

            // SĐT là mã nhận diện thực tế để tìm khách nhanh.
            'so_dien_thoai' => [
                'nullable',
                'string',
                'max:20',
                'unique:nguoi_dungs,so_dien_thoai,' . $khachHang->id,
            ],

            'ngay_sinh' => ['nullable', 'date', 'before:today'],
            'trang_thai_hoat_dong' => ['required', 'boolean'],
        ]);

        $khachHang->update($data);

        return redirect()
            ->route('admin.khach-hang.show', $khachHang)
            ->with('success', 'Cập nhật thông tin khách hàng thành công.');
    }
}
