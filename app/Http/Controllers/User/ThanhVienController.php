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


        // Nếu tài khoản cũ chưa có thẻ thì tạo mới
        if (!$thanhVien) {

            $thanhVien = ThanhVien::create([
                'nguoi_dung_id' => $nguoiDung->id,
                'ma_thanh_vien' => 'TV' . str_pad($nguoiDung->id, 6, '0', STR_PAD_LEFT),
                'ma_gioi_thieu' => 'GT' . str_pad($nguoiDung->id, 6, '0', STR_PAD_LEFT),
                'hang_thanh_vien' => 'member',
                'diem_hien_tai' => 0,
                'tong_diem_tich_luy' => 0,
                'ngay_tham_gia' => now(),
            ]);
        }


        // User cũ chưa có mã giới thiệu thì tự cấp
        if (!$thanhVien->ma_gioi_thieu) {

            $thanhVien->update([
                'ma_gioi_thieu' => 'GT' . str_pad(
                    $nguoiDung->id,
                    6,
                    '0',
                    STR_PAD_LEFT
                )
            ]);

            $thanhVien->refresh();
        }


        $lichSuDiem = $thanhVien
            ->lichSuDiems()
            ->latest()
            ->paginate(10);


        $nguoiDaGioiThieu = $nguoiDung
            ->nguoiDuocGioiThieu()
            ->latest()
            ->get();


        return view('user.thanh_vien.index', compact(
            'thanhVien',
            'lichSuDiem',
            'nguoiDaGioiThieu'
        ));
    }
}
