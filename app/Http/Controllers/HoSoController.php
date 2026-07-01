<?php

namespace App\Http\Controllers;

use App\Models\NguoiDung; // Sử dụng chính xác Model NguoiDung của hệ thống
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HoSoController extends Controller
{
    /**
     * Hiển thị giao diện chỉnh sửa hồ sơ thành viên rạp phim.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Cập nhật thông tin hồ sơ người dùng.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user(); // Thực thể NguoiDung hiện tại đang đăng nhập

        // XỬ LÝ TRỰC TIẾP VALIDATION: Đồng bộ 100% biến tiếng Việt
        $data = $request->validate([
            'ho_ten'    => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('nguoi_dungs')->ignore($user->id)],
            'ngay_sinh' => ['nullable', 'date'],
        ], [
            'ho_ten.required' => 'Vui lòng nhập họ và tên của bạn.',
            'email.required'  => 'Vui lòng nhập địa chỉ email liên hệ.',
            'email.email'     => 'Địa chỉ email không đúng định dạng.',
            'email.unique'    => 'Địa chỉ email này đã được đăng ký bởi thành viên khác.',
            'ngay_sinh.date'  => 'Ngày sinh không đúng định dạng ngày tháng.',
        ]);

        // 🎯 LUẬT NGHIỆP VỤ RẠP PHIM: Nếu đã thiết lập ngày sinh thì loại bỏ, không cho ghi đè (Tránh lạm dụng voucher sinh nhật)
        if ($user->ngay_sinh) {
            unset($data['ngay_sinh']);
        }

        $user->fill($data);

        // Nếu email thay đổi, tạm thời hủy trạng thái kích hoạt cũ để chờ xác minh lại thư điện tử mới
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Hủy/Xóa vĩnh viễn tài khoản thành viên khỏi hệ thống phòng vé.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Kiểm tra dữ liệu đầu vào gửi lên từ trường mật khẩu tiếng Việt
        $request->validateWithBag('userDeletion', [
            'mat_khau' => ['required'],
        ], [
            'mat_khau.required' => 'Vui lòng nhập mật khẩu để xác nhận quyền chủ sở hữu.',
        ]);

        // Đối chiếu mật khẩu thủ công qua Hash để tương thích với trường 'mat_khau'
        if (!Hash::check($request->mat_khau, $user->password)) {
            return back()->withErrors(['mat_khau' => 'Mật khẩu xác nhận danh tính không chính xác.'], 'userDeletion');
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}