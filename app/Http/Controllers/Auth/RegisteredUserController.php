<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung; // Đã sửa sang Model tiếng Việt mới
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        // Đã sửa: Gọi đúng file dang_ky.blade.php tiếng Việt của bạn
        return view('auth.dang_ky');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Đã sửa: Kiểm tra các trường dữ liệu theo tiếng Việt đầu vào của form đăng ký
        $request->validate([
            'ho_ten' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.NguoiDung::class],
            'mat_khau' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Đã sửa: Lưu bản ghi mới vào bảng nguoi_dungs thông qua Model NguoiDung
        $user = NguoiDung::create([
            'ho_ten' => $request->ho_ten,
            'email' => $request->email,
            'mat_khau' => Hash::make($request->mat_khau),
            'vai_tro' => 'khach_hang', // Mặc định tài khoản tự đăng ký trực tuyến là khách hàng
            'trang_thai_hoat_dong' => true,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}