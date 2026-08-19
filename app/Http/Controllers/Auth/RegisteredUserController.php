<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Models\ThanhVien;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{

    public function create(): View
    {
        return view('auth.dang_ky');
    }


    public function store(Request $request): RedirectResponse
    {
        $request->validate([

            'ho_ten' => [
                'required',
                'string',
                'max:255'
            ],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:' . NguoiDung::class
            ],

            'mat_khau' => [
                'required',
                'confirmed',
                Rules\Password::defaults()
            ],

            // Mã giới thiệu không bắt buộc
            'ma_gioi_thieu' => [
                'nullable',
                'string',
                'exists:thanh_viens,ma_gioi_thieu'
            ],

        ]);


        /*
    |--------------------------------------------------------------------------
    | TẠO TÀI KHOẢN
    |--------------------------------------------------------------------------
    */

        $user = NguoiDung::create([

            'ho_ten' => $request->ho_ten,

            'email' => $request->email,

            'mat_khau' => Hash::make(
                $request->mat_khau
            ),

            'vai_tro' => 'khach_hang',

            'trang_thai_hoat_dong' => true,

            'bat_buoc_xac_thuc_email' => false,

            // Chưa xác thực email
            'email_verified_at' => null,

        ]);


        /*
    |--------------------------------------------------------------------------
    | TÌM NGƯỜI GIỚI THIỆU
    |--------------------------------------------------------------------------
    */

        $nguoiGioiThieu = null;

        if ($request->filled('ma_gioi_thieu')) {

            $nguoiGioiThieu = ThanhVien::where(
                'ma_gioi_thieu',
                $request->ma_gioi_thieu
            )->first();
        }


        /*
    |--------------------------------------------------------------------------
    | TẠO THẺ THÀNH VIÊN
    |--------------------------------------------------------------------------
    */

        $thanhVien = ThanhVien::create([

            'nguoi_dung_id' => $user->id,

            'ma_thanh_vien' =>
            'TV' . str_pad(
                $user->id,
                6,
                '0',
                STR_PAD_LEFT
            ),

            'ma_gioi_thieu' =>
            ThanhVien::taoMaGioiThieu(
                $user->id
            ),

            'nguoi_gioi_thieu_id' =>
            $nguoiGioiThieu?->id,

            'hang_thanh_vien' => 'member',

            'diem_hien_tai' => 0,

            'tong_diem_tich_luy' => 0,

            'ngay_tham_gia' => now(),

        ]);


        /*
    |--------------------------------------------------------------------------
    | NGƯỜI GIỚI THIỆU NHẬN 100 ĐIỂM
    |--------------------------------------------------------------------------
    */

        if (
            $nguoiGioiThieu
            && !$nguoiGioiThieu->da_nhan_thuong
        ) {

            $nguoiGioiThieu->congDiem(
                100,
                null,
                'Giới thiệu thành viên mới'
            );

            $nguoiGioiThieu->update([
                'da_nhan_thuong' => true
            ]);
        }


        /*
    |--------------------------------------------------------------------------
    | SỰ KIỆN ĐĂNG KÝ
    |--------------------------------------------------------------------------
    */

        event(
            new Registered($user)
        );


        /*
    |--------------------------------------------------------------------------
    | KHÔNG TỰ ĐĂNG NHẬP
    |--------------------------------------------------------------------------
    |
    | ĐÃ XÓA:
    |
    | Auth::login($user);
    |
    */


        /*
    |--------------------------------------------------------------------------
    | CHUYỂN VỀ TRANG ĐĂNG NHẬP
    |--------------------------------------------------------------------------
    */

        return redirect()
            ->route('login')
            ->with(
                'success',
                'Đăng ký tài khoản thành công. Vui lòng đăng nhập.'
            );
    }
}
