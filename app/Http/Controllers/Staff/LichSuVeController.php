<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\VeXemPhim;
use App\Models\ThongBaoCaNhan;
use Illuminate\Http\Request;

class LichSuVeController extends Controller
{
    public function index(Request $request)
    {
        // Đồng bộ các giao dịch QR đã quá 7 phút để lịch sử hiển thị đúng.
        // Xử lý từng vé để không làm mất thông báo hết hạn của nhân viên.
        $expiredTickets = VeXemPhim::query()
            ->where('loai_ve', 'tai_quay')
            ->where('payment_method', 'vietqr')
            ->where('trang_thai', 'cho_thanh_toan')
            ->whereNotNull('thoi_gian_het_han')
            ->where('thoi_gian_het_han', '<=', now())
            ->get();

        foreach ($expiredTickets as $ve) {
            $ve->update([
                'trang_thai' => 'het_han',
                'thoi_gian_het_han' => null,
            ]);

            $staffId = $ve->nhan_vien_id;

            if (!$staffId) {
                continue;
            }

            $tieuDe = 'Giao dịch VietQR hết hạn';
            $noiDung = 'Giao dịch VietQR của vé ' . $ve->ma_ve
                . ' - Phim: ' . $ve->ten_phim
                . ' - Ghế: ' . $ve->ma_ghe
                . ' đã hết thời gian thanh toán. Ghế đã được giải phóng.';

            $daTonTai = ThongBaoCaNhan::where('nguoi_dung_id', $staffId)
                ->where('tieu_de', $tieuDe)
                ->where('noi_dung', $noiDung)
                ->exists();

            if (!$daTonTai) {
                ThongBaoCaNhan::create([
                    'nguoi_dung_id' => $staffId,
                    'tieu_de' => $tieuDe,
                    'noi_dung' => $noiDung,
                    'loai_thong_bao' => 've',
                    'duong_dan' => route('staff.ban-ve.show', $ve->suat_chieu_id),
                    'da_doc' => false,
                    'doc_luc' => null,
                ]);
            }
        }

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