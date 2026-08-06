<?php

namespace App\Http\Controllers\DatVe;

use App\Http\Controllers\Controller;
use App\Models\BienTheDoAn;
use App\Models\DoAn;
use App\Models\FoodInvoice;
use App\Models\RapChieuPhim;
use App\Models\SuatChieu;
use App\Services\DatVeXemPhimService;
use App\Services\FoodInventoryService;
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

        // EAGER LOADING Thêm phongChieu.gheNgois
        $danhSachSuatChieu = SuatChieu::with(['phim.genres', 'rapChieuPhim', 'phongChieu.gheNgois', 'veXemPhims'])
            ->where('rap_chieu_phim_id', $rap->id)
            ->whereDate('thoi_gian_chieu', $selectedDate)
            ->when($selectedDate->isToday(), fn($query) => $query->where('thoi_gian_chieu', '>=', $now))
            ->when(request('phim_id'), fn($query) => $query->where('phim_id', request('phim_id')))
            ->orderBy('thoi_gian_chieu')
            ->get();

        $suatChieuTheoPhim = $danhSachSuatChieu->groupBy('phim_id');

        foreach ($suatChieuTheoPhim as $suatChieus) {
            foreach ($suatChieus as $suat) {
                // 1. Tổng số ghế đang HOẠT ĐỘNG trong phòng chiếu
                $tongGhe = $suat->phongChieu?->gheNgois?->where('trang_thai', 'hoat_dong')->count() ?? 0;

                // 2. Lọc các vé hợp lệ đang chiếm giữ ghế (bao gồm đơn chờ thanh toán chưa hết hạn)
                $validTickets = $suat->veXemPhims->filter(function ($ve) {
                    if (in_array($ve->trang_thai, ['da_dat', 'da_thanh_toan', 'da_su_dung'])) {
                        return true;
                    }
                    if ($ve->trang_thai === 'cho_thanh_toan' && $ve->thoi_gian_het_han && Carbon::parse($ve->thoi_gian_het_han)->isFuture()) {
                        return true;
                    }
                    return false;
                });

                // 3. Tách các mã ghế trong từng vé (ví dụ "A1, A2") để đếm tổng số ghế đã giữ/đặt
                $bookedSeats = collect();
                foreach ($validTickets as $ve) {
                    if (!empty($ve->ma_ghe)) {
                        foreach (explode(',', (string) $ve->ma_ghe) as $code) {
                            $seatCode = strtoupper(trim($code));
                            if ($seatCode !== '') {
                                $bookedSeats->push($seatCode);
                            }
                        }
                    }
                }

                $gheDaDatCount = $bookedSeats->unique()->count();

                $suat->tong_ghe = $tongGhe;
                $suat->ghe_da_dat = $gheDaDatCount;
                $suat->ghe_trong = max(0, $tongGhe - $gheDaDatCount);
            }
        }

        return view('user.dat_ve.chon_phim', compact('rap', 'suatChieuTheoPhim', 'dateOptions', 'selectedDate'));
    }

public function chonGhe($id)
{
    $suatChieu = SuatChieu::with(['phim', 'rapChieuPhim', 'phongChieu.gheNgois.loaiGhe', 'veXemPhims'])->find($id);

    if (!$suatChieu) {
        $phim = Phims::where('id', $id)->orWhere('slug', $id)->first();
        if ($phim) {
            $suatChieu = SuatChieu::with(['phim', 'rapChieuPhim', 'phongChieu.gheNgois.loaiGhe', 'veXemPhims'])
                ->where('phim_id', $phim->id)
                ->where('thoi_gian_chieu', '>=', now())
                ->orderBy('thoi_gian_chieu')
                ->first();
        }
    }

    if (!$suatChieu) {
        return redirect()->route('dat_ve.chon_phim')->with('error', 'Không tìm thấy suất chiếu phù hợp.');
    }

    // Xử lý nút Hủy & Chọn lại từ View
    if (request()->boolean('reset') && Auth::check()) {
        $oldTicket = VeXemPhim::where('nguoi_dung_id', Auth::id())
            ->where('suat_chieu_id', $suatChieu->id)
            ->where('trang_thai', 'cho_thanh_toan')
            ->first();

        if ($oldTicket) {
            $seats = explode(',', (string) $oldTicket->ma_ghe);
            foreach ($seats as $seat) {
                $seatCode = strtoupper(trim($seat));
                if ($seatCode) {
                    Cache::forget("seat_lock:suat:{$oldTicket->suat_chieu_id}:seat:{$seatCode}");
                }
            }
            $oldTicket->delete();
        }
        return redirect()->route('dat_ve.chon_ghe', ['movie' => $suatChieu->id]);
    }

    // Lấy vé đang chờ thanh toán nếu có
    $pendingTicket = null;
    if (Auth::check()) {
        $pendingTicket = VeXemPhim::where('nguoi_dung_id', Auth::id())
            ->where('suat_chieu_id', $suatChieu->id)
            ->where('trang_thai', 'cho_thanh_toan')
            ->first();

        if ($pendingTicket && $pendingTicket->isExpired()) {
            $seats = explode(',', (string) $pendingTicket->ma_ghe);
            foreach ($seats as $seat) {
                $seatCode = strtoupper(trim($seat));
                if ($seatCode) {
                    Cache::forget("seat_lock:suat:{$pendingTicket->suat_chieu_id}:seat:{$seatCode}");
                }
            }
            $pendingTicket->delete();
            $pendingTicket = null;
        }
    }

    $pendingTicketId = $pendingTicket?->id;
    $pendingDeadline = $pendingTicket?->thoi_gian_het_han?->valueOf();

    // Quét ghế đã bán / đã đặt thành công
    $bookedTickets = VeXemPhim::where('suat_chieu_id', $suatChieu->id)
        ->when($pendingTicketId, fn($q) => $q->where('id', '!=', $pendingTicketId))
        ->where(function ($q) {
            $q->whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung', 'da_dat'])
                ->orWhere(function ($sub) {
                    $sub->where('trang_thai', 'cho_thanh_toan')->where('thoi_gian_het_han', '>', now());
                });
        })
        ->pluck('ma_ghe');

    $bookedSeats = [];
    foreach ($bookedTickets as $maGheStr) {
        foreach (explode(',', (string)$maGheStr) as $code) {
            $seatCode = strtoupper(trim($code));
            if ($seatCode) $bookedSeats[$seatCode] = true;
        }
    }

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

    $selectedSeats = collect();
    if ($pendingTicket) {
        $selectedSeats = collect(explode(',', (string)$pendingTicket->ma_ghe))->map(fn($s) => strtoupper(trim($s)))->filter()->unique()->values();
    } elseif (request()->filled('ghe')) {
        $selectedSeats = collect(explode(',', request('ghe')))->map(fn($s) => strtoupper(trim($s)))->filter()->unique()->values();
    }

    $viewName = view()->exists('user.dat_ve.chon_ghe') ? 'user.dat_ve.chon_ghe' : 'dat_ve.chon_ghe';

    return view($viewName, compact('suatChieu', 'gheTheoHang', 'selectedSeats', 'pendingTicket', 'pendingTicketId', 'pendingDeadline'));
}

   public function chonDoAn($suat_chieu_id)
{
    $suatChieu = SuatChieu::with(['phim', 'rapChieuPhim', 'phongChieu'])->findOrFail($suat_chieu_id);
    $identifier = Auth::id() ?? session()->getId();

    // 1. DỌN DẸP TRIỆT ĐỂ: Đảm bảo 1 tài khoản chỉ có duy nhất 1 đơn chờ thanh toán tại một thời điểm
    if (Auth::check()) {
        $existingPendingTickets = VeXemPhim::where('nguoi_dung_id', Auth::id())
            ->where('trang_thai', 'cho_thanh_toan')
            ->get();

        foreach ($existingPendingTickets as $ticket) {
            // Nếu vé pending thuộc suất chiếu khác HOẶC đã hết hạn -> Giải phóng ghế & xóa vé cũ ngay
            if ($ticket->suat_chieu_id != $suatChieu->id || $ticket->isExpired()) {
                $this->giaiPhongVePending($ticket);
            }
        }
    }

    $selectedSeats = collect(explode(',', request('ghe')))->map(fn($seat) => strtoupper(trim($seat)))->filter()->unique()->values();

    $pendingTicket = Auth::check()
        ? VeXemPhim::where('nguoi_dung_id', Auth::id())->where('suat_chieu_id', $suatChieu->id)->where('trang_thai', 'cho_thanh_toan')->first()
        : null;

    if ($selectedSeats->isEmpty() && $pendingTicket) {
        $selectedSeats = collect(explode(',', $pendingTicket->ma_ghe))->map(fn($seat) => strtoupper(trim($seat)))->filter()->unique()->values();
    }

    if ($selectedSeats->isEmpty()) {
        return redirect()->route('dat_ve.chon_ghe', ['movie' => $suatChieu->id])->with('error', 'Vui lòng chọn ít nhất một ghế!');
    }

    // 2. Đồng bộ Lock Cache & Set Key
    $setKey = "seat_lock_set:suat:{$suat_chieu_id}";
    $seatIds = Cache::get($setKey, []);

    foreach ($selectedSeats as $seat) {
        $lockKey = "seat_lock:suat:{$suat_chieu_id}:seat:{$seat}";
        $lock = Cache::get($lockKey);
        $isOwner = $pendingTicket && str_contains(strtoupper($pendingTicket->ma_ghe), $seat);

        if (!$lock || $isOwner) {
            Cache::put($lockKey, [
                'identifier' => $identifier,
                'user_id' => Auth::id(),
                'reserved_at' => now()->timestamp,
                'expires_at' => now()->addMinutes(7)->timestamp,
            ], now()->addMinutes(7));

            if (!in_array($seat, $seatIds)) {
                $seatIds[] = $seat;
            }
        }
    }
    Cache::put($setKey, array_unique($seatIds), now()->addMinutes(30));

    $seatModels = GheNgoi::with('loaiGhe')->where('phong_chieu_id', $suatChieu->phong_chieu_id)->whereIn('ma_ghe', $selectedSeats)->get();

    $seatTotalPrice = $seatModels->sum(function ($seat) use ($suatChieu) {
        $price = $suatChieu->gia_ve + ($seat->loaiGhe->phu_thu ?? 0);
        if ($seat->loaiGhe?->la_couple) $price = ($suatChieu->gia_ve * 2) + ($seat->loaiGhe->phu_thu ?? 0);
        return $price;
    });

    $foodItems = collect(json_decode(request()->query('food_cart', '[]'), true));
    if ($foodItems->isEmpty() && $pendingTicket) {
        $foodItems = collect($pendingTicket->food_items ?? []);
    }

    $foodTotal = $foodItems->sum(fn($item) => ($item['price'] ?? 0) * ($item['qty'] ?? 0));

    if ($pendingTicket) {
        $pendingTicket->update([
            'ma_ghe' => $selectedSeats->join(', '),
            'tong_tien' => $seatTotalPrice + $foodTotal,
            'seat_total' => $seatTotalPrice,
            'food_items' => $foodItems->toArray(),
            'food_total' => $foodTotal,
        ]);
    } else {
        $pendingTicket = VeXemPhim::create([
            'nguoi_dung_id' => Auth::id(),
            'suat_chieu_id' => $suatChieu->id,
            'ma_ve' => $this->taoMaVeLocal(),
            'ten_phim' => $suatChieu->phim->ten_phim,
            'ten_rap' => $suatChieu->rapChieuPhim->ten_rap,
            'ten_phong' => $suatChieu->phongChieu->ten_phong ?? 'Phòng 1',
            'ma_ghe' => $selectedSeats->join(', '),
            'thoi_gian_chieu' => $suatChieu->thoi_gian_chieu,
            'tong_tien' => $seatTotalPrice + $foodTotal,
            'tien_hoan' => 0,
            'loai_ve' => 'truc_tuyen',
            'trang_thai' => 'cho_thanh_toan',
            'thoi_gian_het_han' => now()->addMinutes(7),
            'food_items' => $foodItems->toArray(),
            'seat_total' => $seatTotalPrice,
            'food_total' => $foodTotal,
        ]);
    }

    $foods = DoAn::active()->with(['category', 'variants', 'comboItems.variant.doAn'])->whereHas('category')->orderBy('sort_order')->orderBy('name')->get();

    $menu = $foods->groupBy(fn($f) => $f->category?->is_combo ? 'Combo' : trim($f->category?->name ?? 'Khác'))
        ->map(function ($items, $category) {
            return [
                'category' => $category,
                'foods' => $items->values()->map(function (DoAn $food) {
                    $isCombo = $food->category?->is_combo ?? false;
                    if ($isCombo) {
                        $comboItems = $food->comboItems->map(fn($ci) => [
                            'variant_id' => $ci->food_variant_id,
                            'name' => $ci->variant?->doAn->name ?? $ci->variant?->value,
                            'variant' => $ci->variant?->value,
                            'price' => (float)($ci->variant?->price ?? 0),
                            'quantity' => (int)($ci->quantity ?? 1),
                            'stock' => (int)($ci->variant?->stock_quantity ?? 0),
                        ]);
                        $price = $comboItems->sum(fn($item) => $item['price'] * $item['quantity']);
                        $available = $comboItems->map(fn($item) => $item['quantity'] > 0 ? intdiv($item['stock'], $item['quantity']) : 0)->min() ?? 0;

                        return [
                            'id' => $food->id, 'name' => $food->name, 'description' => $food->description,
                            'image' => $food->image, 'is_combo' => true, 'price' => (float)($food->price ?? $price),
                            'available' => $available, 'combo_items' => $comboItems->toArray(),
                        ];
                    }

                    return [
                        'id' => $food->id, 'name' => $food->name, 'description' => $food->description,
                        'image' => $food->image, 'is_combo' => false,
                        'variants' => $food->variants->map(fn($v) => [
                            'id' => $v->id, 'food_id' => $v->food_id, 'food_name' => $food->name,
                            'value' => $v->value, 'price' => (float)$v->price, 'stock' => (int)$v->stock_quantity,
                        ]),
                    ];
                })->values(),
            ];
        })->values();

    return view('user.dat_ve.chon_do_an', [
        'suatChieu' => $suatChieu,
        'selectedSeats' => $selectedSeats,
        'menu' => $menu,
        'seatTotalPrice' => $seatTotalPrice,
        'pendingTicketId' => $pendingTicket->id,
        'pendingDeadline' => $pendingTicket->thoi_gian_het_han?->valueOf(),
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
                return redirect()->route('dat_ve.chon_ghe', ['movie' => $suatChieu->id])->with('error', 'Ghế đã hết thời gian giữ.');
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

            foreach ($selectedSeats as $seat) {
                Cache::put("seat_lock:suat:{$suat_chieu_id}:seat:{$seat}", [
                    'identifier' => $identifier,
                    'expires_at' => now()->addMinutes(7)->timestamp,
                ], now()->addMinutes(7));
            }
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
                    ->route('dat_ve.chon_ghe', ['movie' => $suatChieu->id])
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

                return back()->withInput()->with('error', 'Không thể kết nối VietQR lúc này. Vui lòng thử lại sau.');
            }
        }

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

            return back()->withInput()->with('error', 'Không thể hoàn tất đặt vé lúc này. Vui lòng thử lại sau.');
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
        if ($request->filled('orderCode')) {
            $orderCode = $request->input('orderCode');
            $status = strtoupper((string) $request->input('status', ''));
            $code = (string) $request->input('code', '');
            $isCancel = $request->boolean('cancel') || $status === 'CANCELLED' || $code === '24';
            $maVe = Cache::get("payos_mapping:{$orderCode}");

            if ($maVe) {
                $bookingData = Cache::get("pending_ve:{$maVe}");

                if ($isCancel || ($status !== 'PAID' && $code !== '00')) {
                    if ($bookingData) {
                        $this->xuaLyHuyGiaoDich($bookingData, $maVe);
                    }
                    Cache::forget("payos_mapping:{$orderCode}");

                    return redirect()->route('user.ve_xem_phim.index')
                        ->with('error', 'Thanh toán VietQR đã bị hủy hoặc thất bại.');
                }

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
                        return redirect()->route('user.ve_xem_phim.index')->with('error', 'Không thể phát hành vé lúc này. Vui lòng thử lại sau.');
                    }
                }
            }
        }

        $maVe = $request->input('vnp_TxnRef');
        $responseCode = $request->input('vnp_ResponseCode');
        $bookingData = Cache::get("pending_ve:{$maVe}");

        if ($bookingData) {
            if ($responseCode !== '00') {
                $this->xuaLyHuyGiaoDich($bookingData, $maVe);

                return redirect()->route('user.ve_xem_phim.index')
                    ->with('error', 'Giao dịch VNPAY đã bị hủy hoặc thất bại.');
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
                return redirect()->route('user.ve_xem_phim.index')->with('error', 'Không thể phát hành vé lúc này. Vui lòng thử lại sau.');
            }
        }

        return redirect()->route('user.ve_xem_phim.index');
    }

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


    private function xacNhanVietQR($ma_ve)
    {
        $bookingData = Cache::get("pending_ve:{$ma_ve}");

        if (!$bookingData) {
            return redirect()->route('home')->with('error', 'Phiên đặt vé đã hết hạn.');
        }

        try {
            $ve = $this->taoVeTuDuLieuTam($bookingData);
        } catch (\Throwable $exception) {
            Log::error('CONFIRM VIETQR BOOKING ERROR', ['message' => $exception->getMessage()]);
            return redirect()->route('home')->with('error', 'Không thể phát hành vé lúc này. Vui lòng thử lại sau.');
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

        if ($ve->trang_thai === 'cho_thanh_toan') {
            return redirect()->route('user.ve_xem_phim.index')
                ->with('error', 'Đơn hàng của bạn chưa hoàn tất thanh toán.');
        }

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

            $existingTicket = VeXemPhim::query()
                ->where('ma_ve', $bookingData['ma_ve'])
                ->lockForUpdate()
                ->first();

            if (!$existingTicket && !empty($bookingData['pending_ticket_id'])) {
                $existingTicket = VeXemPhim::query()
                    ->lockForUpdate()
                    ->find($bookingData['pending_ticket_id']);
            }

            if ($existingTicket) {
                if ($existingTicket->trang_thai === 'da_thanh_toan') {
                    return $existingTicket;
                }

                if ($existingTicket->trang_thai === 'cho_thanh_toan') {
                    if ($existingTicket->isExpired()) {
                        $this->giaiPhongVePending($existingTicket);
                        throw new \RuntimeException('Phiên thanh toán đã hết hạn (7 phút).');
                    }

                    $this->truKhoDoAn($bookingData['food_items'] ?? []);

                    $existingTicket->update([
                        'ma_ghe' => $bookingData['ma_ghe'],
                        'thoi_gian_chieu' => $bookingData['thoi_gian_chieu'],
                        'tong_tien' => $bookingData['tong_tien'],
                        'tien_hoan' => $bookingData['tien_hoan'] ?? 0,
                        'loai_ve' => $bookingData['loai_ve'] ?? 'truc_tuyen',
                        'trang_thai' => 'da_thanh_toan',
                        'food_items' => $bookingData['food_items'] ?? [],
                        'voucher_id' => $voucherCaNhan?->id,
                    ]);

                    if ($voucherCaNhan) {
                        $voucherCaNhan->update(['da_su_dung' => true, 'ngay_su_dung' => now()]);
                    }

                    $this->taoHoaDonDoAnChoVe($existingTicket, $bookingData['food_items'] ?? []);

                    return $existingTicket;
                }
            }

            $selectedSeats = collect($bookingData['danh_sach_ghe'] ?? [])
                ->map(fn($seat) => strtoupper(trim($seat)))
                ->filter()
                ->unique()
                ->values();

            $pendingTicketId = $bookingData['pending_ticket_id'] ?? null;
            $blockedSeats = VeXemPhim::query()
                ->where('suat_chieu_id', $bookingData['suat_chieu_id'])
                ->when($pendingTicketId, fn($q) => $q->where('id', '!=', $pendingTicketId))
                ->where(function ($q) {
                    $q->whereIn('trang_thai', ['dang_giu', 'da_dat', 'da_thanh_toan', 'da_su_dung'])
                        ->orWhere(function ($sub) {
                            $sub->where('trang_thai', 'cho_thanh_toan')
                                ->where('thoi_gian_het_han', '>', now());
                        });
                })
                ->lockForUpdate()
                ->pluck('ma_ghe')
                ->flatMap(function ($codes) {
                    return collect(explode(',', (string) $codes))
                        ->map(fn($code) => strtoupper(trim($code)))
                        ->filter();
                })
                ->intersect($selectedSeats);

            if ($blockedSeats->isNotEmpty()) {
                throw new \RuntimeException('Ghế đang trong quá trình thanh toán bởi người khác: ' . $blockedSeats->implode(', '));
            }

            $this->truKhoDoAn($bookingData['food_items'] ?? []);

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
                'food_items' => $bookingData['food_items'] ?? [],
                'voucher_id' => $voucherCaNhan?->id,
            ]);

            if ($voucherCaNhan) {
                $voucherCaNhan->update(['da_su_dung' => true, 'ngay_su_dung' => now()]);
            }

            $this->taoHoaDonDoAnChoVe($ve, $bookingData['food_items'] ?? []);

            return $ve;
        });
    }

    /**
     * Giỏ đồ ăn (food_cart) không có field "id" — mỗi dòng có "key" dạng
     * "variant-{food_variant_id}" (món lẻ, đã chọn size cụ thể) hoặc
     * "combo-{food_id}" (combo). Hàm này giải mã key về food_id/variant_id thật.
     */
    private function phanTichCartItem(array $item): ?array
    {
        $quantity = (int) ($item['qty'] ?? 0);
        $key = (string) ($item['key'] ?? '');

        if ($quantity <= 0 || $key === '') {
            return null;
        }

        if (str_starts_with($key, 'combo-')) {
            $foodId = (int) substr($key, strlen('combo-'));

            return $foodId > 0 ? ['food_id' => $foodId, 'variant_id' => null, 'quantity' => $quantity] : null;
        }

        if (str_starts_with($key, 'variant-')) {
            $variantId = (int) substr($key, strlen('variant-'));
            $variant = $variantId > 0 ? BienTheDoAn::find($variantId) : null;

            return $variant ? ['food_id' => $variant->food_id, 'variant_id' => $variant->id, 'quantity' => $quantity] : null;
        }

        return null;
    }

    /**
     * Trừ kho (biến thể + combo) theo giỏ đồ ăn của đơn đặt vé online.
     */
    private function truKhoDoAn(array $foodItems): void
    {
        if (empty($foodItems)) {
            return;
        }

        $items = collect($foodItems)
            ->map(fn($item) => $this->phanTichCartItem($item))
            ->filter();

        if ($items->isEmpty()) {
            return;
        }

        (new FoodInventoryService())->deduct($items);
    }

    /**
     * Ghi lại hóa đơn đồ ăn cho đơn đặt vé online để hiển thị trong
     * trang quản trị "Hóa đơn đồ ăn & Combo" (liên kết qua ticket_id).
     */
    private function taoHoaDonDoAnChoVe(VeXemPhim $ve, array $foodItems): void
    {
        if (empty($foodItems) || FoodInvoice::where('ticket_id', $ve->id)->exists()) {
            return;
        }

        $items = collect($foodItems)
            ->map(function ($item) {
                $parsed = $this->phanTichCartItem($item);
                $quantity = (int) ($item['qty'] ?? 0);
                $unitPrice = (float) ($item['price'] ?? 0);

                return [
                    'food_id' => $parsed['food_id'] ?? null,
                    'food_variant_id' => $parsed['variant_id'] ?? null,
                    'food_name' => $item['name'] ?? 'Đồ ăn',
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $quantity * $unitPrice,
                ];
            })
            ->filter(fn($item) => $item['quantity'] > 0)
            ->values();

        if ($items->isEmpty()) {
            return;
        }

        $subtotal = $items->sum('total_price');

        $invoice = FoodInvoice::create([
            'invoice_code' => 'FD-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
            'user_id' => $ve->nguoi_dung_id,
            'ticket_id' => $ve->id,
            'customer_name' => $ve->nguoiDung?->ho_ten,
            'customer_phone' => $ve->nguoiDung?->so_dien_thoai,
            'subtotal' => $subtotal,
            'discount' => 0,
            'total' => $subtotal,
            'payment_status' => 'paid',
            'inventory_deducted' => true,
            'payment_method' => $ve->payment_method,
            'note' => 'Đặt kèm vé #' . $ve->ma_ve,
        ]);

        foreach ($items as $item) {
            $invoice->items()->create($item);
        }
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

        $thanhVien = ThanhVien::firstOrCreate(
            [
                'nguoi_dung_id' => $veXemPhim->nguoi_dung_id,
            ],
            [
                'ma_thanh_vien' => 'TV' . str_pad(
                    $veXemPhim->nguoi_dung_id,
                    6,
                    '0',
                    STR_PAD_LEFT
                ),
                'ma_gioi_thieu' => ThanhVien::taoMaGioiThieu(
                    $veXemPhim->nguoi_dung_id
                ),
                'nguoi_gioi_thieu_id' => null,
                'da_nhan_thuong' => false,
                'hang_thanh_vien' => 'member',
                'diem_hien_tai' => 0,
                'tong_diem_tich_luy' => 0,
                'ngay_tham_gia' => now(),
            ]
        );

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
            ->where(function ($q) {
                $q->whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung', 'da_dat'])
                    ->orWhere(function ($sub) {
                        $sub->where('trang_thai', 'cho_thanh_toan')
                            ->where('thoi_gian_het_han', '>', now());
                    });
            })
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

    // 1. AJAX Polling: Lấy danh sách các ghế đang bị giữ tạm thời trong Cache
public function getLockedSeats($suat_chieu_id)
{
    $identifier = Auth::id() ?? session()->getId();
    
    // Lấy tất cả cache key bắt đầu bằng seat_lock:suat:{id}
    // Chú ý: Nếu dùng Redis hoặc Cache Driver mặc định (file/array)
    $locked = [];
    $prefix = "seat_lock:suat:{$suat_chieu_id}:seat:";
    
    // Quét đơn giản danh sách ghế từ Cache (hoặc qua helper nếu bạn lưu theo array)
    // Trường hợp dùng Cache file mặc định của Laravel:
    $seatCodes = GheNgoi::pluck('ma_ghe');
    foreach ($seatCodes as $code) {
        $key = $prefix . strtoupper(trim($code));
        $lockData = Cache::get($key);
        if ($lockData) {
            // Không trả về ghế do chính user này đang giữ
            if (($lockData['identifier'] ?? null) !== $identifier) {
                $locked[strtoupper(trim($code))] = true;
            }
        }
    }

    return response()->json(['locked' => $locked]);
}

// 2. Lock 1 ghế khi click chọn
public function lockSeat(Request $request, $suat_chieu_id, $seat_code)
{
    $seat = strtoupper(trim($seat_code));
    $identifier = Auth::id() ?? session()->getId();
    $key = "seat_lock:suat:{$suat_chieu_id}:seat:{$seat}";

    $existingLock = Cache::get($key);
    if ($existingLock && ($existingLock['identifier'] ?? null) !== $identifier) {
        return response()->json(['message' => 'Ghế đã bị người khác chọn.'], 409);
    }

    // Giữ ghế trong Cache 7 phút
    Cache::put($key, [
        'identifier' => $identifier,
        'user_id' => Auth::id(),
        'expires_at' => now()->addMinutes(7)->timestamp,
    ], now()->addMinutes(7));

    return response()->json(['message' => 'Giữ ghế thành công.']);
}

// 3. Unlock 1 ghế khi bỏ chọn
public function unlockSeat(Request $request, $suat_chieu_id, $seat_code)
{
    $seat = strtoupper(trim($seat_code));
    $key = "seat_lock:suat:{$suat_chieu_id}:seat:{$seat}";
    
    Cache::forget($key);
    
    return response()->json(['message' => 'Hủy giữ ghế thành công.']);
}

// 4. Hủy tất cả ghế đang giữ (Dành cho sendBeacon khi tắt trang hoặc đi nơi khác)
public function releaseAllSeats(Request $request, $suat_chieu_id)
{
    $identifier = Auth::id() ?? session()->getId();
    $seatCodes = GheNgoi::pluck('ma_ghe');
    
    foreach ($seatCodes as $code) {
        $seat = strtoupper(trim($code));
        $key = "seat_lock:suat:{$suat_chieu_id}:seat:{$seat}";
        $lock = Cache::get($key);
        
        if ($lock && ($lock['identifier'] ?? null) === $identifier) {
            Cache::forget($key);
        }
    }

    return response()->json(['message' => 'Đã giải phóng tất cả ghế.']);
}

private function giaiPhongVePending(VeXemPhim $ve): void
{
    $seats = explode(',', (string) $ve->ma_ghe);
    $setKey = "seat_lock_set:suat:{$ve->suat_chieu_id}";
    $seatIds = Cache::get($setKey, []);

    foreach ($seats as $seat) {
        $seatCode = strtoupper(trim($seat));
        if ($seatCode) {
            Cache::forget("seat_lock:suat:{$ve->suat_chieu_id}:seat:{$seatCode}");
            $seatIds = array_diff($seatIds, [$seatCode]);
        }
    }

    Cache::put($setKey, array_values(array_unique($seatIds)), now()->addMinutes(30));
    $ve->delete();
}
}
