<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NhatKyHoatDongHeThong;
use Illuminate\Http\Request;

class NhatKyHoatDongHeThongController extends Controller
{
    public function index(Request $request)
    {
        // Validate khoảng ngày
        $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date', 'after_or_equal:from'],
        ], [
            'from.date' => 'Ngày bắt đầu không hợp lệ.',
            'to.date' => 'Ngày kết thúc không hợp lệ.',
            'to.after_or_equal' => 'Ngày kết thúc không được nhỏ hơn ngày bắt đầu.',
        ]);

        $query = NhatKyHoatDongHeThong::query()

            // Lọc theo chức năng
            ->when(
                $request->filled('chuc_nang'),
                fn($query) =>
                $query->where('chuc_nang', $request->chuc_nang)
            )

            // Tìm kiếm
            ->when($request->filled('keyword'), function ($query) use ($request) {

                $keyword = '%' . trim($request->keyword) . '%';

                $query->where(function ($inner) use ($keyword) {

                    $inner
                        ->where('hanh_dong', 'like', $keyword)
                        ->orWhere('mo_ta', 'like', $keyword)
                        ->orWhere('dia_chi_ip', 'like', $keyword)

                        ->orWhereHas('nguoiDung', function ($userQuery) use ($keyword) {

                            $userQuery
                                ->where('ho_ten', 'like', $keyword)
                                ->orWhere('email', 'like', $keyword);
                        });
                });
            })

            // Từ ngày
            ->when(
                $request->filled('from'),
                fn($query) =>
                $query->whereDate(
                    'created_at',
                    '>=',
                    $request->date('from')
                )
            )

            // Đến ngày
            ->when(
                $request->filled('to'),
                fn($query) =>
                $query->whereDate(
                    'created_at',
                    '<=',
                    $request->date('to')
                )
            );


        // Danh sách nhật ký
        $logs = (clone $query)
            ->with('nguoiDung')
            ->latest()
            ->paginate(12)
            ->withQueryString();


        // Danh sách chức năng
        $modules = NhatKyHoatDongHeThong::query()
            ->whereNotNull('chuc_nang')
            ->distinct()
            ->orderBy('chuc_nang')
            ->pluck('chuc_nang');


        // Thống kê
        $summary = [

            // Tổng số bản ghi sau khi lọc
            'filtered' => (clone $query)->count(),

            // Tổng bản ghi hôm nay
            'today' => NhatKyHoatDongHeThong::whereDate(
                'created_at',
                today()
            )->count(),

            // Số chức năng
            'modules' => $modules->count(),

            // Số người thao tác
            'actors' => (clone $query)
                ->whereNotNull('nguoi_dung_id')
                ->distinct('nguoi_dung_id')
                ->count('nguoi_dung_id'),
        ];


        return view(
            'admin.activity-logs.index',
            compact(
                'logs',
                'modules',
                'summary'
            )
        );
    }
}
