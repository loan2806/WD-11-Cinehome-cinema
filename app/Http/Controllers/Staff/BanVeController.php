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

        $suatChieu->load([
            'phim',
            'rapChieuPhim',
            'phongChieu'
        ]);

        // Giữ nguyên bộ trạng thái "đã chiếm chỗ" rộng hơn của quầy (bao gồm cả
        // dang_giu/da_in) — đây là logic ĐÚNG hơn bản online, không hạ cấp xuống.
        $blockedSeatCodes = VeXemPhim::where(
            'suat_chieu_id',
            $suatChieu->id
        )
            ->where(function ($query) {
                $query->whereIn('trang_thai', [
                    'dang_giu',
                    'da_dat',
                    'da_thanh_toan',
                    'da_in',
                    'da_su_dung',
                ])->orWhere(function ($pendingQuery) {
                    $pendingQuery
                        ->where('trang_thai', 'cho_thanh_toan')
                        ->where('thoi_gian_het_han', '>', now());
                });
            })
            ->pluck('ma_ghe')
            ->flatMap(function ($codes) {
                return collect(explode(',', $codes))
                    ->map(fn($code) => strtoupper(trim($code)))
                    ->filter();
            })
            ->unique()
            ->values();

        $bookedSeats = $blockedSeatCodes->flip();

        // QUAN TRỌNG: KHÔNG orderBy('cot') ở đây — DatVeController::chonGhe() lấy
        // ghế theo đúng thứ tự tự nhiên của bảng (không orderBy gì cả) rồi mới gom
        // theo hàng, nên thứ tự HÀNG A,B,C... phụ thuộc thứ tự này. Nếu orderBy
        // theo cột, tất cả ghế cột 1 của mọi hàng đứng lẫn lộn trước, làm xáo trộn
        // thứ tự hàng khi gom nhóm — đúng như lỗi đã gặp (A,H,E,B,F,G,D,C).
        $gheNgois = GheNgoi::with('loaiGhe')
            ->where('phong_chieu_id', $suatChieu->phong_chieu_id)
            ->orderBy('id')
            ->get();

        // Xây $gheTheoHang ĐÚNG CÙNG CẤU TRÚC với DatVeController::chonGhe()
        // (tên hàng lấy từ chữ cái đầu mã ghế, y hệt luồng online) để giao diện
        // và JS phía Blade có thể dùng chung logic/markup với trang đặt vé online.
        $gheTheoHang = [];
        foreach ($gheNgois as $ghe) {
            $hang = preg_replace('/[0-9]/', '', $ghe->ma_ghe) ?: 'A';

            $daDat = $bookedSeats->has(strtoupper(trim($ghe->ma_ghe)));
            // Giữ nguyên logic bảo trì đầy đủ của quầy (có kiểm tra lịch bảo trì
            // đang hiệu lực), không hạ cấp xuống chỉ check trang_thai như online.
            $baoTri = $ghe->isEffectivelyUnderMaintenance();
            $chonDuoc = !$daDat && !$baoTri;

            $loaiGheNorm = mb_strtolower($ghe->loaiGhe->ten_loai_ghe ?? 'Thường');
            $laCouple = (bool) ($ghe->loaiGhe->la_couple ?? false) ||
                str_contains($loaiGheNorm, 'couple') ||
                str_contains($loaiGheNorm, 'đôi') ||
                str_contains($loaiGheNorm, 'doi') ||
                str_contains($loaiGheNorm, 'double');

            $phuThu = (float) ($ghe->loaiGhe->phu_thu ?? 0);
            $giaVe = (float) $suatChieu->gia_ve + $phuThu;

            if ($laCouple) {
                $giaVe = ((float) $suatChieu->gia_ve * 2) + $phuThu;
            }

            $gheTheoHang[$hang][] = [
                'id' => $ghe->id,
                'ma_ghe' => $ghe->ma_ghe,
                'loai_ghe' => $ghe->loaiGhe->ten_loai_ghe ?? 'Thường',
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
        if (!$request->filled('seats')) {
            return redirect()
                ->route('staff.ban-ve.show', $suatChieu->id)
                ->with('error', 'Vui lòng chọn ghế trước khi chọn đồ ăn.');
        }

        $selectedSeats = collect(explode(',', $request->seats))
            ->map(fn($seat) => strtoupper(trim($seat)))
            ->filter()
            ->unique()
            ->values();

        $gheList = GheNgoi::with('loaiGhe')
            ->where('phong_chieu_id', $suatChieu->phong_chieu_id)
            ->whereIn('ma_ghe', $selectedSeats->all())
            ->get();

        $seatTotal = $gheList->sum(function ($ghe) use ($suatChieu) {
            $giaVe = (float) ($suatChieu->gia_ve ?? 0);
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
            $query
                ->where('is_active', true)
                ->orderBy('price');
        },

        'comboItems.variant.doAn',
    ])
    ->orderBy('sort_order')
    ->orderBy('name')
    ->get();

$menu = $foods
    ->groupBy(function (DoAn $food) {
        return trim(
            $food->category?->name ?? 'Khác'
        );
    })
    ->map(function ($foods, $category) {
        $menuItems = $foods
            ->flatMap(function (DoAn $food) {
                /*
                |--------------------------------------------------------------------------
                | Combo
                |--------------------------------------------------------------------------
                */
                if ($food->isCombo()) {
                    $comboItems = $food->comboItems
                        ->map(function ($comboItem) {
                            $variant = $comboItem->variant;

                            return [
                                'variant_id' =>
                                    $comboItem->food_variant_id
                                    ?? $variant?->id,

                                'name' =>
                                    $variant?->doAn?->name
                                    ?? 'Thành phần',

                                'variant' =>
                                    $variant?->value,

                                'price' => (float) (
                                    $variant?->price ?? 0
                                ),

                                'quantity' => max(
                                    (int) (
                                        $comboItem->quantity ?? 1
                                    ),
                                    1
                                ),

                                'stock' => (int) (
                                    $variant?->stock_quantity ?? 0
                                ),
                            ];
                        })
                        ->values();

                    $price = $comboItems->sum(
                        fn($item) =>
                        $item['price']
                            * $item['quantity']
                    );

                    $available = $comboItems
                        ->map(function ($item) {
                            if ($item['quantity'] <= 0) {
                                return 0;
                            }

                            return intdiv(
                                $item['stock'],
                                $item['quantity']
                            );
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

                        'combo_items' =>
                        $comboItems->toArray(),
                    ]];
                }

                /*
                |--------------------------------------------------------------------------
                | Món thường: mỗi biến thể là một lựa chọn riêng
                |--------------------------------------------------------------------------
                */
                return $food->variants
                    ->map(function ($variant) use ($food) {
                        $variantName = trim(
                            (string) $variant->value
                        );

                        return [
                            'cart_key' =>
                                'variant-' . $variant->id,

                            'type' => 'variant',

                            /*
                             * id dùng để nhận diện biến thể.
                             */
                            'id' => $variant->id,
                            'food_id' => $food->id,
                            'variant_id' => $variant->id,

                            'name' => $food->name
                                . (
                                    $variantName !== ''
                                    ? ' - ' . $variantName
                                    : ''
                                ),

                            'description' =>
                                $food->description,

                            'image' => $food->image,

                            'price' => (int) round(
                                $variant->price
                            ),

                            'available' => (int) (
                                $variant->stock_quantity ?? 0
                            ),
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

        $foodCart = [];

        if ($request->filled('food_cart')) {
            $foodCart = json_decode(
                $request->food_cart,
                true
            ) ?? [];
        }

        $foodItems = collect($foodCart)
            ->filter(fn($item) => is_array($item));


        $gheList = GheNgoi::with('loaiGhe')
            ->where(
                'phong_chieu_id',
                $suatChieu->phong_chieu_id
            )
            ->whereIn(
                'ma_ghe',
                $seats->all()
            )
            ->get();

        $seatTotal = $gheList->sum(function ($ghe) use ($suatChieu) {
            $giaVe = (float) ($suatChieu->gia_ve ?? 0);
            $phuThu = (float) ($ghe->loaiGhe?->phu_thu ?? 0);

            if ($ghe->loaiGhe?->la_couple) {
                return ($giaVe * 2) + $phuThu;
            }

            return $giaVe + $phuThu;
        });


        $foodTotal = $foodItems->sum(function ($item) {
            return ($item['price'] ?? 0)
                * ($item['qty'] ?? 0);
        });


        $total = $seatTotal + $foodTotal;


        $suatChieu->load([
            'phim',
            'rapChieuPhim',
            'phongChieu'
        ]);


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
        // dd('ĐÃ VÀO STORE');
        $validated = $request->validate([
            'seats' => [
                'required',
                'string',
            ],

            'food_cart' => [
                'nullable',
                'string',
            ],

            'payment_method' => [
                'required',
                'in:cash,vietqr',
            ],

            'received_amount' => [
                'nullable',
                'required_if:payment_method,cash',
                'numeric',
                'min:0',
            ],


        ], [
            'seats.required' =>
            'Không tìm thấy ghế đã chọn.',

            'payment_method.required' =>
            'Vui lòng chọn phương thức thanh toán.',

            'received_amount.required_if' =>
            'Vui lòng nhập số tiền khách đưa.',

            'received_amount.numeric' =>
            'Số tiền khách đưa không hợp lệ.',
        ]);


        try {
            $result = DB::transaction(function () use (
                $request,
                $validated,
                $suatChieu
            ) {

                // dd('ĐÃ VÀO TRANSACTION');
                /*
            |--------------------------------------------------------------------------
            | Nạp thông tin suất chiếu
            |--------------------------------------------------------------------------
            */

                $suatChieu->load([
                    'phim',
                    'rapChieuPhim',
                    'phongChieu',
                ]);

                /*
            |--------------------------------------------------------------------------
            | Chuẩn hóa danh sách ghế
            |--------------------------------------------------------------------------
            */

                $selectedSeats = collect(
                    explode(',', $validated['seats'])
                )
                    ->map(function ($seat) {
                        return strtoupper(trim($seat));
                    })
                    ->filter()
                    ->unique()
                    ->values();

                if ($selectedSeats->isEmpty()) {
                    throw new \RuntimeException(
                        'Không tìm thấy ghế đã chọn.'
                    );
                }

                if ($selectedSeats->count() > 8) {
                    throw new \RuntimeException(
                        'Mỗi lần chỉ được bán tối đa 8 ghế.'
                    );
                }

                /*
            |--------------------------------------------------------------------------
            | Lấy ghế thật trong database
            |--------------------------------------------------------------------------
            */

                $gheList = GheNgoi::with('loaiGhe')
                    ->where(
                        'phong_chieu_id',
                        $suatChieu->phong_chieu_id
                    )
                    ->whereIn(
                        'ma_ghe',
                        $selectedSeats->all()
                    )
                    ->lockForUpdate()
                    ->get();

                if (
                    $gheList->count()
                    !== $selectedSeats->count()
                ) {
                    $foundSeats = $gheList
                        ->pluck('ma_ghe')
                        ->map(function ($seat) {
                            return strtoupper(trim($seat));
                        });

                    $missingSeats = $selectedSeats->diff(
                        $foundSeats
                    );

                    throw new \RuntimeException(
                        'Không tìm thấy ghế: '
                            . $missingSeats->implode(', ')
                    );
                }

                /*
            |--------------------------------------------------------------------------
            | Kiểm tra ghế bảo trì
            |--------------------------------------------------------------------------
            */

                $inactiveSeats = $gheList
                    ->filter(function ($seat) {
                        if (
                            method_exists(
                                $seat,
                                'isEffectivelyUnderMaintenance'
                            )
                        ) {
                            return
                                $seat
                                ->isEffectivelyUnderMaintenance();
                        }

                        return
                            $seat->trang_thai
                            !== 'hoat_dong';
                    })
                    ->pluck('ma_ghe')
                    ->values();

                if ($inactiveSeats->isNotEmpty()) {
                    throw new \RuntimeException(
                        'Ghế '
                            . $inactiveSeats->implode(', ')
                            . ' đang bảo trì hoặc ngừng hoạt động.'
                    );
                }

                /*
            |--------------------------------------------------------------------------
            | Kiểm tra ghế đã được bán
            |--------------------------------------------------------------------------
            */

                $bookedSeatCodes = VeXemPhim::where(
                    'suat_chieu_id',
                    $suatChieu->id
                )
                    ->where(function ($query) {
                        $query->whereIn('trang_thai', [
                            'dang_giu',
                            'da_dat',
                            'da_thanh_toan',
                            'da_in',
                            'da_su_dung',
                        ])->orWhere(function ($pendingQuery) {
                            $pendingQuery
                                ->where('trang_thai', 'cho_thanh_toan')
                                ->where('thoi_gian_het_han', '>', now());
                        });
                    })
                    ->lockForUpdate()
                    ->pluck('ma_ghe')
                    ->flatMap(function ($codes) {
                        return collect(
                            explode(',', (string) $codes)
                        )
                            ->map(function ($code) {
                                return strtoupper(
                                    trim($code)
                                );
                            })
                            ->filter();
                    })
                    ->unique()
                    ->values();

                $blockedSeats = $selectedSeats
                    ->intersect($bookedSeatCodes)
                    ->values();

                if ($blockedSeats->isNotEmpty()) {
                    throw new \RuntimeException(
                        'Ghế '
                            . $blockedSeats->implode(', ')
                            . ' đã được bán.'
                    );
                }

                /*
            |--------------------------------------------------------------------------
            | Backend tự tính tiền ghế
            |--------------------------------------------------------------------------
            */

                $seatTotal = $gheList->sum(
                    function ($ghe) use ($suatChieu) {
                        $giaVe = (float) (
                            $suatChieu->gia_ve ?? 0
                        );

                        $phuThu = (float) (
                            $ghe->loaiGhe?->phu_thu ?? 0
                        );

                        if (
                            $ghe->loaiGhe?->la_couple
                        ) {
                            return (
                                $giaVe * 2
                            ) + $phuThu;
                        }

                        return $giaVe + $phuThu;
                    }
                );

                /*
            |--------------------------------------------------------------------------
            | Đọc giỏ đồ ăn
            |--------------------------------------------------------------------------
            */

                $foodCart = json_decode(
                    $request->input(
                        'food_cart',
                        '[]'
                    ),
                    true
                );

                if (!is_array($foodCart)) {
                    $foodCart = [];
                }

                $requestedFoodItems = collect(
                    $foodCart
                )
                    ->filter(function ($item) {
                        return is_array($item);
                    })
                    ->values();

                $verifiedFoodItems = collect();
$foodTotal = 0;

foreach ($requestedFoodItems as $foodData) {
    $type = (string) (
        $foodData['type'] ?? ''
    );

    $quantity = (int) (
        $foodData['qty']
        ?? $foodData['quantity']
        ?? 0
    );

    if ($quantity <= 0) {
        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | Combo
    |--------------------------------------------------------------------------
    */
    if ($type === 'combo') {
        $foodId = (int) (
            $foodData['food_id']
            ?? $foodData['id']
            ?? 0
        );

        $food = DoAn::with([
            'category',
            'comboItems.variant.doAn',
        ])
            ->lockForUpdate()
            ->find($foodId);

        if (!$food || !$food->isCombo()) {
            throw new \RuntimeException(
                'Combo không tồn tại.'
            );
        }

        if (!$food->is_active) {
            throw new \RuntimeException(
                'Combo ' . $food->name . ' đã ngừng bán.'
            );
        }

        if ($food->comboItems->isEmpty()) {
            throw new \RuntimeException(
                'Combo ' . $food->name
                    . ' chưa có thành phần.'
            );
        }

        $unitPrice = 0;
        $available = null;

        foreach ($food->comboItems as $comboItem) {
            $variant = $comboItem->variant;

            $quantityPerCombo = max(
                (int) ($comboItem->quantity ?? 1),
                1
            );

            if (!$variant || !$variant->is_active) {
                throw new \RuntimeException(
                    'Combo ' . $food->name
                        . ' có thành phần không hợp lệ.'
                );
            }

            $unitPrice +=
                (float) $variant->price
                * $quantityPerCombo;

            $variantAvailable = intdiv(
                (int) $variant->stock_quantity,
                $quantityPerCombo
            );

            $available = $available === null
                ? $variantAvailable
                : min($available, $variantAvailable);
        }

        if (($available ?? 0) < $quantity) {
            throw new \RuntimeException(
                'Combo ' . $food->name
                    . ' chỉ còn '
                    . ($available ?? 0)
                    . ' phần.'
            );
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

    /*
    |--------------------------------------------------------------------------
    | Biến thể món ăn
    |--------------------------------------------------------------------------
    */
    $variantId = (int) (
        $foodData['variant_id']
        ?? $foodData['id']
        ?? 0
    );

    $variant = BienTheDoAn::with('doAn')
        ->lockForUpdate()
        ->find($variantId);

    if (!$variant || !$variant->doAn) {
        throw new \RuntimeException(
            'Biến thể đồ ăn không tồn tại.'
        );
    }

    if (
        !$variant->is_active
        || !$variant->doAn->is_active
    ) {
        throw new \RuntimeException(
            'Món '
                . $variant->doAn->name
                . ' đã ngừng bán.'
        );
    }

    if (
        (int) $variant->stock_quantity
        < $quantity
    ) {
        throw new \RuntimeException(
            'Món '
                . $variant->doAn->name
                . ' - '
                . $variant->value
                . ' chỉ còn '
                . (int) $variant->stock_quantity
                . ' sản phẩm.'
        );
    }

    $unitPrice = (float) $variant->price;

    $foodTotal += $unitPrice * $quantity;

    $variantLabel = trim(
        (string) $variant->value
    );

    $verifiedFoodItems->push([
        'type' => 'variant',
        'id' => $variant->id,
        'food_id' => $variant->food_id,
        'variant_id' => $variant->id,

        'name' => $variant->doAn->name
            . (
                $variantLabel !== ''
                ? ' - ' . $variantLabel
                : ''
            ),

        'price' => $unitPrice,
        'qty' => $quantity,
    ]);
}

                $tongTien = (int) round(
                    $seatTotal + $foodTotal
                );

                /*
            |--------------------------------------------------------------------------
            | Xử lý thanh toán tiền mặt
            |--------------------------------------------------------------------------
            */

                $paymentMethod = $validated['payment_method'];

                if ($paymentMethod === 'vietqr') {
                    /*
                    |--------------------------------------------------------------------------
                    | Tạo mã vé tạm và mã giao dịch PayOS
                    |--------------------------------------------------------------------------
                    |
                    | Vé tạm được lưu ngay vào database với trạng thái
                    | "cho_thanh_toan". Trong 7 phút, các ghế của vé này
                    | được xem là đang bị khóa đối với các giao dịch khác.
                    |
                    */
                    do {
                        $maVe =
                            'OFF-'
                            . now('Asia/Ho_Chi_Minh')->format('ymdHis')
                            . '-'
                            . Str::upper(Str::random(5));
                    } while (
                        VeXemPhim::where('ma_ve', $maVe)->exists()
                    );

                    do {
                        $orderCode = intval(
                            filter_var(
                                microtime(true) * 10000,
                                FILTER_SANITIZE_NUMBER_INT
                            )
                        ) % 9007199254740991;
                    } while (
                        VeXemPhim::where('payos_order_code', $orderCode)->exists()
                    );

                    $expiresAt = now()->addMinutes(
                        self::STAFF_VIETQR_HOLD_MINUTES
                    );

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

                    /*
                    | Cache chỉ giữ dữ liệu phụ cho giao diện/giỏ hàng.
                    | Trạng thái giữ ghế thật nằm trong database.
                    */
                    Cache::put(
                        "staff_payos_mapping:{$orderCode}",
                        $maVe,
                        now()->addMinutes(self::STAFF_PAYOS_CACHE_MINUTES)
                    );

                    Cache::put(
                        "pending_staff_ve:{$maVe}",
                        [
                            've_id' => $vePending->id,
                            'ma_ve' => $maVe,
                            'suat_chieu_id' => $suatChieu->id,
                            'danh_sach_ghe' => $selectedSeats->values()->all(),
                            'clear_cart_key' => $request->input('clear_cart_key'),
                        ],
                        now()->addMinutes(self::STAFF_PAYOS_CACHE_MINUTES)
                    );

                    $payOS = new PayOS(
                        env('PAYOS_CLIENT_ID'),
                        env('PAYOS_API_KEY'),
                        env('PAYOS_CHECKSUM_KEY')
                    );

                    $paymentData = [
                        'orderCode' => $orderCode,
                        'amount' => $tongTien,
                        'description' => 'VE' . $orderCode,
                        'returnUrl' => route(
                            'staff.ban-ve.payos-callback'
                        ),
                        'cancelUrl' => route(
                            'staff.ban-ve.payos-cancel'
                        ),
                        // PayOS cũng hết hạn cùng thời điểm giữ ghế.
                        'expiredAt' => $expiresAt->timestamp,
                    ];

                    $response = $payOS->paymentRequests->create(
                        $paymentData
                    );

                    // SDK có thể trả object hoặc array, data_get đọc được cả hai.
                    $checkoutUrl = data_get($response, 'checkoutUrl');
                    $qrCode = data_get($response, 'qrCode');

                    if (!$checkoutUrl || !$qrCode) {
                        throw new \RuntimeException(
                            'PayOS không trả về đầy đủ thông tin thanh toán VietQR.'
                        );
                    }

                    // Lưu xuống DB để refresh trang vẫn còn QR và đường dẫn PayOS.
                    $vePending->update([
                        'payos_checkout_url' => $checkoutUrl,
                        'payos_qr_code' => $qrCode,
                    ]);

                    return [
                        'payment_method' => 'vietqr',
                        'pending_ticket_id' => $vePending->id,
                    ];
                }

                /*
|--------------------------------------------------------------------------
| Thanh toán tiền mặt
|--------------------------------------------------------------------------
*/
                $receivedAmount = (int) (
                    $validated['received_amount'] ?? 0
                );

                if ($receivedAmount < $tongTien) {
                    throw new \RuntimeException(
                        'Khách còn thiếu '
                            . number_format(
                                $tongTien - $receivedAmount,
                                0,
                                ',',
                                '.'
                            )
                            . 'đ.'
                    );
                }
                if (
                    $receivedAmount
                    < $tongTien
                ) {
                    throw new \RuntimeException(
                        'Khách còn thiếu '
                            . number_format(
                                $tongTien
                                    - $receivedAmount,
                                0,
                                ',',
                                '.'
                            )
                            . 'đ.'
                    );
                }

                /*
            |--------------------------------------------------------------------------
            | Tạo mã vé không trùng
            |--------------------------------------------------------------------------
            */

                do {
                    $maVe =
                        'OFF-'
                        . now(
                            'Asia/Ho_Chi_Minh'
                        )->format('ymdHis')
                        . '-'
                        . Str::upper(
                            Str::random(5)
                        );
                } while (
                    VeXemPhim::where(
                        'ma_ve',
                        $maVe
                    )->exists()
                );

                /*
            |--------------------------------------------------------------------------
            | Lưu vé
            |--------------------------------------------------------------------------
            */

                $ve = new VeXemPhim();

                $ve->nguoi_dung_id = null;
                $ve->nhan_vien_id = auth()->id();
                $ve->suat_chieu_id =
                    $suatChieu->id;

                $ve->ma_ve = $maVe;

                $ve->ten_phim =
                    $suatChieu->phim->ten_phim;

                $ve->ten_rap =
                    $suatChieu
                    ->rapChieuPhim
                    ->ten_rap;

                $ve->ten_phong =
                    $suatChieu
                    ->phongChieu
                    ->ten_phong;

                $ve->ma_ghe =
                    $selectedSeats->implode(',');

                $ve->thoi_gian_chieu =
                    $suatChieu->thoi_gian_chieu;

                $ve->tong_tien = $tongTien;
                $ve->tien_hoan = 0;
                $ve->loai_ve = 'tai_quay';
                $ve->trang_thai = 'da_thanh_toan';

                // Lưu dữ liệu hóa đơn để có thể in lại về sau.
                $ve->payment_method = 'cash';
                $ve->received_amount = $receivedAmount;
                $ve->change_amount = $receivedAmount - $tongTien;
                $ve->seat_total = (int) round($seatTotal);
                $ve->food_total = (int) round($foodTotal);
                $ve->food_items = $verifiedFoodItems->values()->all();

                $ve->save();

                /*
            |--------------------------------------------------------------------------
            | Bảo đảm vé thật sự đã được lưu
            |--------------------------------------------------------------------------
            */

                if (!$ve->exists || !$ve->id) {
                    throw new \RuntimeException(
                        'Không thể lưu vé vào database.'
                    );
                }

                $this->createSeatTickets(
                    $ve,
                    $selectedSeats->all()
                );

                /*
            |--------------------------------------------------------------------------
            | Trừ kho sau khi vé đã lưu
            |--------------------------------------------------------------------------
            */

                /*
|--------------------------------------------------------------------------
| Trừ kho sau khi vé đã lưu
|--------------------------------------------------------------------------
*/

foreach ($verifiedFoodItems as $foodItem) {
    /*
     * Số lượng khách mua.
     */
    $quantity = (int) (
        $foodItem['qty'] ?? 0
    );

    /*
     * Nếu số lượng không hợp lệ thì bỏ qua.
     */
    if ($quantity <= 0) {
        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | Trường hợp 1: Sản phẩm là combo
    |--------------------------------------------------------------------------
    |
    | Combo không có kho riêng.
    | Khi bán combo, hệ thống phải trừ tồn kho của từng biến thể
    | nằm bên trong combo.
    |
    | Ví dụ:
    | Combo A gồm:
    | - 1 bắp size L
    | - 2 nước size M
    |
    | Khách mua 3 combo thì phải trừ:
    | - 3 bắp size L
    | - 6 nước size M
    |
    */

    if (
        ($foodItem['type'] ?? null)
        === 'combo'
    ) {
        /*
         * Lấy lại combo từ database.
         */
        $food = DoAn::with([
            'comboItems.variant',
        ])
            ->lockForUpdate()
            ->find($foodItem['food_id']);

        /*
         * Kiểm tra combo tồn tại.
         */
        if (!$food || !$food->isCombo()) {
            throw new \RuntimeException(
                'Không tìm thấy combo '
                    . ($foodItem['name'] ?? '')
                    . '.'
            );
        }

        /*
         * Kiểm tra combo có thành phần hay không.
         */
        if ($food->comboItems->isEmpty()) {
            throw new \RuntimeException(
                'Combo '
                    . $food->name
                    . ' chưa có thành phần.'
            );
        }

        /*
         * Lặp qua từng thành phần trong combo.
         */
        foreach ($food->comboItems as $comboItem) {
            /*
             * Lấy ID biến thể của thành phần.
             *
             * Tùy cấu trúc bảng của bạn có thể là:
             * - food_variant_id
             * - variant_id
             */
            $variantId =
                $comboItem->food_variant_id
                ?? $comboItem->variant_id
                ?? $comboItem->variant?->id;

            /*
             * Kiểm tra có ID biến thể hợp lệ không.
             */
            if (!$variantId) {
                throw new \RuntimeException(
                    'Một thành phần của combo '
                        . $food->name
                        . ' không có biến thể hợp lệ.'
                );
            }

            /*
             * Số lượng biến thể cần cho 1 combo.
             *
             * Ví dụ:
             * combo có 2 nước thì quantityPerCombo = 2.
             */
            $quantityPerCombo = max(
                (int) (
                    $comboItem->quantity ?? 1
                ),
                1
            );

            /*
             * Tổng số lượng cần trừ kho.
             *
             * Ví dụ:
             * 2 nước/combo x 3 combo = trừ 6 nước.
             */
            $quantityToDeduct =
                $quantityPerCombo * $quantity;

            /*
             * Trừ kho có điều kiện.
             *
             * Chỉ trừ khi tồn kho vẫn đủ.
             */
            $updatedRows = BienTheDoAn::where(
                'id',
                $variantId
            )
                ->where(
                    'stock_quantity',
                    '>=',
                    $quantityToDeduct
                )
                ->decrement(
                    'stock_quantity',
                    $quantityToDeduct
                );

            /*
             * Nếu không có dòng nào được cập nhật,
             * nghĩa là kho không đủ hoặc biến thể không tồn tại.
             */
            if ($updatedRows === 0) {
                throw new \RuntimeException(
                    'Không đủ tồn kho thành phần của combo '
                        . $food->name
                        . '.'
                );
            }
        }

        /*
         * Combo đã xử lý xong.
         * Bỏ qua phần trừ kho biến thể món đơn bên dưới.
         */
        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | Trường hợp 2: Sản phẩm là biến thể món đơn
    |--------------------------------------------------------------------------
    |
    | Ví dụ:
    | - Coca size M
    | - Coca size L
    | - Bắp phô mai size L
    |
    | Phải trừ đúng biến thể được chọn,
    | không trừ chung theo food_id.
    */

    $variantId = (int) (
        $foodItem['variant_id']
        ?? $foodItem['id']
        ?? 0
    );

    /*
     * Không có ID biến thể thì báo lỗi.
     */
    if ($variantId <= 0) {
        throw new \RuntimeException(
            'Không xác định được biến thể của món '
                . ($foodItem['name'] ?? '')
                . '.'
        );
    }

    /*
     * Trừ đúng tồn kho của biến thể.
     */
    $updatedRows = BienTheDoAn::where(
        'id',
        $variantId
    )
        ->where(
            'stock_quantity',
            '>=',
            $quantity
        )
        ->decrement(
            'stock_quantity',
            $quantity
        );

    /*
     * Nếu không trừ được thì báo lỗi.
     */
    if ($updatedRows === 0) {
        throw new \RuntimeException(
            'Món '
                . ($foodItem['name'] ?? '')
                . ' không còn đủ tồn kho.'
        );
    }
}

                /*
            |--------------------------------------------------------------------------
            | Lưu thông tin đồ ăn để hiển thị cùng vé
            |--------------------------------------------------------------------------
            */

                if ($verifiedFoodItems->isNotEmpty()) {
                    Cache::put(
                        "ve_foods:{$ve->id}",
                        $verifiedFoodItems->values()->all(),
                        now()->addDays(30)
                    );
                }

                return [
                    've' => $ve,
                    'change_amount' => $receivedAmount - $tongTien,
                ];
            });

            if (
                ($result['payment_method'] ?? null)
                === 'vietqr'
            ) {
                return redirect()->route(
                    'staff.ban-ve.vietqr-waiting',
                    ['id' => $result['pending_ticket_id']]
                );
            }

            $ve = $result['ve'];
            $changeAmount =
                $result['change_amount'];

            session()->flash(
    'clear_food_cart_key',
    $request->input(
        'clear_cart_key',
        'staff_food_cart_v2_'
            . auth()->id()
            . '_'
            . $suatChieu->id
    )
);

            return redirect()
                ->route('staff.ban-ve.success', ['id' => $ve->id])
                ->with(
                    'success',
                    'Bán vé thành công. Mã vé: '
                        . $ve->ma_ve
                        . '. Tiền thừa: '
                        . number_format($changeAmount, 0, ',', '.')
                        . 'đ.'
                );
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Không thể bán vé: ' . $e->getMessage()
                );
        }
    }



    public function showCheckout(SuatChieu $suatChieu)
    {
        return redirect()
            ->route(
                'staff.ban-ve.show',
                $suatChieu->id
            );
    }

    /**
     * Trang chờ thanh toán tại quầy.
     * Vé đã tồn tại trong DB ở trạng thái cho_thanh_toan nên ghế được khóa thật.
     */
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

    /**
     * Frontend gọi định kỳ để đồng bộ trạng thái trực tiếp với PayOS.
     * Cách này hoạt động cả khi chạy localhost và chưa cấu hình webhook public.
     */
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

            // Không giải phóng ghế chỉ vì API PayOS tạm thời không phản hồi.
            return response()->json([
                'success' => false,
                'status' => 'PENDING',
                'message' => 'Chưa thể kiểm tra PayOS, hệ thống sẽ tự thử lại.',
            ], 200);
        }
    }

    /** Hủy thủ công giao dịch đang chờ và giải phóng ghế. */
    public function cancelPendingVietQr(int $id)
    {
        $ve = VeXemPhim::query()
            ->where('loai_ve', 'tai_quay')
            ->where('payment_method', 'vietqr')
            ->findOrFail($id);

        if ($ve->trang_thai === 'cho_thanh_toan') {
            // Kiểm tra lần cuối để tránh hủy một giao dịch vừa thanh toán xong.
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

                return redirect()
                    ->route('staff.ban-ve.success', ['id' => $ve->id])
                    ->with('success', 'Thanh toán VietQR thành công. Mã vé: ' . $ve->ma_ve);
            }

            if ($status === 'CANCELLED') {
                $ve->update([
                    'trang_thai' => 'da_huy',
                    'thoi_gian_het_han' => null,
                ]);

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

        // PayOS đưa trình duyệt về cancelUrl; trước khi hủy DB vẫn kiểm tra API lần cuối.
        try {
            if ($this->getPayosStatus($ve) === 'PAID') {
                $ve = $this->finalizePaidVietQr($ve);

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
        }

        return redirect()
            ->route('staff.ban-ve.show', $ve->suat_chieu_id)
            ->with('error', 'Giao dịch VietQR đã được hủy. Các ghế đã được giải phóng.');
    }

    /**
     * Trang kết quả sau khi thanh toán thành công.
     */
    public function success(int $id)
    {
        $ve = $this->findPrintableTicket($id);

        return view('staff.ban-ve.success', compact('ve'));
    }

    /**
     * Đánh dấu vé đã được phát hành/in.
     *
     * Với trình duyệt, không thể xác định chắc chắn người dùng đã bấm
     * Print hay Cancel trong hộp thoại hệ thống. Vì vậy nghiệp vụ coi
     * thao tác bấm nút "In vé" là thời điểm phát hành vé.
     */
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
        }

        return response()->json([
            'success' => true,
            'message' => 'Vé đã được đánh dấu là đã in.',
            'status' => $ve->fresh()->trang_thai,
        ]);
    }

    /**
     * Mẫu vé khổ 80 mm.
     */
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
        ->where(
            'phong_chieu_id',
            $ve->suatChieu->phong_chieu_id
        )
        ->whereIn('ma_ghe', $seatCodes->all())
        ->get()
        ->keyBy(function ($seat) {
            return strtoupper(trim((string) $seat->ma_ghe));
        });

    return view(
        'staff.ban-ve.print-ticket',
        compact('ve', 'seatDetails')
    );
}

    /**
     * Mẫu hóa đơn khổ 80 mm.
     */
    public function printInvoice(int $id)
    {
        $ve = $this->findPrintableTicket($id);

        return view('staff.ban-ve.print-invoice', compact('ve'));
    }

    /**
     * Chỉ cho phép nhân viên có quyền bán vé xem/in vé tại quầy.
     */
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

        if (!in_array(
            $ve->trang_thai,
            ['da_thanh_toan', 'da_in', 'da_su_dung'],
            true
        )) {
            abort(422, 'Vé chưa thanh toán thành công nên chưa thể xem hoặc in.');
        }

        /*
         * Tương thích với vé cũ được tạo trước khi có bảng vé ghế.
         */
        if ($ve->gheVes->isEmpty()) {
            $this->createSeatTickets(
                $ve,
                explode(',', $ve->ma_ghe)
            );

            $ve->load('gheVes');
        }

        return $ve;
    }

    /** Lấy trạng thái chính thức của payment link từ API PayOS. */
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
            ->get(
                'https://api-merchant.payos.vn/v2/payment-requests/'
                . $ve->payos_order_code
            );

        if (!$response->successful()) {
            throw new \RuntimeException(
                'Không thể lấy trạng thái PayOS (HTTP ' . $response->status() . ').'
            );
        }

        return strtoupper((string) data_get($response->json(), 'data.status', 'PENDING'));
    }

    /** Hủy payment link PayOS nhưng không làm hỏng luồng nếu API đang lỗi. */
    private function cancelPayosLinkSilently(VeXemPhim $ve, string $reason): void
    {
        if (!$ve->payos_order_code) {
            return;
        }

        try {
            Http::timeout(10)
                ->acceptJson()
                ->withHeaders([
                    'x-client-id' => (string) env('PAYOS_CLIENT_ID'),
                    'x-api-key' => (string) env('PAYOS_API_KEY'),
                ])
                ->post(
                    'https://api-merchant.payos.vn/v2/payment-requests/'
                    . $ve->payos_order_code
                    . '/cancel',
                    ['cancellationReason' => $reason]
                );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Hoàn tất đúng một lần giao dịch VietQR đã PAID.
     * Tạo vé ghế và trừ kho nằm cùng transaction để tránh trạng thái nửa vời.
     */
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
                ->map(fn ($seat) => strtoupper(trim($seat)))
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
                        $pendingQuery
                            ->where('trang_thai', 'cho_thanh_toan')
                            ->where('thoi_gian_het_han', '>', now());
                    });
                })
                ->lockForUpdate()
                ->pluck('ma_ghe')
                ->flatMap(fn ($codes) => collect(explode(',', (string) $codes)))
                ->map(fn ($code) => strtoupper(trim($code)))
                ->filter()
                ->unique();

            $conflicts = $seatCodes->intersect($otherBlockedSeats)->values();

            if ($conflicts->isNotEmpty()) {
                throw new \RuntimeException(
                    'Ghế ' . $conflicts->implode(', ') . ' đã được giao dịch khác chiếm.'
                );
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

        $query->update([
            'trang_thai' => 'het_han',
        ]);
    }

    private function deductFoodStock(array $foodItems): void
    {
        foreach ($foodItems as $foodItem) {
            if (!is_array($foodItem)) {
                continue;
            }

            $quantity = (int) ($foodItem['qty'] ?? 0);

            if ($quantity <= 0) {
                continue;
            }

            if (($foodItem['type'] ?? null) === 'combo') {
                $food = DoAn::with(['comboItems.variant'])
                    ->lockForUpdate()
                    ->find((int) ($foodItem['food_id'] ?? 0));

                if (!$food || !$food->isCombo()) {
                    throw new \RuntimeException(
                        'Không tìm thấy combo ' . ($foodItem['name'] ?? '') . '.'
                    );
                }

                foreach ($food->comboItems as $comboItem) {
                    $variantId =
                        $comboItem->food_variant_id
                        ?? $comboItem->variant_id
                        ?? $comboItem->variant?->id;

                    if (!$variantId) {
                        throw new \RuntimeException(
                            'Combo ' . $food->name . ' có thành phần không hợp lệ.'
                        );
                    }

                    $quantityPerCombo = max(
                        (int) ($comboItem->quantity ?? 1),
                        1
                    );

                    $quantityToDeduct = $quantityPerCombo * $quantity;

                    $updatedRows = BienTheDoAn::where('id', $variantId)
                        ->where('stock_quantity', '>=', $quantityToDeduct)
                        ->decrement('stock_quantity', $quantityToDeduct);

                    if ($updatedRows === 0) {
                        throw new \RuntimeException(
                            'Không đủ tồn kho thành phần của combo '
                                . $food->name . '.'
                        );
                    }
                }

                continue;
            }

            $variantId = (int) (
                $foodItem['variant_id']
                ?? $foodItem['id']
                ?? 0
            );

            if ($variantId <= 0) {
                throw new \RuntimeException(
                    'Không xác định được biến thể của món '
                        . ($foodItem['name'] ?? '') . '.'
                );
            }

            $updatedRows = BienTheDoAn::where('id', $variantId)
                ->where('stock_quantity', '>=', $quantity)
                ->decrement('stock_quantity', $quantity);

            if ($updatedRows === 0) {
                throw new \RuntimeException(
                    'Món ' . ($foodItem['name'] ?? '')
                        . ' không còn đủ tồn kho.'
                );
            }
        }
    }

    private function createSeatTickets(
        VeXemPhim $ve,
        iterable $seatCodes
    ): void {
        foreach ($seatCodes as $seatCode) {
            $seatCode = strtoupper(trim((string) $seatCode));

            if ($seatCode === '') {
                continue;
            }

            VeXemPhimGhe::firstOrCreate(
                [
                    've_xem_phim_id' => $ve->id,
                    'ma_ghe' => $seatCode,
                ],
                [
                    /*
                 * Mỗi ghế có token QR riêng.
                 */
                    'ma_qr' => 'CH-'
                        . Str::upper(Str::random(48)),

                    'trang_thai' =>
                    VeXemPhimGhe::CHUA_SU_DUNG,
                ]
            );
        }
    }
}