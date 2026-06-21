<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ThanhVien;
use Illuminate\Support\Facades\Auth;

class ThanhVienController extends Controller
{
    /**
     * Hiển thị thông tin thẻ thành viên.
     * Nếu tài khoản cũ chưa có thẻ thì tự động tạo thẻ.
     */
    public function index()
    {
        $nguoiDung = Auth::user();

        $thanhVien = $nguoiDung->thanhVien;

        if (!$thanhVien) {
            $thanhVien = ThanhVien::create([
                'nguoi_dung_id' => $nguoiDung->id,
                'ma_thanh_vien' => 'TV' . str_pad($nguoiDung->id, 6, '0', STR_PAD_LEFT),
                'hang_thanh_vien' => 'member',
                'diem_hien_tai' => 0,
                'tong_diem_tich_luy' => 0,
                'ngay_tham_gia' => now(),
            ]);
        }

        $lichSuDiem = $thanhVien->lichSuDiems()
            ->latest()
            ->paginate(10);

        return view('user.thanh_vien.index', compact(
            'thanhVien',
            'lichSuDiem'
        ));
    }
}