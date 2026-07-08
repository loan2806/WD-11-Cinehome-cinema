<?php

namespace App\Http\Controllers\DatVe;

use App\Http\Controllers\Controller;
use App\Models\DoAn;
use App\Models\RapChieuPhim;
use App\Models\SuatChieu;
use App\Services\DatVeXemPhimService;
use Illuminate\Support\Facades\Auth;
use App\Models\NguoiDungVoucher;
use Carbon\Carbon;
use App\Models\GheNgoi;
use App\Models\Phims;
use App\Models\VeXemPhim;
use App\Models\ThanhVien;
use App\Models\DanhMucDoAn;
use App\Models\BienTheDoAn;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use PayOS\PayOS; // Khai báo thư viện PayOS chính thức

class DatVeController extends Controller
{
    public function chonRap()
    {
        return redirect()->route('dat_ve.chon_phim');
    }

    public function chonPhim()
    {
        $rap = RapChieuPhim::firstOrFail();

        $now = now('Asia/Ho_Chi_Minh');
        $today = today('Asia/Ho_Chi_Minh');
        $limitDay = $today->copy()->addDays(14);

        $selectedDate = $today;
        if (request()->filled('ngay_chieu')) {
            try {
                $selectedDate = Carbon::createFromFormat('Y-m-d', request('ngay_chieu'), 'Asia/Ho_Chi_Minh');
            } catch (\Exception $exception) {
                $selectedDate = $today;
            }
        }

        if ($selectedDate->lt($today)) {
            $selectedDate = $today;
        }

        if ($selectedDate->gt($limitDay)) {
            $selectedDate = $limitDay;
        }

        $weekdayShort = [
            'Sun' => 'CN', 'Mon' => 'T2', 'Tue' => 'T3', 'Wed' => 'T4', 'Thu' => 'T5', 'Fri' => 'T6', 'Sat' => 'T7',
        ];

        $dateOptions = collect(range(0, 14))->map(function ($offset) use ($today, $selectedDate, $weekdayShort) {
            $date = $today->copy()->addDays($offset);

            return [
                'date' => $date->toDateString(),
                'label' => $offset === 0
                    ? 'Hôm nay'
                    : ($weekdayShort[$date->format('D')] ?? $date->format('D')) . ' ' . $date->format('d/m'),
                'active' => $date->isSameDay($selectedDate),
            ];
        });

        $danhSachSuatChieu = SuatChieu::with(['phim.genres', 'rapChieuPhim', 'phongChieu', 'veXemPhims'])
            ->where('rap_chieu_phim_id', $rap->id)
            ->whereDate('thoi_gian_chieu', $selectedDate)
            ->when($selectedDate->isToday(), fn($query) => $query->where('thoi_gian_chieu', '>=', $now))
            ->when(request('phim_id'), fn($query) => $query->where('phim_id', request('phim_id')))
            ->orderBy('thoi_gian_chieu')
            ->get();

        $suatChieuTheoPhim = $danhSachSuatChieu->groupBy('phim_id');

        foreach ($suatChieuTheoPhim as $suatChieus) {
            foreach ($suatChieus as $suat) {
                $tongGhe = $suat->phongChieu->gheNgois->where('trang_thai', 'hoat_dong')->count();
                $gheDaDat = $suat->veXemPhims->whereIn('trang_thai', ['da_dat', 'da_thanh_toan'])->count();

                $suat->tong_ghe = $tongGhe;
                $suat->ghe_da_dat = $gheDaDat;
                $suat->ghe_trong = max(0, $tongGhe - $gheDaDat);
            }
        }

        return view('user.dat_ve.chon_phim', compact('rap', 'suatChieuTheoPhim', 'dateOptions', 'selectedDate'));
    }

    public function chonGhe($movie, DatVeXemPhimService $datVeXemPhimService)
    {
        if (is_numeric($movie)) {
            $suatChieu = SuatChieu::with(['phim', 'rapChieuPhim', 'phongChieu'])->find($movie);
            if (!$suatChieu) {
                $suatChieu = SuatChieu::with(['phim', 'rapChieuPhim', 'phongChieu'])
                    ->where('phim_id', $movie)
                    ->where('thoi_gian_chieu', '>=', now('Asia/Ho_Chi_Minh'))
                    ->orderBy('thoi_gian_chieu')->first();
            }
        } else {
            $suatChieu = SuatChieu::with(['phim', 'rapChieuPhim', 'phongChieu'])
                ->whereHas('phim', function ($query) use ($movie) {
                    $query->where('slug', $movie);
                })
                ->where('thoi_gian_chieu', '>=', now('Asia/Ho_Chi_Minh'))
                ->orderBy('thoi_gian_chieu')->first();
        }

        abort_if(!$suatChieu, 404);
        abort_if($suatChieu->thoi_gian_chieu->lt(now('Asia/Ho_Chi_Minh')), 404);

        session(['reservation_expires_at' => now('Asia/Ho_Chi_Minh')->addMinutes(7)->timestamp]);
        $duLieuChonGhe = $datVeXemPhimService->duLieuChonGhe($suatChieu);

        $duLieuChonGhe['vouchers'] = Auth::check()
            ? NguoiDungVoucher::with('voucher')->where('nguoi_dung_id', Auth::id())->where('da_su_dung', false)
            ->where(function ($query) { $query->whereNull('ngay_het_han')->orWhere('ngay_het_han', '>=', now()); })
            ->whereHas('voucher', function ($query) { $query->where('trang_thai', true)->whereDate('ngay_het_han', '>=', today()); })
            ->get() : collect();

        return view('user.dat_ve.chon_ghe', $duLieuChonGhe);
    }

    public function chonDoAn($suat_chieu_id)
    {
        $suatChieu = SuatChieu::with(['phim', 'rapChieuPhim', 'phongChieu'])->findOrFail($suat_chieu_id);
        $identifier = Auth::id() ?? session()->getId();
        $selectedSeats = collect(explode(',', request('ghe')))->map(fn($seat) => strtoupper(trim($seat)))->filter()->unique()->values();

        foreach ($selectedSeats as $seat) {
            $lock = Cache::get("seat_lock:suat:{$suat_chieu_id}:seat:{$seat}");
            if (!$lock || ($lock['identifier'] ?? null) != $identifier || ($lock['expires_at'] ?? 0) < now()->timestamp) {
                return redirect()->route('dat_ve.chon_ghe', $suatChieu->phim->slug)->with('error', 'Ghế đã hết thời gian giữ.');
            }
        }

        abort_if($suatChieu->thoi_gian_chieu->lt(now('Asia/Ho_Chi_Minh')), 404);

        $seatModels = \App\Models\GheNgoi::with('loaiGhe')
            ->where('phong_chieu_id', $suatChieu->phong_chieu_id)
            ->whereIn('ma_ghe', $selectedSeats)
            ->get();

        $seatTotalPrice = $seatModels->sum(function ($seat) use ($suatChieu) {
            $price = $suatChieu->gia_ve + ($seat->loaiGhe->phu_thu ?? 0);
            if ($seat->loaiGhe?->la_couple) { 
                $price = ($suatChieu->gia_ve * 2) + ($seat->loaiGhe->phu_thu ?? 0); 
            }
            return $price;
        });

        $foods = DoAn::active()->with([
            'category',
            'variants' => function ($query) { $query->where('is_active', true)->orderBy('price'); },
            'comboItems.variant.doAn',
        ])->orderBy('sort_order')->orderBy('name')->get();

        $menu = $foods->groupBy(function ($food) {
            return trim($food->category?->name ?? 'Khác');
        })->map(function ($items, $category) {
            return [
                'category' => $category,
                'foods' => $items->values()->map(function (DoAn $food) {
                    $isCombo = strcasecmp(trim($food->category?->name ?? ''), 'Combo') === 0;

                    if ($isCombo) {
                        $comboItems = $food->comboItems->map(function ($comboItem) {
                            return [
                                'variant_id' => $comboItem->food_variant_id,
                                'name' => $comboItem->variant?->doAn->name ?? $comboItem->variant?->value,
                                'variant' => $comboItem->variant?->value,
                                'price' => (float) ($comboItem->variant?->price ?? 0),
                                'quantity' => (int) ($comboItem->quantity ?? 1),
                                'stock' => (int) ($comboItem->variant?->stock_quantity ?? 0),
                            ];
                        });

                        $price = $comboItems->sum(fn($item) => $item['price'] * $item['quantity']);
                        $available = $comboItems->map(fn($item) => $item['quantity'] > 0 ? intdiv($item['stock'], $item['quantity']) : 0)->min() ?? 0;

                        return [
                            'id' => $food->id, 'name' => $food->name, 'description' => $food->description, 'image' => $food->image,
                            'is_combo' => true, 'price' => $price, 'available' => $available, 'combo_items' => $comboItems,
                        ];
                    }

                    $variants = $food->variants->map(function ($variant) use ($food) {
                        return [
                            'id' => $variant->id, 'food_id' => $variant->food_id, 'food_name' => $food->name,
                            'value' => $variant->value, 'price' => (float) $variant->price, 'stock' => (int) $variant->stock_quantity,
                        ];
                    });

                    return [
                        'id' => $food->id, 'name' => $food->name, 'description' => $food->description, 'image' => $food->image,
                        'is_combo' => false, 'variants' => $variants,
                    ];
                })->values(),
            ];
        })->values();

        return view('user.dat_ve.chon_do_an', [
            'suatChieu' => $suatChieu,
            'selectedSeats' => $selectedSeats,
            'menu' => $menu,
            'seatTotalPrice' => $seatTotalPrice,
        ]);
    }

    public function checkout($suat_chieu_id)
    {
        $suatChieu = SuatChieu::with(['phim', 'rapChieuPhim', 'phongChieu'])->findOrFail($suat_chieu_id);
        $identifier = Auth::id() ?? session()->getId();
        $selectedSeats = collect(explode(',', request('ghe')))->map(fn($seat) => strtoupper(trim($seat)))->filter()->unique()->values();

        foreach ($selectedSeats as $seat) {
            $lock = Cache::get("seat_lock:suat:{$suat_chieu_id}:seat:{$seat}");
            if (!$lock || ($lock['identifier'] ?? null) != $identifier || ($lock['expires_at'] ?? 0) < now()->timestamp) {
                return redirect()->route('dat_ve.chon_ghe', $suatChieu->phim->slug)->with('error', 'Ghế đã hết thời gian giữ.');
            }
        }

        $seatModels = GheNgoi::with('loaiGhe')->where('phong_chieu_id', $suatChieu->phong_chieu_id)->whereIn('ma_ghe', $selectedSeats)->get();
        $seatTotal = $selectedSeats->count();
        $seatTotalPrice = $seatModels->sum(function ($seat) use ($suatChieu) {
            $price = $suatChieu->gia_ve + ($seat->loaiGhe->phu_thu ?? 0);
            if ($seat->loaiGhe?->la_couple) { $price = ($suatChieu->gia_ve * 2) + ($seat->loaiGhe->phu_thu ?? 0); }
            return $price;
        });

        $foodItems = collect(json_decode(request('food_cart', '[]'), true));
        $foodTotal = $foodItems->sum(fn($item) => ($item['price'] ?? 0) * ($item['qty'] ?? 0));
        $grandTotal = $seatTotalPrice + $foodTotal;

        return view('user.dat_ve.checkout', compact('suatChieu', 'selectedSeats', 'seatTotal', 'seatTotalPrice', 'foodItems', 'foodTotal', 'grandTotal'));
    }

    public function xuLyThanhToan(Request $request, $movie)
    {
        $suatChieu = SuatChieu::with(['phim', 'rapChieuPhim', 'phongChieu'])->findOrFail($movie);
        $selectedSeats = collect(explode(',', $request->input('ghe')))->map(fn($s) => strtoupper(trim($s)))->filter()->unique()->values();
        $identifier = Auth::id() ?? session()->getId();

        foreach ($selectedSeats as $seat) {
            $lock = Cache::get("seat_lock:suat:{$suatChieu->id}:seat:{$seat}");
            if ($request->input('payment_method') !== 'gia_lap' && (!$lock || ($lock['identifier'] ?? null) != $identifier)) {
                return redirect()->route('dat_ve.chon_ghe', $suatChieu->phim->slug)->with('error', 'Ghế hết hạn giữ.');
            }
        }

        $seatModels = GheNgoi::with('loaiGhe')->where('phong_chieu_id', $suatChieu->phong_chieu_id)->whereIn('ma_ghe', $selectedSeats)->get();
        $seatTotalPrice = $seatModels->sum(function ($seat) use ($suatChieu) {
            return $seat->loaiGhe?->la_couple ? ($suatChieu->gia_ve * 2) + ($seat->loaiGhe->phu_thu ?? 0) : $suatChieu->gia_ve + ($seat->loaiGhe->phu_thu ?? 0);
        });

        $foodItems = collect(json_decode($request->input('food_cart', '[]'), true));
        $foodTotal = $foodItems->sum(fn($i) => ($i['price'] ?? 0) * ($i['qty'] ?? 0));
        $grandTotal = max(0, ($seatTotalPrice + $foodTotal) - 20000);

        $maVe = $this->taoMaVeLocal();

        $duLieuTam = [
            'nguoi_dung_id' => Auth::id(),
            'suat_chieu_id' => $suatChieu->id,
            'ma_ve' => $maVe,
            'ten_phim' => $suatChieu->phim->ten_phim,
            'ten_rap' => $suatChieu->rapChieuPhim->ten_rap,
            'ten_phong' => $suatChieu->phongChieu->ten_phong ?? 'Phòng 1',
            'ma_ghe' => $selectedSeats->join(', '),
            'thoi_gian_chieu' => $suatChieu->thoi_gian_chieu->toDateTimeString(),
            'tong_tien' => $grandTotal,
            'loai_ve' => 'truc_tuyen',
            'danh_sach_ghe' => $selectedSeats->toArray()
        ];

        Cache::put("pending_ve:{$maVe}", $duLieuTam, now()->addMinutes(15));

        $method = $request->input('payment_method');
        
        // =======================================================
        // LUỒNG 1: THANH TOÁN QUA CỔNG VNPAY
        // =======================================================
        if ($method === 'online') {
            $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
            $vnp_Returnurl = route('dat_ve.vnpay_callback');
            
            $vnp_TmnCode = env('VNP_TMNCODE');
            $vnp_HashSecret = env('VNP_HASHSECRET');

            $inputData = [
                "vnp_Version" => "2.1.0", "vnp_TmnCode" => $vnp_TmnCode, "vnp_Amount" => $grandTotal * 100,
                "vnp_Command" => "pay", "vnp_CreateDate" => date('YmdHis'), "vnp_CurrCode" => "VND", 
                "vnp_IpAddr" => $request->ip(), "vnp_Locale" => "vn", "vnp_OrderInfo" => "Thanh toan ve: " . $maVe, 
                "vnp_OrderType" => "billpayment", "vnp_ReturnUrl" => $vnp_Returnurl, "vnp_TxnRef" => $maVe, 
            ];

            ksort($inputData);
            $query = ""; $i = 0; $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) { $hashdata .= '&' . urlencode($key) . "=" . urlencode($value); } 
                else { $hashdata .= urlencode($key) . "=" . urlencode($value); $i = 1; }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            $vnp_Url = $vnp_Url . "?" . $query;
            if (isset($vnp_HashSecret)) { $vnp_Url .= 'vnp_SecureHash=' . hash_hmac('sha512', rtrim($hashdata, '&'), $vnp_HashSecret); }
            return redirect()->away($vnp_Url);
        }

        // =======================================================
        // LUỒNG 2: THANH TOÁN QUA CỔNG VIETQR (PAYOS)
        // =======================================================
        if ($method === 'vietqr') {
            try {
                // Biến đổi chuỗi chữ mã vé thành số nguyên int duy nhất tương thích với PayOS
                $orderCode = intval(filter_var(microtime(true) * 10000, FILTER_SANITIZE_NUMBER_INT)) % 9007199254740991;

                // Ánh xạ số orderCode của PayOS sang mã vé chuỗi để giải mã khi Callback phản hồi
                Cache::put("payos_mapping:{$orderCode}", $maVe, now()->addMinutes(15));

                // Khởi tạo cổng kết nối cục bộ
                $payOS = new PayOS(env('PAYOS_CLIENT_ID'), env('PAYOS_API_KEY'), env('PAYOS_CHECKSUM_KEY'));

                $paymentData = [
                    "orderCode" => $orderCode,
                    "amount" => (int) $grandTotal,
                    "description" => "Cinema " . $maVe,
                    "returnUrl" => route('dat_ve.vnpay_callback'), // Sử dụng chung hàm callback điều hướng thông minh
                    "cancelUrl" => route('home')
                ];

                $response = $payOS->createPaymentLink($paymentData);
                
                if (isset($response['checkoutUrl'])) {
                    return redirect()->away($response['checkoutUrl']);
                }
                
                return redirect()->back()->with('error', 'Không thể khởi tạo đường dẫn kết nối VietQR.');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Lỗi kết nối API VietQR: ' . $e->getMessage());
            }
        }

        $ve = VeXemPhim::create([
            'nguoi_dung_id' => $duLieuTam['nguoi_dung_id'], 'suat_chieu_id' => $duLieuTam['suat_chieu_id'], 'ma_ve' => $duLieuTam['ma_ve'],
            'ten_phim' => $duLieuTam['ten_phim'], 'ten_rap' => $duLieuTam['ten_rap'], 'ten_phong' => $duLieuTam['ten_phong'],
            'ma_ghe' => $duLieuTam['ma_ghe'], 'thoi_gian_chieu' => $duLieuTam['thoi_gian_chieu'], 'tong_tien' => $duLieuTam['tong_tien'],
            'loai_ve' => $duLieuTam['loai_ve'], 'trang_thai' => 'da_thanh_toan',
        ]);

        foreach ($selectedSeats as $seat) { Cache::forget("seat_lock:suat:{$suatChieu->id}:seat:{$seat}"); }
        Cache::forget("pending_ve:{$maVe}");
        $this->congDiemThanhVienLocal($ve);
        return redirect()->route('dat_ve.thanh_toan_thanh_cong', $ve->id);
    }

    public function vnpayCallback(Request $request)
    {
        // 🌟 KIỂM TRA ĐIỀU HƯỚNG THÔNG MINH: Nếu có tham số của PayOS truyền về
        if ($request->filled('orderCode') && $request->filled('status')) {
            $orderCode = $request->input('orderCode');
            $status = $request->input('status');
            
            $maVe = Cache::get("payos_mapping:{$orderCode}");
            if (!$maVe) { return redirect()->route('home')->with('error', 'Giao dịch VietQR hết hạn hoặc không tìm thấy.'); }

            $bookingData = Cache::get("pending_ve:{$maVe}");
            if (!$bookingData) { return redirect()->route('home')->with('error', 'Phiên đặt vé đã kết thúc.'); }

            if ($status === 'PAID') {
                $ve = VeXemPhim::create([
                    'nguoi_dung_id' => $bookingData['nguoi_dung_id'], 'suat_chieu_id' => $bookingData['suat_chieu_id'], 'ma_ve' => $bookingData['ma_ve'],
                    'ten_phim' => $bookingData['ten_phim'], 'ten_rap' => $bookingData['ten_rap'], 'ten_phong' => $bookingData['ten_phong'],
                    'ma_ghe' => $bookingData['ma_ghe'], 'thoi_gian_chieu' => $bookingData['thoi_gian_chieu'], 'tong_tien' => $bookingData['tong_tien'],
                    'loai_ve' => $bookingData['loai_ve'], 'trang_thai' => 'da_thanh_toan',
                ]);

                foreach ($bookingData['danh_sach_ghe'] as $seat) { Cache::forget("seat_lock:suat:{$bookingData['suat_chieu_id']}:seat:{$seat}"); }
                Cache::forget("pending_ve:{$maVe}");
                Cache::forget("payos_mapping:{$orderCode}");
                $this->congDiemThanhVienLocal($ve);
                return redirect()->route('dat_ve.thanh_toan_thanh_cong', $ve->id);
            }

            Cache::forget("pending_ve:{$maVe}");
            Cache::forget("payos_mapping:{$orderCode}");
            return redirect()->route('home')->with('error', 'Hủy bỏ thanh toán qua cổng VietQR.');
        }

        // 🌟 NẾU KHÔNG THÌ CHẠY TIẾP TỤC LUỒNG CALLBACK CŨ CỦA VNPAY
        $vnp_TxnRef = $request->input('vnp_TxnRef');
        $vnp_ResponseCode = $request->input('vnp_ResponseCode');
        $maVe = $vnp_TxnRef;

        $bookingData = Cache::get("pending_ve:{$maVe}");
        if (!$bookingData) { return redirect()->route('home')->with('error', 'Phiên giao dịch đặt vé đã hết hạn.'); }

        if ($vnp_ResponseCode === '00') {
            $ve = VeXemPhim::create([
                'nguoi_dung_id' => $bookingData['nguoi_dung_id'], 'suat_chieu_id' => $bookingData['suat_chieu_id'], 'ma_ve' => $bookingData['ma_ve'],
                'ten_phim' => $bookingData['ten_phim'], 'ten_rap' => $bookingData['ten_rap'], 'ten_phong' => $bookingData['ten_phong'],
                'ma_ghe' => $bookingData['ma_ghe'], 'thoi_gian_chieu' => $bookingData['thoi_gian_chieu'], 'tong_tien' => $bookingData['tong_tien'],
                'loai_ve' => $bookingData['loai_ve'], 'trang_thai' => 'da_thanh_toan',
            ]);

            foreach ($bookingData['danh_sach_ghe'] as $seat) { Cache::forget("seat_lock:suat:{$bookingData['suat_chieu_id']}:seat:{$seat}"); }
            Cache::forget("pending_ve:{$maVe}");
            $this->congDiemThanhVienLocal($ve);
            return redirect()->route('dat_ve.thanh_toan_thanh_cong', $ve->id);
        }

        Cache::forget("pending_ve:{$maVe}");
        return redirect()->route('home')->with('error', 'Giao dịch qua VNPAY thất bại hoặc bị hủy.');
    }

    public function xacNhanVietQR($ma_ve)
    {
        $bookingData = Cache::get("pending_ve:{$ma_ve}");
        if (!$bookingData) { return redirect()->route('home')->with('error', 'Phiên đặt vé đã hết hạn.'); }

        $ve = VeXemPhim::create([
            'nguoi_dung_id' => $bookingData['nguoi_dung_id'], 'suat_chieu_id' => $bookingData['suat_chieu_id'], 'ma_ve' => $bookingData['ma_ve'],
            'ten_phim' => $bookingData['ten_phim'], 'ten_rap' => $bookingData['ten_rap'], 'ten_phong' => $bookingData['ten_phong'],
            'ma_ghe' => $bookingData['ma_ghe'], 'thoi_gian_chieu' => $bookingData['thoi_gian_chieu'], 'tong_tien' => $bookingData['tong_tien'],
            'loai_ve' => $bookingData['loai_ve'], 'trang_thai' => 'da_thanh_toan',
        ]);

        foreach ($bookingData['danh_sach_ghe'] as $seat) { Cache::forget("seat_lock:suat:{$bookingData['suat_chieu_id']}:seat:{$seat}"); }
        Cache::forget("pending_ve:{$ma_ve}");
        $this->congDiemThanhVienLocal($ve);
        return redirect()->route('dat_ve.thanh_toan_thanh_cong', $ve->id);
    }

    public function thanhToanThanhCong($ve_id)
    {
        $ve = VeXemPhim::findOrFail($ve_id);

        $thoiGianChieu = \Carbon\Carbon::parse($ve->thoi_gian_chieu);
        $ngayChieu = $thoiGianChieu->format('d/m/Y');
        $gioChieu = $thoiGianChieu->format('H:i');

        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($ve->ma_ve);

        return response("
        <div style='background:#0a0a0a; color:#fff; min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center; font-family: system-ui, -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; padding:30px; box-sizing: border-box;'>
            <div style='background:#141414; border:1px solid rgba(255,255,255,0.08); width:100%; max-width:540px; border-radius:28px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.7);'>
                
                <div style='background: linear-gradient(135deg, #eab308, #ca8a04); padding: 32px 24px; text-align: center; color: #000;'>
                    <div style='font-size: 48px; margin-bottom: 12px; line-height: 1;'>🎉</div>
                    <h1 style='margin:0; font-size:26px; font-weight:900; text-transform:uppercase; letter-spacing:1.5px;'>ĐẶT VÉ THÀNH CÔNG!</h1>
                    <p style='margin:8px 0 0 0; font-size:14px; font-weight:600; opacity:0.85;'>Chúc bạn có một buổi xem phim vui vẻ</p>
                </div>

                <div style='padding: 32px; position: relative;'>
                    
                    <h2 style='color:#facc15; font-size:24px; font-weight:900; margin:0 0 20px 0; text-transform:uppercase; line-height:1.4; letter-spacing: 0.5px;'>
                        {$ve->ten_phim}
                    </h2>

                    <div style='display:grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;'>
                        <div>
                            <span style='font-size:12px; color:#9ca3af; text-transform:uppercase; font-weight:700; letter-spacing: 0.5px;'>Rạp chiếu</span>
                            <div style='font-size:16px; font-weight:700; color:#f3f4f6; margin-top:4px;'>{$ve->ten_rap}</div>
                        </div>
                        <div>
                            <span style='font-size:12px; color:#9ca3af; text-transform:uppercase; font-weight:700; letter-spacing: 0.5px;'>Phòng chiếu</span>
                            <div style='font-size:16px; font-weight:700; color:#f3f4f6; margin-top:4px;'>{$ve->ten_phong}</div>
                        </div>
                        <div>
                            <span style='font-size:12px; color:#9ca3af; text-transform:uppercase; font-weight:700; letter-spacing: 0.5px;'>Ngày chiếu</span>
                            <div style='font-size:16px; font-weight:700; color:#f3f4f6; margin-top:4px;'>{$ngayChieu}</div>
                        </div>
                        <div>
                            <span style='font-size:12px; color:#9ca3af; text-transform:uppercase; font-weight:700; letter-spacing: 0.5px;'>Suất chiếu</span>
                            <div style='font-size:16px; font-weight:700; color:#facc15; margin-top:4px;'>{$gioChieu}</div>
                        </div>
                    </div>

                    <div style='background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); padding:16px 20px; border-radius:14px; margin-bottom:28px; display:flex; justify-content:space-between; align-items:center;'>
                        <div>
                            <span style='font-size:12px; color:#9ca3af; text-transform:uppercase; font-weight:700; letter-spacing: 0.5px;'>Ghế đã chọn</span>
                            <div style='font-size:18px; font-weight:900; color:#fff; margin-top:4px;'>{$ve->ma_ghe}</div>
                        </div>
                        <div style='text-align:right;'>
                            <span style='font-size:12px; color:#9ca3af; text-transform:uppercase; font-weight:700; letter-spacing: 0.5px;'>Tổng tiền</span>
                            <div style='font-size:18px; font-weight:900; color:#facc15; margin-top:4px;'>".number_format($ve->tong_tien)."đ</div>
                        </div>
                    </div>

                    <div style='border-top: 2px dashed rgba(255,255,255,0.15); margin: 28px 0; position: relative;'>
                        <div style='position:absolute; width:24px; height:24px; background:#0a0a0a; border-radius:50%; top:-12px; left:-45px; border-right:1px solid rgba(255,255,255,0.08); box-sizing:border-box;'></div>
                        <div style='position:absolute; width:24px; height:24px; background:#0a0a0a; border-radius:50%; top:-12px; right:-45px; border-left:1px solid rgba(255,255,255,0.08); box-sizing:border-box;'></div>
                    </div>

                    <div style='text-align:center; margin-top:15px;'>
                        <div style='background:#fff; padding:16px; display:inline-block; border-radius:20px; box-shadow:0 10px 25px rgba(0,0,0,0.3);'>
                            <img src='{$qrUrl}' style='width:180px; height:180px; display:block;' alt='Mã QR vé'>
                        </div>
                        <div style='margin-top:16px;'>
                            <span style='font-size:12px; color:#9ca3af; text-transform:uppercase; font-weight:700; letter-spacing:1px; display:block;'>Mã số vé (Ticket Code)</span>
                            <strong style='font-size:18px; color:#fff; letter-spacing:2px; font-family:monospace; display:block; margin-top:6px;'>{$ve->ma_ve}</strong>
                        </div>
                    </div>

                </div>

                <div style='padding:0 32px 32px 32px; display:flex; flex-direction:column; gap:14px;'>
                    <a href='".route('user.ve_xem_phim.index')."' style='background:#facc15; color:#000; text-align:center; padding:15px; font-weight:900; font-size:14px; text-transform:uppercase; letter-spacing:1px; text-decoration:none; border-radius:14px; transition:0.2s; box-shadow:0 4px 14px rgba(234,179,8,0.3); display:block;'>
                        🎫 Quản lý vé của tôi
                    </a>
                    
                    <a href='".route('home')."' style='background:rgba(255,255,255,0.05); color:#9ca3af; text-align:center; padding:15px; font-weight:700; font-size:14px; text-transform:uppercase; letter-spacing:1px; text-decoration:none; border-radius:14px; border:1px solid rgba(255,255,255,0.08); transition:0.2s; display:block;'>
                        🏠 Quay lại Trang chủ
                    </a>
                </div>

            </div>
        </div>
        ");
    }

    private function taoMaVeLocal(): string
    {
        do { $maVe = 'VE' . Carbon::now('Asia/Ho_Chi_Minh')->format('ymd') . Str::upper(Str::random(6)); } 
        while (VeXemPhim::where('ma_ve', $maVe)->exists());
        return $maVe;
    }

    private function congDiemThanhVienLocal(VeXemPhim $veXemPhim): void
    {
        if (!$veXemPhim->nguoi_dung_id || $veXemPhim->trang_thai !== 'da_thanh_toan') return;
        $thanhVien = ThanhVien::firstOrCreate(['nguoi_dung_id' => $veXemPhim->nguoi_dung_id], [
            'ma_thanh_vien' => 'TV' . str_pad($veXemPhim->nguoi_dung_id, 6, '0', STR_PAD_LEFT),
            'hang_thanh_vien' => 'member', 'diem_hien_tai' => 0, 'tong_diem_tich_luy' => 0, 'ngay_tham_gia' => now(),
            'ma_gioi_thieu' => '',
        ]);
        $diemCong = (int) floor((float) $veXemPhim->tong_tien / 10000);
        if (method_exists($thanhVien, 'congDiem')) { $thanhVien->congDiem($diemCong, $veXemPhim, 'Tích lũy mua vé.'); } 
        else { $thanhVien->increment('diem_hien_tai', $diemCong); $thanhVien->increment('tong_diem_tich_luy', $diemCong); }
    }
}