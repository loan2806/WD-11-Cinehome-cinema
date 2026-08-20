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
use Illuminate\Support\Facades\Validator;

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
     * Hàm helper làm tròn LÊN mốc 5 phút gần nhất
     * Ví dụ: 20:38 -> 20:40, 10:19 -> 10:20, 14:57 -> 15:00
     */
    private function lamTronLen5Phut(Carbon $dateTime): Carbon
    {
        $minute = $dateTime->minute;
        $remainder = $minute % 5;

        if ($remainder !== 0) {
            $dateTime->addMinutes(5 - $remainder)->second(0);
        } else {
            $dateTime->second(0);
        }

        return $dateTime;
    }

    public function index(Request $request): View
    {
        $now = Carbon::now();

        // Suất chiếu kết thúc quá 24h sẽ tự động chuyển sang Xóa mềm (deleted_at được ghi nhận)
        SuatChieu::where('thoi_gian_ket_thuc', '<=', $now->copy()->subHours(24))->delete();

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

        if ($request->filled('phim_id')) {
            $movieQuery->where('id', $request->phim_id);
        }

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
        $thoiGianDonPhong = max(15, min(30, (int)$thoiGianDonPhong));

        return view('admin.suat-chieus.create', compact('phims', 'rapMacDinh', 'phongChieus', 'phongChieuId', 'thoiGianDonPhong'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phim_id' => 'required|exists:phims,id',
            'phong_chieu_id' => 'required|exists:phong_chieus,id',
            'loai_tao' => 'required|in:don_le,hang_loat',
            'che_do_hang_loat' => 'required_if:loai_tao,hang_loat|nullable|in:tu_dong,thu_cong',
            'thoi_gian_don_phong' => 'nullable|integer|min:15|max:30',
            'ngay_chieu_don_le' => 'required_if:loai_tao,don_le|nullable|date',
            'gio_chieu_don_le' => 'required_if:loai_tao,don_le|nullable|string',
            'ngay_bat_dau' => 'required_if:loai_tao,hang_loat|nullable|date',
            'ngay_ket_thuc' => 'required_if:loai_tao,hang_loat|nullable|date|after_or_equal:ngay_bat_dau',
            'gio_bat_dau_tu_dong' => 'required_if:che_do_hang_loat,tu_dong|nullable|string',
            'gio_ket_thuc_tu_dong' => 'required_if:che_do_hang_loat,tu_dong|nullable|string',
            'khung_gio' => 'required_if:che_do_hang_loat,thu_cong|nullable|array|min:1',
            'khung_gio.*' => 'string',
            'gia_ve_tuy_chinh' => 'nullable|numeric|min:0',
            'gia_ve_ngay_le' => 'nullable|numeric|min:0',
        ], [
            'phim_id.required' => 'Vui lòng chọn phim trình chiếu.',
            'phong_chieu_id.required' => 'Vui lòng chọn phòng chiếu.',
            'thoi_gian_don_phong.min' => 'Thời gian dọn phòng tối thiểu là 15 phút.',
            'thoi_gian_don_phong.max' => 'Thời gian dọn phòng tối đa là 30 phút.',
            'ngay_chieu_don_le.required_if' => 'Vui lòng chọn ngày chiếu cho suất đơn lẻ.',
            'gio_chieu_don_le.required_if' => 'Vui lòng chọn giờ chiếu cho suất đơn lẻ.',
            'ngay_bat_dau.required_if' => 'Vui lòng chọn ngày bắt đầu khi tạo hàng loạt.',
            'ngay_ket_thuc.required_if' => 'Vui lòng chọn ngày kết thúc khi tạo hàng loạt.',
            'ngay_ket_thuc.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
            'gio_bat_dau_tu_dong.required_if' => 'Vui lòng nhập giờ bắt đầu cho suất đầu tiên.',
            'gio_ket_thuc_tu_dong.required_if' => 'Vui lòng nhập giờ kết thúc tối đa cho suất cuối cùng.',
            'khung_gio.required_if' => 'Vui lòng chọn hoặc chèn ít nhất một khung giờ chiếu!',
            'khung_gio.min' => 'Vui lòng chọn hoặc chèn ít nhất một khung giờ chiếu.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $settings = CaiDatHeThong::first();
        $thoiGianDonPhongInput = $request->input('thoi_gian_don_phong', $settings ? $settings->thoi_gian_don_phong : 15);
        $thoiGianDonPhong = max(15, min(30, (int)$thoiGianDonPhongInput));

        $phim = Phims::findOrFail($request->phim_id);
        $phongChieu = PhongChieu::findOrFail($request->phong_chieu_id);
        $rapChieuId = $phongChieu->rap_chieu_phim_id ?? RapChieuPhim::first()?->id ?? 1;
        $thoiLuongPhim = ((int)$phim->thoi_luong > 0) ? (int)$phim->thoi_luong : 90;

        // TRƯỜNG HỢP 1: TẠO 1 SUẤT CHIẾU ĐƠN LẺ
        if ($request->loai_tao === 'don_le') {
            $thoiGianChieu = Carbon::parse($request->ngay_chieu_don_le . ' ' . $request->gio_chieu_don_le);
            $thoiGianChieu = $this->lamTronLen5Phut($thoiGianChieu); // Tự động làm tròn 5 phút

            $thoiGianKetThucChiemDung = $thoiGianChieu->copy()->addMinutes($thoiLuongPhim + $thoiGianDonPhong);

            $suatChieuTrung = $this->layDanhSachSuatChieuTrung($request->phong_chieu_id, $thoiGianChieu, $thoiGianKetThucChiemDung);
            
            if ($suatChieuTrung->isNotEmpty()) {
                return redirect()->back()->withInput()->with('suat_chieu_trung_danh_sach', $suatChieuTrung);
            }

            $giaVeTuDong = ! $request->filled('gia_ve_tuy_chinh');
            $giaVeCuoiCung = $giaVeTuDong ? $this->tinhGiaVeTuDong($thoiGianChieu, $phongChieu, $settings) : $request->gia_ve_tuy_chinh;
            if ($this->isNgayLe($thoiGianChieu) && $request->filled('gia_ve_ngay_le')) {
                $giaVeCuoiCung = $request->gia_ve_ngay_le;
                $giaVeTuDong = false;
            }

            SuatChieu::create([
                'phim_id' => $request->phim_id,
                'rap_chieu_phim_id' => $rapChieuId,
                'phong_chieu_id' => $request->phong_chieu_id,
                'thoi_gian_chieu' => $thoiGianChieu,
                'thoi_luong' => $thoiLuongPhim,
                'thoi_gian_ket_thuc' => $thoiGianKetThucChiemDung,
                'gia_ve' => $giaVeCuoiCung,
                'gia_ve_tu_dong' => $giaVeTuDong,
                'trang_thai' => $this->xacDinhTrangThaiBanDau($thoiGianChieu, $thoiGianKetThucChiemDung),
            ]);

            $this->ghiNhatKy($request, 'Thêm suất chiếu', 'Quản lý phim & lịch chiếu', "Thêm suất chiếu đơn lẻ cho phim: {$phim->ten_phim}");
            return redirect()->route('admin.suat-chieus.index')->with('success', 'Tạo suất chiếu đơn lẻ thành công.');
        }

        // TRƯỜNG HỢP 2: TẠO SUẤT CHIẾU HÀNG LOẠT
        $ngayBatDau = Carbon::parse($request->ngay_bat_dau);
        $ngayKetThuc = Carbon::parse($request->ngay_ket_thuc);
        $cheDoHangLoat = $request->input('che_do_hang_loat', 'tu_dong');
        $boQuaTrung = $request->boolean('bo_qua_trung', false);

        $suatChieuCanTao = [];
        $tatCaSuatChieuTrungDb = collect();
        $danhSachKhungGioTrungNoiBo = [];

        // CHẾ ĐỘ 1: TỰ ĐỘNG TÍNH THEO GIỜ BẮT ĐẦU & KẾT THÚC
        if ($cheDoHangLoat === 'tu_dong') {
            $gioBatDauStr = $request->input('gio_bat_dau_tu_dong');
            $gioKetThucStr = $request->input('gio_ket_thuc_tu_dong');

            for ($ngayQuet = $ngayBatDau->copy(); $ngayQuet->lte($ngayKetThuc); $ngayQuet->addDay()) {
                $curStart = Carbon::parse($ngayQuet->format('Y-m-d') . ' ' . $gioBatDauStr);
                $curStart = $this->lamTronLen5Phut($curStart); // Làm tròn mốc khởi đầu

                $maxEnd = Carbon::parse($ngayQuet->format('Y-m-d') . ' ' . $gioKetThucStr);

                if ($maxEnd->lte($curStart)) {
                    $maxEnd->addDay();
                }

                while ($curStart->copy()->addMinutes($thoiLuongPhim)->lte($maxEnd)) {
                    $curEndChiemDung = $curStart->copy()->addMinutes($thoiLuongPhim + $thoiGianDonPhong);
                    $suatChieuTrung = $this->layDanhSachSuatChieuTrung($request->phong_chieu_id, $curStart, $curEndChiemDung);

                    if ($suatChieuTrung->isNotEmpty()) {
                        foreach ($suatChieuTrung as $scTrung) {
                            $tatCaSuatChieuTrungDb->put($scTrung->id, $scTrung);
                        }
                    } else {
                        $giaVeTuDong = ! $request->filled('gia_ve_tuy_chinh');
                        $giaVeCuoiCung = $giaVeTuDong ? $this->tinhGiaVeTuDong($curStart, $phongChieu, $settings) : $request->gia_ve_tuy_chinh;
                        if ($this->isNgayLe($curStart) && $request->filled('gia_ve_ngay_le')) {
                            $giaVeCuoiCung = $request->gia_ve_ngay_le;
                            $giaVeTuDong = false;
                        }

                        $suatChieuCanTao[] = [
                            'phim_id' => $request->phim_id,
                            'rap_chieu_phim_id' => $rapChieuId,
                            'phong_chieu_id' => $request->phong_chieu_id,
                            'thoi_gian_chieu' => $curStart->copy(),
                            'thoi_luong' => $thoiLuongPhim,
                            'thoi_gian_ket_thuc' => $curEndChiemDung->copy(),
                            'gia_ve' => $giaVeCuoiCung,
                            'gia_ve_tu_dong' => $giaVeTuDong,
                            'trang_thai' => $this->xacDinhTrangThaiBanDau($curStart, $curEndChiemDung),
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }

                    // Tăng thời gian cho suất tiếp theo và TỰ ĐỘNG LÀM TRÒN LÊN 5 PHÚT
                    $nextStartRaw = $curStart->copy()->addMinutes($thoiLuongPhim + $thoiGianDonPhong);
                    $curStart = $this->lamTronLen5Phut($nextStartRaw);
                }
            }
        } 
        // CHẾ ĐỘ 2: CHỌN KHUNG GIỜ THỦ CÔNG
        else {
            $danhSachKhungGioInput = $request->input('khung_gio', []);

            // Làm tròn từng khung giờ nếu người dùng chèn/chọn giờ lẻ
            $danhSachKhungGio = [];
            foreach ($danhSachKhungGioInput as $gio) {
                $cVal = Carbon::parse('2000-01-01 ' . $gio);
                $cVal = $this->lamTronLen5Phut($cVal);
                $danhSachKhungGio[] = $cVal->format('H:i');
            }
            $danhSachKhungGio = array_unique($danhSachKhungGio);

            usort($danhSachKhungGio, function($a, $b) {
                return strtotime($a) - strtotime($b);
            });

            $khungGioHopLeNoiBo = [];
            $lastEndTime = null;

            foreach ($danhSachKhungGio as $gio) {
                $timeCarbon = Carbon::parse('2000-01-01 ' . $gio);
                if ($lastEndTime !== null && $timeCarbon->lt($lastEndTime)) {
                    $danhSachKhungGioTrungNoiBo[] = $gio;
                } else {
                    $khungGioHopLeNoiBo[] = $gio;
                    $lastEndTime = $timeCarbon->copy()->addMinutes($thoiLuongPhim + $thoiGianDonPhong);
                }
            }

            $danhSachGioQuet = $boQuaTrung ? $khungGioHopLeNoiBo : $danhSachKhungGio;

            for ($ngayQuet = $ngayBatDau->copy(); $ngayQuet->lte($ngayKetThuc); $ngayQuet->addDay()) {
                foreach ($danhSachGioQuet as $gioChieu) {
                    if (!$boQuaTrung && in_array($gioChieu, $danhSachKhungGioTrungNoiBo)) {
                        continue;
                    }

                    $thoiGianChieu = Carbon::parse($ngayQuet->format('Y-m-d') . ' ' . $gioChieu);
                    $thoiGianChieu = $this->lamTronLen5Phut($thoiGianChieu);

                    $thoiGianKetThucChiemDung = $thoiGianChieu->copy()->addMinutes($thoiLuongPhim + $thoiGianDonPhong);

                    $suatChieuTrung = $this->layDanhSachSuatChieuTrung($request->phong_chieu_id, $thoiGianChieu, $thoiGianKetThucChiemDung);

                    if ($suatChieuTrung->isNotEmpty()) {
                        foreach ($suatChieuTrung as $scTrung) {
                            $tatCaSuatChieuTrungDb->put($scTrung->id, $scTrung);
                        }
                    } else {
                        $giaVeTuDong = ! $request->filled('gia_ve_tuy_chinh');
                        $giaVeCuoiCung = $giaVeTuDong ? $this->tinhGiaVeTuDong($thoiGianChieu, $phongChieu, $settings) : $request->gia_ve_tuy_chinh;
                        if ($this->isNgayLe($thoiGianChieu) && $request->filled('gia_ve_ngay_le')) {
                            $giaVeCuoiCung = $request->gia_ve_ngay_le;
                            $giaVeTuDong = false;
                        }

                        $suatChieuCanTao[] = [
                            'phim_id' => $request->phim_id,
                            'rap_chieu_phim_id' => $rapChieuId,
                            'phong_chieu_id' => $request->phong_chieu_id,
                            'thoi_gian_chieu' => $thoiGianChieu,
                            'thoi_luong' => $thoiLuongPhim,
                            'thoi_gian_ket_thuc' => $thoiGianKetThucChiemDung,
                            'gia_ve' => $giaVeCuoiCung,
                            'gia_ve_tu_dong' => $giaVeTuDong,
                            'trang_thai' => $this->xacDinhTrangThaiBanDau($thoiGianChieu, $thoiGianKetThucChiemDung),
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                }
            }
        }

        if ((!empty($danhSachKhungGioTrungNoiBo) || $tatCaSuatChieuTrungDb->isNotEmpty()) && !$boQuaTrung) {
            return redirect()->back()
                ->withInput()
                ->with('suat_chieu_trung_danh_sach', $tatCaSuatChieuTrungDb)
                ->with('khung_gio_trung_noibo', $danhSachKhungGioTrungNoiBo)
                ->with('thoi_luong_phim_phut', $thoiLuongPhim)
                ->with('thoi_gian_don_phong_phut', $thoiGianDonPhong);
        }

        if (empty($suatChieuCanTao)) {
            return redirect()->back()->withInput()->with('error', 'Tất cả khung giờ tạo ra đều bị trùng lịch. Không có suất chiếu nào được tạo.');
        }

        foreach ($suatChieuCanTao as $data) {
            SuatChieu::create($data);
        }

        $this->ghiNhatKy($request, 'Thêm suất chiếu', 'Quản lý phim & lịch chiếu', "Thêm chuỗi suất chiếu cho phim: {$phim->ten_phim}");
        
        $thongBao = "Đã tạo thành công " . count($suatChieuCanTao) . " suất chiếu.";
        if ($boQuaTrung && (!empty($danhSachKhungGioTrungNoiBo) || $tatCaSuatChieuTrungDb->isNotEmpty())) {
            $thongBao .= " (Hệ thống đã tự động bỏ qua các suất chiếu bị trùng giờ).";
        }

        return redirect()->route('admin.suat-chieus.index')->with('success', $thongBao);
    }

    public function show(SuatChieu $suatChieu): View 
    {
        $suatChieu->load(['phim', 'rapChieuPhim', 'phongChieu.hangGhes', 'phongChieu.gheNgois.loaiGhe']);
        
        $chuoiGheDaDats = DB::table('ve_xem_phims')
            ->where('suat_chieu_id', $suatChieu->id)
            ->whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung', 'cho_thanh_toan', 'da_dat'])
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
        $thoiGianDonPhong = max(15, min(30, (int)$thoiGianDonPhong));
        return view('admin.suat-chieus.edit', compact('suatChieu', 'phims', 'rapChieuPhims', 'phongChieus', 'thoiGianDonPhong'));
    }

    public function update(Request $request, SuatChieu $suatChieu) 
    {
        $validator = Validator::make($request->all(), [
            'phim_id' => 'required|exists:phims,id', 
            'phong_chieu_id' => 'required|exists:phong_chieus,id', 
            'ngay_chieu' => 'required|date', 
            'gio_chieu' => 'required|string', 
            'gia_ve_tuy_chinh' => 'nullable|numeric|min:0', 
            'trang_thai' => 'required|in:sap_ra_mat,sap_chieu,dang_chieu,dung_nhan_ve,da_chieu,huy'
        ], [
            'phim_id.required' => 'Vui lòng chọn phim.',
            'phong_chieu_id.required' => 'Vui lòng chọn phòng chiếu.',
            'ngay_chieu.required' => 'Vui lòng chọn ngày chiếu.',
            'gio_chieu.required' => 'Vui lòng nhập giờ chiếu.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $settings = CaiDatHeThong::first(); 
        $thoiGianDonPhongInput = $request->input('thoi_gian_don_phong', $settings ? $settings->thoi_gian_don_phong : 15);
        $thoiGianDonPhong = max(15, min(30, (int)$thoiGianDonPhongInput));

        $phim = Phims::findOrFail($request->phim_id); 
        $phongChieu = PhongChieu::findOrFail($request->phong_chieu_id); 
        $rapChieuId = $phongChieu->rap_chieu_phim_id ?? 1;
        $thoiLuongPhim = $phim->thoi_luong ?? 90;

        $thoiGianChieu = Carbon::parse($request->ngay_chieu . ' ' . $request->gio_chieu); 
        $thoiGianChieu = $this->lamTronLen5Phut($thoiGianChieu); // Tự động làm tròn

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

        $giaVeTuDong = ! $request->filled('gia_ve_tuy_chinh');
        $giaVeCuoiCung = $giaVeTuDong ? $this->tinhGiaVeTuDong($thoiGianChieu, $phongChieu, $settings) : $request->gia_ve_tuy_chinh;
        $trangThaiCuoi = $request->trang_thai === 'huy' ? 'huy' : $this->xacDinhTrangThaiBanDau($thoiGianChieu, $thoiGianKetThucChiemDung);

        $suatChieu->update([
            'phim_id' => $request->phim_id,
            'rap_chieu_phim_id' => $rapChieuId,
            'phong_chieu_id' => $request->phong_chieu_id,
            'thoi_gian_chieu' => $thoiGianChieu,
            'thoi_luong' => $thoiLuongPhim,
            'thoi_gian_ket_thuc' => $thoiGianKetThucChiemDung,
            'gia_ve' => $giaVeCuoiCung,
            'gia_ve_tu_dong' => $giaVeTuDong,
            'trang_thai' => $trangThaiCuoi
        ]);

        $this->ghiNhatKy($request, 'Cập nhật suất chiếu', 'Quản lý phim & lịch chiếu', "Cập nhật suất chiếu đơn lẻ cho phim: {$phim->ten_phim}");
        return redirect()->route('admin.suat-chieus.index')->with('success', 'Cập nhật thông tin suất chiếu thành công.');
    }

    public function destroy(Request $request, SuatChieu $suatChieu) 
    {
        $validator = Validator::make($request->all(), [
            'ly_do_huy' => 'required|string|max:500',
        ], [
            'ly_do_huy.required' => 'Vui lòng nhập lý do xóa suất chiếu.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $now = Carbon::now();
        $thoiGianChieu = Carbon::parse($suatChieu->thoi_gian_chieu);

        if ($thoiGianChieu->lt($now->copy()->addDays(3))) {
            return redirect()->back()->with('error', 'Không thể xóa! Chỉ được phép xóa suất chiếu trước giờ chiếu ít nhất 3 ngày.');
        }

        $coNguoiDatVe = DB::table('ve_xem_phims')
            ->where('suat_chieu_id', $suatChieu->id)
            ->whereIn('trang_thai', ['da_thanh_toan', 'cho_thanh_toan', 'da_su_dung', 'da_dat'])
            ->exists();

        if ($coNguoiDatVe) {
            return redirect()->back()->with('error', 'Không thể xóa! Suất chiếu này đã có khách hàng đặt vé.');
        }

        $lyDoHuy = $request->input('ly_do_huy');
        $tenPhim = $suatChieu->phim->ten_phim ?? 'N/A';

        $this->ghiNhatKy(
            $request, 
            'Xóa suất chiếu', 
            'Quản lý phim & lịch chiếu', 
            "Xóa suất chiếu ID #{$suatChieu->id} (Phim: {$tenPhim}, Ngày chiếu: {$thoiGianChieu->format('d/m/Y H:i')}). Lý do: {$lyDoHuy}"
        );

        $suatChieu->delete(); 

        return redirect()->route('admin.suat-chieus.index')->with('success', 'Đã chuyển suất chiếu vào thùng rác hệ thống thành công.');
    }

    public function restore($id)
    {
        $suatChieu = SuatChieu::onlyTrashed()->findOrFail($id);
        $suatChieu->restore();

        $this->ghiNhatKy(
            request(),
            'Khôi phục suất chiếu',
            'Quản lý phim & lịch chiếu',
            "Khôi phục suất chiếu ID #{$id}"
        );

        return redirect()->back()->with('success', 'Đã khôi phục suất chiếu thành công!');
    }

    public function forceDelete($id)
    {
        $suatChieu = SuatChieu::onlyTrashed()->findOrFail($id);

        $coNguoiDatVe = DB::table('ve_xem_phims')
            ->where('suat_chieu_id', $suatChieu->id)
            ->whereIn('trang_thai', ['da_thanh_toan', 'cho_thanh_toan', 'da_su_dung', 'da_dat'])
            ->exists();

        if ($coNguoiDatVe) {
            return redirect()->back()->with('error', 'Không thể xóa vĩnh viễn! Suất chiếu này vẫn còn liên kết với dữ liệu vé của khách hàng.');
        }

        $suatChieu->forceDelete();

        $this->ghiNhatKy(
            request(),
            'Xóa vĩnh viễn suất chiếu',
            'Quản trị hệ thống',
            "Xóa vĩnh viễn suất chiếu ID #{$id}"
        );

        return redirect()->back()->with('success', 'Đã xóa vĩnh viễn suất chiếu khỏi hệ thống!');
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

        return in_array($thoiGianChieu->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY, Carbon::SUNDAY]) ? $giaCuoiTuan : $giaNgayThuong;
    }
}