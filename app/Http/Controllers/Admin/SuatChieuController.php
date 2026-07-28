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
use Illuminate\Support\Facades\DB;

class SuatChieuController extends Controller
{
    use Loggable;

    private $cacNgayLe = [
        '01-01' => 'Tết Dương Lịch',
        '04-30' => 'Ngày Giải Phóng Miền Nam',
        '05-01' => 'Ngày Quốc Tế Lao Động',
        '09-02' => 'Ngày Quốc Khánh',
        '09-03' => 'Ngày Quốc Khánh (Ngày gối đầu)',
    ];

    /**
     * 🌟 HÀM INDEX: CHUẨN HÓA LOGIC LỌC SUẤT CHIẾU 100%
     */
    public function index(Request $request): View
    {
        $now = Carbon::now();

        // Cập nhật trạng thái các suất đã chiếu qua thời gian kết thúc
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

        // 1. Lọc trực tiếp theo ID phim nếu được chọn
        if ($request->filled('phim_id')) {
            $movieQuery->where('id', $request->phim_id);
        }

        // 2. LỌC CHUẨN: Chỉ lấy những phim CÓ suất chiếu thỏa mãn điều kiện lọc
        if ($request->filled('phong_chieu_id') || $request->filled('trang_thai') || $request->filled('ngay_chieu')) {
            $movieQuery->whereHas('showtimes', function ($query) use ($request) {
                if ($request->filled('trang_thai')) {
                    $query->where('trang_thai', $request->trang_thai);
                }
                if ($request->filled('phong_chieu_id')) {
                    $query->where('phong_chieu_id', $request->phong_chieu_id);
                }
                if ($request->filled('ngay_chieu')) {
                    $query->whereDate('thoi_gian_chieu', $request->ngay_chieu);
                }
            });
        }

        // 3. Eager load danh sách suất chiếu thỏa mãn bộ lọc
        $movieQuery->with(['showtimes' => function ($query) use ($request) {
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
        }]);

        $phimsPhanTrang = $movieQuery->orderBy('ten_phim', 'asc')->paginate(5)->withQueryString();
        $phims = Phims::orderBy('ten_phim')->get();
        $phongChieus = PhongChieu::with('rapChieuPhim')->orderBy('ten_phong')->get();

        return view('admin.suat-chieus.index', compact('phimsPhanTrang', 'phims', 'phongChieus'));
    }

    public function create(Request $request): View
    {
        $phims = Phims::orderBy('ten_phim')->get();
        $rapMacDinh = RapChieuPhim::first();
        $phongChieus = PhongChieu::where('trang_thai', 'hoat_dong')->orderBy('ten_phong')->get();
        $phongChieuId = $request->phong_chieu_id;

        $settings = CaiDatHeThong::first();
        $thoiGianDonPhong = $settings ? $settings->thoi_gian_don_phong : 15;

        return view('admin.suat-chieus.create', compact('phims', 'rapMacDinh', 'phongChieus', 'phongChieuId', 'thoiGianDonPhong'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'phim_id' => 'required|exists:phims,id',
            'phong_chieu_id' => 'required|exists:phong_chieus,id',
            'loai_tao' => 'required|in:don_le,hang_loat',
            'ngay_chieu_don_le' => 'required_if:loai_tao,don_le|nullable|date',
            'gio_chieu_don_le' => 'required_if:loai_tao,don_le|nullable|string',
            'ngay_bat_dau' => 'required_if:loai_tao,hang_loat|nullable|date',
            'ngay_ket_thuc' => 'required_if:loai_tao,hang_loat|nullable|date|after_or_equal:ngay_bat_dau',
            'khung_gio' => 'required_if:loai_tao,hang_loat|nullable|array',
            'khung_gio.*' => 'string',
            'gia_ve_tuy_chinh' => 'nullable|numeric|min:0',
            'gia_ve_ngay_le' => 'nullable|numeric|min:0',
        ]);

        $settings = CaiDatHeThong::first();
        $thoiGianDonPhong = $settings ? $settings->thoi_gian_don_phong : 15;

        $phim = Phims::findOrFail($request->phim_id);
        $phongChieu = PhongChieu::findOrFail($request->phong_chieu_id);
        $rapChieuId = $phongChieu->rap_chieu_phim_id ?? RapChieuPhim::first()?->id ?? 1;
        
        $thoiLuongPhim = ((int)$phim->thoi_luong > 0) ? (int)$phim->thoi_luong : 90;

        if ($request->loai_tao === 'don_le') {
            $thoiGianChieu = Carbon::parse($request->ngay_chieu_don_le . ' ' . $request->gio_chieu_don_le);
            $thoiGianKetThucChiemDung = $thoiGianChieu->copy()->addMinutes($thoiLuongPhim + $thoiGianDonPhong);

            $suatChieuTrung = $this->layDanhSachSuatChieuTrung($request->phong_chieu_id, $thoiGianChieu, $thoiGianKetThucChiemDung);
            
            if ($suatChieuTrung->isNotEmpty()) {
                return redirect()->back()->withInput()->with('suat_chieu_trung_danh_sach', $suatChieuTrung);
            }

            $giaVeCuoiCung = $request->filled('gia_ve_tuy_chinh') ? $request->gia_ve_tuy_chinh : $this->tinhGiaVeTuDong($thoiGianChieu, $phongChieu, $settings);
            if ($this->isNgayLe($thoiGianChieu) && $request->filled('gia_ve_ngay_le')) {
                $giaVeCuoiCung = $request->gia_ve_ngay_le;
            }

            SuatChieu::create([
                'phim_id' => $request->phim_id,
                'rap_chieu_phim_id' => $rapChieuId,
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
                    continue;
                }

                $giaVeCuoiCung = $request->filled('gia_ve_tuy_chinh') ? $request->gia_ve_tuy_chinh : $this->tinhGiaVeTuDong($thoiGianChieu, $phongChieu, $settings);
                if ($this->isNgayLe($thoiGianChieu) && $request->filled('gia_ve_ngay_le')) {
                    $giaVeCuoiCung = $request->gia_ve_ngay_le;
                }

                $suatChieuCanTao[] = [
                    'phim_id' => $request->phim_id,
                    'rap_chieu_phim_id' => $rapChieuId,
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

        if ($tatCaSuatChieuTrungHangLoat->isNotEmpty()) {
            return redirect()->back()->withInput()->with('suat_chieu_trung_danh_sach', $tatCaSuatChieuTrungHangLoat);
        }

        foreach ($suatChieuCanTao as $data) {
            SuatChieu::create($data);
        }

        $this->ghiNhatKy($request, 'Thêm suất chiếu', 'Quản lý phim & lịch chiếu', "Thêm chuỗi suất chiếu cho phim: {$phim->ten_phim}");
        return redirect()->route('admin.suat-chieus.index')->with('success', "Đã tạo thành công " . count($suatChieuCanTao) . " suất chiếu.");
    }

    public function show(SuatChieu $suatChieu): View 
    {
        $suatChieu->load(['phim', 'rapChieuPhim', 'phongChieu.hangGhes', 'phongChieu.gheNgois.loaiGhe']);
        
        $chuoiGheDaDats = DB::table('ve_xem_phims')
            ->where('suat_chieu_id', $suatChieu->id)
            ->whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung', 'cho_thanh_toan'])
            ->pluck('ma_ghe')
            ->toArray();

        $danhSachMaGheDaDat = [];
        foreach ($chuoiGheDaDats as $chuoiGhe) {
            if (!empty($chuoiGhe)) {
                $mangGhe = explode(',', $chuoiGhe);
                foreach ($mangGhe as $tenGhe) { 
                    $danhSachMaGheDaDat[] = trim($tenGhe); 
                }
            }
        }
        $danhSachMaGheDaDat = array_unique($danhSachMaGheDaDat);

        $seatMap = [];
        $maxCotTimDuoc = 1;

        foreach ($suatChieu->phongChieu->gheNgois as $g) {
            $tHang = $g->hangGhe->ten_hang ?? preg_replace('/[0-9]/', '', $g->ma_ghe) ?? 'A';
            
            $soCotTuMaGhe = (int) preg_replace('/[^0-9]/', '', $g->ma_ghe);
            $vCot = (int) ($g->vi_tri_cot ?: ($soCotTuMaGhe ?: 1));

            if ($vCot > $maxCotTimDuoc) {
                $maxCotTimDuoc = $vCot;
            }

            $trangThaiThucTe = 'trong'; 
            if (in_array($g->ma_ghe, $danhSachMaGheDaDat)) { 
                $trangThaiThucTe = 'da_dat'; 
            } elseif ($g->trang_thai === 'bao_tri') { 
                $trangThaiThucTe = 'bao_tri'; 
            }

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

        $tongSoGhe = $suatChieu->phongChieu->gheNgois->count();
        $soGheDaDat = count($danhSachMaGheDaDat);
        $soGheTrong = max(0, $tongSoGhe - $soGheDaDat);
        $tyLeLapDay = $tongSoGhe > 0 ? round(($soGheDaDat / $tongSoGhe) * 100, 1) : 0;

        $soCot = max(10, $maxCotTimDuoc);

        return view('admin.suat-chieus.show', compact('suatChieu', 'seatMap', 'soCot', 'tongSoGhe', 'soGheDaDat', 'soGheTrong', 'tyLeLapDay'));
    }

    public function edit(SuatChieu $suatChieu): View 
    {
        $phims = Phims::orderBy('ten_phim')->get();
        $rapChieuPhims = RapChieuPhim::orderBy('ten_rap')->get();
        $phongChieus = PhongChieu::with('rapChieuPhim')->where('trang_thai', 'hoat_dong')->orderBy('ten_phong')->get();
        $settings = CaiDatHeThong::first();
        $thoiGianDonPhong = $settings ? $settings->thoi_gian_don_phong : 15;
        return view('admin.suat-chieus.edit', compact('suatChieu', 'phims', 'rapChieuPhims', 'phongChieus', 'thoiGianDonPhong'));
    }

    public function update(Request $request, SuatChieu $suatChieu) 
    {
        $request->validate([
            'phim_id' => 'required|exists:phims,id', 
            'phong_chieu_id' => 'required|exists:phong_chieus,id', 
            'ngay_chieu' => 'required|date', 
            'gio_chieu' => 'required|string', 
            'gia_ve_tuy_chinh' => 'nullable|numeric|min:0', 
            'trang_thai' => 'required|in:sap_ra_mat,sap_chieu,dang_chieu,dung_nhan_ve,da_chieu,huy'
        ]);

        $settings = CaiDatHeThong::first(); 
        $thoiGianDonPhong = $settings ? $settings->thoi_gian_don_phong : 15;
        $phim = Phims::findOrFail($request->phim_id); 
        $phongChieu = PhongChieu::findOrFail($request->phong_chieu_id); 
        $rapChieuId = $phongChieu->rap_chieu_phim_id ?? 1;
        $thoiLuongPhim = $phim->thoi_luong ?? 90;

        $thoiGianChieu = Carbon::parse($request->ngay_chieu . ' ' . $request->gio_chieu); 
        $thoiGianKetThucChiemDung = $thoiGianChieu->copy()->addMinutes($thoiLuongPhim + $thoiGianDonPhong);

        $coXungDot = SuatChieu::where('phong_chieu_id', $request->phong_chieu_id)
            ->where('id', '!=', $suatChieu->id)
            ->where('trang_thai', '!=', 'huy')
            ->where(function ($query) use ($thoiGianChieu, $thoiGianKetThucChiemDung) { 
                $query->where('thoi_gian_chieu', '<', $thoiGianKetThucChiemDung)
                      ->where('thoi_gian_ket_thuc', '>', $thoiGianChieu); 
            })->exists();

        if ($coXungDot) { 
            return redirect()->back()->withInput()->with('error', 'Không thể cập nhật! Khung giờ điều chỉnh đã bị trùng lịch với suất chiếu khác cùng phòng.'); 
        }

        $giaVeCuoiCung = $request->filled('gia_ve_tuy_chinh') ? $request->gia_ve_tuy_chinh : $this->tinhGiaVeTuDong($thoiGianChieu, $phongChieu, $settings);
        $trangThaiCuoi = $request->trang_thai === 'huy' ? 'huy' : $this->xacDinhTrangThaiBanDau($thoiGianChieu, $thoiGianKetThucChiemDung);
        
        $suatChieu->update([
            'phim_id' => $request->phim_id, 
            'rap_chieu_phim_id' => $rapChieuId, 
            'phong_chieu_id' => $request->phong_chieu_id, 
            'thoi_gian_chieu' => $thoiGianChieu, 
            'thoi_luong' => $thoiLuongPhim, 
            'thoi_gian_ket_thuc' => $thoiGianKetThucChiemDung, 
            'gia_ve' => $giaVeCuoiCung, 
            'trang_thai' => $trangThaiCuoi
        ]);

        $this->ghiNhatKy($request, 'Cập nhật suất chiếu', 'Quản lý phim & lịch chiếu', "Cập nhật suất chiếu đơn lẻ cho phim: {$phim->ten_phim}");
        return redirect()->route('admin.suat-chieus.index')->with('success', 'Cập nhật thông tin suất chiếu thành công.');
    }

    public function destroy(Request $request, SuatChieu $suatChieu) 
    {
        $coNguoiDatVe = DB::table('ve_xem_phims')
            ->where('suat_chieu_id', $suatChieu->id)
            ->whereIn('trang_thai', ['da_thanh_toan', 'cho_thanh_toan', 'da_su_dung'])
            ->exists();

        if ($coNguoiDatVe) {
            return redirect()->back()->with('error', 'Không thể xóa! Suất chiếu này đã có khách hàng đặt vé.');
        }

        $lyDoHuy = $request->input('ly_do_huy', 'Không có lý do cụ thể');
        $tenPhim = $suatChieu->phim->ten_phim ?? 'N/A';

        $this->ghiNhatKy(
            $request, 
            'Xóa suất chiếu', 
            'Quản lý phim & lịch chiếu', 
            "Xóa suất chiếu ID #{$suatChieu->id} (Phim: {$tenPhim}). Lý do hủy: {$lyDoHuy}"
        );

        $suatChieu->delete(); 

        return redirect()->route('admin.suat-chieus.index')->with('success', 'Xóa suất chiếu thành công và đã lưu vết nhật ký.');
    }

    private function isNgayLe(Carbon $date): bool 
    {
        return array_key_exists($date->format('m-d'), $this->cacNgayLe);
    }

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

    private function xacDinhTrangThaiBanDau($thoiGianChieu, $thoiGianKetThuc): string 
    {
        $settings = CaiDatHeThong::first(); 
        $phutDangChieu = $settings ? $settings->so_phut_truoc_chieu_dang_chieu : 15; 
        $ngaySapRaMat = $settings ? $settings->so_ngay_truoc_chieu_sap_ra_mat : 30; 
        $phutDongBanVeOnline = 10; 
        $now = Carbon::now();
        
        $mocDungBanVeOnline = $thoiGianChieu->copy()->subMinutes($phutDongBanVeOnline); 
        $mocChuyenDangChieu = $thoiGianChieu->copy()->subMinutes($phutDangChieu);
        
        if ($now->gte($thoiGianKetThuc)) return 'da_chieu';
        if ($now->gte($thoiGianChieu) && $now->lt($thoiGianKetThuc)) return 'dang_chieu';
        if ($now->gte($mocDungBanVeOnline) && $now->lt($thoiGianChieu)) return 'dung_nhan_ve';
        if ($now->gte($mocChuyenDangChieu) && $now->lt($mocDungBanVeOnline)) return 'dang_chieu';
        if ($now->diffInDays($thoiGianChieu, false) > $ngaySapRaMat) return 'sap_ra_mat';
        return 'sap_chieu';
    }

    private function tinhGiaVeTuDong(Carbon $thoiGianChieu, PhongChieu $phongChieu, $settings): float 
    {
        $giaNgayThuong = $settings ? $settings->gia_ngay_thuong : 75000; 
        $giaCuoiTuan = $settings ? $settings->gia_cuoi_tuan : 120000; 
        $phuThuVip = $settings ? $settings->phu_thu_ghe_vip : 20000;
        
        $giaCoBas = in_array($thoiGianChieu->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY, Carbon::SUNDAY]) ? $giaCuoiTuan : $giaNgayThuong;
        if (strtolower($phongChieu->loai_phong) === 'imax' || strtolower($phongChieu->loai_phong) === '4dx') $giaCoBas += $phuThuVip;
        
        return $giaCoBas;
    }
}