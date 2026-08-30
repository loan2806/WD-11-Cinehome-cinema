<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NhatKyHoatDongHeThong;
use Illuminate\Http\Request;

class NhatKyHoatDongHeThongController extends Controller
{
    public function index(Request $request)
    {
        if (
            $request->filled('from') &&
            $request->filled('to') &&
            $request->date('from')->gt($request->date('to'))
        ) {
            return back()
                ->withInput()
                ->with('error', 'Ngày bắt đầu không được lớn hơn ngày kết thúc.');
        }
        $query = NhatKyHoatDongHeThong::query()
            ->when($request->filled('chuc_nang'), fn($query) => $query->where('chuc_nang', $request->chuc_nang))
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $keyword = '%' . trim($request->keyword) . '%';

                $query->where(function ($inner) use ($keyword) {
                    $inner->where('hanh_dong', 'like', $keyword)
                        ->orWhere('mo_ta', 'like', $keyword)
                        ->orWhere('dia_chi_ip', 'like', $keyword)
                        ->orWhereHas('nguoiDung', function ($userQuery) use ($keyword) {
                            $userQuery->where('ho_ten', 'like', $keyword)
                                ->orWhere('email', 'like', $keyword);
                        });
                });
            })
            ->when($request->filled('from'), fn($query) => $query->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn($query) => $query->whereDate('created_at', '<=', $request->date('to')));

        $logs = (clone $query)
            ->with('nguoiDung')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $modules = NhatKyHoatDongHeThong::query()
            ->whereNotNull('chuc_nang')
            ->distinct()
            ->orderBy('chuc_nang')
            ->pluck('chuc_nang');

        $summary = [
            'filtered' => (clone $query)->count(),
            'today' => NhatKyHoatDongHeThong::whereDate('created_at', today())->count(),
            'modules' => $modules->count(),
            'actors' => (clone $query)->whereNotNull('nguoi_dung_id')->distinct('nguoi_dung_id')->count('nguoi_dung_id'),
        ];

        return view('admin.activity-logs.index', compact('logs', 'modules', 'summary'));
    }
}
