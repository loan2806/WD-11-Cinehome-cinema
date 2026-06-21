<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\VeXemPhim; // Đã đổi từ Ticket sang VeXemPhim
use Illuminate\Http\Request;

class VeXemPhimController extends Controller
{
    public function index()
    {
        // Đã sửa: Đổi sang bảng ve_xem_phims và cột nguoi_dung_id. 
        // LƯU Ý: Bỏ with('movie') vì bảng ve_xem_phims hiện tại lưu trực tiếp tên_phim dạng chuỗi.
        $veXemPhims = VeXemPhim::where('nguoi_dung_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('user.ve_xem_phim.index', compact('veXemPhims'));
    }

    public function show(VeXemPhim $veXemPhim)
    {
        // Đã sửa: Kiểm tra quyền sở hữu vé bằng cột nguoi_dung_id
        abort_if($veXemPhim->nguoi_dung_id !== Auth::id(), 403);

        // LƯU Ý: Tạm thời bỏ load quan hệ cũ để tránh lỗi do các bảng foodOrders chưa được việt hóa
        return view('user.ve_xem_phim.show', compact('veXemPhim'));
    }

    public function cancel(VeXemPhim $veXemPhim)
    {
        abort_if($veXemPhim->nguoi_dung_id !== Auth::id(), 403);

        // Gọi hàm kiểm tra thời gian hủy vé bên Model VeXemPhim
        if (!$veXemPhim->canCancel()) {
            return back()->with('error', 'Chỉ được hủy vé trong vòng 5 phút sau khi đặt.');
        }

        // Đã sửa: Cập nhật dữ liệu theo các cột tiếng Việt và trạng thái 'da_huy'
        $veXemPhim->update([
            'trang_thai' => 'da_huy',
            'tien_hoan' => $veXemPhim->tong_tien * 0.5,
        ]);

        // Nếu vé đã được cộng điểm trước đó thì trừ lại điểm khi khách hủy vé
        $lichSuCongDiem = $veXemPhim->lichSuDiems()
            ->where('loai_giao_dich', 'cong_diem')
            ->first();

        if ($lichSuCongDiem && $veXemPhim->nguoiDung?->thanhVien) {
            $veXemPhim->nguoiDung->thanhVien->truDiem(
                $lichSuCongDiem->so_diem,
                $veXemPhim,
                'Trừ điểm do hủy vé phim ' . $veXemPhim->ten_phim
            );
        }

        return redirect()
            ->route('user.ve_xem_phim.index')
            ->with('success', 'Hủy vé thành công. Bạn được hoàn 50% giá trị vé.');
    }
}
