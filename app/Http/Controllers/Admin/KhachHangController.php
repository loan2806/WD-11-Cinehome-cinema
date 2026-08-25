<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;

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

        $tongMoiTrongThang = NguoiDung::where('vai_tro', 'khach_hang')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        $tongCoTheThanhVien = NguoiDung::where('vai_tro', 'khach_hang')
            ->whereHas('thanhVien')
            ->count();

        return view('admin.khach_hang.index', compact(
            'khachHangs',
            'tongKhachHang',
            'tongDangHoatDong',
            'tongBiKhoa',
            'tongMoiTrongThang',
            'tongCoTheThanhVien'
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

            'so_dien_thoai' => [
                'nullable',
                'string',
                'max:20',
                'unique:nguoi_dungs,so_dien_thoai,' . $khachHang->id,
            ],

            'trang_thai_hoat_dong' => ['required', 'boolean'],
        ]);


        // Không cập nhật ngày sinh
        unset($data['ngay_sinh']);


        $khachHang->update($data);

        return redirect()
            ->route('admin.khach-hang.show', $khachHang)
            ->with('success', 'Cập nhật thông tin khách hàng thành công.');
    }

    public function create()
    {
        return view('admin.khach_hang.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ho_ten' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:nguoi_dungs,email'],
            'so_dien_thoai' => ['nullable', 'string', 'max:20'],
            'ngay_sinh' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:1900-01-01',
                'before:today',
            ],
            'mat_khau' => ['required', 'string', 'min:6'],
        ], [

            'ho_ten.required' => 'Vui lòng nhập họ tên khách hàng.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã tồn tại.',
            'mat_khau.required' => 'Vui lòng nhập mật khẩu.',
            'mat_khau.min' => 'Mật khẩu tối thiểu 6 ký tự.',

            'ngay_sinh.required' => 'Vui lòng nhập ngày sinh.',
            'ngay_sinh.date_format' => 'Ngày sinh không đúng định dạng.',
            'ngay_sinh.after_or_equal' => 'Năm sinh không hợp lệ.',
            'ngay_sinh.before' => 'Ngày sinh phải trước ngày hôm nay.',
        ]);

        $data['vai_tro'] = 'khach_hang';
        $data['trang_thai_hoat_dong'] = true;

        // Tài khoản do Admin tạo bắt buộc phải xác thực email
        $data['bat_buoc_xac_thuc_email'] = true;
        $data['email_verified_at'] = null;

        $nguoiDung = NguoiDung::create($data);

        // Gửi email xác thực
        event(new Registered($nguoiDung));

        // Tạo thẻ thành viên
        $nguoiDung->thanhVien()->create([
            'ma_thanh_vien' => 'TV' . str_pad($nguoiDung->id, 6, '0', STR_PAD_LEFT),
            'hang_thanh_vien' => 'member',
            'diem_hien_tai' => 0,
            'tong_diem_tich_luy' => 0,
            'ngay_tham_gia' => now(),
            'da_nhan_thuong' => false,
        ]);

        return redirect()
            ->route('admin.khach-hang.index')
            ->with(
                'success',
                'Thêm khách hàng thành công. Email xác thực đã được gửi đến khách hàng.'
            );
    }
    /**
     * Xóa mềm khách hàng
     */
    public function destroy(NguoiDung $khachHang)
    {
        abort_if($khachHang->vai_tro !== 'khach_hang', 404);

        $khachHang->delete();

        return redirect()
            ->route('admin.khach-hang.index')
            ->with('success', 'Đã chuyển khách hàng vào thùng rác.');
    }
    /**
     * Thùng rác khách hàng
     */
    public function trash(Request $request)
    {
        $query = NguoiDung::onlyTrashed()
            ->where('vai_tro', 'khach_hang');

        // Tìm theo tên, email, số điện thoại
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);

            $query->where(function ($q) use ($keyword) {
                $q->where('ho_ten', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('so_dien_thoai', 'like', "%{$keyword}%");
            });
        }

        // Lọc từ ngày xóa
        if ($request->filled('deleted_from')) {
            $query->whereDate(
                'deleted_at',
                '>=',
                $request->deleted_from
            );
        }

        // Lọc đến ngày xóa
        if ($request->filled('deleted_to')) {
            $query->whereDate(
                'deleted_at',
                '<=',
                $request->deleted_to
            );
        }

        $khachHangs = $query
            ->latest('deleted_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.khach_hang.trash', compact(
            'khachHangs'
        ));
    }

    /**
     * Khôi phục khách hàng
     */
    public function restore($id)
    {
        $khachHang = NguoiDung::onlyTrashed()
            ->where('vai_tro', 'khach_hang')
            ->findOrFail($id);

        $khachHang->restore();

        return redirect()
            ->route('admin.thung-rac.index', ['tab' => 'khach_hang'])
            ->with('success', 'Đã khôi phục khách hàng thành công!');
    }


    /**
     * Xóa vĩnh viễn khách hàng
     */
    /**
     * Xóa vĩnh viễn khách hàng
     *
     * Chỉ cho phép xóa cứng khi khách hàng không còn
     * dữ liệu liên quan.
     */
    public function forceDelete($id)
    {
        $khachHang = NguoiDung::onlyTrashed()
            ->where('vai_tro', 'khach_hang')
            ->findOrFail($id);

        /*
    |--------------------------------------------------------------------------
    | KIỂM TRA DỮ LIỆU LIÊN QUAN
    |--------------------------------------------------------------------------
    */

        $coVe = $khachHang->veXemPhims()->exists();




        /*
    |--------------------------------------------------------------------------
    | NẾU CÒN DỮ LIỆU -> KHÔNG CHO XÓA CỨNG
    |--------------------------------------------------------------------------
    */

        if (
            $coVe
        ) {
            return back()->with(
                'error',
                'Không thể xóa vĩnh viễn khách hàng này vì vẫn còn dữ liệu liên quan.'
            );
        }


        /*
    |--------------------------------------------------------------------------
    | KHÔNG CÒN DỮ LIỆU -> XÓA CỨNG
    |--------------------------------------------------------------------------
    */

        $khachHang->forceDelete();

        return redirect()
            ->route('admin.thung-rac.index', ['tab' => 'khach_hang'])
            ->with(
                'success',
                'Đã xóa vĩnh viễn khách hàng.'
            );
    }
}
