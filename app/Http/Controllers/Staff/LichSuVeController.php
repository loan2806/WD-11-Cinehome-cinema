<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\VeXemPhim;
use Illuminate\Http\Request;

class LichSuVeController extends Controller
{
    public function index(Request $request)
    {
        // Đồng bộ các giao dịch QR đã quá 7 phút để lịch sử hiển thị đúng.
        VeXemPhim::query()
            ->where('loai_ve', 'tai_quay')
            ->where('payment_method', 'vietqr')
            ->where('trang_thai', 'cho_thanh_toan')
            ->whereNotNull('thoi_gian_het_han')
            ->where('thoi_gian_het_han', '<=', now())
            ->update([
                'trang_thai' => 'het_han',
                'thoi_gian_het_han' => null,
            ]);

        $query = VeXemPhim::query()
            ->with(['nhanVien', 'nguoiDung'])
            ->latest('id');

        $keyword = trim((string) $request->input('keyword', ''));
        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('ma_ve', 'like', "%{$keyword}%")
                    ->orWhere('ten_phim', 'like', "%{$keyword}%")
                    ->orWhere('ma_ghe', 'like', "%{$keyword}%")
                    ->orWhere('ten_rap', 'like', "%{$keyword}%")
                    ->orWhere('ten_phong', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('loai_ve')) {
            $query->where('loai_ve', $request->string('loai_ve')->toString());
        }

        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->string('trang_thai')->toString());
        }

        $tickets = $query
            ->paginate(20)
            ->withQueryString();

        return view('staff.lich-su-ve.index', compact('tickets'));
    }
}