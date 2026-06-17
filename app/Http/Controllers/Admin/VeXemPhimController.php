<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VeXemPhim;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;

class VeXemPhimController extends Controller
{
    /**
     * Hiển thị danh sách vé trong Admin.
     * Có tìm kiếm, lọc và thống kê nhanh.
     */
    public function index(Request $request)
    {
        // Lấy danh sách vé kèm thông tin người mua, nhân viên và suất chiếu
        $query = VeXemPhim::with([
            'nguoiDung',
            'nhanVien',
            'suatChieu.phim',
            'suatChieu.rapChieuPhim',
            'suatChieu.phongChieu',
        ]);

        // Tìm kiếm theo mã vé, tên phim, rạp, phòng hoặc ghế
        if ($request->filled('tim_kiem')) {
            $keyword = $request->tim_kiem;

            $query->where(function ($q) use ($keyword) {
                $q->where('ma_ve', 'like', "%{$keyword}%")
                    ->orWhere('ten_phim', 'like', "%{$keyword}%")
                    ->orWhere('ten_rap', 'like', "%{$keyword}%")
                    ->orWhere('ten_phong', 'like', "%{$keyword}%")
                    ->orWhere('ma_ghe', 'like', "%{$keyword}%");
            });
        }

        // Lọc theo trạng thái vé
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        // Lọc theo loại vé: trực tuyến hoặc tại quầy
        if ($request->filled('loai_ve')) {
            $query->where('loai_ve', $request->loai_ve);
        }

        // Phân trang danh sách vé, giữ lại query lọc khi chuyển trang
        $tickets = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Thống kê nhanh hiển thị trên đầu trang
        $totalTickets = VeXemPhim::count();
        $onlineTickets = VeXemPhim::where('loai_ve', 'truc_tuyen')->count();
        $counterTickets = VeXemPhim::where('loai_ve', 'tai_quay')->count();
        $cancelledTickets = VeXemPhim::where('trang_thai', 'da_huy')->count();

        return view('admin.ve-xem-phims.index', compact(
            'tickets',
            'totalTickets',
            'onlineTickets',
            'counterTickets',
            'cancelledTickets'
        ));
    }

    /**
     * Xem chi tiết một vé.
     */
    public function show(VeXemPhim $veXemPhim)
    {
        // Load thêm dữ liệu liên quan để hiển thị đầy đủ trong trang chi tiết
        $veXemPhim->load([
            'nguoiDung',
            'nhanVien',
            'suatChieu.phim',
            'suatChieu.rapChieuPhim',
            'suatChieu.phongChieu',
        ]);

        return view('admin.ve-xem-phims.show', compact('veXemPhim'));
    }

    /**
     * Hiển thị form cập nhật trạng thái vé.
     * Admin chỉ sửa trạng thái, không sửa phim, ghế, giá vé.
     */
    public function edit(VeXemPhim $veXemPhim)
    {
        // Load thông tin để admin đối chiếu trước khi cập nhật
        $veXemPhim->load([
            'nguoiDung',
            'nhanVien',
            'suatChieu.phim',
            'suatChieu.rapChieuPhim',
            'suatChieu.phongChieu',
        ]);

        return view('admin.ve-xem-phims.edit', compact('veXemPhim'));
    }

    /**
     * Cập nhật trạng thái vé.
     */
    public function update(Request $request, VeXemPhim $veXemPhim)
    {
        // Chỉ cho phép cập nhật các trạng thái hợp lệ
        $data = $request->validate([
            'trang_thai' => ['required', 'in:da_thanh_toan,da_su_dung,da_huy'],
        ], [
            'trang_thai.required' => 'Vui lòng chọn trạng thái vé',
            'trang_thai.in' => 'Trạng thái vé không hợp lệ',
        ]);

        // Vé đã sử dụng thì không được chuyển sang hủy
        if (
            $veXemPhim->trang_thai === 'da_su_dung'
            && $data['trang_thai'] === 'da_huy'
        ) {
            return back()->with('error', 'Không thể hủy vé đã sử dụng');
        }

        $trangThaiCu = $veXemPhim->trang_thai;


        $updateData = [
            'trang_thai' => $data['trang_thai'],
        ];

        // Nếu hủy vé thì ghi nhận số tiền hoàn
        if ($data['trang_thai'] === 'da_huy') {
            $updateData['tien_hoan'] = $veXemPhim->tong_tien;
        }

        // Nếu chuyển lại sang trạng thái khác thì reset tiền hoàn
        if ($data['trang_thai'] !== 'da_huy') {
            $updateData['tien_hoan'] = 0;
        }

        $veXemPhim->update($updateData);


        AdminNotificationService::push(
            '🎟️ Vé xem phim đã được cập nhật',
            'Trạng thái vé #' . $veXemPhim->ma_ve .
                ' đã chuyển từ ' . $trangThaiCu .
                ' sang ' . $data['trang_thai'],
            'Success'
        );


        return redirect()
            ->route('admin.ve-xem-phims.show', $veXemPhim)
            ->with('success', 'Cập nhật trạng thái vé thành công');
    }

    /**
     * Hủy vé nhanh từ danh sách hoặc chi tiết vé.
     */
    public function huy(VeXemPhim $veXemPhim)
    {
        // Không hủy lại vé đã hủy
        if ($veXemPhim->trang_thai === 'da_huy') {
            return back()->with('warning', 'Vé này đã bị hủy trước đó');
        }

        // Không hủy vé đã sử dụng
        if ($veXemPhim->trang_thai === 'da_su_dung') {
            return back()->with('error', 'Không thể hủy vé đã sử dụng');
        }

        // Cập nhật trạng thái và tiền hoàn
        $veXemPhim->update([
            'trang_thai' => 'da_huy',
            'tien_hoan' => $veXemPhim->tong_tien,
        ]);


        AdminNotificationService::push(
            '❌ Vé đã bị hủy',
            'Vé #' . $veXemPhim->ma_ve .
                ' của suất chiếu #' . $veXemPhim->suatChieu->id .
                ' đã bị hủy',
            'Danger'
        );



        return back()->with('success', 'Hủy vé thành công');
    }

    /**
     * Đánh dấu vé đã sử dụng.
     */
    public function suDung(VeXemPhim $veXemPhim)
    {
        // Vé đã hủy thì không được sử dụng
        if ($veXemPhim->trang_thai === 'da_huy') {
            return back()->with('error', 'Không thể sử dụng vé đã hủy');
        }

        // Vé đã sử dụng rồi thì không cập nhật lại
        if ($veXemPhim->trang_thai === 'da_su_dung') {
            return back()->with('warning', 'Vé này đã được sử dụng');
        }

        // Chuyển trạng thái vé sang đã sử dụng
        $veXemPhim->update([
            'trang_thai' => 'da_su_dung',
            'tien_hoan' => 0,
        ]);


        AdminNotificationService::push(
            '✔️ Vé đã được sử dụng',
            'Vé #' . $veXemPhim->ma_ve .
                ' đã được check-in thành công',
            'Success'
        );

        return back()->with('success', 'Cập nhật vé đã sử dụng thành công');
    }

    public function capNhatTrangThai(Request $request, VeXemPhim $veXemPhim)
    {
        $data = $request->validate([
            'trang_thai' => ['required', 'in:da_thanh_toan,da_su_dung,da_huy'],
        ]);

        if (
            $veXemPhim->trang_thai === 'da_su_dung'
            && $data['trang_thai'] === 'da_huy'
        ) {
            return back()->with('error', 'Không thể hủy vé đã sử dụng');
        }

        $veXemPhim->update([
            'trang_thai' => $data['trang_thai'],
            'tien_hoan' => $data['trang_thai'] === 'da_huy'
                ? $veXemPhim->tong_tien
                : 0,
        ]);

        return back()->with('success', 'Cập nhật trạng thái vé thành công');
    }
}
