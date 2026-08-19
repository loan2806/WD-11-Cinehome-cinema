<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ThongBaoCaNhan;
use App\Models\ThongBaoPushNguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $allowedTypes = [
            'he_thong',
            've',
            'diem',
            'voucher',
            'hang_thanh_vien',
        ];

        $activeType = in_array(
            $request->query('loai'),
            $allowedTypes,
            true
        )
            ? $request->query('loai')
            : null;


        // ==============================
        // THÔNG BÁO CÁ NHÂN
        // ==============================

        $personalNotifications = ThongBaoCaNhan::where(
            'nguoi_dung_id',
            $user->id
        )
            ->get()
            ->map(function ($item) {

                return (object) [
                    'id' => 'personal_' . $item->id,

                    'loai_thong_bao' =>
                    $item->loai_thong_bao,

                    'tieu_de' =>
                    $item->tieu_de,

                    'noi_dung' =>
                    $item->noi_dung,

                    'da_doc' =>
                    (bool) $item->da_doc,

                    'doc_luc' =>
                    $item->doc_luc,

                    'duong_dan' =>
                    $item->duong_dan,

                    'created_at' =>
                    $item->created_at,
                ];
            });


        // ==============================
        // THÔNG BÁO PUSH
        // ==============================
        //
        // CHỈ LẤY PUSH ĐÃ GỬI
        // KHÔNG LẤY BẢN NHÁP
        // ==============================

        $pushNotifications = ThongBaoPushNguoiDung::with(
            'thongBaoPush'
        )
            ->where(
                'nguoi_dung_id',
                $user->id
            )
            ->whereHas(
                'thongBaoPush',
                function ($query) {

                    $query->where(
                        'trang_thai',
                        'da_gui'
                    );
                }
            )
            ->get()
            ->map(function ($item) {

                $push = $item->thongBaoPush;

                /*
            |--------------------------------------------------------------------------
            | Không còn thông báo cha
            |--------------------------------------------------------------------------
            */

                if (!$push) {
                    return null;
                }

                /*
            |--------------------------------------------------------------------------
            | Chỉ hiển thị thông báo đã gửi
            |--------------------------------------------------------------------------
            */

                if ($push->trang_thai !== 'da_gui') {
                    return null;
                }

                return (object) [
                    'id' =>
                    'push_' . $item->id,

                    /*
                | Push được đưa vào nhóm Hệ thống
                */

                    'loai_thong_bao' =>
                    'he_thong',

                    'tieu_de' =>
                    $push->tieu_de,

                    'noi_dung' =>
                    $push->noi_dung,

                    'da_doc' =>
                    (bool) $item->da_doc,

                    'doc_luc' =>
                    $item->doc_luc,

                    'duong_dan' =>
                    null,

                    'created_at' =>
                    $push->created_at,
                ];
            })
            ->filter()
            ->values();


        // ==============================
        // GỘP 2 LOẠI
        // ==============================

        $allNotifications = $personalNotifications
            ->concat($pushNotifications)
            ->sortByDesc('created_at')
            ->values();


        // ==============================
        // THỐNG KÊ
        // ==============================

        $notificationStats = [
            'total' =>
            $allNotifications->count(),

            'unread' =>
            $allNotifications
                ->where('da_doc', false)
                ->count(),

            'read' =>
            $allNotifications
                ->where('da_doc', true)
                ->count(),

            'he_thong' =>
            $allNotifications
                ->where(
                    'loai_thong_bao',
                    'he_thong'
                )
                ->count(),

            've' =>
            $allNotifications
                ->where(
                    'loai_thong_bao',
                    've'
                )
                ->count(),

            'diem' =>
            $allNotifications
                ->where(
                    'loai_thong_bao',
                    'diem'
                )
                ->count(),

            'voucher' =>
            $allNotifications
                ->where(
                    'loai_thong_bao',
                    'voucher'
                )
                ->count(),

            'hang_thanh_vien' =>
            $allNotifications
                ->where(
                    'loai_thong_bao',
                    'hang_thanh_vien'
                )
                ->count(),
        ];


        // ==============================
        // THÔNG BÁO CHƯA ĐỌC MỚI NHẤT
        // ==============================

        $latestUnread = $allNotifications
            ->where('da_doc', false)
            ->sortByDesc('created_at')
            ->first();


        // ==============================
        // LỌC
        // ==============================

        $filteredNotifications = $allNotifications;

        if ($activeType) {

            $filteredNotifications =
                $filteredNotifications
                ->where(
                    'loai_thong_bao',
                    $activeType
                )
                ->values();
        }


        // ==============================
        // PHÂN TRANG
        // ==============================

        $page = max(
            1,
            (int) $request->query('page', 1)
        );

        $perPage = 8;

        $items = $filteredNotifications
            ->forPage(
                $page,
                $perPage
            )
            ->values();

        $thongBaos = new LengthAwarePaginator(
            $items,
            $filteredNotifications->count(),
            $perPage,
            $page,
            [
                'path' =>
                $request->url(),

                'query' =>
                $request->query(),
            ]
        );


        return view(
            'user.thong_bao.index',
            compact(
                'thongBaos',
                'notificationStats',
                'activeType',
                'latestUnread'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Đánh dấu tất cả đã đọc
    |--------------------------------------------------------------------------
    */
    public function markAllRead()
    {
        $userId = Auth::id();

        /*
    |--------------------------------------------------------------------------
    | THÔNG BÁO CÁ NHÂN
    |--------------------------------------------------------------------------
    */

        ThongBaoCaNhan::where(
            'nguoi_dung_id',
            $userId
        )
            ->where(
                'da_doc',
                false
            )
            ->update([
                'da_doc' => true,
                'doc_luc' => now(),
            ]);


        /*
    |--------------------------------------------------------------------------
    | THÔNG BÁO PUSH
    |--------------------------------------------------------------------------
    |
    | CHỈ ĐÁNH DẤU PUSH ĐÃ GỬI
    |
    */

        ThongBaoPushNguoiDung::where(
            'nguoi_dung_id',
            $userId
        )
            ->where(
                'da_doc',
                false
            )
            ->whereHas(
                'thongBaoPush',
                function ($query) {

                    $query->where(
                        'trang_thai',
                        'da_gui'
                    );
                }
            )
            ->update([
                'da_doc' => true,
                'doc_luc' => now(),
            ]);


        return response()->json([
            'success' => true,
        ]);
    }
}
