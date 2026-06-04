<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DanhGiaPhim; 
use App\Models\Phims; // Giữ nguyên tên Model Phims theo đúng cấu trúc file của bạn
use App\Models\NhatKyHoatDongHeThong; // Đã thêm import Model tiếng Việt mới
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DanhGiaPhimController extends Controller
{
    public function store(Request $request, Phims $phim)
    {
        // ĐÃ SỬA: Kiểm tra dữ liệu đầu vào theo các trường tiếng Việt mới
        $data = $request->validate([
            'diem_danh_gia' => ['required', 'integer', 'min:1', 'max:5'],
            'noi_dung' => ['nullable', 'string', 'max:1000'],
        ]);

        // ĐÃ SỬA: Thực hiện lưu trữ thông qua bảng danh_gia_phims
        DanhGiaPhim::updateOrCreate(
            [
                'phim_id' => $phim->id,
                'nguoi_dung_id' => Auth::id()
            ],
            [
                'diem_danh_gia' => $data['diem_danh_gia'],
                'noi_dung' => $data['noi_dung'] ?? null,
                'trang_thai' => 'hien_thi', 
            ]
        );

        // ĐÃ SỬA LUỒNG LOG: Chuyển hoàn toàn sang Model NhatKyHoatDongHeThong và các cột tiếng Việt
        if (class_exists(NhatKyHoatDongHeThong::class)) {
            NhatKyHoatDongHeThong::create([
                'nguoi_dung_id' => Auth::id(),
                'hanh_dong'     => 'review_movie',
                'chuc_nang'     => 'reviews',
                'mo_ta'         => 'Đánh giá phim ' . $phim->ten_phim,
                'dia_chi_ip'    => $request->ip(),
                'user_agent'    => substr((string) $request->userAgent(), 0, 255),
                'thuoc_tinh'    => ['phim_id' => $phim->id, 'diem_danh_gia' => $data['diem_danh_gia']],
            ]);
        }

        return back()->with('success', 'Đã gửi đánh giá phim.');
    }
}