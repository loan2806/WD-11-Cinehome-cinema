<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phims;
use App\Models\PhongChieu;
use App\Models\RapChieuPhim;
use App\Models\SuatChieu;
use App\Models\CaiDatHeThong;
use App\Traits\Loggable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuatChieuController extends Controller
{
    use Loggable;

    /**
     * Danh sách ngày lễ cố định tại Việt Nam (Định dạng: Tháng-Ngày)
     */
    private $cacNgayLe = [
        '01-01' => 'Tết Dương Lịch',
        '04-30' => 'Ngày Giải Phóng Miền Nam',
        '05-01' => 'Ngày Quốc Tế Lao Động',
        '09-02' => 'Ngày Quốc Khánh',
        '09-03' => 'Ngày Quốc Khánh (Ngày gối đầu)',
    ];

    /**
     * 1. TRANG DANH SÁCH SUẤT CHIẾU
     */
    public function index(Request $request): View
    {
        $now = Carbon::now();

        // Đóng toàn bộ suất chiếu đã kết thúc
        SuatChieu::where('trang_thai', '!=', 'huy')
            ->where('trang_thai', '!=', 'da_chieu')
            ->where('thoi_gian_ket_thuc', '<=', $now)
            ->update(['trang_thai' => 'da_chieu']);

        $activeSuatChieus = SuatChieu::where('trang_thai', '!=', 'huy')
            ->where('trang_thai', '!=', 'da_chieu')
            ->get();

        foreach ($activeSuatChieus as $sc) {
            $realStatus = $this->xacDinhTrangThaiBanDau(Carbon::parse($sc->thoi_gian_chieu), Carbon::parse($sc->thoi_gian_ket_thuc));
            if ($sc->trang_thai !== $realStatus) {
                $sc->update(['trang_thai' => $realStatus]);
            }
        }

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

        // SỬA LỖI: Bỏ hẳn whereHas bắt buộc để phim mới (chưa có suất) vẫn hiển thị ra trực quan cho Admin quản lý lịch
        $movieQuery->with(['showtimes' => $filterSuatChieu]);

        $phimsPhanTrang = $movieQuery->orderBy('ten_phim', 'asc')->paginate(5)->withQueryString();
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

        $settings = CaiDatHeThong::first();
        $thoiGianDonPhong = $settings ? $settings->thoi_gian_don_phong : 15;

        return view('admin.suat-chieus.create', compact('phims', 'rapChieuPhims', 'phongChieus', 'phongChieuId', 'thoiGianDonPhong'));
    }

    /**
     * 3. XỬ LÝ LƯU DỮ LIỆU SUẤT CHIẾU MỚI
     */
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
            'khung_gio.*' => 'string',
            'gia_ve_tuy_chinh' => 'nullable|numeric|min:0',
            'gia_ve_ngay_le' => 'nullable|numeric|min:0', // Thêm biến hứng giá ngày lễ riêng
        ]);

        $settings = CaiDatHeThong::first();
        $thoiGianDonPhong = $settings ? $settings->thoi_gian_don_phong : 15;

        $phim = Phims::findOrFail($request->phim_id);
        $phongChieu = PhongChieu::findOrFail($request->phong_chieu_id);
        
        // Bảo vệ thời lượng phim phòng hờ phim mới nhập thiếu dữ liệu trống
        $thoiLuongPhim = ((int)$phim->thoi_luong > 0) ? (int)$phim->thoi_luong : 90;

        // XỬ LÝ TẠO ĐƠN LẺ
        if ($request->loai_tao === 'don_le') {
            $thoiGianChieu = Carbon::parse($request->ngay_chieu_don_le . ' ' . $request->gio_chieu_don_le);
            $thoiGianKetThucChiemDung = $thoiGianChieu->copy()->addMinutes($thoiLuongPhim + $thoiGianDonPhong);

            // KIỂM TRA TRÙNG LỊCH CHI TIẾT
            $suatChieuTrung = $this->layDanhSachSuatChieuTrung($request->phong_chieu_id, $thoiGianChieu, $thoiGianKetThucChiemDung);
            
            if ($suatChieuTrung->isNotEmpty()) {
                return redirect()->back()->withInput()->with('suat_chieu_trung_danh_sach', $suatChieuTrung);
            }

            // Kiểm tra áp giá ngày lễ hoặc tùy chỉnh
            $giaVeCuoiCung = $request->filled('gia_ve_tuy_chinh') ? $request->gia_ve_tuy_chinh : $this->tinhGiaVeTuDong($thoiGianChieu, $phongChieu, $settings);
            if ($this->isNgayLe($thoiGianChieu) && $request->filled('gia_ve_ngay_le')) {
                $giaVeCuoiCung = $request->gia_ve_ngay_le;
            }

            SuatChieu::create([
                'phim_id' => $request->phim_id,
                'rap_chieu_phim_id' => $request->rap_chieu_phim_id,
                'phong_chieu_id' => $request->phong_chieu_id,
                'thoi_gian_chieu' => $thoiGianChieu,
                'thoi_luong' => $thoiLuongPhim,
                'thoi_gian_ket_thuc' => $thoiGianKetThucChiemDung,
                'gia_ve' => $giaVeCuoiCung,
                'trang_thai' => $this->xacDinhTrangThaiBanDau($thoiGianChieu, $thoiGianKetThucChiemDung),
            ]);

            $this->ghiNhatKy($request, 'Thêm suất chiếu', 'Quản lý phim & lịch chiếu', "Thêm suất chiếu đơn lẻ cho phim: {$phim->ten_phim}");
            return redirect()->route('admin.suat-chieus.index')->with('success', 'Tạo suất chiếu đơn lẻ thành công.');
        }

        // XỬ LÝ TẠO HÀNG LOẠT
        $ngayBatDau = Carbon::parse($request->ngay_bat_dau);
        $ngayKetThuc = Carbon::parse($request->ngay_ket_thuc);
        $tatCaSuatChieuTrungHangLoat = collect();
        $suatChieuCanTao = [];

        for ($ngayQuet = $ngayBatDau->copy(); $ngayQuet->lte($ngayKetThuc); $ngayQuet->addDay()) {
            foreach ($request->khung_gio as $gioChieu) {
                $thoiGianChieu = Carbon::parse($ngayQuet->format('Y-m-d') . ' ' . $gioChieu);
                $thoiGianKetThucChiemDung = $thoiGianChieu->copy()->addMinutes($thoiLuongPhim + $thoiGianDonPhong);

                $suatChieuTrung = $this->layDanhSachSuatChieuTrung($request->phong_chieu_id, $thoiGianChieu, $thoiGianKetThucChiemDung);
                
                if ($suatChieuTrung->isNotEmpty()) {
                    foreach ($suatChieuTrung as $scTrung) {
                        $tatCaSuatChieuTrungHangLoat->put($scTrung->id, $scTrung);
                    }
                    continue; // Bỏ qua suất chiếu lỗi lịch này để quét suất tiếp theo
                }

                $giaVeCuoiCung = $request->filled('gia_ve_tuy_chinh') ? $request->gia_ve_tuy_chinh : $this->tinhGiaVeTuDong($thoiGianChieu, $phongChieu, $settings);
                if ($this->isNgayLe($thoiGianChieu) && $request->filled('gia_ve_ngay_le')) {
                    $giaVeCuoiCung = $request->gia_ve_ngay_le;
                }

                $suatChieuCanTao[] = [
                    'phim_id' => $request->phim_id,
                    'rap_chieu_phim_id' => $request->rap_chieu_phim_id,
                    'phong_chieu_id' => $request->phong_chieu_id,
                    'thoi_gian_chieu' => $thoiGianChieu,
                    'thoi_luong' => $thoiLuongPhim,
                    'thoi_gian_ket_thuc' => $thoiGianKetThucChiemDung,
                    'gia_ve' => $giaVeCuoiCung,
                    'trang_thai' => $this->xacDinhTrangThaiBanDau($thoiGianChieu, $thoiGianKetThucChiemDung),
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        // Nếu có bất kỳ suất nào bị trùng lịch khi rải chuỗi, chặn và xuất danh sách
        if ($tatCaSuatChieuTrungHangLoat->isNotEmpty()) {
            return redirect()->back()->withInput()->with('suat_chieu_trung_danh_sach', $tatCaSuatChieuTrungHangLoat);
        }

        // Nếu sạch lịch hoàn toàn thì tiến hành tạo
        foreach ($suatChieuCanTao as $data) {
            SuatChieu::create($data);
        }

        $this->ghiNhatKy($request, 'Thêm suất chiếu', 'Quản lý phim & lịch chiếu', "Thêm chuỗi suất chiếu cho phim: {$phim->ten_phim}");
        return redirect()->route('admin.suat-chieus.index')->with('success', "Đã tạo thành công " . count($suatChieuCanTao) . " suất chiếu.");
    }

    /**
     * Hàm kiểm tra xem ngày chọn có phải ngày lễ Việt Nam không
     */
    private function isNgayLe(Carbon $date): bool 
    {
        return array_key_exists($date->format('m-d'), $this->cacNgayLe);
    }

    /**
     * Lấy danh sách các suất chiếu đang đè lịch lên khung giờ được chọn
     */
    private function layDanhSachSuatChieuTrung($phongChieuId, $thoiGianChieu, $thoiGianKetThuc)
    {
        return SuatChieu::where('phong_chieu_id', $phongChieuId)
            ->where('trang_thai', '!=', 'huy')
            ->where(function ($query) use ($thoiGianChieu, $thoiGianKetThuc) {
                $query->where('thoi_gian_chieu', '<', $thoiGianKetThuc)
                      ->where('thoi_gian_ket_thuc', '>', $thoiGianChieu);
            })
            ->with(['phim', 'phongChieu'])
            ->get();
    }

    /**
     * Sao chép giữ nguyên hàm cũ của bạn dưới này...
     */
    public function show(SuatChieu $suatChieu): \Illuminate\View\View {
        $suatChieu->load(['phim', 'rapChieuPhim', 'phongChieu.hangGhes', 'phongChieu.gheNgois.loaiGhe']);
        $chuoiGheDaDats = \DB::table('ve_xem_phims')->where('suat_chieu_id', $suatChieu->id)->whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung'])->pluck('ma_ghe')->toArray();
        $danhSachMaGheDaDat = [];
        foreach ($chuoiGheDaDats as $chuoiGhe) {
            if (!empty($chuoiGhe)) {
                $mangGhe = explode(',', $chuoiGhe);
                foreach ($mangGhe as $tenGhe) { $danhSachMaGheDaDat[] = trim($tenGhe); }
            }
        }
        $danhSachMaGheDaDat = array_unique($danhSachMaGheDaDat);
        $seatMap = [];
        foreach ($suatChieu->phongChieu->gheNgois as $g) {
            $tHang = $g->hangGhe->ten_hang ?? '';
            $vCot = (int)($g->vi_tri_cot ?? 1);
            $trangThaiThucTe = 'trong'; 
            if (in_array($g->ma_ghe, $danhSachMaGheDaDat)) { $trangThaiThucTe = 'da_dat'; }
            elseif ($g->trang_thai === 'bao_tri') { $trangThaiThucTe = 'bao_tri'; }
            if ($tHang) {
                $seatMap[$tHang][$vCot] = [
                    'ma_ghe' => $g->ma_ghe,
                    'loai_ghe' => $g->loaiGhe->ten_loai ?? 'Regular',
                    'trang_thai' => $trangThaiThucTe, 
                    'is_couple' => ($g->loaiGhe->ten_loai ?? '') === 'Couple',
                    'cot_end' => $g->cot_end ?? null
                ];
            }
        }
        $soCot = (int)$suatChieu->phongChieu->gheNgois->max('vi_tri_cot') ?: 12;
        return view('admin.suat-chieus.show', compact('suatChieu', 'seatMap', 'soCot'));
    }

    public function edit(SuatChieu $suatChieu): View {
        $phims = Phims::orderBy('ten_phim')->get();
        $rapChieuPhims = RapChieuPhim::orderBy('ten_rap')->get();
        $phongChieus = PhongChieu::with('rapChieuPhim')->where('trang_thai', 'hoat_dong')->orderBy('ten_phong')->get();
        $settings = CaiDatHeThong::first();
        $thoiGianDonPhong = $settings ? $settings->thoi_gian_don_phong : 15;
        return view('admin.suat-chieus.edit', compact('suatChieu', 'phims', 'rapChieuPhims', 'phongChieus', 'thoiGianDonPhong'));
    }

    public function update(Request $request, SuatChieu $suatChieu) {
        $request->validate(['phim_id' => 'required|exists:phims,id', 'rap_chieu_phim_id' => 'required|exists:rap_chieu_phims,id', 'phong_chieu_id' => 'required|exists:phong_chieus,id', 'ngay_chieu' => 'required|date', 'gio_chieu' => 'required|string', 'gia_ve_tuy_chinh' => 'nullable|numeric|min:0', 'trang_thai' => 'required|in:sap_ra_mat,sap_chieu,dang_chieu,dung_nhan_ve,da_chieu,huy']);
        $settings = CaiDatHeThong::first(); $thoiGianDonPhong = $settings ? $settings->thoi_gian_don_phong : 15;
        $phim = Phims::findOrFail($request->phim_id); $phongChieu = PhongChieu::findOrFail($request->phong_chieu_id); $thoiLuongPhim = $phim->thoi_luong ?? 90;
        $thoiGianChieu = Carbon::parse($request->ngay_chieu . ' ' . $request->gio_chieu); $thoiGianKetThucChiemDung = $thoiGianChieu->copy()->addMinutes($thoiLuongPhim + $thoiGianDonPhong);
        $coXungDot = SuatChieu::where('phong_chieu_id', $request->phong_chieu_id)->where('id', '!=', $suatChieu->id)->where('trang_thai', '!=', 'huy')->where(function ($query) use ($thoiGianChieu, $thoiGianKetThucChiemDung) { $query->where('thoi_gian_chieu', '<', $thoiGianKetThucChiemDung)->where('thoi_gian_ket_thuc', '>', $thoiGianChieu); })->exists();
        if ($coXungDot) { return redirect()->back()->withInput()->with('error', 'Không thể cập nhật! Khung giờ điều chỉnh đã bị trùng lịch với suất chiếu khác cùng phòng.'); }
        $giaVeCuoiCung = $request->filled('gia_ve_tuy_chinh') ? $request->gia_ve_tuy_chinh : $this->tinhGiaVeTuDong($thoiGianChieu, $phongChieu, $settings);
        $trangThaiCuoi = $request->trang_thai === 'huy' ? 'huy' : $this->xacDinhTrangThaiBanDau($thoiGianChieu, $thoiGianKetThucChiemDung);
        $suatChieu->update(['phim_id' => $request->phim_id, 'rap_chieu_phim_id' => $request->rap_chieu_phim_id, 'phong_chieu_id' => $request->phong_chieu_id, 'thoi_gian_chieu' => $thoiGianChieu, 'thoi_luong' => $thoiLuongPhim, 'thoi_gian_ket_thuc' => $thoiGianKetThucChiemDung, 'gia_ve' => $giaVeCuoiCung, 'trang_thai' => $trangThaiCuoi]);
        $this->ghiNhatKy($request, 'Cập nhật suất chiếu', 'Quản lý phim & lịch chiếu', "Cập nhật suất chiếu đơn lẻ cho phim: {$phim->ten_phim}");
        return redirect()->route('admin.suat-chieus.index')->with('success', 'Cập nhật thông tin suất chiếu thành công.');
    }

    public function destroy(Request $request, SuatChieu $suatChieu) {
        $this->ghiNhatKy($request, 'Xóa suất chiếu', 'Quản lý phim & lịch chiếu', "Xóa suất chiếu ID: {$suatChieu->id}");
        $suatChieu->delete(); return redirect()->route('admin.suat-chieus.index')->with('success', 'Xóa suất chiếu thành công khỏi hệ thống.');
    }

    private function xacDinhTrangThaiBanDau($thoiGianChieu, $thoiGianKetThuc): string {
        $settings = CaiDatHeThong::first(); $phutDangChieu = $settings ? $settings->so_phut_truoc_chieu_dang_chieu : 15; $ngaySapRaMat = $settings ? $settings->so_ngay_truoc_chieu_sap_ra_mat : 30; $phutDongBanVeOnline = 10; $now = Carbon::now();
        $mocDungBanVeOnline = $thoiGianChieu->copy()->subMinutes($phutDongBanVeOnline); $mocChuyenDangChieu = $thoiGianChieu->copy()->subMinutes($phutDangChieu);
        if ($now->gte($thoiGianKetThuc)) return 'da_chieu';
        if ($now->gte($thoiGianChieu) && $now->lt($thoiGianKetThuc)) return 'dang_chieu';
        if ($now->gte($mocDungBanVeOnline) && $now->lt($thoiGianChieu)) return 'dung_nhan_ve';
        if ($now->gte($mocChuyenDangChieu) && $now->lt($mocDungBanVeOnline)) return 'dang_chieu';
        if ($now->diffInDays($thoiGianChieu, false) > $ngaySapRaMat) return 'sap_ra_mat';
        return 'sap_chieu';
    }

    private function tinhGiaVeTuDong(Carbon $thoiGianChieu, PhongChieu $phongChieu, $settings): float {
        $giaNgayThuong = $settings ? $settings->gia_ngay_thuong : 75000; $giaCuoiTuan = $settings ? $settings->gia_cuoi_tuan : 120000; $phuThuVip = $settings ? $settings->phu_thu_ghe_vip : 20000;
        $giaCoBas = in_array($thoiGianChieu->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY, Carbon::SUNDAY]) ? $giaCuoiTuan : $giaNgayThuong;
        if (strtolower($phongChieu->loai_phong) === 'imax' || strtolower($phongChieu->loai_phong) === '4dx') $giaCoBas += $phuThuVip;
        return $giaCoBas;
    }
}