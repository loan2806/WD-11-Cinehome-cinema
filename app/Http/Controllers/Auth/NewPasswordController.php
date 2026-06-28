<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung; // ĐÃ SỬA: Thay đổi hoàn toàn sang Model NguoiDung của hệ thống để triệt tiêu lỗi TypeError
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Hiển thị giao diện đặt lại mật khẩu mới.
     */
    public function create(Request $request): View
    {
        return view('auth.dat_lai_mat_khau', ['request' => $request]);
    }

    /**
     * Xử lý lưu mật khẩu mới vào cơ sở dữ liệu.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. VALIDATION CÁC BIẾN TIẾNG VIỆT TỪ FORM
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'mat_khau' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'token.required' => 'Mã xác thực không hợp lệ.',
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email'    => 'Địa chỉ email không đúng định dạng.',
            'mat_khau.required' => 'Vui lòng nhập mật khẩu mới.',
            'mat_khau.confirmed' => 'Xác nhận mật khẩu mới không trùng khớp.',
        ]);

        // 2. MAPPING DỮ LIỆU: Đồng bộ hóa mảng sang cấu trúc tiếng Anh mặc định của lõi Laravel Broker
        $credentials = [
            'token'                 => $request->token,
            'email'                 => $request->email,
            'password'              => $request->mat_khau,
            'password_confirmation' => $request->mat_khau_confirmation,
        ];

        // 3. THỰC THI ĐỔI MẬT KHẨU QUA BROKER
        $status = Password::reset(
            $credentials,
            // ĐÃ SỬA: Thay đổi kiểu gợi ý (Type-hint) từ User sang NguoiDung để khớp 100% với database của bạn
            function (NguoiDung $user) use ($request) {
                // Thực hiện mã hóa mật khẩu mới nhận từ trường 'mat_khau'
                $user->forceFill([
                    'password'       => Hash::make($request->mat_khau),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // 4. TRẢ VỀ PHẢN HỒI THÔNG BÁO THÀNH CÔNG
        if ($status == Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with('status', 'Mật khẩu của bạn đã được cập nhật thành công! Vui lòng tiến hành đăng nhập.');
        }

        // Trường hợp token hết hạn hoặc email không tồn tại
        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Đường dẫn xác thực này đã hết hạn hoặc không hợp lệ. Vui lòng gửi lại yêu cầu.']);
    }
}