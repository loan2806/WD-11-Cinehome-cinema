<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\VeXemPhim;
use Illuminate\Http\Request;

class SoatVeController extends Controller
{
    /**
     * Hiển thị trang soát vé.
     * Trang này cho phép nhân viên nhập mã vé hoặc mã lấy từ QR để kiểm tra.
     */
    public function index()
    {
        return view('staff.soat-ve.index');
    }

    /**
     * Xử lý kiểm tra vé.
     * Nếu vé hợp lệ thì hệ thống sẽ chuyển trạng thái vé sang "đã sử dụng".
     */
    public function check(Request $request)
    {
        // Kiểm tra nhân viên đã nhập mã vé chưa
        $request->validate([
            'ma_ve' => 'required|string|max:255',
        ], [
            'ma_ve.required' => 'Vui lòng nhập mã vé cần kiểm tra.',
        ]);

        // Chuẩn hóa mã vé để tránh lỗi thừa khoảng trắng khi nhập
        $maVe = trim($request->ma_ve);

        // Tìm vé trong hệ thống theo mã vé
        $ve = VeXemPhim::where('ma_ve', $maVe)->first();

        // Nếu không tìm thấy vé thì báo lỗi
        if (!$ve) {
            return back()
                ->withInput()
                ->with('error', 'Không tìm thấy vé trong hệ thống.');
        }

        // Nếu vé đã bị hủy thì không được check-in
        if ($ve->trang_thai === 'da_huy') {
            return back()
                ->withInput()
                ->with('ticket', $ve)
                ->with('error', 'Vé này đã bị hủy, không thể sử dụng.');
        }

        // Nếu vé đã được dùng rồi thì không cho dùng lại
        if ($ve->trang_thai === 'da_su_dung') {
            return back()
                ->withInput()
                ->with('ticket', $ve)
                ->with('error', 'Vé này đã được sử dụng trước đó.');
        }

        // Chỉ vé đã thanh toán mới được phép vào rạp
        if ($ve->trang_thai !== 'da_thanh_toan') {
            return back()
                ->withInput()
                ->with('ticket', $ve)
                ->with('error', 'Vé chưa được thanh toán hoặc trạng thái không hợp lệ.');
        }

        // Vé hợp lệ thì cập nhật trạng thái sang đã sử dụng
        $ve->update([
            'trang_thai' => 'da_su_dung',
        ]);

        // Lấy lại dữ liệu mới nhất sau khi cập nhật,
        // để giao diện hiển thị đúng trạng thái "Đã sử dụng".
        $ve->refresh();

        return back()
            ->with('ticket', $ve)
            ->with('success', 'Soát vé thành công. Vé hợp lệ.');
    }
}
