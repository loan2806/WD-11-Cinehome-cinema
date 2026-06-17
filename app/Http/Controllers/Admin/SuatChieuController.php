<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phims;
use App\Models\PhongChieu;
use App\Models\RapChieuPhim;
use App\Models\SuatChieu;
use App\Services\SeatGeneratorService;
use App\Traits\Loggable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuatChieuController extends Controller
{
    use Loggable;

    private const THOI_GIAN_DON_PHONG = 15; // Phút dọn rạp giãn cách giữa các suất

    // Bảo lưu danh sách hằng số trạng thái phục vụ hệ thống nhật ký vận hành
    private const TRANG_THAI_SAP_RA_MAT = 'sap_ra_mat';
    private const TRANG_THAI_SAP_CHIEU = 'sap_chieu';
    private const TRANG_THAI_DANG_CHIEU = 'dang_chieu';
    private const TRANG_THAI_DA_CHIEU = 'da_chieu';
    private const TRANG_THAI_HUY = 'huy';

    /**
     * 1. TRANG DANH SÁCH SUẤT CHIẾU (Đã tối ưu phân trang theo đầu Phim cho Dropdown UI)
     */
    public function index(Request $request): View
    {
        $movieQuery = Phims::query();

        // Khởi tạo bộ lọc đóng dành riêng cho các suất chiếu bên trong phim
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
            $query->orderBy('thoi_gian_chieu', 'asc'); // Giờ chiếu sớm xếp lên trước
        };

        if ($request->filled('phim_id')) {
            $movieQuery->where('id', $request->phim_id);
        }

        // Lọc danh sách phim dựa trên điều kiện của suất chiếu
        $movieQuery->whereHas('showtimes', function($q) use ($filterSuatChieu) {
            $filterSuatChieu($q);
        })->with(['showtimes' => $filterSuatChieu]);

        // Phân trang 5 phim để giữ giao diện Admin cân đối với Sidebar
        $phimsPhanTrang = $movieQuery->orderBy('ten_phim', 'asc')
            ->paginate(5)
            ->withQueryString();

        $phims = Phims::orderBy('ten_phim')->get();
        $phongChieus = PhongChieu::with('rapChieuPhim')->orderBy('ten_phong')->get();

        return view('admin.suat-chieus.index', compact('phimsPhanTrang', 'phims', 'phongChieus'));
    }

    /**
     * 2. GIAO DIỆN THÊM MỚI SUẤT CHIẾU
     */
    public function create(Request $request): View
    {
        $phims = Phims::orderBy('ten_phim')->get();
        $rapChieuPhims = RapChieuPhim::orderBy('ten_rap')->get();
        $phongChieus = PhongChieu::with('rapChieuPhim')->where('trang_thai', 'hoat_dong')->orderBy('ten_phong')->get();
        $phongChieuId = $request->phong_chieu_id;

        return view('admin.suat-chieus.create', compact('phims', 'rapChieuPhims', 'phongChieus', 'phongChieuId'));
    }

    /**
     * 3. XỬ LÝ LƯU DỮ LIỆU (Hỗ trợ cấu hình Đơn lẻ & Chuỗi hàng loạt tự động)
     */
    public function store(Request $request)
    {
        // 1. Cấu hình Validate dữ liệu đầu vào (Thêm trường giá vé tùy chỉnh)
        $request->validate([
            'phim_id' => 'required|exists:phims,id',
            'rap_chieu_phim_id' => 'required|exists:rap_chieu_phims,id',
            'phong_chieu_id' => 'required|exists:phong_chieus,id',
            'loai_tao' => 'required|in:don_le,hang_loat',
            // Tạo đơn lẻ (Tách rời ngày và giờ rõ ràng để bắt cặp với UI mới)
            'ngay_chieu_don_le' => 'required_if:loai_tao,don_le|nullable|date',
            'gio_chieu_don_le' => 'required_if:loai_tao,don_le|nullable|string',
            // Tạo hàng loạt
            'ngay_bat_dau' => 'required_if:loai_tao,hang_loat|nullable|date',
            'ngay_ket_thuc' => 'required_if:loai_tao,hang_loat|nullable|date|after_or_equal:ngay_bat_dau',
            'khung_gio' => 'required_if:loai_tao,hang_loat|nullable|array',
            'khung_gio.*' => 'string',
            // Trường giá vé cấu hình thủ công cho ngày lễ / sự kiện đặc biệt
            'gia_ve_tuy_chinh' => 'nullable|numeric|min:0',
        ]);

        $phim = Phims::findOrFail($request->phim_id);
        $phongChieu = PhongChieu::findOrFail($request->phong_chieu_id);
        $thoiLuongPhim = $phim->thoi_luong ?? 90;

        $ketQuaScript = ['thanh_cong' => 0, 'that_bai' => 0];

        // TRƯỜNG HỢP A: TẠO SUẤT CHIẾU ĐƠN LẺ
        if ($request->loai_tao === 'don_le') {
            // Hợp nhất Ngày và Giờ chọn từ UI thành chuỗi Carbon hoàn chỉnh
            $thoiGianChieu = Carbon::parse($request->ngay_chieu_don_le . ' ' . $request->gio_chieu_don_le);
            $thoiGianKetThucChiemDung = $thoiGianChieu->copy()->addMinutes($thoiLuongPhim + self::THOI_GIAN_DON_PHONG);

            if ($this->kiemTraXungDotLich($request->phong_chieu_id, $thoiGianChieu, $thoiGianKetThucChiemDung)) {
                return redirect()->back()->withInput()->with('error', 'Không thể tạo! Khung giờ này đã bị trùng lịch với một suất chiếu khác cùng phòng.');
            }

            // 💡 CHỐT LOGIC GIÁ VÉ: Ưu tiên giá tùy chỉnh nhập tay, nếu không có mới chạy ma trận tự động
            $giaVeCuoiCung = $request->filled('gia_ve_tuy_chinh') ? $request->gia_ve_tuy_chinh : $this->tinhGiaVeTuDong($thoiGianChieu, $phongChieu);

            $suatChieu = SuatChieu::create([
                'phim_id' => $request->phim_id,
                'rap_chieu_phim_id' => $request->rap_chieu_phim_id,
                'phong_chieu_id' => $request->phong_chieu_id,
                'thoi_gian_chieu' => $thoiGianChieu,
                'thoi_luong' => $thoiLuongPhim,
                'thoi_gian_ket_thuc' => $thoiGianKetThucChiemDung,
                'gia_ve' => $giaVeCuoiCung,
                'trang_thai' => $this->xacDinhTrangThaiBanDau($thoiGianChieu, $thoiGianKetThucChiemDung),
            ]);

            $this->sinhMaGheTuDongChoSuatChieu($suatChieu);

            // Ghi nhật ký hệ thống từ nhánh main
            $this->ghiNhatKy($request, 'Thêm suất chiếu', 'Quản lý phim & lịch chiếu', "Thêm suất chiếu đơn lẻ cho phim: {$phim->ten_phim}");

            return redirect()->route('admin.suat-chieus.index')->with('success', 'Tạo suất chiếu đơn lẻ thành công.');
        }

        // TRƯỜNG HỢP B: TẠO CHUỖI LỊCH SUẤT CHIẾU HÀNG LOẠT
        $ngayBatDau = Carbon::parse($request->ngay_bat_dau);
        $ngayKetThuc = Carbon::parse($request->ngay_ket_thuc);

        for ($ngayQuet = $ngayBatDau->copy(); $ngayQuet->lte($ngayKetThuc); $ngayQuet->addDay()) {
            foreach ($request->khung_gio as $gioChieu) {
                $thoiGianChieu = Carbon::parse($ngayQuet->format('Y-m-d') . ' ' . $gioChieu);
                $thoiGianKetThucChiemDung = $thoiGianChieu->copy()->addMinutes($thoiLuongPhim + self::THOI_GIAN_DON_PHONG);

                if ($this->kiemTraXungDotLich($request->phong_chieu_id, $thoiGianChieu, $thoiGianKetThucChiemDung)) {
                    $ketQuaScript['that_bai']++;
                    continue;
                }

                $giaVeCuoiCung = $request->filled('gia_ve_tuy_chinh') ? $request->gia_ve_tuy_chinh : $this->tinhGiaVeTuDong($thoiGianChieu, $phongChieu);

                $suatChieu = SuatChieu::create([
                    'phim_id' => $request->phim_id,
                    'rap_chieu_phim_id' => $request->rap_chieu_phim_id,
                    'phong_chieu_id' => $request->phong_chieu_id,
                    'thoi_gian_chieu' => $thoiGianChieu,
                    'thoi_luong' => $thoiLuongPhim,
                    'thoi_gian_ket_thuc' => $thoiGianKetThucChiemDung,
                    'gia_ve' => $giaVeCuoiCung,
                    'trang_thai' => $this->xacDinhTrangThaiBanDau($thoiGianChieu, $thoiGianKetThucChiemDung),
                ]);

                $this->sinhMaGheTuDongChoSuatChieu($suatChieu);
                $ketQuaScript['thanh_cong']++;
            }
        }

        // Hợp nhất ghi log nghiệp vụ từ main và thông báo tiến trình của feature/vietanh
        $this->ghiNhatKy($request, 'Thêm suất chiếu', 'Quản lý phim & lịch chiếu', "Thêm chuỗi gồm {$ketQuaScript['thanh_cong']} suất chiếu cho phim: {$phim->ten_phim}");

        $msg = "Xử lý hàng loạt hoàn tất! Đã tạo thành công " . $ketQuaScript['thanh_cong'] . " suất chiếu.";
        if ($ketQuaScript['that_bai'] > 0) {
            return redirect()->route('admin.suat-chieus.index')->with('success', $msg)->with('warning', "Bỏ qua " . $ketQuaScript['that_bai'] . " suất do trùng lịch phòng.");
        }
        return redirect()->route('admin.suat-chieus.index')->with('success', $msg);
    }

    /**
     * 4. XEM CHI TIẾT SƠ ĐỒ GHẾ NGỒI CỦA SUẤT CHIẾU
     */
    public function show(SuatChieu $suatChieu): View
    {
        return view('admin.suat-chieus.show', compact('suatChieu'));
    }

    /**
     * 5. GIAO DIỆN CHỈNH SỬA THAM SỐ SUẤT CHIẾU
     */
    public function edit(SuatChieu $suatChieu): View
    {
        $phims = Phims::orderBy('ten_phim')->get();
        $rapChieuPhims = RapChieuPhim::orderBy('ten_rap')->get();
        $phongChieus = PhongChieu::with('rapChieuPhim')->where('trang_thai', 'hoat_dong')->orderBy('ten_phong')->get();

        return view('admin.suat-chieus.edit', compact('suatChieu', 'phims', 'rapChieuPhims', 'phongChieus'));
    }

    /**
     * 6. CẬP NHẬT THÔNG TIN SUẤT CHIẾU ĐƠN LẺ
     */
    public function update(Request $request, SuatChieu $suatChieu)
    {
        $request->validate([
            'phim_id' => 'required|exists:phims,id',
            'rap_chieu_phim_id' => 'required|exists:rap_chieu_phims,id',
            'phong_chieu_id' => 'required|exists:phong_chieus,id',
            'thoi_gian_chieu' => 'required|date',
            'gia_ve' => 'required|numeric|min:0',
            'trang_thai' => 'required|string',
        ]);

        $phim = Phims::findOrFail($request->phim_id);
        $thoiLuongPhim = $phim->thoi_luong ?? 90;

        $thoiGianChieu = Carbon::parse($request->thoi_gian_chieu);
        $thoiGianKetThucChiemDung = $thoiGianChieu->copy()->addMinutes($thoiLuongPhim + self::THOI_GIAN_DON_PHONG);

        $xungDot = SuatChieu::where('phong_chieu_id', $request->phong_chieu_id)
            ->where('id', '!=', $suatChieu->id)
            ->where('trang_thai', '!=', 'huy')
            ->where(function ($query) use ($thoiGianChieu, $thoiGianKetThucChiemDung) {
                $query->where('thoi_gian_chieu', '<', $thoiGianKetThucChiemDung)
                      ->where('thoi_gian_ket_thuc', '>', $thoiGianChieu);
            })->exists();

        if ($xungDot) {
            return redirect()->back()->withInput()->with('error', 'Cập nhật thất bại! Khung giờ mới bị trùng với lịch của suất chiếu khác.');
        }

        $suatChieu->update([
            'phim_id' => $request->phim_id,
            'rap_chieu_phim_id' => $request->rap_chieu_phim_id,
            'phong_chieu_id' => $request->phong_chieu_id,
            'thoi_gian_chieu' => $thoiGianChieu,
            'thoi_luong' => $thoiLuongPhim,
            'thoi_gian_ket_thuc' => $thoiGianKetThucChiemDung,
            'gia_ve' => $request->gia_ve,
            'trang_thai' => $request->trang_thai,
        ]);

        // Kích hoạt ghi log cập nhật từ main
        $this->ghiNhatKy($request, 'Cập nhật suất chiếu', 'Quản lý phim & lịch chiếu', "Cập nhật suất chiếu #{$suatChieu->id}");

        return redirect()->route('admin.suat-chieus.index')->with('success', 'Cập nhật dữ liệu suất chiếu thành công.');
    }

    /**
     * 7. XÓA SUẤT CHIẾU KHỎI HỆ THỐNG
     */
    public function destroy(SuatChieu $suatChieu)
    {
        $idBackup = $suatChieu->id;
        $suatChieu->delete();

        // Đồng bộ Log của nhánh main vào hàm xóa
        $this->ghiNhatKy(request(), 'Xóa suất chiếu', 'Quản lý phim & lịch chiếu', "Xóa thành công suất chiếu #{$idBackup}");

        return redirect()->route('admin.suat-chieus.index')->with('success', 'Xóa suất chiếu thành công.');
    }

    /**
     * THUẬT TOÁN BỔ TRỢ: Kiểm tra xung đột lịch rạp phim
     */
    private function kiemTraXungDotLich($phongChieuId, $thoiGianChieu, $thoiGianKetThuc): bool
    {
        return SuatChieu::where('phong_chieu_id', $phongChieuId)
            ->where('trang_thai', '!=', 'huy')
            ->where(function ($query) use ($thoiGianChieu, $thoiGianKetThuc) {
                $query->where('thoi_gian_chieu', '<', $thoiGianKetThuc)
                      ->where('thoi_gian_ket_thuc', '>', $thoiGianChieu);
            })->exists();
    }

    /**
     * THUẬT TOÁN BỔ TRỢ: Tính giá vé sàn tự động
     */
    private function tinhGiaVeTuDong(Carbon $thoiGianChieu, PhongChieu $phongChieu): float
    {
        $giaCoBas = 75000;
        if (in_array($thoiGianChieu->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY, Carbon::SUNDAY])) {
            $giaCoBas = 120000;
        }

        switch (strtolower($phongChieu->loai_phong)) {
            case '3d':   $giaCoBas += 20000; break;
            case 'imax': $giaCoBas += 50000; break;
            case '4dx':  $giaCoBas += 70000; break;
        }

        if ($thoiGianChieu->hour >= 22 || $thoiGianChieu->hour < 4) {
            $giaCoBas += 15000;
        }

        return $giaCoBas;
    }

    /**
     * THUẬT TOÁN BỔ TRỢ: Nhân bản ghế trống cho suất chiếu mới
     */
    private function sinhMaGheTuDongChoSuatChieu(SuatChieu $suatChieu): void
    {
        $suatChieu->load('phongChieu.hangGhes.gheNgois');
    }

    /**
     * THUẬT TOÁN BỔ TRỢ: Phân tích mốc thời gian thực để gán trạng thái ban đầu
     */
    private function xacDinhTrangThaiBanDau($thoiGianChieu, $thoiGianKetThuc): string
    {
        $now = Carbon::now();
        if ($now->lt($thoiGianChieu)) {
            return SuatChieu::TRANG_THAI_SAP_CHIEU;
        }
        if ($now->gte($thoiGianChieu) && $now->lt($thoiGianKetThuc)) {
            return SuatChieu::TRANG_THAI_DANG_CHIEU;
        }
        return SuatChieu::TRANG_THAI_DA_CHIEU;
    }
}