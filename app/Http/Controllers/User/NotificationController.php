<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ThongBaoCaNhan;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Hiển thị danh sách thông báo cá nhân của khách hàng.
     */
    public function index()
    {
        $user = Auth::user();

        $thongBaos = ThongBaoCaNhan::where('nguoi_dung_id', $user->id)
            ->latest()
            ->paginate(10);

        /**
         * Khi người dùng vào trang thông báo,
         * hệ thống tự đánh dấu tất cả thông báo chưa đọc thành đã đọc.
         */
        ThongBaoCaNhan::where('nguoi_dung_id', $user->id)
            ->where('da_doc', false)
            ->update([
                'da_doc' => true,
                'doc_luc' => now(),
            ]);

        return view('user.thong_bao.index', compact('thongBaos'));
    }
}
