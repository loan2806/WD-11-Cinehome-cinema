<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\VeXemPhim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class VeXemPhimController extends Controller
{
    public function index(Request $request)
    {
        // 🌟 BỔ SUNG: Tự động cập nhật các vé đã quá giờ chiếu thành trạng thái Hết Hạn 'het_han'
        VeXemPhim::where('trang_thai', 'da_thanh_toan')
            ->where('thoi_gian_chieu', '<', now('Asia/Ho_Chi_Minh'))
            ->update(['trang_thai' => 'het_han']);

        // 🌟 BỔ SUNG: Thêm 'het_han' vào danh sách bộ lọc được chấp nhận
        $allowedStatuses = ['da_thanh_toan', 'da_su_dung', 'da_huy', 'het_han'];
        $activeStatus = in_array($request->query('trang_thai'), $allowedStatuses, true)
            ? $request->query('trang_thai')
            : null;

        $baseQuery = VeXemPhim::where('nguoi_dung_id', Auth::id());

        // 🌟 BỔ SUNG: Thống kê cả số vé hết hạn 'expired'
        $ticketStats = [
            'total' => (clone $baseQuery)->count(),
            'paid' => (clone $baseQuery)->where('trang_thai', 'da_thanh_toan')->count(),
            'used' => (clone $baseQuery)->where('trang_thai', 'da_su_dung')->count(),
            'cancelled' => (clone $baseQuery)->where('trang_thai', 'da_huy')->count(),
            'expired' => (clone $baseQuery)->where('trang_thai', 'het_han')->count(),
        ];

        $nextTicket = (clone $baseQuery)
            ->where('trang_thai', 'da_thanh_toan')
            ->whereNotNull('thoi_gian_chieu')
            ->where('thoi_gian_chieu', '>=', now())
            ->orderBy('thoi_gian_chieu')
            ->first();

        $cancelMinutes = (int) SystemSetting::getValue('ticket_cancel_minutes', 5);

        $veXemPhims = (clone $baseQuery)
            ->when($activeStatus, fn ($query) => $query->where('trang_thai', $activeStatus))
            ->latest()
            ->paginate(8)
            ->withQueryString();

        $veXemPhims->getCollection()->transform(function ($ve) {
            $ve->food_items = Cache::get("ve_foods:{$ve->id}", []);
            return $ve;
        });

        return view('user.ve_xem_phim.index', compact(
            'veXemPhims',
            'ticketStats',
            'nextTicket',
            'activeStatus',
            'cancelMinutes'
        ));
    }

    public function show(VeXemPhim $veXemPhim)
    {
        abort_if($veXemPhim->nguoi_dung_id !== Auth::id(), 403);

        // 🌟 BỔ SUNG: Tự động cập nhật nếu người dùng xem chi tiết của một vé đã quá giờ chiếu
        if ($veXemPhim->trang_thai === 'da_thanh_toan' && $veXemPhim->thoi_gian_chieu->lt(now('Asia/Ho_Chi_Minh'))) {
            $veXemPhim->update(['trang_thai' => 'het_han']);
            $veXemPhim->trang_thai = 'het_han'; // Gán nóng thuộc tính để view kết xuất chính xác luôn
        }

        $cancelMinutes = (int) SystemSetting::getValue('ticket_cancel_minutes', 5);
        $foodItems = Cache::get("ve_foods:{$veXemPhim->id}", []);

        return view('user.ve_xem_phim.show', compact('veXemPhim', 'cancelMinutes', 'foodItems'));
    }
}