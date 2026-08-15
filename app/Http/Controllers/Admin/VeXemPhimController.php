<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VeXemPhim;
use App\Models\ThongBaoCaNhan;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VeXemPhimController extends Controller
{
    /**
     * Hiển thị danh sách vé trong Admin.
     * Có tìm kiếm, lọc, thống kê và tự dọn vé chờ thanh toán hết hạn.
     */
    public function index(Request $request)
    {
        /*
         * Đồng bộ các vé đã thanh toán nhưng suất chiếu đã qua giờ.
         * Chỉ chuyển các vé chưa sử dụng/đã in? Theo logic hiện tại chỉ
         * áp dụng cho da_thanh_toan để tránh ghi đè trạng thái khác.
         */
        VeXemPhim::where('trang_thai', 'da_thanh_toan')
            ->whereNotNull('thoi_gian_chieu')
            ->where('thoi_gian_chieu', '<', now('Asia/Ho_Chi_Minh'))
            ->update([
                'trang_thai' => 'het_han',
            ]);

        /*
         * Đồng bộ các vé VietQR chờ thanh toán đã quá hạn.
         *
         * Không xóa bản ghi nữa. Nếu xóa ở đây thì command
         * staff:vietqr-het-han sẽ không còn tìm thấy vé để tạo thông báo.
         */
        $expiredPendingTickets = VeXemPhim::query()
            ->where('loai_ve', 'tai_quay')
            ->where('payment_method', 'vietqr')
            ->where('trang_thai', 'cho_thanh_toan')
            ->whereNotNull('thoi_gian_het_han')
            ->where(
                'thoi_gian_het_han',
                '<=',
                now('Asia/Ho_Chi_Minh')
            )
            ->get();

        foreach ($expiredPendingTickets as $pendingTicket) {
            $seats = explode(',', (string) $pendingTicket->ma_ghe);

            foreach ($seats as $seat) {
                $seatCode = strtoupper(trim($seat));

                if ($seatCode === '') {
                    continue;
                }

                Cache::forget(
                    "seat_lock:suat:"
                    . $pendingTicket->suat_chieu_id
                    . ":seat:"
                    . $seatCode
                );
            }

            $pendingTicket->update([
                'trang_thai' => 'het_han',
                'thoi_gian_het_han' => null,
            ]);

            $staffId = $pendingTicket->nhan_vien_id;

            if ($staffId) {
                $tieuDe = 'Giao dịch VietQR hết hạn';

                $noiDung = 'Giao dịch VietQR của vé '
                    . $pendingTicket->ma_ve
                    . ' - Phim: '
                    . $pendingTicket->ten_phim
                    . ' - Ghế: '
                    . $pendingTicket->ma_ghe
                    . ' đã hết thời gian thanh toán. Ghế đã được giải phóng.';

                $daTonTai = ThongBaoCaNhan::query()
                    ->where('nguoi_dung_id', $staffId)
                    ->where('tieu_de', $tieuDe)
                    ->where('noi_dung', $noiDung)
                    ->exists();

                if (!$daTonTai) {
                    ThongBaoCaNhan::create([
                        'nguoi_dung_id' => $staffId,
                        'tieu_de' => $tieuDe,
                        'noi_dung' => $noiDung,
                        'loai_thong_bao' => 've',
                        'duong_dan' => route(
                            'staff.ban-ve.show',
                            $pendingTicket->suat_chieu_id
                        ),
                        'da_doc' => false,
                        'doc_luc' => null,
                    ]);
                }
            }
        }

        $query = VeXemPhim::with([
            'nguoiDung',
            'nhanVien',
            'suatChieu.phim',
            'suatChieu.rapChieuPhim',
            'suatChieu.phongChieu',
        ]);

        if ($request->filled('tim_kiem')) {
            $keyword = trim((string) $request->tim_kiem);

            $query->where(function ($q) use ($keyword) {
                $q->where(
                    'ma_ve',
                    'like',
                    "%{$keyword}%"
                )
                    ->orWhere(
                        'ten_phim',
                        'like',
                        "%{$keyword}%"
                    )
                    ->orWhere(
                        'ma_ghe',
                        'like',
                        "%{$keyword}%"
                    )
                    ->orWhereHas(
                        'nguoiDung',
                        function ($userQuery) use ($keyword) {
                            $userQuery->where(
                                'ho_ten',
                                'like',
                                "%{$keyword}%"
                            );
                        }
                    )
                    ->orWhereHas(
                        'nhanVien',
                        function ($staffQuery) use ($keyword) {
                            $staffQuery->where(
                                'ho_ten',
                                'like',
                                "%{$keyword}%"
                            );
                        }
                    );
            });
        }

        if ($request->filled('trang_thai')) {
            $query->where(
                'trang_thai',
                $request->trang_thai
            );
        }

        if ($request->filled('loai_ve')) {
            $query->where(
                'loai_ve',
                $request->loai_ve
            );
        }

        $tickets = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $totalTickets = VeXemPhim::count();

        $onlineTickets = VeXemPhim::where(
            'loai_ve',
            'truc_tuyen'
        )->count();

        $counterTickets = VeXemPhim::where(
            'loai_ve',
            'tai_quay'
        )->count();

        $pendingTickets = VeXemPhim::where(
            'trang_thai',
            'cho_thanh_toan'
        )->count();

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

        $expiredTickets = VeXemPhim::where(
            'trang_thai',
            'het_han'
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
            'pending' => $pendingTickets,
            'paid' => $paidTickets,
            'printed' => $printedTickets,
            'used' => $usedTickets,
            'cancelled' => $cancelledTickets,
            'expired' => $expiredTickets,
            'revenue' => $ticketRevenue,
        ];

        return view(
            'admin.ve-xem-phims.index',
            compact(
                'tickets',
                'summary',
                'totalTickets',
                'onlineTickets',
                'counterTickets',
                'pendingTickets',
                'paidTickets',
                'printedTickets',
                'usedTickets',
                'cancelledTickets',
                'expiredTickets'
            )
        );
    }

    /**
     * Xem chi tiết một vé.
     */
    public function show($id)
    {
        $veXemPhim = VeXemPhim::with([
            'nguoiDung',
            'nhanVien',
            'suatChieu.phim',
            'suatChieu.rapChieuPhim',
            'suatChieu.phongChieu',
        ])->findOrFail($id);

        $foodItems = Cache::get(
            "ve_foods:{$veXemPhim->id}",
            $veXemPhim->food_items ?? []
        );

        return view(
            'admin.ve-xem-phims.show',
            compact(
                'veXemPhim',
                'foodItems'
            )
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
                'in:cho_thanh_toan,da_thanh_toan,da_in,da_su_dung,da_huy,het_han',
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

        if (
            $veXemPhim->trang_thai === 'da_thanh_toan'
            && $data['trang_thai'] === 'da_su_dung'
        ) {
            return back()->with(
                'error',
                'Vé chưa được in, không thể chuyển sang đã sử dụng.'
            );
        }

        $trangThaiCu = $veXemPhim->trang_thai;

        $updateData = [
            'trang_thai' => $data['trang_thai'],
            'tien_hoan' =>
                $data['trang_thai'] === 'da_huy'
                    ? $veXemPhim->tong_tien
                    : 0,
        ];

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

        if ($veXemPhim->trang_thai === 'het_han') {
            return back()->with(
                'error',
                'Không thể hủy vé đã hết hạn'
            );
        }

        $veXemPhim->update([
            'trang_thai' => 'da_huy',
            'tien_hoan' => $veXemPhim->tong_tien,
        ]);

        $showtimeId = $veXemPhim->suat_chieu_id
            ?? $veXemPhim->suatChieu?->id;

        if ($showtimeId) {
            foreach (
                explode(
                    ',',
                    (string) $veXemPhim->ma_ghe
                ) as $seat
            ) {
                $seatCode = strtoupper(trim($seat));

                if ($seatCode === '') {
                    continue;
                }

                Cache::forget(
                    "seat_lock:suat:"
                    . $showtimeId
                    . ":seat:"
                    . $seatCode
                );
            }
        }

        AdminNotificationService::push(
            '❌ Vé đã bị hủy',
            'Vé #'
                . $veXemPhim->ma_ve
                . ' của suất chiếu #'
                . ($showtimeId ?? '---')
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

        if ($veXemPhim->trang_thai === 'het_han') {
            return back()->with(
                'error',
                'Không thể sử dụng vé đã hết hạn'
            );
        }

        if ($veXemPhim->trang_thai === 'cho_thanh_toan') {
            return back()->with(
                'error',
                'Vé chưa thanh toán, không thể sử dụng.'
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
                'in:cho_thanh_toan,da_thanh_toan,da_in,da_su_dung,da_huy,het_han',
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

        if (
            $veXemPhim->trang_thai === 'da_thanh_toan'
            && $data['trang_thai'] === 'da_su_dung'
        ) {
            return back()->with(
                'error',
                'Vé chưa được in, không thể chuyển sang đã sử dụng.'
            );
        }

        if (
            in_array(
                $veXemPhim->trang_thai,
                ['da_huy', 'het_han'],
                true
            )
            && $data['trang_thai'] !== $veXemPhim->trang_thai
        ) {
            return back()->with(
                'error',
                'Không thể thay đổi trạng thái của vé đã hủy hoặc hết hạn.'
            );
        }

        $oldStatus = $veXemPhim->trang_thai;

        $veXemPhim->update([
            'trang_thai' => $data['trang_thai'],
            'tien_hoan' =>
                $data['trang_thai'] === 'da_huy'
                    ? $veXemPhim->tong_tien
                    : 0,
        ]);

        AdminNotificationService::push(
            '🎟️ Vé xem phim đã được cập nhật',
            'Trạng thái vé #'
                . $veXemPhim->ma_ve
                . ' đã chuyển từ '
                . $oldStatus
                . ' sang '
                . $data['trang_thai'],
            'Success'
        );

        return back()->with(
            'success',
            'Cập nhật trạng thái vé thành công'
        );
    }
}