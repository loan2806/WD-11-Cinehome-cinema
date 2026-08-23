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

        // Xử lý bảo vệ: Nếu 'tu_ngay' > 'den_ngay' -> Tự điều chỉnh 'den_ngay' = 'tu_ngay'
        if ($request->filled('tu_ngay') && $request->filled('den_ngay')) {
            if ($request->tu_ngay > $request->den_ngay) {
                $request->merge(['den_ngay' => $request->tu_ngay]);
            }
        }

        // 1. Tự động chuyển suất chiếu đã kết thúc sang 'da_chieu'
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

        // 2. Quản lý phân loại theo Tab (hoat_dong | da_chieu | tat_ca)
        $tab = $request->get('tab', 'hoat_dong');

        $movieQuery = Phims::query();

        if ($request->filled('phim_id')) {
            $movieQuery->where('id', $request->phim_id);
        }

        $showtimeFilter = function ($query) use ($request, $tab) {
            if ($tab === 'hoat_dong') {
                $query->whereNotIn('trang_thai', ['da_chieu', 'huy']);
            } elseif ($tab === 'da_chieu') {
                $query->where('trang_thai', 'da_chieu');
                
                if (! $request->filled('tu_ngay') && ! $request->filled('den_ngay')) {
                    $query->where('thoi_gian_chieu', '>=', now()->subDays(30));
                }
            }

            if ($request->filled('phong_chieu_id')) {
                $query->where('phong_chieu_id', $request->phong_chieu_id);
            }

            if ($request->filled('tu_ngay')) {
                $query->whereDate('thoi_gian_chieu', '>=', $request->tu_ngay);
            }
            if ($request->filled('den_ngay')) {
                $query->whereDate('thoi_gian_chieu', '<=', $request->den_ngay);
            }
        };

        $movieQuery->whereHas('showtimes', $showtimeFilter);

        $movieQuery->with(['showtimes' => function ($query) use ($showtimeFilter) {
            $query->with(['rapChieuPhim', 'phongChieu']);
            $showtimeFilter($query);
            $query->orderBy('thoi_gian_chieu', 'asc');
        }]);

        $phimsPhanTrang = $movieQuery->orderBy('ten_phim', 'asc')->paginate(5)->withQueryString();
        $phims = Phims::orderBy('ten_phim')->get();
        $phongChieus = PhongChieu::with('rapChieuPhim')->orderBy('ten_phong')->get();

        // 3. Thống kê số lượng bản ghi cho các Tab
        $tabCounts = [
            'hoat_dong' => SuatChieu::whereNotIn('trang_thai', ['da_chieu', 'huy'])->count(),
            'da_chieu'  => SuatChieu::where('trang_thai', 'da_chieu')->count(),
            'tat_ca'    => SuatChieu::count(),
        ];

        return view('admin.suat-chieus.index', compact('phimsPhanTrang', 'phims', 'phongChieus', 'tab', 'tabCounts'));
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
            
            // Đơn lẻ
            'ngay_chieu_don_le' => 'required_if:loai_tao,don_le|nullable|date|after_or_equal:today',
            'gio_chieu_don_le' => 'required_if:loai_tao,don_le|nullable|string',
            
            // Hàng loạt
            'ngay_bat_dau' => 'required_if:loai_tao,hang_loat|nullable|date|after_or_equal:today',
            'ngay_ket_thuc' => 'required_if:loai_tao,hang_loat|nullable|date|after_or_equal:ngay_bat_dau',
            
            // Chế độ 1: Tự động
            'gio_bat_dau_tu_dong' => 'required_if:che_do_hang_loat,tu_dong|nullable|string',
            'gio_ket_thuc_tu_dong' => 'required_if:che_do_hang_loat,tu_dong|nullable|string',
            
            // Chế độ 2: Thủ công
            'khung_gio' => 'required_if:che_do_hang_loat,thu_cong|nullable|array|min:1',
            'khung_gio.*' => 'required_if:che_do_hang_loat,thu_cong|string',
            
            'gia_ve_tuy_chinh' => 'nullable|numeric|min:0',
            'gia_ve_ngay_le' => 'nullable|numeric|min:0',
        ], [
            'phim_id.required' => 'Vui lòng chọn phim trình chiếu.',
            'phong_chieu_id.required' => 'Vui lòng chọn phòng chiếu.',
            'thoi_gian_don_phong.min' => 'Thời gian dọn phòng tối thiểu là 15 phút.',
            'thoi_gian_don_phong.max' => 'Thời gian dọn phòng tối đa là 30 phút.',
            'ngay_chieu_don_le.required_if' => 'Vui lòng chọn ngày chiếu cho suất đơn lẻ.',
            'ngay_chieu_don_le.after_or_equal' => 'Ngày chiếu đơn lẻ không được nằm trong quá khứ.',
            'gio_chieu_don_le.required_if' => 'Vui lòng chọn giờ chiếu cho suất đơn lẻ.',
            'ngay_bat_dau.required_if' => 'Vui lòng chọn ngày bắt đầu khi tạo hàng loạt.',
            'ngay_bat_dau.after_or_equal' => 'Ngày bắt đầu tạo hàng loạt không được nằm trong quá khứ.',
            'ngay_ket_thuc.required_if' => 'Vui lòng chọn ngày kết thúc khi tạo hàng loạt.',
            'ngay_ket_thuc.after_or_equal' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.',
            'gio_bat_dau_tu_dong.required_if' => 'Vui lòng nhập giờ bắt đầu cho suất đầu tiên.',
            'gio_ket_thuc_tu_dong.required_if' => 'Vui lòng nhập giờ kết thúc tối đa cho suất cuối cùng.',
            'khung_gio.required_if' => 'Vui lòng chọn hoặc chèn ít nhất một khung giờ chiếu!',
            'khung_gio.min' => 'Vui lòng chọn hoặc chèn ít nhất một khung giờ chiếu.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $now = Carbon::now();
        // Mốc thời gian tối thiểu hợp lệ là sau thời điểm hiện tại 12 tiếng
        $mocToiThieuHopLe = $now->copy()->addHours(12);

        $settings = CaiDatHeThong::first();
        $thoiGianDonPhongInput = $request->input('thoi_gian_don_phong', $settings ? $settings->thoi_gian_don_phong : 15);
        $thoiGianDonPhong = max(15, min(30, (int)$thoiGianDonPhongInput));

        $phim = Phims::findOrFail($request->phim_id);
        $phongChieu = PhongChieu::findOrFail($request->phong_chieu_id);
        $rapChieuId = $phongChieu->rap_chieu_phim_id ?? RapChieuPhim::first()?->id ?? 1;
        $thoiLuongPhim = ((int)$phim->thoi_luong > 0) ? (int)$phim->thoi_luong : 90;

        // TRƯỜNG HỢP 1: TẠO SUẤT CHIẾU ĐƠN LẺ
        if ($request->loai_tao === 'don_le') {
            $thoiGianChieu = Carbon::parse($request->ngay_chieu_don_le . ' ' . $request->gio_chieu_don_le);
            $thoiGianChieu = $this->lamTronLen5Phut($thoiGianChieu);

            // Chặn tạo suất chiếu đơn lẻ trước ít nhất 12 tiếng
            if ($thoiGianChieu->lt($mocToiThieuHopLe)) {
                return redirect()->back()->withInput()->with('error', 'Chỉ được phép tạo suất chiếu trước giờ chiếu ít nhất 12 tiếng để phục vụ người dùng đặt vé (Suất sớm nhất hợp lệ lúc này: ' . $mocToiThieuHopLe->format('d/m/Y H:i') . ').');
            }

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
        $soSuatBoQuaKhongDu12Tieng = 0;

        // CHẾ ĐỘ 1: TỰ ĐỘNG TÍNH KHUNG GIỜ ĐẦU - CUỐI
        if ($cheDoHangLoat === 'tu_dong') {
            $gioBatDauStr = $request->input('gio_bat_dau_tu_dong');
            $gioKetThucStr = $request->input('gio_ket_thuc_tu_dong');

            for ($ngayQuet = $ngayBatDau->copy(); $ngayQuet->lte($ngayKetThuc); $ngayQuet->addDay()) {
                $curStart = Carbon::parse($ngayQuet->format('Y-m-d') . ' ' . $gioBatDauStr);
                $curStart = $this->lamTronLen5Phut($curStart);

                $maxEnd = Carbon::parse($ngayQuet->format('Y-m-d') . ' ' . $gioKetThucStr);

                if ($maxEnd->lte($curStart)) {
                    $maxEnd->addDay();
                }

                while ($curStart->copy()->addMinutes($thoiLuongPhim)->lte($maxEnd)) {
                    // Tự động bỏ qua các khung giờ không đủ 12 tiếng so với hiện tại
                    if ($curStart->lt($mocToiThieuHopLe)) {
                        $soSuatBoQuaKhongDu12Tieng++;
                        $nextStartRaw = $curStart->copy()->addMinutes($thoiLuongPhim + $thoiGianDonPhong);
                        $curStart = $this->lamTronLen5Phut($nextStartRaw);
                        continue;
                    }

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

                    $nextStartRaw = $curStart->copy()->addMinutes($thoiLuongPhim + $thoiGianDonPhong);
                    $curStart = $this->lamTronLen5Phut($nextStartRaw);
                }
            }
        } 
        // CHẾ ĐỘ 2: CHỌN CÁC KHUNG GIỜ CHIẾU THỦ CÔNG
        else {
            $danhSachKhungGioInput = $request->input('khung_gio', []);

            $danhSachKhungGio = [];
            foreach ($danhSachKhungGioInput as $gio) {
                $cVal = Carbon::parse('2000-01-01 ' . $gio);
                $cVal = $this->lamTronLen5Phut($cVal);
                $danhSachKhungGio[] = $cVal->format('H:i');
            }
            $danhSachKhungGio = array_values(array_unique($danhSachKhungGio));

            usort($danhSachKhungGio, function($a, $b) {
                return strtotime($a) - strtotime($b);
            });

            // Validate trùng lặp nội bộ giữa các khung giờ thủ công đã chọn
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

                    // Bỏ qua khung giờ thủ công không đủ 12 tiếng
                    if ($thoiGianChieu->lt($mocToiThieuHopLe)) {
                        $soSuatBoQuaKhongDu12Tieng++;
                        continue;
                    }

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
            $msg = 'Không có suất chiếu nào được tạo do tất cả các khung giờ đã chọn đều không đủ khoảng thời gian tạo trước 12 tiếng (Giờ hợp lệ sớm nhất: ' . $mocToiThieuHopLe->format('d/m/Y H:i') . ')';
            if ($tatCaSuatChieuTrungDb->isNotEmpty()) {
                $msg .= ' hoặc bị trùng lịch với phòng chiếu';
            }
            return redirect()->back()->withInput()->with('error', $msg . '.');
        }

        foreach ($suatChieuCanTao as $data) {
            SuatChieu::create($data);
        }

        $this->ghiNhatKy($request, 'Thêm suất chiếu', 'Quản lý phim & lịch chiếu', "Thêm chuỗi suất chiếu cho phim: {$phim->ten_phim}");
        
        $thongBao = "Đã tạo thành công " . count($suatChieuCanTao) . " suất chiếu.";
        if ($soSuatBoQuaKhongDu12Tieng > 0) {
            $thongBao .= " (Hệ thống đã tự động bỏ qua {$soSuatBoQuaKhongDu12Tieng} khung giờ do cách thời điểm hiện tại chưa đủ 12 tiếng).";
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

        $now = Carbon::now();
        $thoiGianChieu = Carbon::parse($suatChieu->thoi_gian_chieu);

        // Kiểm tra xem đã có người đặt vé chưa
        $coNguoiDatVe = DB::table('ve_xem_phims')
            ->where('suat_chieu_id', $suatChieu->id)
            ->whereIn('trang_thai', ['da_thanh_toan', 'cho_thanh_toan', 'da_su_dung', 'da_dat'])
            ->exists();

        // Điều kiện sửa giá: Chưa có người đặt vé AND còn hơn 48 tiếng trước giờ chiếu
        $choPhepSuaGia = ! $coNguoiDatVe && $now->diffInHours($thoiGianChieu, false) >= 48;

        // Điều kiện hủy suất: Chưa có người đặt vé
        $choPhepHuy = ! $coNguoiDatVe;

        return view('admin.suat-chieus.edit', compact(
            'suatChieu', 
            'phims', 
            'rapChieuPhims', 
            'phongChieus', 
            'thoiGianDonPhong',
            'coNguoiDatVe',
            'choPhepSuaGia',
            'choPhepHuy'
        ));
    }

    public function update(Request $request, SuatChieu $suatChieu) 
    {
        $validator = Validator::make($request->all(), [
            'phim_id' => 'required|exists:phims,id', 
            'phong_chieu_id' => 'required|exists:phong_chieus,id', 
            'ngay_chieu' => 'required|date', 
            'gio_chieu' => 'required|string', 
            'gia_ve_tuy_chinh' => 'nullable|numeric|min:0', 
            'trang_thai' => 'required|in:sap_ra_mat,sap_chieu,dang_chieu,dung_nhan_ve,da_chieu,huy,tu_dong'
        ], [
            'phim_id.required' => 'Vui lòng chọn phim.',
            'phong_chieu_id.required' => 'Vui lòng chọn phòng chiếu.',
            'ngay_chieu.required' => 'Vui lòng chọn ngày chiếu.',
            'gio_chieu.required' => 'Vui lòng nhập giờ chiếu.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $now = Carbon::now();
        $settings = CaiDatHeThong::first(); 
        $thoiGianDonPhongInput = $request->input('thoi_gian_don_phong', $settings ? $settings->thoi_gian_don_phong : 15);
        $thoiGianDonPhong = max(15, min(30, (int)$thoiGianDonPhongInput));

        $phim = Phims::findOrFail($request->phim_id); 
        $phongChieu = PhongChieu::findOrFail($request->phong_chieu_id); 
        $rapChieuId = $phongChieu->rap_chieu_phim_id ?? 1;
        $thoiLuongPhim = $phim->thoi_luong ?? 90;

        $thoiGianChieu = Carbon::parse($request->ngay_chieu . ' ' . $request->gio_chieu); 
        $thoiGianChieu = $this->lamTronLen5Phut($thoiGianChieu);

        // 1. Kiểm tra tồn tại vé
        $coNguoiDatVe = DB::table('ve_xem_phims')
            ->where('suat_chieu_id', $suatChieu->id)
            ->whereIn('trang_thai', ['da_thanh_toan', 'cho_thanh_toan', 'da_su_dung', 'da_dat'])
            ->exists();

        // 2. Validate bảo vệ Hủy suất chiếu
        if ($request->trang_thai === 'huy' && $coNguoiDatVe) {
            return redirect()->back()->withInput()->with('error', 'Không thể hủy suất chiếu này do đã có khách hàng đặt vé! Vui lòng thực hiện hoàn vé cho khách hàng trước.');
        }

        // 3. Validate bảo vệ Giá vé
        $choPhepSuaGia = ! $coNguoiDatVe && $now->diffInHours($thoiGianChieu, false) >= 48;
        $giaVeCuoiCung = $suatChieu->gia_ve;
        $giaVeTuDong = $suatChieu->gia_ve_tu_dong;

        if ($choPhepSuaGia) {
            $giaVeTuDong = ! $request->filled('gia_ve_tuy_chinh');
            $giaVeCuoiCung = $giaVeTuDong ? $this->tinhGiaVeTuDong($thoiGianChieu, $phongChieu, $settings) : $request->gia_ve_tuy_chinh;
        }

        // 4. Kiểm tra xung đột lịch với suất khác
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