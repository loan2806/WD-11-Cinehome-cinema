<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\DoAn;
use App\Models\GheNgoi;
use App\Models\SuatChieu;
use App\Models\VeXemPhim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use PayOS\PayOS;
use App\Models\BienTheDoAn;
use App\Models\VeXemPhimGhe;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\ThongBaoCaNhan;

class BanVeController extends Controller
{
    private const STAFF_VIETQR_HOLD_MINUTES = 7;
    private const STAFF_PAYOS_CACHE_MINUTES = 15;

    public function index()
    {
        $this->expirePendingTickets();

        $showtimes = SuatChieu::with([
            'phim',
            'rapChieuPhim',
            'phongChieu'
        ])
            ->withCount([
                'veXemPhims as sold_tickets_count' => function ($query) {
                    $query->whereIn('trang_thai', ['da_thanh_toan', 'da_in', 'da_su_dung']);
                }
            ])
            ->where('thoi_gian_chieu', '>=', now())
            ->orderBy('thoi_gian_chieu')
            ->get();

        return view('staff.ban-ve.index', compact('showtimes'));
    }

    public function show(SuatChieu $suatChieu)
    {
        $this->expirePendingTickets($suatChieu->id);

        $suatChieu->load(['phim', 'rapChieuPhim', 'phongChieu']);

        // 1. Lấy tất cả ghế bị khóa từ bảng VeXemPhim
        $blockedFromTickets = VeXemPhim::where('suat_chieu_id', $suatChieu->id)
            ->where(function ($query) {
                $query->whereIn('trang_thai', [
                    'dang_giu', 'da_dat', 'da_thanh_toan', 'da_in', 'da_su_dung', 'cho_thanh_toan'
                ]);
            })
            ->where(function ($query) {
                $query->whereNull('thoi_gian_het_han')
                      ->orWhere('thoi_gian_het_han', '>', now());
            })
            ->pluck('ma_ghe')
            ->flatMap(function ($codes) {
                return collect(explode(',', (string)$codes))
                    ->map(fn($code) => strtoupper(trim($code)))
                    ->filter();
            });

        // 2. Lấy ghế đang giữ tạm từ Cache/Redis
        $lockedFromCache = collect(Cache::get("seat_locks_{$suatChieu->id}", []))
            ->keys()
            ->map(fn($code) => strtoupper(trim($code)));

        $blockedSeatCodes = $blockedFromTickets->concat($lockedFromCache)->unique()->values();
        $bookedSeats = $blockedSeatCodes->flip();

        $gheNgois = GheNgoi::with('loaiGhe')
            ->where('phong_chieu_id', $suatChieu->phong_chieu_id)
            ->orderBy('id')
            ->get();

        $gheTheoHang = [];
        foreach ($gheNgois as $ghe) {
            $hang = preg_replace('/[0-9]/', '', $ghe->ma_ghe) ?: 'A';
            $daDat = $bookedSeats->has(strtoupper(trim($ghe->ma_ghe)));
            $baoTri = $ghe->isEffectivelyUnderMaintenance();
            $chonDuoc = !$daDat && !$baoTri;

            $loaiGheNorm = mb_strtolower($ghe->loaiGhe->ten_loai ?? 'Thường');
            $laCouple = (bool) ($ghe->loaiGhe->la_couple ?? false) ||
                str_contains($loaiGheNorm, 'couple') ||
                str_contains($loaiGheNorm, 'đôi') ||
                str_contains($loaiGheNorm, 'doi') ||
                str_contains($loaiGheNorm, 'double');

            $phuThu = (float) ($ghe->loaiGhe->phu_thu ?? 0);
            $giaVe = (float) $suatChieu->gia_ve_cuoi_cung + $phuThu;

            if ($laCouple) {
                $giaVe = ((float) $suatChieu->gia_ve_cuoi_cung * 2) + $phuThu;
            }

            $gheTheoHang[$hang][] = [
                'id' => $ghe->id,
                'ma_ghe' => $ghe->ma_ghe,
                'loai_ghe' => $ghe->loaiGhe->ten_loai ?? 'Thường',
                'la_couple' => $laCouple,
                'gia' => $giaVe,
                'phu_thu' => $phuThu,
                'mau_sac' => $ghe->loaiGhe->mau_sac ?? '#3b82f6',
                'da_dat' => $daDat,
                'bao_tri' => $baoTri,
                'chon_duoc' => $chonDuoc,
            ];
        }

        return view('staff.ban-ve.show', [
            'suatChieu' => $suatChieu,
            'gheTheoHang' => $gheTheoHang,
        ]);
    }

    public function food(Request $request, SuatChieu $suatChieu)
    {
        if ($request->isMethod('post') && $request->filled('seats')) {
            session(['staff_seats_' . $suatChieu->id => $request->seats]);
        }

        $seatsInput = $request->input('seats') ?? session('staff_seats_' . $suatChieu->id);

        if (!$seatsInput) {
            return redirect()
                ->route('staff.ban-ve.show', $suatChieu->id)
                ->with('error', 'Vui lòng chọn ghế trước khi chọn đồ ăn.');
        }

        $selectedSeats = collect(explode(',', $seatsInput))
            ->map(fn($seat) => strtoupper(trim($seat)))
            ->filter()
            ->unique()
            ->values();

        // Kiểm tra logic vị trí ghế chọn
        if (!$this->validateSeatsAdjacent($selectedSeats->all(), $suatChieu->id)) {
            return redirect()
                ->route('staff.ban-ve.show', $suatChieu->id)
                ->with('error', 'Sơ đồ ghế chọn không hợp lệ. Vui lòng không để trống 1 ghế đơn lẻ hoặc chừa khoảng trống ở giữa các ghế chọn.');
        }

        $gheList = GheNgoi::with('loaiGhe')
            ->where('phong_chieu_id', $suatChieu->phong_chieu_id)
            ->whereIn('ma_ghe', $selectedSeats->all())
            ->get();

        $seatTotal = $gheList->sum(function ($ghe) use ($suatChieu) {
            $giaVe = (float) ($suatChieu->gia_ve_cuoi_cung ?? 0);
            $phuThu = (float) ($ghe->loaiGhe?->phu_thu ?? 0);

            if ($ghe->loaiGhe?->la_couple) {
                return ($giaVe * 2) + $phuThu;
            }

            return $giaVe + $phuThu;
        });

        $foods = DoAn::active()
            ->with([
                'category',
                'variants' => function ($query) {
                    $query->where('is_active', true)->orderBy('price');
                },
                'comboItems.variant.doAn',
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $menu = $foods
            ->groupBy(function (DoAn $food) {
                return trim($food->category?->name ?? 'Khác');
            })
            ->map(function ($foods, $category) {
                $menuItems = $foods
                    ->flatMap(function (DoAn $food) {
                        if ($food->isCombo()) {
                            $comboItems = $food->comboItems
                                ->map(function ($comboItem) {
                                    $variant = $comboItem->variant;

                                    return [
                                        'variant_id' => $comboItem->food_variant_id ?? $variant?->id,
                                        'name' => $variant?->doAn?->name ?? 'Thành phần',
                                        'variant' => $variant?->value,
                                        'price' => (float) ($variant?->price ?? 0),
                                        'quantity' => max((int) ($comboItem->quantity ?? 1), 1),
                                        'stock' => (int) ($variant?->stock_quantity ?? 0),
                                    ];
                                })
                                ->values();

                            $price = $comboItems->sum(fn($item) => $item['price'] * $item['quantity']);

                            $available = $comboItems
                                ->map(function ($item) {
                                    if ($item['quantity'] <= 0) return 0;
                                    return intdiv($item['stock'], $item['quantity']);
                                })
                                ->min() ?? 0;

                            return [[
                                'cart_key' => 'combo-' . $food->id,
                                'type' => 'combo',
                                'id' => $food->id,
                                'food_id' => $food->id,
                                'variant_id' => null,
                                'name' => $food->name,
                                'description' => $food->description,
                                'image' => $food->image,
                                'price' => (int) round($price),
                                'available' => (int) $available,
                                'combo_items' => $comboItems->toArray(),
                            ]];
                        }

                        return $food->variants
                            ->map(function ($variant) use ($food) {
                                $variantName = trim((string) $variant->value);

                                return [
                                    'cart_key' => 'variant-' . $variant->id,
                                    'type' => 'variant',
                                    'id' => $variant->id,
                                    'food_id' => $food->id,
                                    'variant_id' => $variant->id,
                                    'name' => $food->name . ($variantName !== '' ? ' - ' . $variantName : ''),
                                    'description' => $food->description,
                                    'image' => $food->image,
                                    'price' => (int) round($variant->price),
                                    'available' => (int) ($variant->stock_quantity ?? 0),
                                ];
                            })
                            ->values()
                            ->all();
                    })
                    ->values()
                    ->toArray();

                return [
                    'category' => $category,
                    'foods' => $menuItems,
                ];
            })
            ->values()
            ->toArray();

        return view('staff.ban-ve.chon-do-an', [
            'suatChieu' => $suatChieu,
            'selectedSeats' => $selectedSeats,
            'menu' => $menu,
            'seatTotal' => $seatTotal
        ]);
    }

    public function checkout(Request $request, SuatChieu $suatChieu)
    {
        if (!$request->filled('seats')) {
            return redirect()
                ->route('staff.ban-ve.show', $suatChieu->id)
                ->with('error', 'Chưa chọn ghế.');
        }

        $seats = collect(explode(',', $request->seats))
            ->map(fn($seat) => strtoupper(trim($seat)))
            ->filter()
            ->unique()
            ->values();

        if (!$this->validateSeatsAdjacent($seats->all(), $suatChieu->id)) {
            return redirect()
                ->route('staff.ban-ve.show', $suatChieu->id)
                ->with('error', 'Sơ đồ ghế chọn không hợp lệ. Vui lòng không để trống 1 ghế đơn lẻ hoặc chừa khoảng trống ở giữa.');
        }

        $foodCart = [];
        if ($request->filled('food_cart')) {
            $foodCart = json_decode($request->food_cart, true) ?? [];
        }

        $foodItems = collect($foodCart)->filter(fn($item) => is_array($item));

        $gheList = GheNgoi::with('loaiGhe')
            ->where('phong_chieu_id', $suatChieu->phong_chieu_id)
            ->whereIn('ma_ghe', $seats->all())
            ->get();

        $seatTotal = $gheList->sum(function ($ghe) use ($suatChieu) {
            $giaVe = (float) ($suatChieu->gia_ve_cuoi_cung ?? 0);
            $phuThu = (float) ($ghe->loaiGhe?->phu_thu ?? 0);

            if ($ghe->loaiGhe?->la_couple) {
                return ($giaVe * 2) + $phuThu;
            }

            return $giaVe + $phuThu;
        });

        $foodTotal = $foodItems->sum(fn($item) => ($item['price'] ?? 0) * ($item['qty'] ?? 0));
        $total = $seatTotal + $foodTotal;

        $suatChieu->load(['phim', 'rapChieuPhim', 'phongChieu']);

        return view('staff.ban-ve.checkout', [
            'suatChieu' => $suatChieu,
            'seats' => $seats,
            'foodItems' => $foodItems,
            'foodCart' => $foodItems,
            'seatTotal' => $seatTotal,
            'foodTotal' => $foodTotal,
            'total' => $total
        ]);
    }

    public function store(Request $request, SuatChieu $suatChieu)
    {
        $validated = $request->validate([
            'seats' => ['required', 'string'],
            'food_cart' => ['nullable', 'string'],
            'payment_method' => ['required', 'in:cash,vietqr'],
            'received_amount' => ['nullable', 'required_if:payment_method,cash', 'numeric', 'min:0'],
        ], [
            'seats.required' => 'Không tìm thấy ghế đã chọn.',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
            'received_amount.required_if' => 'Vui lòng nhập số tiền khách đưa.',
            'received_amount.numeric' => 'Số tiền khách đưa không hợp lệ.',
        ]);

        try {
            $result = DB::transaction(function () use ($request, $validated, $suatChieu) {
                $suatChieu->load(['phim', 'rapChieuPhim', 'phongChieu']);

                $selectedSeats = collect(explode(',', $validated['seats']))
                    ->map(fn($seat) => strtoupper(trim($seat)))
                    ->filter()
                    ->unique()
                    ->values();

                if ($selectedSeats->isEmpty()) {
                    throw new \RuntimeException('Không tìm thấy ghế đã chọn.');
                }

                if ($selectedSeats->count() > 8) {
                    throw new \RuntimeException('Mỗi lần chỉ được bán tối đa 8 ghế.');
                }

                // Kiểm tra sơ đồ vị trí ghế chọn
                if (!$this->validateSeatsAdjacent($selectedSeats->all(), $suatChieu->id)) {
                    throw new \RuntimeException('Sơ đồ ghế chọn không hợp lệ. Vui lòng không để trống 1 ghế đơn lẻ hoặc chừa khoảng trống ở giữa các ghế chọn.');
                }

                $gheList = GheNgoi::with('loaiGhe')
                    ->where('phong_chieu_id', $suatChieu->phong_chieu_id)
                    ->whereIn('ma_ghe', $selectedSeats->all())
                    ->lockForUpdate()
                    ->get();

                if ($gheList->count() !== $selectedSeats->count()) {
                    $foundSeats = $gheList->pluck('ma_ghe')->map(fn($seat) => strtoupper(trim($seat)));
                    $missingSeats = $selectedSeats->diff($foundSeats);

                    throw new \RuntimeException('Không tìm thấy ghế: ' . $missingSeats->implode(', '));
                }

                $inactiveSeats = $gheList
                    ->filter(function ($seat) {
                        if (method_exists($seat, 'isEffectivelyUnderMaintenance')) {
                            return $seat->isEffectivelyUnderMaintenance();
                        }
                        return $seat->trang_thai !== 'hoat_dong';
                    })
                    ->pluck('ma_ghe')
                    ->values();

                if ($inactiveSeats->isNotEmpty()) {
                    throw new \RuntimeException('Ghế ' . $inactiveSeats->implode(', ') . ' đang bảo trì hoặc ngừng hoạt động.');
                }

                $bookedSeatCodes = VeXemPhim::where('suat_chieu_id', $suatChieu->id)
                    ->where(function ($query) {
                        $query->whereIn('trang_thai', [
                            'dang_giu', 'da_dat', 'da_thanh_toan', 'da_in', 'da_su_dung',
                        ])->orWhere(function ($pendingQuery) {
                            $pendingQuery->where('trang_thai', 'cho_thanh_toan')
                                ->where('thoi_gian_het_han', '>', now());
                        });
                    })
                    ->lockForUpdate()
                    ->pluck('ma_ghe')
                    ->flatMap(fn($codes) => collect(explode(',', (string) $codes))->map(fn($code) => strtoupper(trim($code)))->filter())
                    ->unique()
                    ->values();

                $blockedSeats = $selectedSeats->intersect($bookedSeatCodes)->values();

                if ($blockedSeats->isNotEmpty()) {
                    throw new \RuntimeException('Ghế ' . $blockedSeats->implode(', ') . ' đã được bán.');
                }

                $seatTotal = $gheList->sum(function ($ghe) use ($suatChieu) {
                    $giaVe = (float) ($suatChieu->gia_ve_cuoi_cung ?? 0);
                    $phuThu = (float) ($ghe->loaiGhe?->phu_thu ?? 0);

                    if ($ghe->loaiGhe?->la_couple) {
                        return ($giaVe * 2) + $phuThu;
                    }

                    return $giaVe + $phuThu;
                });

                $foodCart = json_decode($request->input('food_cart', '[]'), true);
                if (!is_array($foodCart)) {
                    $foodCart = [];
                }

                $requestedFoodItems = collect($foodCart)->filter(fn($item) => is_array($item))->values();
                $verifiedFoodItems = collect();
                $foodTotal = 0;

                foreach ($requestedFoodItems as $foodData) {
                    $type = (string) ($foodData['type'] ?? '');
                    $quantity = (int) ($foodData['qty'] ?? $foodData['quantity'] ?? 0);

                    if ($quantity <= 0) continue;

                    if ($type === 'combo') {
                        $foodId = (int) ($foodData['food_id'] ?? $foodData['id'] ?? 0);
                        $food = DoAn::with(['category', 'comboItems.variant.doAn'])->lockForUpdate()->find($foodId);

                        if (!$food || !$food->isCombo()) throw new \RuntimeException('Combo không tồn tại.');
                        if (!$food->is_active) throw new \RuntimeException('Combo ' . $food->name . ' đã ngừng bán.');
                        if ($food->comboItems->isEmpty()) throw new \RuntimeException('Combo ' . $food->name . ' chưa có thành phần.');

                        $unitPrice = 0;
                        $available = null;

                        foreach ($food->comboItems as $comboItem) {
                            $variant = $comboItem->variant;
                            $quantityPerCombo = max((int) ($comboItem->quantity ?? 1), 1);

                            if (!$variant || !$variant->is_active) {
                                throw new \RuntimeException('Combo ' . $food->name . ' có thành phần không hợp lệ.');
                            }

                            $unitPrice += (float) $variant->price * $quantityPerCombo;
                            $variantAvailable = intdiv((int) $variant->stock_quantity, $quantityPerCombo);
                            $available = $available === null ? $variantAvailable : min($available, $variantAvailable);
                        }

                        if (($available ?? 0) < $quantity) {
                            throw new \RuntimeException('Combo ' . $food->name . ' chỉ còn ' . ($available ?? 0) . ' phần.');
                        }

                        $foodTotal += $unitPrice * $quantity;
                        $verifiedFoodItems->push([
                            'type' => 'combo',
                            'id' => $food->id,
                            'food_id' => $food->id,
                            'variant_id' => null,
                            'name' => $food->name,
                            'price' => $unitPrice,
                            'qty' => $quantity,
                        ]);
                        continue;
                    }

                    $variantId = (int) ($foodData['variant_id'] ?? $foodData['id'] ?? 0);
                    $variant = BienTheDoAn::with('doAn')->lockForUpdate()->find($variantId);

                    if (!$variant || !$variant->doAn) throw new \RuntimeException('Biến thể đồ ăn không tồn tại.');
                    if (!$variant->is_active || !$variant->doAn->is_active) {
                        throw new \RuntimeException('Món ' . $variant->doAn->name . ' đã ngừng bán.');
                    }
                    if ((int) $variant->stock_quantity < $quantity) {
                        throw new \RuntimeException('Món ' . $variant->doAn->name . ' - ' . $variant->value . ' chỉ còn ' . (int) $variant->stock_quantity . ' sản phẩm.');
                    }

                    $unitPrice = (float) $variant->price;
                    $foodTotal += $unitPrice * $quantity;
                    $variantLabel = trim((string) $variant->value);

                    $verifiedFoodItems->push([
                        'type' => 'variant',
                        'id' => $variant->id,
                        'food_id' => $variant->food_id,
                        'variant_id' => $variant->id,
                        'name' => $variant->doAn->name . ($variantLabel !== '' ? ' - ' . $variantLabel : ''),
                        'price' => $unitPrice,
                        'qty' => $quantity,
                    ]);
                }

                $tongTien = (int) round($seatTotal + $foodTotal);
                $paymentMethod = $validated['payment_method'];

                if ($paymentMethod === 'vietqr') {
                    do {
                        $maVe = 'OFF-' . now('Asia/Ho_Chi_Minh')->format('ymdHis') . '-' . Str::upper(Str::random(5));
                    } while (VeXemPhim::where('ma_ve', $maVe)->exists());

                    do {
                        $orderCode = intval(filter_var(microtime(true) * 10000, FILTER_SANITIZE_NUMBER_INT)) % 9007199254740991;
                    } while (VeXemPhim::where('payos_order_code', $orderCode)->exists());

                    $expiresAt = now()->addMinutes(self::STAFF_VIETQR_HOLD_MINUTES);

                    $vePending = VeXemPhim::create([
                        'nguoi_dung_id' => null,
                        'nhan_vien_id' => auth()->id(),
                        'suat_chieu_id' => $suatChieu->id,
                        'ma_ve' => $maVe,
                        'ten_phim' => $suatChieu->phim->ten_phim,
                        'ten_rap' => $suatChieu->rapChieuPhim->ten_rap,
                        'ten_phong' => $suatChieu->phongChieu->ten_phong,
                        'ma_ghe' => $selectedSeats->implode(','),
                        'thoi_gian_chieu' => $suatChieu->thoi_gian_chieu,
                        'tong_tien' => $tongTien,
                        'tien_hoan' => 0,
                        'loai_ve' => 'tai_quay',
                        'trang_thai' => 'cho_thanh_toan',
                        'thoi_gian_het_han' => $expiresAt,
                        'food_items' => $verifiedFoodItems->values()->all(),
                        'payment_method' => 'vietqr',
                        'payos_order_code' => $orderCode,
                        'received_amount' => 0,
                        'change_amount' => 0,
                        'seat_total' => (int) round($seatTotal),
                        'food_total' => (int) round($foodTotal),
                    ]);

                    Cache::put("staff_payos_mapping:{$orderCode}", $maVe, now()->addMinutes(self::STAFF_PAYOS_CACHE_MINUTES));
                    Cache::put("pending_staff_ve:{$maVe}", [
                        've_id' => $vePending->id,
                        'ma_ve' => $maVe,
                        'suat_chieu_id' => $suatChieu->id,
                        'danh_sach_ghe' => $selectedSeats->values()->all(),
                        'clear_cart_key' => $request->input('clear_cart_key'),
                    ], now()->addMinutes(self::STAFF_PAYOS_CACHE_MINUTES));

                    $payOS = new PayOS(env('PAYOS_CLIENT_ID'), env('PAYOS_API_KEY'), env('PAYOS_CHECKSUM_KEY'));

                    $paymentData = [
                        'orderCode' => $orderCode,
                        'amount' => $tongTien,
                        'description' => 'VE' . $orderCode,
                        'returnUrl' => route('staff.ban-ve.payos-callback'),
                        'cancelUrl' => route('staff.ban-ve.payos-cancel'),
                        'expiredAt' => $expiresAt->timestamp,
                    ];

                    $response = $payOS->paymentRequests->create($paymentData);
                    $checkoutUrl = data_get($response, 'checkoutUrl');
                    $qrCode = data_get($response, 'qrCode');

                    if (!$checkoutUrl || !$qrCode) {
                        throw new \RuntimeException('PayOS không trả về đầy đủ thông tin thanh toán VietQR.');
                    }

                    $vePending->update([
                        'payos_checkout_url' => $checkoutUrl,
                        'payos_qr_code' => $qrCode,
                    ]);

                    return [
                        'payment_method' => 'vietqr',
                        'pending_ticket_id' => $vePending->id,
                    ];
                }

                // Thanh toán tiền mặt
                $receivedAmount = (int) ($validated['received_amount'] ?? 0);

                if ($receivedAmount < $tongTien) {
                    throw new \RuntimeException('Khách còn thiếu ' . number_format($tongTien - $receivedAmount, 0, ',', '.') . 'đ.');
                }

                do {
                    $maVe = 'OFF-' . now('Asia/Ho_Chi_Minh')->format('ymdHis') . '-' . Str::upper(Str::random(5));
                } while (VeXemPhim::where('ma_ve', $maVe)->exists());

                $ve = new VeXemPhim();
                $ve->nguoi_dung_id = null;
                $ve->nhan_vien_id = auth()->id();
                $ve->suat_chieu_id = $suatChieu->id;
                $ve->ma_ve = $maVe;
                $ve->ten_phim = $suatChieu->phim->ten_phim;
                $ve->ten_rap = $suatChieu->rapChieuPhim->ten_rap;
                $ve->ten_phong = $suatChieu->phongChieu->ten_phong;
                $ve->ma_ghe = $selectedSeats->implode(',');
                $ve->thoi_gian_chieu = $suatChieu->thoi_gian_chieu;
                $ve->tong_tien = $tongTien;
                $ve->tien_hoan = 0;
                $ve->loai_ve = 'tai_quay';
                $ve->trang_thai = 'da_thanh_toan';
                $ve->payment_method = 'cash';
                $ve->received_amount = $receivedAmount;
                $ve->change_amount = $receivedAmount - $tongTien;
                $ve->seat_total = (int) round($seatTotal);
                $ve->food_total = (int) round($foodTotal);
                $ve->food_items = $verifiedFoodItems->values()->all();
                $ve->save();

                if (!$ve->exists || !$ve->id) {
                    throw new \RuntimeException('Không thể lưu vé vào database.');
                }

                $this->createSeatTickets($ve, $selectedSeats->all());

                foreach ($verifiedFoodItems as $foodItem) {
                    $quantity = (int) ($foodItem['qty'] ?? 0);
                    if ($quantity <= 0) continue;

                    if (($foodItem['type'] ?? null) === 'combo') {
                        $food = DoAn::with(['comboItems.variant'])->lockForUpdate()->find($foodItem['food_id']);
                        if (!$food || !$food->isCombo()) throw new \RuntimeException('Không tìm thấy combo ' . ($foodItem['name'] ?? '') . '.');

                        foreach ($food->comboItems as $comboItem) {
                            $variantId = $comboItem->food_variant_id ?? $comboItem->variant_id ?? $comboItem->variant?->id;
                            if (!$variantId) throw new \RuntimeException('Một thành phần của combo ' . $food->name . ' không có biến thể hợp lệ.');

                            $quantityPerCombo = max((int) ($comboItem->quantity ?? 1), 1);
                            $quantityToDeduct = $quantityPerCombo * $quantity;

                            $updatedRows = BienTheDoAn::where('id', $variantId)
                                ->where('stock_quantity', '>=', $quantityToDeduct)
                                ->decrement('stock_quantity', $quantityToDeduct);

                            if ($updatedRows === 0) {
                                throw new \RuntimeException('Không đủ tồn kho thành phần của combo ' . $food->name . '.');
                            }
                        }
                        continue;
                    }

                    $variantId = (int) ($foodItem['variant_id'] ?? $foodItem['id'] ?? 0);
                    if ($variantId <= 0) throw new \RuntimeException('Không xác định được biến thể của món ' . ($foodItem['name'] ?? '') . '.');

                    $updatedRows = BienTheDoAn::where('id', $variantId)
                        ->where('stock_quantity', '>=', $quantity)
                        ->decrement('stock_quantity', $quantity);

                    if ($updatedRows === 0) {
                        throw new \RuntimeException('Món ' . ($foodItem['name'] ?? '') . ' không còn đủ tồn kho.');
                    }
                }

                if ($verifiedFoodItems->isNotEmpty()) {
                    Cache::put("ve_foods:{$ve->id}", $verifiedFoodItems->values()->all(), now()->addDays(30));
                }

                return [
                    've' => $ve,
                    'change_amount' => $receivedAmount - $tongTien,
                ];
            });

            if (($result['payment_method'] ?? null) === 'vietqr') {
                return redirect()->route('staff.ban-ve.vietqr-waiting', ['id' => $result['pending_ticket_id']]);
            }

            $ve = $result['ve'];
            $changeAmount = $result['change_amount'];

            $this->taoThongBaoStaff(
                'Bán vé thành công',
                'Đã bán vé ' . $ve->ma_ve . ' - Phim: ' . $ve->ten_phim . ' - Ghế: ' . $ve->ma_ghe . ' - Tổng tiền: ' . number_format($ve->tong_tien, 0, ',', '.') . 'đ.',
                've',
                route('staff.ban-ve.success', ['id' => $ve->id])
            );

            session()->flash('clear_food_cart_key', $request->input('clear_cart_key', 'staff_food_cart_v2_' . auth()->id() . '_' . $suatChieu->id));

            return redirect()
                ->route('staff.ban-ve.success', ['id' => $ve->id])
                ->with('success', 'Bán vé thành công. Mã vé: ' . $ve->ma_ve . '. Tiền thừa: ' . number_format($changeAmount, 0, ',', '.') . 'đ.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Không thể bán vé: ' . $e->getMessage());
        }
    }

    public function showCheckout(SuatChieu $suatChieu)
    {
        return redirect()->route('staff.ban-ve.show', $suatChieu->id);
    }

    public function vietQrWaiting(int $id)
    {
        $this->expirePendingTickets();

        $ve = VeXemPhim::query()
            ->with(['nhanVien', 'suatChieu.phim', 'suatChieu.rapChieuPhim', 'suatChieu.phongChieu'])
            ->where('loai_ve', 'tai_quay')
            ->where('payment_method', 'vietqr')
            ->findOrFail($id);

        if (in_array($ve->trang_thai, ['da_thanh_toan', 'da_in', 'da_su_dung'], true)) {
            return redirect()->route('staff.ban-ve.success', ['id' => $ve->id]);
        }

        if (in_array($ve->trang_thai, ['da_huy', 'het_han'], true)) {
            return redirect()
                ->route('staff.ban-ve.show', $ve->suat_chieu_id)
                ->with('error', 'Giao dịch VietQR đã hủy hoặc hết hạn. Ghế đã được giải phóng.');
        }

        if (!$ve->payos_qr_code) {
            return redirect()
                ->route('staff.ban-ve.show', $ve->suat_chieu_id)
                ->with('error', 'Giao dịch VietQR thiếu dữ liệu QR. Vui lòng tạo lại giao dịch.');
        }

        $qrSvg = QrCode::format('svg')
            ->size(300)
            ->margin(1)
            ->generate((string) $ve->payos_qr_code);

        return view('staff.ban-ve.vietqr-waiting', compact('ve', 'qrSvg'));
    }

    public function vietQrStatus(int $id)
    {
        $ve = VeXemPhim::query()
            ->where('loai_ve', 'tai_quay')
            ->where('payment_method', 'vietqr')
            ->findOrFail($id);

        if (in_array($ve->trang_thai, ['da_thanh_toan', 'da_in', 'da_su_dung'], true)) {
            return response()->json([
                'success' => true,
                'status' => 'PAID',
                'redirect_url' => route('staff.ban-ve.success', ['id' => $ve->id]),
            ]);
        }

        if ($ve->trang_thai === 'da_huy') {
            return response()->json([
                'success' => true,
                'status' => 'CANCELLED',
                'redirect_url' => route('staff.ban-ve.show', $ve->suat_chieu_id),
            ]);
        }

        if ($ve->trang_thai === 'het_han' || $ve->isExpired()) {
            if ($ve->trang_thai === 'cho_thanh_toan') {
                $ve->update([
                    'trang_thai' => 'het_han',
                    'thoi_gian_het_han' => null,
                ]);
                $this->cancelPayosLinkSilently($ve, 'Het thoi gian giu ghe');

                $this->taoThongBaoVeMotLan(
                    $ve,
                    'Giao dịch VietQR hết hạn',
                    'Giao dịch VietQR của vé ' . $ve->ma_ve . ' - Phim: ' . $ve->ten_phim . ' - Ghế: ' . $ve->ma_ghe . ' đã hết thời gian thanh toán. Ghế đã được giải phóng.',
                    've',
                    route('staff.ban-ve.show', $ve->suat_chieu_id)
                );
            }

            return response()->json([
                'success' => true,
                'status' => 'EXPIRED',
                'redirect_url' => route('staff.ban-ve.show', $ve->suat_chieu_id),
            ]);
        }

        try {
            $payosStatus = $this->getPayosStatus($ve);

            if ($payosStatus === 'PAID') {
                $ve = $this->finalizePaidVietQr($ve);

                $this->taoThongBaoVeMotLan(
                    $ve,
                    'Thanh toán VietQR thành công',
                    'Vé ' . $ve->ma_ve . ' - Phim: ' . $ve->ten_phim . ' - Ghế: ' . $ve->ma_ghe . ' đã thanh toán VietQR thành công - Tổng tiền: ' . number_format($ve->tong_tien, 0, ',', '.') . 'đ.',
                    've',
                    route('staff.ban-ve.success', ['id' => $ve->id])
                );

                return response()->json([
                    'success' => true,
                    'status' => 'PAID',
                    'redirect_url' => route('staff.ban-ve.success', ['id' => $ve->id]),
                ]);
            }

            if ($payosStatus === 'CANCELLED') {
                $ve->update([
                    'trang_thai' => 'da_huy',
                    'thoi_gian_het_han' => null,
                ]);

                $this->taoThongBaoVeMotLan(
                    $ve,
                    'Giao dịch VietQR đã hủy',
                    'Giao dịch VietQR của vé ' . $ve->ma_ve . ' - Ghế: ' . $ve->ma_ghe . ' đã bị hủy. Ghế đã được giải phóng.',
                    've',
                    route('staff.ban-ve.show', $ve->suat_chieu_id)
                );

                return response()->json([
                    'success' => true,
                    'status' => 'CANCELLED',
                    'redirect_url' => route('staff.ban-ve.show', $ve->suat_chieu_id),
                ]);
            }

            return response()->json([
                'success' => true,
                'status' => $payosStatus ?: 'PENDING',
                'expires_at' => optional($ve->thoi_gian_het_han)?->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'status' => 'PENDING',
                'message' => 'Chưa thể kiểm tra PayOS, hệ thống sẽ tự thử lại.',
            ], 200);
        }
    }

    public function cancelPendingVietQr(int $id)
    {
        $ve = VeXemPhim::query()
            ->where('loai_ve', 'tai_quay')
            ->where('payment_method', 'vietqr')
            ->findOrFail($id);

        if ($ve->trang_thai === 'cho_thanh_toan') {
            try {
                if ($this->getPayosStatus($ve) === 'PAID') {
                    $paidTicket = $this->finalizePaidVietQr($ve);

                    return redirect()
                        ->route('staff.ban-ve.success', ['id' => $paidTicket->id])
                        ->with('success', 'PayOS vừa xác nhận thanh toán thành công nên giao dịch không bị hủy.');
                }
            } catch (\Throwable $e) {
                report($e);
            }

            $this->cancelPayosLinkSilently($ve, 'Nhan vien huy giao dich tai quay');

            $ve->update([
                'trang_thai' => 'da_huy',
                'thoi_gian_het_han' => null,
            ]);

            $this->taoThongBaoVeMotLan(
                $ve,
                'Đã hủy giao dịch VietQR',
                'Nhân viên đã hủy giao dịch VietQR của vé ' . $ve->ma_ve . ' - Phim: ' . $ve->ten_phim . ' - Ghế: ' . $ve->ma_ghe . '. Ghế đã được giải phóng.',
                've',
                route('staff.ban-ve.show', $ve->suat_chieu_id)
            );
        }

        return redirect()
            ->route('staff.ban-ve.show', $ve->suat_chieu_id)
            ->with('error', 'Đã hủy giao dịch VietQR. Ghế đã được giải phóng.');
    }

    public function payosCallback(Request $request)
    {
        $orderCode = (int) $request->input('orderCode');

        $ve = $orderCode > 0
            ? VeXemPhim::query()
                ->where('loai_ve', 'tai_quay')
                ->where('payment_method', 'vietqr')
                ->where('payos_order_code', $orderCode)
                ->first()
            : null;

        if (!$ve) {
            return redirect()
                ->route('staff.ban-ve.index')
                ->with('error', 'Không tìm thấy giao dịch VietQR.');
        }

        try {
            $status = $this->getPayosStatus($ve);

            if ($status === 'PAID') {
                $ve = $this->finalizePaidVietQr($ve);

                $this->taoThongBaoVeMotLan(
                    $ve,
                    'Thanh toán VietQR thành công',
                    'Vé ' . $ve->ma_ve . ' - Phim: ' . $ve->ten_phim . ' - Ghế: ' . $ve->ma_ghe . ' đã thanh toán VietQR thành công - Tổng tiền: ' . number_format($ve->tong_tien, 0, ',', '.') . 'đ.',
                    've',
                    route('staff.ban-ve.success', ['id' => $ve->id])
                );

                return redirect()
                    ->route('staff.ban-ve.success', ['id' => $ve->id])
                    ->with('success', 'Thanh toán VietQR thành công. Mã vé: ' . $ve->ma_ve);
            }

            if ($status === 'CANCELLED') {
                $ve->update([
                    'trang_thai' => 'da_huy',
                    'thoi_gian_het_han' => null,
                ]);

                $this->taoThongBaoVeMotLan(
                    $ve,
                    'Giao dịch VietQR đã hủy',
                    'Giao dịch VietQR của vé ' . $ve->ma_ve . ' - Ghế: ' . $ve->ma_ghe . ' đã bị hủy. Ghế đã được giải phóng.',
                    've',
                    route('staff.ban-ve.show', $ve->suat_chieu_id)
                );

                return redirect()
                    ->route('staff.ban-ve.show', $ve->suat_chieu_id)
                    ->with('error', 'Giao dịch VietQR đã bị hủy.');
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('staff.ban-ve.vietqr-waiting', ['id' => $ve->id]);
    }

    public function payosCancel(Request $request)
    {
        $orderCode = (int) $request->input('orderCode');

        $ve = $orderCode > 0
            ? VeXemPhim::query()
                ->where('loai_ve', 'tai_quay')
                ->where('payment_method', 'vietqr')
                ->where('payos_order_code', $orderCode)
                ->first()
            : null;

        if (!$ve) {
            return redirect()
                ->route('staff.ban-ve.index')
                ->with('error', 'Không tìm thấy giao dịch VietQR cần hủy.');
        }

        try {
            if ($this->getPayosStatus($ve) === 'PAID') {
                $ve = $this->finalizePaidVietQr($ve);

                $this->taoThongBaoVeMotLan(
                    $ve,
                    'Thanh toán VietQR thành công',
                    'Vé ' . $ve->ma_ve . ' - Phim: ' . $ve->ten_phim . ' - Ghế: ' . $ve->ma_ghe . ' đã thanh toán VietQR thành công - Tổng tiền: ' . number_format($ve->tong_tien, 0, ',', '.') . 'đ.',
                    've',
                    route('staff.ban-ve.success', ['id' => $ve->id])
                );

                return redirect()
                    ->route('staff.ban-ve.success', ['id' => $ve->id])
                    ->with('success', 'Thanh toán VietQR đã thành công.');
            }
        } catch (\Throwable $e) {
            report($e);
        }

        if ($ve->trang_thai === 'cho_thanh_toan') {
            $ve->update([
                'trang_thai' => 'da_huy',
                'thoi_gian_het_han' => null,
            ]);

            $this->taoThongBaoVeMotLan(
                $ve,
                'Giao dịch VietQR đã hủy',
                'Giao dịch VietQR của vé ' . $ve->ma_ve . ' - Ghế: ' . $ve->ma_ghe . ' đã được hủy. Ghế đã được giải phóng.',
                've',
                route('staff.ban-ve.show', $ve->suat_chieu_id)
            );
        }

        return redirect()
            ->route('staff.ban-ve.show', $ve->suat_chieu_id)
            ->with('error', 'Giao dịch VietQR đã được hủy. Các ghế đã được giải phóng.');
    }

    public function success(int $id)
    {
        $ve = $this->findPrintableTicket($id);

        return view('staff.ban-ve.success', compact('ve'));
    }

    public function markAsPrinted(int $id)
    {
        $ve = VeXemPhim::query()
            ->where('loai_ve', 'tai_quay')
            ->findOrFail($id);

        if ($ve->trang_thai === 'da_huy') {
            return response()->json([
                'success' => false,
                'message' => 'Vé đã bị hủy, không thể đánh dấu đã in.',
            ], 422);
        }

        if ($ve->trang_thai === 'da_su_dung') {
            return response()->json([
                'success' => true,
                'message' => 'Vé đã được sử dụng.',
                'status' => 'da_su_dung',
            ]);
        }

        if (!in_array($ve->trang_thai, ['da_thanh_toan', 'da_in'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Vé chưa thanh toán thành công nên chưa thể in.',
                'status' => $ve->trang_thai,
            ], 422);
        }

        if ($ve->trang_thai === 'da_thanh_toan') {
            $ve->update([
                'trang_thai' => 'da_in',
            ]);

            $this->taoThongBaoVeMotLan(
                $ve,
                'In vé thành công',
                'Vé ' . $ve->ma_ve . ' - Phim: ' . $ve->ten_phim . ' - Ghế: ' . $ve->ma_ghe . ' đã được in/phát hành.',
                've',
                route('staff.ban-ve.success', ['id' => $ve->id])
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Vé đã được đánh dấu là đã in.',
            'status' => $ve->fresh()->trang_thai,
        ]);
    }

    public function printTicket(int $id)
    {
        $ve = $this->findPrintableTicket($id);

        $seatCodes = $ve->gheVes
            ->pluck('ma_ghe')
            ->map(fn($seatCode) => strtoupper(trim((string) $seatCode)))
            ->filter()
            ->values();

        $seatDetails = GheNgoi::query()
            ->with('loaiGhe')
            ->where('phong_chieu_id', $ve->suatChieu->phong_chieu_id)
            ->whereIn('ma_ghe', $seatCodes->all())
            ->get()
            ->keyBy(fn($seat) => strtoupper(trim((string) $seat->ma_ghe)));

        return view('staff.ban-ve.print-ticket', compact('ve', 'seatDetails'));
    }

    public function printInvoice(int $id)
    {
        $ve = $this->findPrintableTicket($id);

        return view('staff.ban-ve.print-invoice', compact('ve'));
    }

    /**
     * Hàm kiểm tra vị trí ghế chọn hợp lệ (Đồng bộ logic 100% với Client JS / DatVeController)
     */
    private function validateSeatsAdjacent(array $seats, $suatChieuId): bool
    {
        if (empty($seats)) {
            return true;
        }

        $selectedSeats = collect($seats)
            ->map(fn($s) => strtoupper(trim($s)))
            ->filter()
            ->unique();

        $suatChieu = SuatChieu::find($suatChieuId);
        if (!$suatChieu) {
            return false;
        }

        // 1. Lấy danh sách ghế không thể chọn (đã đặt / giữ chỗ / bảo trì)
        $bookedTickets = VeXemPhim::where('suat_chieu_id', $suatChieuId)
            ->where(function ($q) {
                $q->whereIn('trang_thai', ['dang_giu', 'da_thanh_toan', 'da_su_dung', 'da_dat', 'da_in'])
                    ->orWhere(function ($sub) {
                        $sub->where('trang_thai', 'cho_thanh_toan')
                            ->where('thoi_gian_het_han', '>', now());
                    });
            })
            ->pluck('ma_ghe');

        $bookedSeatsMap = [];
        foreach ($bookedTickets as $maGheString) {
            foreach (explode(',', (string)$maGheString) as $code) {
                $seatCode = strtoupper(trim($code));
                if ($seatCode !== '') {
                    $bookedSeatsMap[$seatCode] = true;
                }
            }
        }

        $inactiveSeats = GheNgoi::where('phong_chieu_id', $suatChieu->phong_chieu_id)
            ->where('trang_thai', '!=', 'hoat_dong')
            ->pluck('ma_ghe');

        foreach ($inactiveSeats as $code) {
            $seatCode = strtoupper(trim($code));
            if ($seatCode !== '') {
                $bookedSeatsMap[$seatCode] = true;
            }
        }

        // 2. Gom nhóm toàn bộ ghế trong phòng chiếu theo hàng
        $allRoomSeats = GheNgoi::where('phong_chieu_id', $suatChieu->phong_chieu_id)->get();
        $roomSeatsByRow = [];

        foreach ($allRoomSeats as $g) {
            $code = strtoupper(trim($g->ma_ghe));
            if (preg_match('/^([A-Z]+)([0-9]+)$/', $code, $m)) {
                $row = $m[1];
                $num = (int)$m[2];
                $isUnavailable = isset($bookedSeatsMap[$code]);
                $isSelected = $selectedSeats->contains($code);

                $roomSeatsByRow[$row][] = [
                    'code' => $code,
                    'num' => $num,
                    'isUnavailable' => $isUnavailable,
                    'isSelected' => $isSelected,
                    'isOccupied' => $isUnavailable || $isSelected,
                ];
            }
        }

        // 3. Danh sách hàng có ghế được chọn
        $selectedRows = [];
        foreach ($selectedSeats as $code) {
            if (preg_match('/^([A-Z]+)([0-9]+)$/', $code, $m)) {
                $selectedRows[$m[1]] = true;
            }
        }

        // 4. Kiểm tra từng hàng theo 2 trường hợp
        foreach (array_keys($selectedRows) as $row) {
            if (!isset($roomSeatsByRow[$row])) continue;

            $physicalSeats = $roomSeatsByRow[$row];
            usort($physicalSeats, fn($a, $b) => $a['num'] <=> $b['num']);

            $hasBookedOrLockedInRow = collect($physicalSeats)->contains('isUnavailable', true);

            // Trường hợp 1: Hàng ĐÃ CÓ ghế người khác đặt hoặc ghế bảo trì
            if ($hasBookedOrLockedInRow) {
                $selectedIndices = [];
                foreach ($physicalSeats as $idx => $seat) {
                    if ($seat['isSelected']) $selectedIndices[] = $idx;
                }

                if (count($selectedIndices) > 1) {
                    $minIdx = min($selectedIndices);
                    $maxIdx = max($selectedIndices);
                    for ($i = $minIdx + 1; $i < $maxIdx; $i++) {
                        if (!$physicalSeats[$i]['isOccupied']) {
                            return false; // Không được để trống ghế ở giữa cụm ghế chọn
                        }
                    }
                }
            }
            // Trường hợp 2: Hàng HOÀN TOÀN TRỐNG
            else {
                $emptyBlockLength = 0;
                foreach ($physicalSeats as $seat) {
                    if ($seat['isOccupied']) {
                        if ($emptyBlockLength === 1) {
                            return false; // Không được chừa 1 ghế lẻ bên trái cụm ghế
                        }
                        $emptyBlockLength = 0;
                    } else {
                        $emptyBlockLength++;
                    }
                }
                if ($emptyBlockLength === 1) {
                    return false; // Không được chừa 1 ghế lẻ bên phải cụm ghế
                }
            }
        }

        return true;
    }

    private function findPrintableTicket(int $id): VeXemPhim
    {
        $ve = VeXemPhim::query()
            ->with([
                'nhanVien',
                'gheVes',
                'suatChieu.phim',
                'suatChieu.rapChieuPhim',
                'suatChieu.phongChieu',
            ])
            ->where('loai_ve', 'tai_quay')
            ->findOrFail($id);

        if (!in_array($ve->trang_thai, ['da_thanh_toan', 'da_in', 'da_su_dung'], true)) {
            abort(422, 'Vé chưa thanh toán thành công nên chưa thể xem hoặc in.');
        }

        if ($ve->gheVes->isEmpty()) {
            $this->createSeatTickets($ve, explode(',', $ve->ma_ghe));
            $ve->load('gheVes');
        }

        return $ve;
    }

    private function getPayosStatus(VeXemPhim $ve): string
    {
        if (!$ve->payos_order_code) {
            throw new \RuntimeException('Giao dịch không có PayOS orderCode.');
        }

        $response = Http::timeout(10)
            ->acceptJson()
            ->withHeaders([
                'x-client-id' => (string) env('PAYOS_CLIENT_ID'),
                'x-api-key' => (string) env('PAYOS_API_KEY'),
            ])
            ->get('https://api-merchant.payos.vn/v2/payment-requests/' . $ve->payos_order_code);

        if (!$response->successful()) {
            throw new \RuntimeException('Không thể lấy trạng thái PayOS (HTTP ' . $response->status() . ').');
        }

        return strtoupper((string) data_get($response->json(), 'data.status', 'PENDING'));
    }

    private function cancelPayosLinkSilently(VeXemPhim $ve, string $reason): void
    {
        if (!$ve->payos_order_code) return;

        try {
            Http::timeout(10)
                ->acceptJson()
                ->withHeaders([
                    'x-client-id' => (string) env('PAYOS_CLIENT_ID'),
                    'x-api-key' => (string) env('PAYOS_API_KEY'),
                ])
                ->post('https://api-merchant.payos.vn/v2/payment-requests/' . $ve->payos_order_code . '/cancel', [
                    'cancellationReason' => $reason
                ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function finalizePaidVietQr(VeXemPhim $pendingTicket): VeXemPhim
    {
        return DB::transaction(function () use ($pendingTicket) {
            $ve = VeXemPhim::query()
                ->whereKey($pendingTicket->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($ve->trang_thai, ['da_thanh_toan', 'da_in', 'da_su_dung'], true)) {
                return $ve;
            }

            if ($ve->trang_thai === 'da_huy') {
                throw new \RuntimeException('Giao dịch đã bị hủy trước khi phát hành vé.');
            }

            $seatCodes = collect(explode(',', (string) $ve->ma_ghe))
                ->map(fn($seat) => strtoupper(trim($seat)))
                ->filter()
                ->unique()
                ->values();

            $otherBlockedSeats = VeXemPhim::query()
                ->where('suat_chieu_id', $ve->suat_chieu_id)
                ->where('id', '!=', $ve->id)
                ->where(function ($query) {
                    $query->whereIn('trang_thai', [
                        'dang_giu', 'da_dat', 'da_thanh_toan', 'da_in', 'da_su_dung',
                    ])->orWhere(function ($pendingQuery) {
                        $pendingQuery->where('trang_thai', 'cho_thanh_toan')
                            ->where('thoi_gian_het_han', '>', now());
                    });
                })
                ->lockForUpdate()
                ->pluck('ma_ghe')
                ->flatMap(fn($codes) => collect(explode(',', (string) $codes)))
                ->map(fn($code) => strtoupper(trim($code)))
                ->filter()
                ->unique();

            $conflicts = $seatCodes->intersect($otherBlockedSeats)->values();

            if ($conflicts->isNotEmpty()) {
                throw new \RuntimeException('Ghế ' . $conflicts->implode(', ') . ' đã được giao dịch khác chiếm.');
            }

            $this->deductFoodStock(is_array($ve->food_items) ? $ve->food_items : []);

            $ve->update([
                'trang_thai' => 'da_thanh_toan',
                'thoi_gian_het_han' => null,
                'received_amount' => $ve->tong_tien,
                'change_amount' => 0,
            ]);

            $this->createSeatTickets($ve, $seatCodes->all());

            if (!empty($ve->food_items)) {
                Cache::put('ve_foods:' . $ve->id, $ve->food_items, now()->addDays(30));
            }

            Cache::forget('pending_staff_ve:' . $ve->ma_ve);
            Cache::forget('staff_payos_mapping:' . $ve->payos_order_code);

            return $ve->fresh();
        });
    }

    private function expirePendingTickets(?int $showtimeId = null): void
    {
        $query = VeXemPhim::query()
            ->where('loai_ve', 'tai_quay')
            ->where('payment_method', 'vietqr')
            ->where('trang_thai', 'cho_thanh_toan')
            ->whereNotNull('thoi_gian_het_han')
            ->where('thoi_gian_het_han', '<=', now());

        if ($showtimeId !== null) {
            $query->where('suat_chieu_id', $showtimeId);
        }

        $expiredTickets = $query->get();

        foreach ($expiredTickets as $ve) {
            $ve->update([
                'trang_thai' => 'het_han',
                'thoi_gian_het_han' => null,
            ]);

            $this->cancelPayosLinkSilently($ve, 'Het thoi gian giu ghe');

            $this->taoThongBaoVeMotLan(
                $ve,
                'Giao dịch VietQR hết hạn',
                'Giao dịch VietQR của vé ' . $ve->ma_ve . ' - Phim: ' . $ve->ten_phim . ' - Ghế: ' . $ve->ma_ghe . ' đã hết thời gian thanh toán. Ghế đã được giải phóng.',
                've',
                route('staff.ban-ve.show', $ve->suat_chieu_id)
            );
        }
    }

    private function deductFoodStock(array $foodItems): void
    {
        foreach ($foodItems as $foodItem) {
            if (!is_array($foodItem)) continue;

            $quantity = (int) ($foodItem['qty'] ?? 0);
            if ($quantity <= 0) continue;

            if (($foodItem['type'] ?? null) === 'combo') {
                $food = DoAn::with(['comboItems.variant'])
                    ->lockForUpdate()
                    ->find((int) ($foodItem['food_id'] ?? 0));

                if (!$food || !$food->isCombo()) {
                    throw new \RuntimeException('Không tìm thấy combo ' . ($foodItem['name'] ?? '') . '.');
                }

                foreach ($food->comboItems as $comboItem) {
                    $variantId = $comboItem->food_variant_id ?? $comboItem->variant_id ?? $comboItem->variant?->id;
                    if (!$variantId) throw new \RuntimeException('Combo ' . $food->name . ' có thành phần không hợp lệ.');

                    $quantityPerCombo = max((int) ($comboItem->quantity ?? 1), 1);
                    $quantityToDeduct = $quantityPerCombo * $quantity;

                    $updatedRows = BienTheDoAn::where('id', $variantId)
                        ->where('stock_quantity', '>=', $quantityToDeduct)
                        ->decrement('stock_quantity', $quantityToDeduct);

                    if ($updatedRows === 0) {
                        throw new \RuntimeException('Không đủ tồn kho thành phần của combo ' . $food->name . '.');
                    }
                }
                continue;
            }

            $variantId = (int) ($foodItem['variant_id'] ?? $foodItem['id'] ?? 0);
            if ($variantId <= 0) {
                throw new \RuntimeException('Không xác định được biến thể của món ' . ($foodItem['name'] ?? '') . '.');
            }

            $updatedRows = BienTheDoAn::where('id', $variantId)
                ->where('stock_quantity', '>=', $quantity)
                ->decrement('stock_quantity', $quantity);

            if ($updatedRows === 0) {
                throw new \RuntimeException('Món ' . ($foodItem['name'] ?? '') . ' không còn đủ tồn kho.');
            }
        }
    }

    private function createSeatTickets(VeXemPhim $ve, iterable $seatCodes): void
    {
        foreach ($seatCodes as $seatCode) {
            $seatCode = strtoupper(trim((string) $seatCode));
            if ($seatCode === '') continue;

            VeXemPhimGhe::firstOrCreate(
                [
                    've_xem_phim_id' => $ve->id,
                    'ma_ghe' => $seatCode,
                ],
                [
                    'ma_qr' => 'CH-' . Str::upper(Str::random(48)),
                    'trang_thai' => VeXemPhimGhe::CHUA_SU_DUNG,
                ]
            );
        }
    }

    private function taoThongBaoStaff(string $tieuDe, string $noiDung, string $loai = 've', ?string $duongDan = null): void
    {
        $staffId = auth()->id();
        if (!$staffId) return;

        ThongBaoCaNhan::create([
            'nguoi_dung_id' => $staffId,
            'tieu_de' => $tieuDe,
            'noi_dung' => $noiDung,
            'loai_thong_bao' => $loai,
            'duong_dan' => $duongDan,
            'da_doc' => false,
            'doc_luc' => null,
        ]);
    }

    private function taoThongBaoVeMotLan(VeXemPhim $ve, string $tieuDe, string $noiDung, string $loai = 've', ?string $duongDan = null): void
    {
        $staffId = $ve->nhan_vien_id ?: auth()->id();
        if (!$staffId) return;

        $daTonTai = ThongBaoCaNhan::where('nguoi_dung_id', $staffId)
            ->where('tieu_de', $tieuDe)
            ->where('noi_dung', $noiDung)
            ->exists();

        if ($daTonTai) return;

        ThongBaoCaNhan::create([
            'nguoi_dung_id' => $staffId,
            'tieu_de' => $tieuDe,
            'noi_dung' => $noiDung,
            'loai_thong_bao' => $loai,
            'duong_dan' => $duongDan,
            'da_doc' => false,
            'doc_luc' => null,
        ]);
    }
}