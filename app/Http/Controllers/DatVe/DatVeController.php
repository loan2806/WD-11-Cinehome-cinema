<?php

namespace App\Http\Controllers\DatVe;

use App\Http\Controllers\Controller;
use App\Models\DoAn;
use App\Models\RapChieuPhim;
use App\Models\SuatChieu;
use App\Services\DatVeXemPhimService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\NguoiDungVoucher;
use Carbon\Carbon;
use App\Models\GheNgoi;
use App\Models\Phims;
use App\Models\VeXemPhim;
use App\Models\ThanhVien;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\VeXemPhimDaDatMail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use PayOS\PayOS;

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
            } catch (\Exception $e) {
                Log::error('DATE PARSING ERROR', [
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                ]);

                return redirect()->back()
                    ->with('error', 'Lỗi định dạng ngày chiếu: ' . $e->getMessage());
            }
        }

        if ($selectedDate->lt($today)) {
            $selectedDate = $today;
        }

        if ($selectedDate->gt($limitDay)) {
            $selectedDate = $limitDay;
        }

        $weekdayShort = [
            'Sun' => 'CN',
            'Mon' => 'T2',
            'Tue' => 'T3',
            'Wed' => 'T4',
            'Thu' => 'T5',
            'Fri' => 'T6',
            'Sat' => 'T7',
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

    public function chonGhe($id)
    {
        // 1. Tìm suất chiếu theo ID Suất chiếu hoặc Slug/ID Phim
        $suatChieu = SuatChieu::with([
            'phim',
            'rapChieuPhim',
            'phongChieu.gheNgois.loaiGhe',
            'veXemPhims'
        ])->find($id);

        if (!$suatChieu) {
            $phim = Phims::where('id', $id)->orWhere('slug', $id)->first();
            if ($phim) {
                $suatChieu = SuatChieu::with([
                    'phim',
                    'rapChieuPhim',
                    'phongChieu.gheNgois.loaiGhe',
                    'veXemPhims'
                ])->where('phim_id', $phim->id)
                  ->where('thoi_gian_chieu', '>=', now())
                  ->orderBy('thoi_gian_chieu')
                  ->first();
            }
        }

        if (!$suatChieu) {
            return redirect()->route('dat_ve.chon_phim')->with('error', 'Không tìm thấy suất chiếu phù hợp.');
        }

        // 2. Kiểm tra và dọn dẹp đơn hàng chờ (pending) nếu hết hạn 7 phút
        $pendingTicket = null;
        if (Auth::check()) {
            $pendingTicket = VeXemPhim::where('nguoi_dung_id', Auth::id())
                ->where('suat_chieu_id', $suatChieu->id)
                ->where('trang_thai', 'cho_thanh_toan')
                ->first();

            if ($pendingTicket && $pendingTicket->isExpired()) {
                $this->giaiPhongVePending($pendingTicket);
                $pendingTicket = null;
            }
        }

        $pendingTicketId = $pendingTicket?->id;
        $pendingDeadline = $pendingTicket?->thoi_gian_het_han?->valueOf();

        // 3. Lấy danh sách ghế đã được đặt/bán
        $bookedTickets = VeXemPhim::where('suat_chieu_id', $suatChieu->id)
            ->whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung', 'da_dat'])
            ->pluck('ma_ghe');

        $bookedSeats = [];
        foreach ($bookedTickets as $maGheStr) {
            foreach (explode(',', (string)$maGheStr) as $code) {
                $seatCode = strtoupper(trim($code));
                if ($seatCode) {
                    $bookedSeats[$seatCode] = true;
                }
            }
        }

        // 4. Nhóm danh sách ghế phòng chiếu theo Hàng (Row)
        $gheNgois = $suatChieu->phongChieu->gheNgois ?? collect();

        $gheTheoHang = [];
        foreach ($gheNgois as $ghe) {
            $hang = $ghe->hang_ghe ?? preg_replace('/[0-9]/', '', $ghe->ma_ghe);
            if (!$hang) $hang = 'A';

            $daDat = isset($bookedSeats[strtoupper(trim($ghe->ma_ghe))]);
            $baoTri = $ghe->trang_thai !== 'hoat_dong';
            $chonDuoc = !$daDat && !$baoTri;

            $loaiGheNorm = mb_strtolower($ghe->loaiGhe->ten_loai_ghe ?? 'Thường');
            $phuThu = (float)($ghe->loaiGhe->phu_thu ?? 0);
            $giaVe = (float)$suatChieu->gia_ve + $phuThu;

            if ($ghe->loaiGhe?->la_couple || str_contains($loaiGheNorm, 'couple') || str_contains($loaiGheNorm, 'đôi') || str_contains($loaiGheNorm, 'doi')) {
                $giaVe = ((float)$suatChieu->gia_ve * 2) + $phuThu;
            }

            $gheTheoHang[$hang][] = [
                'id' => $ghe->id,
                'ma_ghe' => $ghe->ma_ghe,
                'loai_ghe' => $ghe->loaiGhe->ten_loai_ghe ?? 'Thường',
                'gia' => $giaVe,
                'phu_thu' => $phuThu,
                'mau_sac' => $ghe->loaiGhe->mau_sac ?? '#3b82f6',
                'da_dat' => $daDat,
                'bao_tri' => $baoTri,
                'chon_duoc' => $chonDuoc,
            ];
        }

        // 5. Lấy danh sách ghế đang chọn sẵn (từ vé chờ hoặc query parameter)
        $selectedSeats = collect();
        if ($pendingTicket) {
            $selectedSeats = collect(explode(',', (string)$pendingTicket->ma_ghe))
                ->map(fn($s) => strtoupper(trim($s)))
                ->filter()
                ->unique()
                ->values();
        } elseif (request()->filled('ghe')) {
            $selectedSeats = collect(explode(',', request('ghe')))
                ->map(fn($s) => strtoupper(trim($s)))
                ->filter()
                ->unique()
                ->values();
        }

        $viewName = view()->exists('user.dat_ve.chon_ghe') ? 'user.dat_ve.chon_ghe' : 'dat_ve.chon_ghe';

        return view($viewName, compact(
            'suatChieu',
            'gheTheoHang',
            'selectedSeats',
            'pendingTicketId',
            'pendingDeadline'
        ));
    }

    public function chonDoAn($suat_chieu_id)
    {
        $suatChieu = SuatChieu::with(['phim', 'rapChieuPhim', 'phongChieu'])->findOrFail($suat_chieu_id);
        $identifier = Auth::id() ?? session()->getId();
        $selectedSeats = collect(explode(',', request('ghe')))
            ->map(fn($seat) => strtoupper(trim($seat)))
            ->filter()
            ->unique()
            ->values();

        // 🌟 KHÓA CHẶN 3: KIỂM TRA ĐƠN HÀNG CHỜ KHÁC
        if (Auth::check()) {
            $otherPending = VeXemPhim::where('nguoi_dung_id', Auth::id())
                ->where('trang_thai', 'cho_thanh_toan')
                ->where('suat_chieu_id', '!=', $suatChieu->id)
                ->first();

            if ($otherPending) {
                if ($otherPending->isExpired()) {
                    $this->giaiPhongVePending($otherPending);
                } else {
                    return redirect()->route('user.ve_xem_phim.index')
                        ->with('error', 'Bạn đang có đơn hàng chờ thanh toán ở suất chiếu khác. Vui lòng hoàn tất hoặc hủy đơn trước!');
                }
            }
        }

        $pendingTicket = Auth::check()
            ? VeXemPhim::where('nguoi_dung_id', Auth::id())
                ->where('suat_chieu_id', $suatChieu->id)
                ->where('trang_thai', 'cho_thanh_toan')
                ->first()
            : null;

        if ($pendingTicket && $pendingTicket->isExpired()) {
            $this->giaiPhongVePending($pendingTicket);
            $pendingTicket = null;
        }

        if ($pendingTicket && $selectedSeats->isEmpty()) {
            $selectedSeats = collect(explode(',', $pendingTicket->ma_ghe))
                ->map(fn($seat) => strtoupper(trim($seat)))
                ->filter()
                ->unique()
                ->values();
        }

        if (!$this->validateSeatsAdjacent($selectedSeats->toArray(), $suat_chieu_id)) {
            foreach ($selectedSeats as $seat) {
                Cache::forget("seat_lock:suat:{$suat_chieu_id}:seat:{$seat}");
            }

            $message = 'Các ghế bạn chọn phải cạnh nhau trong cùng một hàng!';
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return redirect()->back()->withInput()->with('error', $message);
        }

        foreach ($selectedSeats as $seat) {
            $lock = Cache::get("seat_lock:suat:{$suat_chieu_id}:seat:{$seat}");
            if (!$lock || ($lock['identifier'] ?? null) != $identifier || ($lock['expires_at'] ?? 0) < now()->timestamp) {
                return redirect()->route('dat_ve.chon_ghe', $suatChieu->phim->slug)->with('error', 'Ghế đã hết thời gian giữ.');
            }
        }

        abort_if($suatChieu->thoi_gian_chieu->lt(now('Asia/Ho_Chi_Minh')), 404);

        $seatModels = GheNgoi::with('loaiGhe')
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

        $foodItems = collect(json_decode(request()->query('food_cart', '[]'), true));
        if ($foodItems->isEmpty() && $pendingTicket) {
            $foodItems = collect($pendingTicket->food_items ?? []);
        }

        // 🌟 BƯỚC 1: SANG CHỌN ĐỒ ĂN MỚI CHÍNH THỨC TẠO VÉ "CHO_THANH_TOAN" (THỜI GIAN 7 PHÚT)
        if ($selectedSeats->isNotEmpty()) {
            if ($pendingTicket) {
                $pendingTicket->update([
                    'ma_ghe' => $selectedSeats->join(', '),
                    'tong_tien' => $seatTotalPrice + $pendingTicket->food_total,
                    'seat_total' => $seatTotalPrice,
                    'food_items' => $foodItems->toArray(),
                    'food_total' => $foodItems->sum(fn($item) => ($item['price'] ?? 0) * ($item['qty'] ?? 0)),
                ]);
            } else {
                $pendingTicket = VeXemPhim::create([
                    'nguoi_dung_id' => Auth::id(),
                    'nhan_vien_id' => null,
                    'suat_chieu_id' => $suatChieu->id,
                    'ma_ve' => $this->taoMaVeLocal(),
                    'ten_phim' => $suatChieu->phim->ten_phim,
                    'ten_rap' => $suatChieu->rapChieuPhim->ten_rap,
                    'ten_phong' => $suatChieu->phongChieu->ten_phong ?? 'Phòng 1',
                    'ma_ghe' => $selectedSeats->join(', '),
                    'thoi_gian_chieu' => $suatChieu->thoi_gian_chieu,
                    'tong_tien' => $seatTotalPrice,
                    'tien_hoan' => 0,
                    'loai_ve' => 'truc_tuyen',
                    'trang_thai' => 'cho_thanh_toan',
                    'thoi_gian_het_han' => now()->addMinutes(7),
                    'food_items' => $foodItems->toArray(),
                    'seat_total' => $seatTotalPrice,
                    'food_total' => $foodItems->sum(fn($item) => ($item['price'] ?? 0) * ($item['qty'] ?? 0)),
                ]);
            }
        }

        $pendingTicketId = $pendingTicket?->id;
        $pendingDeadline = $pendingTicket?->thoi_gian_het_han?->valueOf();

        $foods = DoAn::active()->with([
            'category',
            'variants' => function ($query) {
                $query->where('is_active', true)->orderBy('price');
            },
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
                            'id' => $food->id,
                            'name' => $food->name,
                            'description' => $food->description,
                            'image' => $food->image,
                            'is_combo' => true,
                            'price' => $price,
                            'available' => $available,
                            'combo_items' => $comboItems,
                        ];
                    }

                    $variants = $food->variants->map(function ($variant) use ($food) {
                        return [
                            'id' => $variant->id,
                            'food_id' => $variant->food_id,
                            'food_name' => $food->name,
                            'value' => $variant->value,
                            'price' => (float) $variant->price,
                            'stock' => (int) $variant->stock_quantity,
                        ];
                    });

                    return [
                        'id' => $food->id,
                        'name' => $food->name,
                        'description' => $food->description,
                        'image' => $food->image,
                        'is_combo' => false,
                        'variants' => $variants,
                    ];
                })->values(),
            ];
        })->values();

        return view('user.dat_ve.chon_do_an', [
            'suatChieu' => $suatChieu,
            'selectedSeats' => $selectedSeats,
            'menu' => $menu,
            'seatTotalPrice' => $seatTotalPrice,
            'pendingTicketId' => $pendingTicketId ?? null,
            'pendingDeadline' => $pendingDeadline ?? null,
            'initialFoodCart' => $foodItems->values()->all(),
        ]);
    }

    public function checkout(Request $request, $suat_chieu_id)
    {
        $suatChieu = SuatChieu::with(['phim', 'rapChieuPhim', 'phongChieu'])->findOrFail($suat_chieu_id);
        $identifier = Auth::id() ?? session()->getId();

        $pendingTicket = null;
        if ($request->filled('pending_ticket_id')) {
            $pendingTicket = VeXemPhim::where('id', $request->input('pending_ticket_id'))
                ->where('nguoi_dung_id', Auth::id())
                ->where('trang_thai', 'cho_thanh_toan')
                ->first();
        }

        if (!$pendingTicket) {
            $pendingTicket = VeXemPhim::where('nguoi_dung_id', Auth::id())
                ->where('suat_chieu_id', $suatChieu->id)
                ->where('trang_thai', 'cho_thanh_toan')
                ->first();
        }

        if ($pendingTicket && $pendingTicket->isExpired()) {
            $this->giaiPhongVePending($pendingTicket);

            return redirect()
                ->route('user.ve_xem_phim.index')
                ->with('error', 'Phiên thanh toán đã hết hạn (7 phút). Vui lòng chọn lại vé.');
        }

        if ($pendingTicket) {
            $selectedSeats = collect(explode(',', $pendingTicket->ma_ghe))
                ->map(fn($seat) => strtoupper(trim($seat)))
                ->filter()
                ->unique()
                ->values();
            $foodItems = collect($pendingTicket->food_items ?? []);
            $pendingTicketId = $pendingTicket->id;
            $pendingDeadline = $pendingTicket?->thoi_gian_het_han?->valueOf();

            if ($request->filled('food_cart')) {
                $parsedFood = collect(json_decode($request->query('food_cart', '[]'), true));
                if ($parsedFood->isNotEmpty()) {
                    $foodItems = $parsedFood;
                    $pendingTicket->update([
                        'food_items' => $foodItems->toArray(),
                        'food_total' => $foodItems->sum(fn($item) => ($item['price'] ?? 0) * ($item['qty'] ?? 0)),
                    ]);
                }
            }
        } else {
            $selectedSeats = collect(explode(',', $request->query('ghe')))
                ->map(fn($seat) => strtoupper(trim($seat)))
                ->filter()
                ->unique()
                ->values();
            $foodItems = collect(json_decode($request->query('food_cart', '[]'), true));
            $pendingTicketId = null;
            $pendingDeadline = null;
        }

        if (!$this->validateSeatsAdjacent($selectedSeats->toArray(), $suat_chieu_id)) {
            foreach ($selectedSeats as $seat) {
                Cache::forget("seat_lock:suat:{$suat_chieu_id}:seat:{$seat}");
            }

            $message = 'Các ghế bạn chọn phải cạnh nhau trong cùng một hàng!';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return redirect()->back()->withInput()->with('error', $message);
        }

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
            if ($seat->loaiGhe?->la_couple) {
                $price = ($suatChieu->gia_ve * 2) + ($seat->loaiGhe->phu_thu ?? 0);
            }
            return $price;
        });

        $foodTotal = $foodItems->sum(fn($item) => ($item['price'] ?? 0) * ($item['qty'] ?? 0));
        $grandTotal = $seatTotalPrice + $foodTotal;

        if ($pendingTicket) {
            $pendingTicket->update([
                'ma_ghe' => $selectedSeats->join(', '),
                'tong_tien' => $grandTotal,
                'seat_total' => $seatTotalPrice,
                'food_total' => $foodTotal,
                'food_items' => $foodItems->toArray(),
            ]);
        }

        return view('user.dat_ve.checkout', compact(
            'suatChieu',
            'selectedSeats',
            'seatTotal',
            'seatTotalPrice',
            'foodItems',
            'foodTotal',
            'grandTotal',
            'pendingTicketId',
            'pendingDeadline'
        ));
    }

    public function apDungVoucher(Request $request)
    {
        $data = $request->validate([
            'voucher_code' => ['required', 'string', 'max:100'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng nhập để sử dụng voucher cá nhân.',
            ], 401);
        }

        $voucherCode = Str::upper(trim($data['voucher_code']));

        $voucherCaNhan = NguoiDungVoucher::query()
            ->with('voucher')
            ->where('ma_voucher_ca_nhan', $voucherCode)
            ->where('nguoi_dung_id', Auth::id())
            ->where('da_su_dung', false)
            ->where(function ($query) {
                $query->whereNull('ngay_het_han')
                    ->orWhere('ngay_het_han', '>=', now());
            })
            ->whereHas('voucher', function ($query) {
                $query->where('trang_thai', true)
                    ->where(function ($voucherQuery) {
                        $voucherQuery->whereNull('ngay_het_han')
                            ->orWhereDate('ngay_het_han', '>=', today());
                    });
            })
            ->first();

        if (!$voucherCaNhan || !$voucherCaNhan->voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher không thuộc tài khoản này, đã sử dụng hoặc đã hết hạn.',
            ], 422);
        }

        $subtotal = (float) $data['subtotal'];
        $discount = min((float) $voucherCaNhan->voucher->gia_tri_giam, $subtotal);

        return response()->json([
            'success' => true,
            'message' => 'Áp dụng voucher thành công.',
            'voucher_code' => $voucherCaNhan->ma_voucher_ca_nhan,
            'discount' => $discount,
            'final_total' => max(0, $subtotal - $discount),
        ]);
    }

    public function xuLyThanhToan(Request $request, $movie)
    {
        $request->validate([
            'ghe' => ['required', 'string'],
            'food_cart' => ['nullable', 'string'],
            'payment_method' => ['required', 'in:online,vietqr,gia_lap'],
            'voucher_code' => ['nullable', 'string', 'max:100'],
        ]);

        $suatChieu = SuatChieu::with(['phim', 'rapChieuPhim', 'phongChieu'])
            ->findOrFail($movie);

        $selectedSeats = collect(explode(',', $request->input('ghe')))
            ->map(fn($seat) => strtoupper(trim($seat)))
            ->filter()
            ->unique()
            ->values();

        $pendingTicketId = $request->input('pending_ticket_id');
        $pendingTicket = null;

        if (!empty($pendingTicketId)) {
            $pendingTicket = VeXemPhim::where('id', $pendingTicketId)
                ->where('nguoi_dung_id', Auth::id())
                ->where('trang_thai', 'cho_thanh_toan')
                ->first();

            if ($pendingTicket && $pendingTicket->isExpired()) {
                $this->giaiPhongVePending($pendingTicket);

                return back()->with('error', 'Phiên thanh toán đã hết hạn (7 phút). Vui lòng thực hiện lại.');
            }
        }

        $identifier = Auth::id() ?? session()->getId();

        if ($selectedSeats->isEmpty()) {
            return back()->withInput()->with('error', 'Vui lòng chọn ít nhất một ghế.');
        }

        if (!$this->validateSeatsAdjacent($selectedSeats->toArray(), $suatChieu->id)) {
            foreach ($selectedSeats as $seat) {
                Cache::forget("seat_lock:suat:{$suatChieu->id}:seat:{$seat}");
            }

            return back()->withInput()->with('error', 'Các ghế bạn chọn phải cạnh nhau trong cùng một hàng!');
        }

        foreach ($selectedSeats as $seat) {
            $lock = Cache::get("seat_lock:suat:{$suatChieu->id}:seat:{$seat}");

            if (
                $request->input('payment_method') !== 'gia_lap'
                && (
                    !$lock
                    || ($lock['identifier'] ?? null) != $identifier
                    || ($lock['expires_at'] ?? 0) < now()->timestamp
                )
            ) {
                return redirect()
                    ->route('dat_ve.chon_ghe', $suatChieu->phim->slug)
                    ->with('error', 'Ghế đã hết thời gian giữ.');
            }
        }

        $seatModels = GheNgoi::with('loaiGhe')
            ->where('phong_chieu_id', $suatChieu->phong_chieu_id)
            ->whereIn('ma_ghe', $selectedSeats)
            ->get();

        if ($seatModels->count() !== $selectedSeats->count()) {
            return back()->withInput()->with('error', 'Danh sách ghế không hợp lệ.');
        }

        $seatTotalPrice = $seatModels->sum(function ($seat) use ($suatChieu) {
            if ($seat->loaiGhe?->la_couple) {
                return ($suatChieu->gia_ve * 2) + ($seat->loaiGhe->phu_thu ?? 0);
            }
            return $suatChieu->gia_ve + ($seat->loaiGhe->phu_thu ?? 0);
        });

        $foodItems = collect(json_decode($request->input('food_cart', '[]'), true));
        $foodTotal = $foodItems->sum(fn($item) => (float) ($item['price'] ?? 0) * (int) ($item['qty'] ?? 0));

        $subtotal = (float) $seatTotalPrice + (float) $foodTotal;
        $voucherCaNhan = null;
        $voucherDiscount = 0;

        if ($request->filled('voucher_code')) {
            if (!Auth::check()) {
                return back()->withInput()->with('error', 'Bạn cần đăng nhập để sử dụng voucher cá nhân.');
            }

            $voucherCode = Str::upper(trim($request->input('voucher_code')));

            $voucherCaNhan = NguoiDungVoucher::query()
                ->with('voucher')
                ->where('ma_voucher_ca_nhan', $voucherCode)
                ->where('nguoi_dung_id', Auth::id())
                ->where('da_su_dung', false)
                ->where(function ($query) {
                    $query->whereNull('ngay_het_han')->orWhere('ngay_het_han', '>=', now());
                })
                ->whereHas('voucher', function ($query) {
                    $query->where('trang_thai', true)
                        ->where(function ($voucherQuery) {
                            $voucherQuery->whereNull('ngay_het_han')->orWhereDate('ngay_het_han', '>=', today());
                        });
                })
                ->first();

            if (!$voucherCaNhan || !$voucherCaNhan->voucher) {
                return back()->withInput()->with('error', 'Voucher không thuộc tài khoản này, đã sử dụng hoặc đã hết hạn.');
            }

            $voucherDiscount = min((float) $voucherCaNhan->voucher->gia_tri_giam, $subtotal);
        }

        $grandTotal = max(0, $subtotal - $voucherDiscount);
        $maVe = $pendingTicket ? $pendingTicket->ma_ve : $this->taoMaVeLocal();

        if ($voucherCaNhan) {
            $voucherLockKey = "voucher_checkout_lock:{$voucherCaNhan->id}";
            $locked = Cache::add($voucherLockKey, ['identifier' => $identifier, 'ma_ve' => $maVe], now()->addMinutes(7));

            if (!$locked) {
                return back()->withInput()->with('error', 'Voucher này đang được sử dụng trong một giao dịch khác.');
            }
        }

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
            'tam_tinh' => $subtotal,
            'giam_gia' => $voucherDiscount,
            'voucher_ca_nhan_id' => $voucherCaNhan?->id,
            'voucher_code' => $voucherCaNhan?->ma_voucher_ca_nhan,
            'loai_ve' => 'truc_tuyen',
            'danh_sach_ghe' => $selectedSeats->toArray(),
            'food_items' => $foodItems->toArray(),
            'source' => 'user',
            'pending_ticket_id' => $pendingTicket?->id,
        ];

        Cache::put("pending_ve:{$maVe}", $duLieuTam, now()->addMinutes(7));

        $method = $request->input('payment_method');

        if ($method === 'online') {
            $vnpUrl = 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';
            $vnpReturnUrl = route('dat_ve.vnpay_callback');
            $vnpTmnCode = env('VNP_TMNCODE');
            $vnpHashSecret = env('VNP_HASHSECRET');

            $inputData = [
                'vnp_Version' => '2.1.0',
                'vnp_TmnCode' => $vnpTmnCode,
                'vnp_Amount' => (int) round($grandTotal * 100),
                'vnp_Command' => 'pay',
                'vnp_CreateDate' => date('YmdHis'),
                'vnp_CurrCode' => 'VND',
                'vnp_IpAddr' => $request->ip(),
                'vnp_Locale' => 'vn',
                'vnp_OrderInfo' => 'Thanh toan ve: ' . $maVe,
                'vnp_OrderType' => 'billpayment',
                'vnp_ReturnUrl' => $vnpReturnUrl,
                'vnp_TxnRef' => $maVe,
            ];

            ksort($inputData);
            $query = '';
            $hashData = '';
            $index = 0;

            foreach ($inputData as $key => $value) {
                if ($index === 1) {
                    $hashData .= '&';
                }
                $hashData .= urlencode($key) . '=' . urlencode($value);
                $query .= urlencode($key) . '=' . urlencode($value) . '&';
                $index = 1;
            }

            $vnpUrl .= '?' . $query;
            if ($vnpHashSecret) {
                $vnpUrl .= 'vnp_SecureHash=' . hash_hmac('sha512', $hashData, $vnpHashSecret);
            }

            return redirect()->away($vnpUrl);
        }

        if ($method === 'vietqr') {
            try {
                $orderCode = intval(filter_var(microtime(true) * 10000, FILTER_SANITIZE_NUMBER_INT)) % 9007199254740991;

                Cache::put("payos_mapping:{$orderCode}", $maVe, now()->addMinutes(7));

                $payOS = new PayOS(
                    env('PAYOS_CLIENT_ID'),
                    env('PAYOS_API_KEY'),
                    env('PAYOS_CHECKSUM_KEY')
                );

                $paymentData = [
                    'orderCode' => $orderCode,
                    'amount' => (int) round($grandTotal),
                    'description' => 'Cinema ' . $maVe,
                    'returnUrl' => route('dat_ve.payos_callback'),
                    'cancelUrl' => route('dat_ve.payos_callback') . '?cancel=true&orderCode=' . $orderCode,
                ];

                $response = $payOS->paymentRequests->create($paymentData);
                $checkoutUrl = is_array($response) ? ($response['checkoutUrl'] ?? null) : ($response->checkoutUrl ?? null);

                if ($checkoutUrl) {
                    return redirect()->away($checkoutUrl);
                }

                $this->xoaKhoaVoucherTam($duLieuTam);
                Cache::forget("pending_ve:{$maVe}");

                return back()->withInput()->with('error', 'Không thể khởi tạo đường dẫn kết nối VietQR.');
            } catch (\Throwable $exception) {
                $this->xoaKhoaVoucherTam($duLieuTam);
                Cache::forget("pending_ve:{$maVe}");

                Log::error('PAYOS CREATE LINK ERROR', [
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ]);

                return back()->withInput()->with('error', 'Lỗi kết nối API VietQR: ' . $exception->getMessage());
            }
        }

        // Phương thức giả lập thanh toán
        try {
            $ve = $this->taoVeTuDuLieuTam($duLieuTam);
        } catch (\Throwable $exception) {
            $this->xoaKhoaVoucherTam($duLieuTam);
            Cache::forget("pending_ve:{$maVe}");

            Log::error('BOOKING CREATE ERROR', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return back()->withInput()->with('error', 'Không thể hoàn tất đặt vé: ' . $exception->getMessage());
        }

        if (!empty($duLieuTam['food_items'])) {
            Cache::put("ve_foods:{$ve->id}", $duLieuTam['food_items'], now()->addDays(30));
        }

        foreach ($selectedSeats as $seat) {
            Cache::forget("seat_lock:suat:{$suatChieu->id}:seat:{$seat}");
        }

        $this->xoaKhoaVoucherTam($duLieuTam);
        Cache::forget("pending_ve:{$maVe}");

        $this->congDiemThanhVienLocal($ve);
        $this->guiEmailXacNhanLocal($ve);

        return redirect()->route('dat_ve.thanh_toan_thanh_cong', $ve->id);
    }

    public function vnpayCallback(Request $request)
    {
        /*
         * 🌟 BƯỚC 2: BẤM HỦY THANH TOÁN TRÊN PAYOS (VIETQR)
         */
        if ($request->filled('orderCode')) {
            $orderCode = $request->input('orderCode');
            $status = strtoupper((string) $request->input('status', ''));
            $isCancel = $request->boolean('cancel') || $status === 'CANCELLED';
            $maVe = Cache::get("payos_mapping:{$orderCode}");

            if ($maVe) {
                $bookingData = Cache::get("pending_ve:{$maVe}");

                if ($isCancel || $status !== 'PAID') {
                    if ($bookingData) {
                        $this->xuaLyHuyGiaoDich($bookingData, $maVe);
                    }
                    Cache::forget("payos_mapping:{$orderCode}");

                    return redirect()->route('user.ve_xem_phim.index')
                        ->with('error', 'Thanh toán VietQR đã bị hủy. Vé của bạn đã được cập nhật trạng thái Đã Hủy.');
                }

                // THÀNH CÔNG (PAID)
                if ($bookingData) {
                    try {
                        $ve = $this->taoVeTuDuLieuTam($bookingData);
                        if (!empty($bookingData['food_items'])) {
                            Cache::put("ve_foods:{$ve->id}", $bookingData['food_items'], now()->addDays(30));
                        }
                        foreach (($bookingData['danh_sach_ghe'] ?? []) as $seat) {
                            Cache::forget("seat_lock:suat:{$bookingData['suat_chieu_id']}:seat:{$seat}");
                        }
                        $this->xoaKhoaVoucherTam($bookingData);
                        Cache::forget("pending_ve:{$maVe}");
                        Cache::forget("payos_mapping:{$orderCode}");

                        $this->congDiemThanhVienLocal($ve);
                        $this->guiEmailXacNhanLocal($ve);

                        return redirect()->route('dat_ve.thanh_toan_thanh_cong', $ve->id);
                    } catch (\Throwable $exception) {
                        Log::error('PAYOS CALLBACK BOOKING ERROR', ['message' => $exception->getMessage()]);
                        return redirect()->route('user.ve_xem_phim.index')->with('error', 'Lỗi phát hành vé: ' . $exception->getMessage());
                    }
                }
            }
        }

        /*
         * 🌟 BƯỚC 2: BẤM HỦY THANH TOÁN TRÊN VNPAY
         */
        $maVe = $request->input('vnp_TxnRef');
        $responseCode = $request->input('vnp_ResponseCode');
        $bookingData = Cache::get("pending_ve:{$maVe}");

        if ($bookingData) {
            if ($responseCode !== '00') {
                $this->xuaLyHuyGiaoDich($bookingData, $maVe);

                return redirect()->route('user.ve_xem_phim.index')
                    ->with('error', 'Giao dịch VNPAY đã bị hủy hoặc thất bại. Trạng thái vé đã cập nhật thành Đã Hủy.');
            }

            try {
                $ve = $this->taoVeTuDuLieuTam($bookingData);
                if (!empty($bookingData['food_items'])) {
                    Cache::put("ve_foods:{$ve->id}", $bookingData['food_items'], now()->addDays(30));
                }
                foreach (($bookingData['danh_sach_ghe'] ?? []) as $seat) {
                    Cache::forget("seat_lock:suat:{$bookingData['suat_chieu_id']}:seat:{$seat}");
                }
                $this->xoaKhoaVoucherTam($bookingData);
                Cache::forget("pending_ve:{$maVe}");

                $this->congDiemThanhVienLocal($ve);
                $this->guiEmailXacNhanLocal($ve);

                return redirect()->route('dat_ve.thanh_toan_thanh_cong', $ve->id);
            } catch (\Throwable $exception) {
                Log::error('VNPAY CALLBACK BOOKING ERROR', ['message' => $exception->getMessage()]);
                return redirect()->route('user.ve_xem_phim.index')->with('error', 'Không thể phát hành vé: ' . $exception->getMessage());
            }
        }

        return redirect()->route('user.ve_xem_phim.index');
    }

    /**
     * 🌟 HÀM NÚT "HỦY ĐƠN" TRỰC TIẾP TẠI TRANG "VÉ CỦA TÔI"
     */
    public function huyVePending($id)
    {
        $ve = VeXemPhim::where('id', $id)
            ->where('nguoi_dung_id', Auth::id())
            ->where('trang_thai', 'cho_thanh_toan')
            ->first();

        if ($ve) {
            $this->giaiPhongVePending($ve);
            return redirect()->route('user.ve_xem_phim.index')->with('success', 'Đã hủy đơn hàng thành công và nhả ghế trống!');
        }

        return redirect()->route('user.ve_xem_phim.index')->with('error', 'Không tìm thấy đơn hàng cần hủy hoặc đơn hàng đã xử lý.');
    }

    private function xuaLyHuyGiaoDich(array $bookingData, string $maVe): void
    {
        $pendingTicketId = $bookingData['pending_ticket_id'] ?? null;
        if ($pendingTicketId) {
            $ve = VeXemPhim::find($pendingTicketId);
            if ($ve && $ve->trang_thai === 'cho_thanh_toan') {
                $this->giaiPhongVePending($ve);
            }
        } else {
            $ve = VeXemPhim::where('ma_ve', $maVe)->where('trang_thai', 'cho_thanh_toan')->first();
            if ($ve) {
                $this->giaiPhongVePending($ve);
            }
        }

        foreach (($bookingData['danh_sach_ghe'] ?? []) as $seat) {
            Cache::forget("seat_lock:suat:{$bookingData['suat_chieu_id']}:seat:" . strtoupper(trim($seat)));
        }

        $this->xoaKhoaVoucherTam($bookingData);
        Cache::forget("pending_ve:{$maVe}");
    }

    private function giaiPhongVePending(VeXemPhim $ve): void
    {
        $ve->update(['trang_thai' => 'da_huy']);

        $seats = explode(',', (string) $ve->ma_ghe);
        foreach ($seats as $seat) {
            $seatCode = strtoupper(trim($seat));
            if ($seatCode) {
                Cache::forget("seat_lock:suat:{$ve->suat_chieu_id}:seat:{$seatCode}");
            }
        }
    }

    public function xacNhanVietQR($ma_ve)
    {
        $bookingData = Cache::get("pending_ve:{$ma_ve}");

        if (!$bookingData) {
            return redirect()->route('home')->with('error', 'Phiên đặt vé đã hết hạn.');
        }

        try {
            $ve = $this->taoVeTuDuLieuTam($bookingData);
        } catch (\Throwable $exception) {
            Log::error('CONFIRM VIETQR BOOKING ERROR', ['message' => $exception->getMessage()]);
            return redirect()->route('home')->with('error', 'Không thể phát hành vé: ' . $exception->getMessage());
        }

        if (!empty($bookingData['food_items'])) {
            Cache::put("ve_foods:{$ve->id}", $bookingData['food_items'], now()->addDays(30));
        }

        foreach (($bookingData['danh_sach_ghe'] ?? []) as $seat) {
            Cache::forget("seat_lock:suat:{$bookingData['suat_chieu_id']}:seat:{$seat}");
        }

        $this->xoaKhoaVoucherTam($bookingData);
        Cache::forget("pending_ve:{$ma_ve}");

        $this->congDiemThanhVienLocal($ve);
        $this->guiEmailXacNhanLocal($ve);

        return redirect()->route('dat_ve.thanh_toan_thanh_cong', $ve->id);
    }

    public function thanhToanThanhCong($ve_id)
    {
        $ve = VeXemPhim::findOrFail($ve_id);

        $thoiGianChieu = Carbon::parse($ve->thoi_gian_chieu);
        $ngayChieu = $thoiGianChieu->format('d/m/Y');
        $gioChieu = $thoiGianChieu->format('H:i');

        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($ve->ma_ve);

        $foodItems = Cache::get("ve_foods:{$ve->id}", []);
        $foodHtml = '';

        if (!empty($foodItems)) {
            $foodHtml .= "
            <div style='background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); padding:16px 20px; border-radius:14px; margin-bottom:20px;'>
                <span style='font-size:12px; color:#9ca3af; text-transform:uppercase; font-weight:700; letter-spacing: 0.5px; display:block; margin-bottom:10px;'>🍿 ĐỒ ĂN ĐI KÈM ĐÃ ĐẶT</span>
            ";
            foreach ($foodItems as $item) {
                $qty = $item['qty'] ?? $item['quantity'] ?? 1;
                $price = $item['price'] ?? 0;
                $name = $item['name'] ?? 'Đồ ăn';
                $foodHtml .= "
                <div style='display:flex; justify-content:space-between; font-size:14px; color:#e5e7eb; margin-bottom:6px;'>
                    <span>{$name} <span style='color:#facc15;'>x{$qty}</span></span>
                    <span style='font-weight:600; color:#fff;'>" . number_format($price * $qty) . "đ</span>
                </div>
                ";
            }
            $foodHtml .= "</div>";
        }

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

                    <div style='background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); padding:16px 20px; border-radius:14px; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center;'>
                        <div>
                            <span style='font-size:12px; color:#9ca3af; text-transform:uppercase; font-weight:700; letter-spacing: 0.5px;'>Ghế đã chọn</span>
                            <div style='font-size:18px; font-weight:900; color:#fff; margin-top:4px;'>{$ve->ma_ghe}</div>
                        </div>
                        <div style='text-align:right;'>
                            <span style='font-size:12px; color:#9ca3af; text-transform:uppercase; font-weight:700; letter-spacing: 0.5px;'>Tổng tiền</span>
                            <div style='font-size:18px; font-weight:900; color:#facc15; margin-top:4px;'>" . number_format($ve->tong_tien) . "đ</div>
                        </div>
                    </div>

                    {$foodHtml}

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
                    <a href='" . route('user.ve_xem_phim.index') . "' style='background:#facc15; color:#000; text-align:center; padding:15px; font-weight:900; font-size:14px; text-transform:uppercase; letter-spacing:1px; text-decoration:none; border-radius:14px; transition:0.2s; box-shadow:0 4px 14px rgba(234,179,8,0.3); display:block;'>
                        🎫 Quản lý vé của tôi
                    </a>
                    
                    <a href='" . route('home') . "' style='background:rgba(255,255,255,0.05); color:#9ca3af; text-align:center; padding:15px; font-weight:700; font-size:14px; text-transform:uppercase; letter-spacing:1px; text-decoration:none; border-radius:14px; border:1px solid rgba(255,255,255,0.08); transition:0.2s; display:block;'>
                        🏠 Quay lại Trang chủ
                    </a>
                </div>

            </div>
        </div>
        ");
    }

    private function taoVeTuDuLieuTam(array $bookingData): VeXemPhim
    {
        return DB::transaction(function () use ($bookingData) {
            $existingTicket = VeXemPhim::query()
                ->where('ma_ve', $bookingData['ma_ve'])
                ->lockForUpdate()
                ->first();

            if ($existingTicket) {
                return $existingTicket;
            }

            if (!empty($bookingData['pending_ticket_id'])) {
                $pendingTicket = VeXemPhim::query()
                    ->lockForUpdate()
                    ->find($bookingData['pending_ticket_id']);

                if ($pendingTicket && $pendingTicket->trang_thai === 'cho_thanh_toan') {
                    if ($pendingTicket->isExpired()) {
                        $this->giaiPhongVePending($pendingTicket);
                        throw new \RuntimeException('Phiên thanh toán đã hết hạn (7 phút).');
                    }

                    $pendingTicket->update([
                        'ma_ghe' => $bookingData['ma_ghe'],
                        'thoi_gian_chieu' => $bookingData['thoi_gian_chieu'],
                        'tong_tien' => $bookingData['tong_tien'],
                        'tien_hoan' => $bookingData['tien_hoan'] ?? 0,
                        'loai_ve' => $bookingData['loai_ve'] ?? 'truc_tuyen',
                        'trang_thai' => 'da_thanh_toan',
                        'food_items' => $bookingData['food_items'] ?? [],
                    ]);

                    return $pendingTicket;
                }
            }

            $selectedSeats = collect($bookingData['danh_sach_ghe'] ?? [])
                ->map(fn($seat) => strtoupper(trim($seat)))
                ->filter()
                ->unique()
                ->values();

            $blockedSeats = VeXemPhim::query()
                ->where('suat_chieu_id', $bookingData['suat_chieu_id'])
                ->whereIn('trang_thai', ['dang_giu', 'da_dat', 'da_thanh_toan', 'da_su_dung'])
                ->lockForUpdate()
                ->pluck('ma_ghe')
                ->flatMap(function ($codes) {
                    return collect(explode(',', (string) $codes))
                        ->map(fn($code) => strtoupper(trim($code)))
                        ->filter();
                })
                ->intersect($selectedSeats);

            if ($blockedSeats->isNotEmpty()) {
                throw new \RuntimeException('Ghế đã được bán trong thời gian thanh toán: ' . $blockedSeats->implode(', '));
            }

            $voucherCaNhan = null;
            $voucherCaNhanId = (int) ($bookingData['voucher_ca_nhan_id'] ?? 0);

            if ($voucherCaNhanId > 0) {
                $voucherCaNhan = NguoiDungVoucher::query()
                    ->with('voucher')
                    ->whereKey($voucherCaNhanId)
                    ->where('nguoi_dung_id', $bookingData['nguoi_dung_id'] ?? null)
                    ->lockForUpdate()
                    ->first();

                if (
                    !$voucherCaNhan || $voucherCaNhan->da_su_dung
                    || ($voucherCaNhan->ngay_het_han && $voucherCaNhan->ngay_het_han->lt(now()))
                    || !$voucherCaNhan->voucher || !$voucherCaNhan->voucher->trang_thai
                    || ($voucherCaNhan->voucher->ngay_het_han && $voucherCaNhan->voucher->ngay_het_han->lt(today()))
                ) {
                    throw new \RuntimeException('Voucher đã được sử dụng hoặc không hợp lệ.');
                }
            }

            foreach (($bookingData['food_items'] ?? []) as $foodItem) {
                $foodId = $foodItem['id'] ?? null;
                $quantity = (int) ($foodItem['qty'] ?? 0);

                if (!$foodId || $quantity <= 0) continue;

                $food = DoAn::query()->lockForUpdate()->find($foodId);
                if (!$food) continue;

                if (isset($food->stock_quantity) && (int) $food->stock_quantity < $quantity) {
                    throw new \RuntimeException('Đồ ăn ' . $food->name . ' không còn đủ số lượng.');
                }

                if (isset($food->stock_quantity)) {
                    $food->decrement('stock_quantity', $quantity);
                }
            }

            $ve = VeXemPhim::create([
                'nguoi_dung_id' => $bookingData['nguoi_dung_id'] ?? null,
                'nhan_vien_id' => $bookingData['nhan_vien_id'] ?? null,
                'suat_chieu_id' => $bookingData['suat_chieu_id'],
                'ma_ve' => $bookingData['ma_ve'],
                'ten_phim' => $bookingData['ten_phim'],
                'ten_rap' => $bookingData['ten_rap'],
                'ten_phong' => $bookingData['ten_phong'],
                'ma_ghe' => $bookingData['ma_ghe'],
                'thoi_gian_chieu' => $bookingData['thoi_gian_chieu'],
                'tong_tien' => $bookingData['tong_tien'],
                'tien_hoan' => $bookingData['tien_hoan'] ?? 0,
                'loai_ve' => $bookingData['loai_ve'] ?? 'truc_tuyen',
                'trang_thai' => 'da_thanh_toan',
                'voucher_id' => $voucherCaNhan?->id,
            ]);

            if ($voucherCaNhan) {
                $voucherCaNhan->update(['da_su_dung' => true, 'ngay_su_dung' => now()]);
            }

            return $ve;
        });
    }

    private function xoaKhoaVoucherTam(array $bookingData): void
    {
        $voucherCaNhanId = (int) ($bookingData['voucher_ca_nhan_id'] ?? 0);

        if ($voucherCaNhanId > 0) {
            Cache::forget("voucher_checkout_lock:{$voucherCaNhanId}");
        }
    }

    private function taoMaVeLocal(): string
    {
        do {
            $maVe = 'VE' . Carbon::now('Asia/Ho_Chi_Minh')->format('ymd') . Str::upper(Str::random(6));
        } while (VeXemPhim::where('ma_ve', $maVe)->exists());

        return $maVe;
    }

    private function congDiemThanhVienLocal(VeXemPhim $veXemPhim): void
    {
        if (!$veXemPhim->nguoi_dung_id || $veXemPhim->trang_thai !== 'da_thanh_toan') return;

        $thanhVien = ThanhVien::firstOrCreate(['nguoi_dung_id' => $veXemPhim->nguoi_dung_id], [
            'ma_thanh_vien' => 'TV' . str_pad($veXemPhim->nguoi_dung_id, 6, '0', STR_PAD_LEFT),
            'hang_thanh_vien' => 'member',
            'diem_hien_tai' => 0,
            'tong_diem_tich_luy' => 0,
            'ngay_tham_gia' => now(),
            'ma_gioi_thieu' => '',
        ]);

        $diemCong = (int) floor((float) $veXemPhim->tong_tien / 10000);
        if (method_exists($thanhVien, 'congDiem')) {
            $thanhVien->congDiem($diemCong, $veXemPhim, 'Tích lũy mua vé.');
        } else {
            $thanhVien->increment('diem_hien_tai', $diemCong);
            $thanhVien->increment('tong_diem_tich_luy', $diemCong);
        }
    }

    private function guiEmailXacNhanLocal(VeXemPhim $veXemPhim): void
    {
        try {
            if (!$veXemPhim->relationLoaded('nguoiDung')) {
                $veXemPhim->load('nguoiDung');
            }

            $email = $veXemPhim->nguoiDung->email ?? null;

            if ($email) {
                $foodItems = Cache::get("ve_foods:{$veXemPhim->id}", []);
                Mail::to($email)->send(new VeXemPhimDaDatMail($veXemPhim, $foodItems));
            }
        } catch (\Throwable $exception) {
            Log::error('LỖI GỬI VÉ QUA EMAIL', [
                've_id' => $veXemPhim->id,
                'message' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);
        }
    }

    private function validateSeatsAdjacent(array $seats, $suatChieuId): bool
    {
        if (count($seats) <= 1) {
            return true;
        }

        $parsedSeats = [];
        foreach ($seats as $seat) {
            $seat = strtoupper(trim($seat));

            if (preg_match('/^([A-Z]+)([0-9]+)$/', $seat, $matches)) {
                $parsedSeats[] = ['row' => $matches[1], 'num' => (int) $matches[2]];
            } else {
                if (str_contains($seat, '-')) {
                    $subSeats = explode('-', $seat);
                    foreach ($subSeats as $sub) {
                        if (preg_match('/^([A-Z]+)([0-9]+)$/', $sub, $matches)) {
                            $parsedSeats[] = ['row' => $matches[1], 'num' => (int) $matches[2]];
                        }
                    }
                } elseif (str_contains($seat, '|')) {
                    $subSeats = explode('|', $seat);
                    foreach ($subSeats as $sub) {
                        if (preg_match('/^([A-Z]+)([0-9]+)$/', $sub, $matches)) {
                            $parsedSeats[] = ['row' => $matches[1], 'num' => (int) $matches[2]];
                        }
                    }
                }
            }
        }

        if (empty($parsedSeats)) {
            return true;
        }

        $suatChieu = SuatChieu::findOrFail($suatChieuId);

        $bookedTickets = VeXemPhim::where('suat_chieu_id', $suatChieuId)
            ->whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung', 'da_dat'])
            ->pluck('ma_ghe');

        $unavailableSeats = [];
        foreach ($bookedTickets as $maGheString) {
            foreach (explode(',', $maGheString) as $code) {
                $unavailableSeats[strtoupper(trim($code))] = true;
            }
        }

        $inactiveSeats = GheNgoi::where('phong_chieu_id', $suatChieu->phong_chieu_id)
            ->where('trang_thai', '!=', 'hoat_dong')
            ->pluck('ma_ghe');
        foreach ($inactiveSeats as $code) {
            $unavailableSeats[strtoupper(trim($code))] = true;
        }

        $groupedByRow = collect($parsedSeats)->groupBy('row');

        foreach ($groupedByRow as $row => $seatsInRow) {
            $nums = $seatsInRow->pluck('num')->unique()->sort()->values();

            for ($i = 0; $i < $nums->count() - 1; $i++) {
                $start = $nums[$i];
                $end = $nums[$i + 1];

                if ($end - $start > 1) {
                    for ($middleNum = $start + 1; $middleNum < $end; $middleNum++) {
                        $middleSeatCode = $row . $middleNum;

                        $isBookedOrInactive = isset($unavailableSeats[$middleSeatCode]);
                        $isLockedInCache = Cache::has("seat_lock:suat:{$suatChieuId}:seat:{$middleSeatCode}");

                        if (!$isBookedOrInactive && !$isLockedInCache && !in_array($middleSeatCode, $seats)) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }
}