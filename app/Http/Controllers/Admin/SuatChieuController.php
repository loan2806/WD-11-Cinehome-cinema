<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSuatChieuRequest;
use App\Http\Requests\Admin\UpdateSuatChieuRequest;
use App\Models\Phims;
use App\Models\PhongChieu;
use App\Models\RapChieuPhim;
use App\Models\SuatChieu;
use App\Services\SeatGeneratorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuatChieuController extends Controller
{
    private const THOI_GIAN_DON_PHONG = 15;

    private const TRANG_THAI_SAP_CHIEU = 'sap_chieu';
    private const TRANG_THAI_DANG_CHIEU = 'dang_chieu';
    private const TRANG_THAI_DA_CHIEU = 'da_chieu';
    private const TRANG_THAI_HUY = 'huy';

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = SuatChieu::with(['phim', 'rapChieuPhim', 'phongChieu']);

        if ($request->has('phim_id') && $request->phim_id) {
            $query->where('phim_id', $request->phim_id);
        }

        if ($request->has('trang_thai') && $request->trang_thai) {
            $query->where('trang_thai', $request->trang_thai);
        }

        if ($request->has('phong_chieu_id') && $request->phong_chieu_id) {
            $query->where('phong_chieu_id', $request->phong_chieu_id);
        }

        if ($request->has('ngay_chieu') && $request->ngay_chieu) {
            $query->whereDate('thoi_gian_chieu', $request->ngay_chieu);
        }

        $suatChieus = $query->orderByDesc('thoi_gian_chieu')
            ->paginate(15)
            ->withQueryString();

        // Lấy tất cả phim để filter (kể cả phim chưa có suất chiếu)
        $phims = Phims::orderBy('ten_phim')->get();

        $phongChieus = PhongChieu::with('rapChieuPhim')
            ->orderBy('ten_phong')
            ->get();

        return view('admin.suat-chieus.index', compact(
            'suatChieus',
            'phims',
            'phongChieus'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        // Lấy tất cả phim để tạo suất chiếu (kể cả phim chưa có suất chiếu)
        $phims = Phims::orderBy('ten_phim')->get();

        $rapChieuPhims = RapChieuPhim::orderBy('ten_rap')->get();

        $phongChieus = PhongChieu::with('rapChieuPhim')
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSuatChieuRequest $request)
    {
        $data = $request->validated();

        $phim = Phims::findOrFail($data['phim_id']);
        $phongChieu = PhongChieu::findOrFail($data['phong_chieu_id']);

        $thoiGianChieu = Carbon::parse($data['thoi_gian_chieu']);
        $thoiLuong = $phim->thoi_luong ?? 120;
        $thoiGianKetThuc = $thoiGianChieu->copy()->addMinutes($thoiLuong + self::THOI_GIAN_DON_PHONG);

        $data['thoi_luong'] = $thoiLuong;
        $data['thoi_gian_ket_thuc'] = $thoiGianKetThuc;

        $trangThai = $this->xacDinhTrangThai($thoiGianChieu, $thoiGianKetThuc);
        $data['trang_thai'] = $data['trang_thai'] ?? $trangThai;

        $suatChieu = SuatChieu::create($data);

        return redirect()
            ->route('admin.suat-chieus.index')
            ->with('success', 'Suất chiếu đã được tạo thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SuatChieu $suatChieu): View
    {
        $suatChieu->load(['phim', 'rapChieuPhim', 'phongChieu.hangGhes.gheNgois.loaiGhe']);

        $seatMap = [];
        $soHang = 0;
        $soCot = 0;

        if ($suatChieu->phongChieu) {
            $soHang = $suatChieu->phongChieu->hangGhes->count();
            $soCot = $suatChieu->phongChieu->gheNgois->max('cot') ?? 0;
            $seatMap = app(SeatGeneratorService::class)->getSeatMap($suatChieu->phongChieu);
        }

        return view('admin.suat-chieus.show', compact(
            'suatChieu',
            'seatMap',
            'soHang',
            'soCot'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SuatChieu $suatChieu): View
    {
        $phims = Phims::orderBy('ten_phim')->get();

        $rapChieuPhims = RapChieuPhim::orderBy('ten_rap')->get();

        $phongChieus = PhongChieu::with('rapChieuPhim')
            ->orderBy('ten_phong')
            ->get();

        return view('admin.suat-chieus.edit', compact(
            'suatChieu',
            'phims',
            'rapChieuPhims',
            'phongChieus'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSuatChieuRequest $request, SuatChieu $suatChieu)
    {
        $data = $request->validated();

        $phim = Phims::findOrFail($data['phim_id']);
        $thoiGianChieu = Carbon::parse($data['thoi_gian_chieu']);
        $thoiLuong = $phim->thoi_luong ?? 120;
        $thoiGianKetThuc = $thoiGianChieu->copy()->addMinutes($thoiLuong + self::THOI_GIAN_DON_PHONG);

        $data['thoi_luong'] = $thoiLuong;
        $data['thoi_gian_ket_thuc'] = $thoiGianKetThuc;

        if (empty($data['trang_thai'])) {
            $data['trang_thai'] = $this->xacDinhTrangThai($thoiGianChieu, $thoiGianKetThuc);
        }

        $suatChieu->update($data);

        return redirect()
            ->route('admin.suat-chieus.index')
            ->with('success', 'Suất chiếu đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SuatChieu $suatChieu)
    {
        if ($suatChieu->thoi_gian_chieu <= Carbon::now()) {
            return redirect()
                ->route('admin.suat-chieus.index')
                ->with('error', 'Không thể xóa suất chiếu đã chiếu.');
        }

        $suatChieu->delete();

        return redirect()
            ->route('admin.suat-chieus.index')
            ->with('success', 'Suất chiếu đã được xóa thành công.');
    }

    /**
     * Get showtimes for a room on a specific date (AJAX).
     */
    public function getByRoomAndDate(Request $request)
    {
        $request->validate([
            'phong_chieu_id' => 'required|exists:phong_chieus,id',
            'ngay_chieu' => 'required|date',
        ]);

        $suatChieus = SuatChieu::where('phong_chieu_id', $request->phong_chieu_id)
            ->whereDate('thoi_gian_chieu', $request->ngay_chieu)
            ->with('phim')
            ->orderBy('thoi_gian_chieu')
            ->get();

        return response()->json($suatChieus);
    }

    /**
     * Get available rooms for a cinema (AJAX).
     */
    public function getAvailableRooms(Request $request)
    {
        $request->validate([
            'rap_chieu_phim_id' => 'required|exists:rap_chieu_phims,id',
        ]);

        $phongChieus = PhongChieu::where('rap_chieu_phim_id', $request->rap_chieu_phim_id)
            ->where('trang_thai', 'hoat_dong')
            ->withCount('gheNgois')
            ->orderBy('ten_phong')
            ->get();

        return response()->json($phongChieus);
    }

    private function xacDinhTrangThai($thoiGianChieu, $thoiGianKetThuc): string
    {
        $now = Carbon::now();

        if ($now < $thoiGianChieu) {
            return self::TRANG_THAI_SAP_CHIEU;
        }

        if ($now >= $thoiGianChieu && $now < $thoiGianKetThuc) {
            return self::TRANG_THAI_DANG_CHIEU;
        }

        return self::TRANG_THAI_DA_CHIEU;
    }
}
