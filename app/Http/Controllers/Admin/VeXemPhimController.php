<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VeXemPhim;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VeXemPhimController extends Controller
{
    /**
     * Hiển thị danh sách vé trong Admin.
     * Có tìm kiếm, lọc và thống kê nhanh.
     */
    public function index(Request $request)
    {
        $query = VeXemPhim::with([
            'nguoiDung',
            'nhanVien',
            'suatChieu.phim',
            'suatChieu.rapChieuPhim',
            'suatChieu.phongChieu',
        ]);

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

        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        if ($request->filled('loai_ve')) {
            $query->where('loai_ve', $request->loai_ve);
        }

        $tickets = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $totalTickets = VeXemPhim::count();
        $onlineTickets = VeXemPhim::where('loai_ve', 'truc_tuyen')->count();
        $counterTickets = VeXemPhim::where('loai_ve', 'tai_quay')->count();

        $paidTickets = VeXemPhim::where(
            'trang_thai',
            'da_thanh_toan'
        )->count();

        $printedTickets = VeXemPhim::where(
            'trang_thai',
            'da_in'
        )->count();

        $usedTickets = VeXemPhim::where(
            'trang_thai',
            'da_su_dung'
        )->count();

        $cancelledTickets = VeXemPhim::where(
            'trang_thai',
            'da_huy'
        )->count();

        $ticketRevenue = VeXemPhim::whereIn(
            'trang_thai',
            [
                'da_thanh_toan',
                'da_in',
                'da_su_dung',
            ]
        )->sum('tong_tien');

        $summary = [
            'total' => $totalTickets,
            'online' => $onlineTickets,
            'counter' => $counterTickets,
            'paid' => $paidTickets,
            'printed' => $printedTickets,
            'used' => $usedTickets,
            'cancelled' => $cancelledTickets,
            'revenue' => $ticketRevenue,
        ];

        return view('admin.ve-xem-phims.index', compact(
            'tickets',
            'summary',
            'totalTickets',
            'onlineTickets',
            'counterTickets',
            'paidTickets',
            'printedTickets',
            'usedTickets',
            'cancelledTickets'
        ));
    }

    /**
     * Xem chi tiết một vé.
     */
    public function show($id)
    {
        $veXemPhim = VeXemPhim::findOrFail($id);

        $foodItems = Cache::get(
            "ve_foods:{$veXemPhim->id}",
            []
        );

        return view(
            'admin.ve-xem-phims.show',
            compact('veXemPhim', 'foodItems')
        );
    }

    /**
     * Hiển thị form cập nhật trạng thái vé.
     */
    public function edit(VeXemPhim $veXemPhim)
    {
        $veXemPhim->load([
            'nguoiDung',
            'nhanVien',
            'suatChieu.phim',
            'suatChieu.rapChieuPhim',
            'suatChieu.phongChieu',
        ]);

        return view(
            'admin.ve-xem-phims.edit',
            compact('veXemPhim')
        );
    }

    /**
     * Cập nhật trạng thái vé.
     */
    public function update(
        Request $request,
        VeXemPhim $veXemPhim
    ) {
        $data = $request->validate([
            'trang_thai' => [
                'required',
                'in:da_thanh_toan,da_in,da_su_dung,da_huy',
            ],
        ], [
            'trang_thai.required' =>
                'Vui lòng chọn trạng thái vé',
            'trang_thai.in' =>
                'Trạng thái vé không hợp lệ',
        ]);

        if (
            $veXemPhim->trang_thai === 'da_su_dung'
            && $data['trang_thai'] === 'da_huy'
        ) {
            return back()->with(
                'error',
                'Không thể hủy vé đã sử dụng'
            );
        }

        $trangThaiCu = $veXemPhim->trang_thai;

        $updateData = [
            'trang_thai' => $data['trang_thai'],
        ];

        if ($data['trang_thai'] === 'da_huy') {
            $updateData['tien_hoan'] =
                $veXemPhim->tong_tien;
        } else {
            $updateData['tien_hoan'] = 0;
        }

        $veXemPhim->update($updateData);

        AdminNotificationService::push(
            '🎟️ Vé xem phim đã được cập nhật',
            'Trạng thái vé #'
                . $veXemPhim->ma_ve
                . ' đã chuyển từ '
                . $trangThaiCu
                . ' sang '
                . $data['trang_thai'],
            'Success'
        );

        return redirect()
            ->route(
                'admin.ve-xem-phims.show',
                $veXemPhim
            )
            ->with(
                'success',
                'Cập nhật trạng thái vé thành công'
            );
    }

    /**
     * Hủy vé nhanh.
     */
    public function huy(VeXemPhim $veXemPhim)
    {
        if ($veXemPhim->trang_thai === 'da_huy') {
            return back()->with(
                'warning',
                'Vé này đã bị hủy trước đó'
            );
        }

        if ($veXemPhim->trang_thai === 'da_su_dung') {
            return back()->with(
                'error',
                'Không thể hủy vé đã sử dụng'
            );
        }

        $veXemPhim->update([
            'trang_thai' => 'da_huy',
            'tien_hoan' => $veXemPhim->tong_tien,
        ]);

        AdminNotificationService::push(
            '❌ Vé đã bị hủy',
            'Vé #'
                . $veXemPhim->ma_ve
                . ' của suất chiếu #'
                . $veXemPhim->suatChieu->id
                . ' đã bị hủy',
            'Danger'
        );

        return back()->with(
            'success',
            'Hủy vé thành công'
        );
    }

    /**
     * Đánh dấu vé đã sử dụng.
     */
    public function suDung(VeXemPhim $veXemPhim)
    {
        if ($veXemPhim->trang_thai === 'da_huy') {
            return back()->with(
                'error',
                'Không thể sử dụng vé đã hủy'
            );
        }

        if ($veXemPhim->trang_thai === 'da_thanh_toan') {
            return back()->with(
                'error',
                'Vé chưa được in, không thể chuyển sang đã sử dụng.'
            );
        }

        if ($veXemPhim->trang_thai === 'da_su_dung') {
            return back()->with(
                'warning',
                'Vé này đã được sử dụng'
            );
        }

        $veXemPhim->update([
            'trang_thai' => 'da_su_dung',
            'tien_hoan' => 0,
        ]);

        AdminNotificationService::push(
            '✔️ Vé đã được sử dụng',
            'Vé #'
                . $veXemPhim->ma_ve
                . ' đã được check-in thành công',
            'Success'
        );

        return back()->with(
            'success',
            'Cập nhật vé đã sử dụng thành công'
        );
    }

    /**
     * Cập nhật trạng thái nhanh ngay trên bảng quản lý vé.
     */
    public function capNhatTrangThai(
        Request $request,
        VeXemPhim $veXemPhim
    ) {
        $data = $request->validate([
            'trang_thai' => [
                'required',
                'in:da_thanh_toan,da_in,da_su_dung,da_huy',
            ],
        ], [
            'trang_thai.required' =>
                'Vui lòng chọn trạng thái vé.',
            'trang_thai.in' =>
                'Trạng thái vé không hợp lệ.',
        ]);

        if (
            $veXemPhim->trang_thai === 'da_su_dung'
            && $data['trang_thai'] === 'da_huy'
        ) {
            return back()->with(
                'error',
                'Không thể hủy vé đã sử dụng'
            );
        }

        /*
         * Không cho chuyển thẳng từ đã thanh toán sang đã sử dụng.
         * Vé tại quầy phải được in/phát hành trước.
         */
        if (
            $veXemPhim->trang_thai === 'da_thanh_toan'
            && $data['trang_thai'] === 'da_su_dung'
        ) {
            return back()->with(
                'error',
                'Vé chưa được in, không thể chuyển sang đã sử dụng.'
            );
        }

        $veXemPhim->update([
            'trang_thai' => $data['trang_thai'],
            'tien_hoan' =>
                $data['trang_thai'] === 'da_huy'
                    ? $veXemPhim->tong_tien
                    : 0,
        ]);

        return back()->with(
            'success',
            'Cập nhật trạng thái vé thành công'
        );
    }
}