<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phims;
use App\Models\PhongChieu;
use App\Models\RapChieuPhim;
use App\Models\SuatChieu;
use App\Services\AdminNotificationService;
use App\Traits\Loggable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuatChieuController extends Controller
{
    use Loggable;

    private const THOI_GIAN_DON_PHONG = 15;

    private const TRANG_THAI_SAP_CHIEU = 'sap_chieu';
    private const TRANG_THAI_DANG_CHIEU = 'dang_chieu';
    private const TRANG_THAI_DA_CHIEU = 'da_chieu';

    public function index(Request $request): View
    {
        $movieQuery = Phims::query();

        $filterSuatChieu = function ($query) use ($request) {
            $query->with(['rapChieuPhim', 'phongChieu']);

            if ($request->filled('trang_thai')) {
                $query->where('trang_thai', $request->trang_thai);
            }

            if ($request->filled('phong_chieu_id')) {
                $query->where('phong_chieu_id', $request->phong_chieu_id);
            }

            if ($request->filled('ngay_chieu')) {
                $query->whereDate('thoi_gian_chieu', $request->ngay_chieu);
            }

            $query->orderBy('thoi_gian_chieu', 'asc');
        };

        if ($request->filled('phim_id')) {
            $movieQuery->where('id', $request->phim_id);
        }

        $movieQuery->whereHas('showtimes', function ($q) use ($filterSuatChieu) {
            $filterSuatChieu($q);
        })->with(['showtimes' => $filterSuatChieu]);

        $phimsPhanTrang = $movieQuery->orderBy('ten_phim', 'asc')
            ->paginate(5)
            ->withQueryString();

        $phims = Phims::orderBy('ten_phim')->get();
        $phongChieus = PhongChieu::with('rapChieuPhim')->orderBy('ten_phong')->get();

        return view('admin.suat-chieus.index', compact(
            'phimsPhanTrang',
            'phims',
            'phongChieus'
        ));
    }

    public function create(Request $request): View
    {
        $phims = Phims::orderBy('ten_phim')->get();
        $rapChieuPhims = RapChieuPhim::orderBy('ten_rap')->get();
        $phongChieus = PhongChieu::with('rapChieuPhim')
            ->where('trang_thai', 'hoat_dong')
            ->orderBy('ten_phong')
            ->get();

        $phongChieuId = $request->phong_chieu_id;

        return view('admin.suat-chieus.create', compact(
            'phims',
            'rapChieuPhims',
            'phongChieus',
            'phongChieuId'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'phim_id' => 'required|exists:phims,id',
            'rap_chieu_phim_id' => 'required|exists:rap_chieu_phims,id',
            'phong_chieu_id' => 'required|exists:phong_chieus,id',
            'loai_tao' => 'required|in:don_le,hang_loat',

            'ngay_chieu_don_le' => 'required_if:loai_tao,don_le|nullable|date',
            'gio_chieu_don_le' => 'required_if:loai_tao,don_le|nullable|string',

            'ngay_bat_dau' => 'required_if:loai_tao,hang_loat|nullable|date',
            'ngay_ket_thuc' => 'required_if:loai_tao,hang_loat|nullable|date|after_or_equal:ngay_bat_dau',
            'khung_gio' => 'required_if:loai_tao,hang_loat|nullable|array',

            'gia_ve_tuy_chinh' => 'nullable|numeric|min:0',
        ]);

        $phim = Phims::findOrFail($request->phim_id);
        $phongChieu = PhongChieu::findOrFail($request->phong_chieu_id);
        $thoiLuongPhim = $phim->thoi_luong ?? 90;

        $created = 0;
        $failed = 0;

        // ====== TẠO ĐƠN LẺ ======
        if ($request->loai_tao === 'don_le') {

            $start = Carbon::parse($request->ngay_chieu_don_le . ' ' . $request->gio_chieu_don_le);
            $end = $start->copy()->addMinutes($thoiLuongPhim + self::THOI_GIAN_DON_PHONG);

            if ($this->kiemTraXungDotLich($phongChieu->id, $start, $end)) {
                return back()->withInput()->with('error', 'Trùng lịch chiếu.');
            }

            $gia = $request->filled('gia_ve_tuy_chinh')
                ? $request->gia_ve_tuy_chinh
                : $this->tinhGiaVeTuDong($start, $phongChieu);

            $suat = SuatChieu::create([
                'phim_id' => $phim->id,
                'rap_chieu_phim_id' => $request->rap_chieu_phim_id,
                'phong_chieu_id' => $phongChieu->id,
                'thoi_gian_chieu' => $start,
                'thoi_luong' => $thoiLuongPhim,
                'thoi_gian_ket_thuc' => $end,
                'gia_ve' => $gia,
                'trang_thai' => $this->xacDinhTrangThaiBanDau($start, $end),
            ]);

            $this->sinhMaGheTuDongChoSuatChieu($suat);

            $this->ghiNhatKy($request, 'Thêm suất chiếu', 'Quản lý lịch chiếu', $phim->ten_phim);

            AdminNotificationService::push(
                '🎬 Suất chiếu mới',
                "Phim {$phim->ten_phim}",
                'Success'
            );

            return redirect()->route('admin.suat-chieus.index')
                ->with('success', 'Tạo suất chiếu thành công');
        }

        // ====== HÀNG LOẠT ======
        $from = Carbon::parse($request->ngay_bat_dau);
        $to = Carbon::parse($request->ngay_ket_thuc);

        for ($d = $from; $d->lte($to); $d->addDay()) {
            foreach ($request->khung_gio as $time) {

                $start = Carbon::parse($d->format('Y-m-d') . ' ' . $time);
                $end = $start->copy()->addMinutes($thoiLuongPhim + self::THOI_GIAN_DON_PHONG);

                if ($this->kiemTraXungDotLich($phongChieu->id, $start, $end)) {
                    $failed++;
                    continue;
                }

                $gia = $request->filled('gia_ve_tuy_chinh')
                    ? $request->gia_ve_tuy_chinh
                    : $this->tinhGiaVeTuDong($start, $phongChieu);

                $suat = SuatChieu::create([
                    'phim_id' => $phim->id,
                    'rap_chieu_phim_id' => $request->rap_chieu_phim_id,
                    'phong_chieu_id' => $phongChieu->id,
                    'thoi_gian_chieu' => $start,
                    'thoi_luong' => $thoiLuongPhim,
                    'thoi_gian_ket_thuc' => $end,
                    'gia_ve' => $gia,
                    'trang_thai' => $this->xacDinhTrangThaiBanDau($start, $end),
                ]);

                $this->sinhMaGheTuDongChoSuatChieu($suat);

                $created++;
            }
        }

        $this->ghiNhatKy($request, 'Thêm suất chiếu hàng loạt', 'Quản lý lịch chiếu', $phim->ten_phim);

        return redirect()->route('admin.suat-chieus.index')
            ->with('success', "Tạo {$created} suất chiếu")
            ->with('warning', $failed ? "Bỏ qua {$failed} suất do trùng lịch" : null);
    }

    public function update(Request $request, SuatChieu $suatChieu)
    {
        $request->validate([
            'phim_id' => 'required',
            'rap_chieu_phim_id' => 'required',
            'phong_chieu_id' => 'required',
            'thoi_gian_chieu' => 'required|date',
            'gia_ve' => 'required|numeric',
            'trang_thai' => 'required',
        ]);

        $phim = Phims::findOrFail($request->phim_id);
        $start = Carbon::parse($request->thoi_gian_chieu);
        $end = $start->copy()->addMinutes(($phim->thoi_luong ?? 90) + self::THOI_GIAN_DON_PHONG);

        $suatChieu->update([
            'phim_id' => $request->phim_id,
            'rap_chieu_phim_id' => $request->rap_chieu_phim_id,
            'phong_chieu_id' => $request->phong_chieu_id,
            'thoi_gian_chieu' => $start,
            'thoi_gian_ket_thuc' => $end,
            'gia_ve' => $request->gia_ve,
            'trang_thai' => $request->trang_thai,
        ]);

        $this->ghiNhatKy($request, 'Cập nhật suất chiếu', 'Quản lý lịch chiếu', $suatChieu->id);

        AdminNotificationService::push(
            '✏️ Cập nhật suất chiếu',
            "ID {$suatChieu->id}",
            'Info'
        );

        return back()->with('success', 'Cập nhật thành công');
    }

    public function destroy(SuatChieu $suatChieu)
    {
        if ($suatChieu->thoi_gian_chieu <= now()) {
            return back()->with('error', 'Không thể xóa suất đã chiếu');
        }

        $id = $suatChieu->id;
        $suatChieu->delete();

        $this->ghiNhatKy(request(), 'Xóa suất chiếu', 'Quản lý lịch chiếu', $id);

        AdminNotificationService::push(
            '🗑️ Xóa suất chiếu',
            "ID {$id}",
            'Warning'
        );

        return back()->with('success', 'Xóa thành công');
    }

    private function kiemTraXungDotLich($phongId, $start, $end): bool
    {
        return SuatChieu::where('phong_chieu_id', $phongId)
            ->where('trang_thai', '!=', 'huy')
            ->where(function ($q) use ($start, $end) {
                $q->where('thoi_gian_chieu', '<', $end)
                  ->where('thoi_gian_ket_thuc', '>', $start);
            })->exists();
    }

    private function tinhGiaVeTuDong($start, $room): float
    {
        $price = 75000;

        if (in_array($start->dayOfWeek, [5, 6, 0])) {
            $price = 120000;
        }

        if ($room->loai_phong === 'imax') $price += 50000;
        if ($room->loai_phong === '4dx') $price += 70000;

        return $price;
    }

    private function sinhMaGheTuDongChoSuatChieu(SuatChieu $suat): void
    {
        $suat->load('phongChieu.hangGhes.gheNgois');
    }

    private function xacDinhTrangThaiBanDau($start, $end): string
    {
        $now = now();

        if ($now->lt($start)) return self::TRANG_THAI_SAP_CHIEU;
        if ($now->between($start, $end)) return self::TRANG_THAI_DANG_CHIEU;

        return self::TRANG_THAI_DA_CHIEU;
    }
}