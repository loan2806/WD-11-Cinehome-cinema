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
use PayOS\PayOS;
use App\Models\BienTheDoAn;
use App\Models\VeXemPhimGhe;

class BanVeController extends Controller
{
    public function index()
    {
        $showtimes = SuatChieu::with([
            'phim',
            'rapChieuPhim',
            'phongChieu'
        ])
            ->withCount([
                'veXemPhims as sold_tickets_count' => function ($query) {
                    $query->whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung']);
                }
            ])
            ->where('thoi_gian_chieu', '>=', now())
            ->orderBy('thoi_gian_chieu')
            ->get();

        return view('staff.ban-ve.index', compact('showtimes'));
    }

    public function show(SuatChieu $suatChieu)
    {
        $suatChieu->load([
            'phim',
            'rapChieuPhim',
            'phongChieu'
        ]);

        $blockedSeatCodes = VeXemPhim::where(
            'suat_chieu_id',
            $suatChieu->id
        )
            ->whereIn('trang_thai', [
                'dang_giu',
                'da_thanh_toan',
                'da_in',
                'da_su_dung'
            ])
            ->pluck('ma_ghe')
            ->flatMap(function ($codes) {
                return collect(explode(',', $codes))
                    ->map(fn($code) => strtoupper(trim($code)))
                    ->filter();
            })
            ->values();

        $maintenanceSeatCodes = GheNgoi::where(
            'phong_chieu_id',
            $suatChieu->phong_chieu_id
        )
            ->get(['ma_ghe', 'trang_thai'])
            ->filter(fn($seat) => $seat->isEffectivelyUnderMaintenance())
            ->pluck('ma_ghe')
            ->map(fn($code) => strtoupper(trim($code)))
            ->values()
            ->all();

        $seatsByRow = GheNgoi::with([
            'hangGhe',
            'loaiGhe'
        ])
            ->where(
                'phong_chieu_id',
                $suatChieu->phong_chieu_id
            )
            ->orderBy('hang_ghe_id')
            ->orderBy('cot')
            ->get()
            ->groupBy(
                fn($seat) => $seat->hangGhe->ten_hang ?? 'Khác'
            );

        return view('staff.ban-ve.show', [
            'suatChieu' => $suatChieu,
            'soldSeatCodes' => $blockedSeatCodes,
            'maintenanceSeatCodes' => $maintenanceSeatCodes,
            'seatsByRow' => $seatsByRow
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
                    ->whereIn('trang_thai', [
                        'dang_giu',
                        'da_dat',
                        'da_thanh_toan',
                        'da_in',
                        'da_su_dung',
                    ])
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
    | Tạo mã vé tạm
    |--------------------------------------------------------------------------
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

                    /*
    |--------------------------------------------------------------------------
    | Lưu dữ liệu bán vé tại quầy chờ PayOS xác nhận
    |--------------------------------------------------------------------------
    */
                    $pendingData = [
                        'nguoi_dung_id' => null,
                        'nhan_vien_id' => auth()->id(),
                        'suat_chieu_id' => $suatChieu->id,
                        'ma_ve' => $maVe,

                        'ten_phim' => $suatChieu->phim->ten_phim,
                        'ten_rap' => $suatChieu->rapChieuPhim->ten_rap,
                        'ten_phong' => $suatChieu->phongChieu->ten_phong,

                        'ma_ghe' => $selectedSeats->implode(','),
                        'danh_sach_ghe' => $selectedSeats->values()->all(),

                        'thoi_gian_chieu' =>
                        $suatChieu->thoi_gian_chieu->toDateTimeString(),

                        'tong_tien' => $tongTien,
                        'seat_total' => (int) round($seatTotal),
                        'food_total' => (int) round($foodTotal),
                        'payment_method' => 'vietqr',
                        'loai_ve' => 'tai_quay',
                        'food_items' => $verifiedFoodItems->values()->all(),

                        'clear_cart_key' =>
                        $request->input('clear_cart_key'),
                    ];

                    Cache::put(
                        "pending_staff_ve:{$maVe}",
                        $pendingData,
                        now()->addMinutes(15)
                    );

                    /*
    |--------------------------------------------------------------------------
    | Tạo giao dịch PayOS
    |--------------------------------------------------------------------------
    */
                    $orderCode = intval(
                        filter_var(
                            microtime(true) * 10000,
                            FILTER_SANITIZE_NUMBER_INT
                        )
                    ) % 9007199254740991;

                    Cache::put(
                        "staff_payos_mapping:{$orderCode}",
                        $maVe,
                        now()->addMinutes(15)
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
                            'staff.ban-ve.show',
                            $suatChieu->id
                        ),
                    ];

                    $response = $payOS->paymentRequests->create(
                        $paymentData
                    );

                    $checkoutUrl = $response->checkoutUrl ?? null;

                    if (!$checkoutUrl) {
                        throw new \RuntimeException(
                            'PayOS không trả về đường dẫn thanh toán.'
                        );
                    }

                    return [
                        'payment_method' => 'vietqr',
                        'checkout_url' => $checkoutUrl,
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
                return redirect()->away(
                    $result['checkout_url']
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

    public function payosCallback(Request $request)
    {
        $orderCode = $request->input('orderCode');
        $status = strtoupper(
            (string) $request->input('status')
        );

        $maVe = Cache::get(
            "staff_payos_mapping:{$orderCode}"
        );

        if (!$maVe) {
            return redirect()
                ->route('staff.ban-ve.index')
                ->with(
                    'error',
                    'Giao dịch VietQR hết hạn hoặc không tồn tại.'
                );
        }

        $pendingData = Cache::get(
            "pending_staff_ve:{$maVe}"
        );

        if (!$pendingData) {
            return redirect()
                ->route('staff.ban-ve.index')
                ->with(
                    'error',
                    'Dữ liệu bán vé đã hết hạn.'
                );
        }

        if ($status !== 'PAID') {
            Cache::forget(
                "staff_payos_mapping:{$orderCode}"
            );

            return redirect()
                ->route(
                    'staff.ban-ve.show',
                    $pendingData['suat_chieu_id']
                )
                ->with(
                    'error',
                    'Thanh toán VietQR chưa thành công.'
                );
        }

        try {
            $ve = DB::transaction(
                function () use ($pendingData) {
                    $existingTicket = VeXemPhim::where(
                        'ma_ve',
                        $pendingData['ma_ve']
                    )->first();

                    if ($existingTicket) {
                        $this->createSeatTickets(
                            $existingTicket,
                            $pendingData['danh_sach_ghe']
                                ?? explode(',', $pendingData['ma_ghe'])
                        );

                        return $existingTicket;
                    }

                    $ve = VeXemPhim::create([
                        'nguoi_dung_id' => null,
                        'nhan_vien_id' =>
                        $pendingData['nhan_vien_id'],

                        'suat_chieu_id' =>
                        $pendingData['suat_chieu_id'],

                        'ma_ve' =>
                        $pendingData['ma_ve'],

                        'ten_phim' =>
                        $pendingData['ten_phim'],

                        'ten_rap' =>
                        $pendingData['ten_rap'],

                        'ten_phong' =>
                        $pendingData['ten_phong'],

                        'ma_ghe' =>
                        $pendingData['ma_ghe'],

                        'thoi_gian_chieu' =>
                        $pendingData['thoi_gian_chieu'],

                        'tong_tien' =>
                        $pendingData['tong_tien'],

                        'seat_total' =>
                        $pendingData['seat_total'] ?? 0,

                        'food_total' =>
                        $pendingData['food_total'] ?? 0,

                        'payment_method' => 'vietqr',
                        'received_amount' =>
                        $pendingData['tong_tien'],

                        'change_amount' => 0,
                        'food_items' =>
                        $pendingData['food_items'] ?? [],

                        'loai_ve' => 'tai_quay',
                        'trang_thai' => 'da_thanh_toan',
                    ]);

                    if (
                        !empty($pendingData['food_items'])
                    ) {
                        Cache::put(
                            "ve_foods:{$ve->id}",
                            $pendingData['food_items'],
                            now()->addDays(30)
                        );
                    }

                    $this->createSeatTickets(
                        $ve,
                        $pendingData['danh_sach_ghe']
                            ?? explode(',', $pendingData['ma_ghe'])
                    );

                    return $ve;
                }
            );

            Cache::forget(
                "pending_staff_ve:{$maVe}"
            );

            Cache::forget(
    "staff_payos_mapping:{$orderCode}"
);

session()->flash(
    'clear_food_cart_key',
    $pendingData['clear_cart_key']
        ?? (
            'staff_food_cart_v2_'
            . ($pendingData['nhan_vien_id'] ?? auth()->id())
            . '_'
            . $pendingData['suat_chieu_id']
        )
);

return redirect()
    ->route('staff.ban-ve.success', ['id' => $ve->id])
    ->with(
        'success',
        'Thanh toán VietQR thành công. Mã vé: '
            . $ve->ma_ve
    );
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('staff.ban-ve.index')
                ->with(
                    'error',
                    'Đã nhận thanh toán nhưng không thể phát hành vé: '
                        . $e->getMessage()
                );
        }
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