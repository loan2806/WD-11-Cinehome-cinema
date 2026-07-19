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

        $menu = DoAn::with([
            'variants',
            'category'
        ])
            ->where('is_active', true)
            ->get()
            ->groupBy(
                fn($food) => $food->category->name ?? 'Khác'
            )
            ->map(function ($foods, $category) {

                return [
                    'category' => $category,
                    'foods' => $foods->map(function ($food) {

                        return [
                            'id' => $food->id,
                            'name' => $food->name,
                            'image' => $food->image,
                            'price' => (int)$food->price,
                            'available' => (int)$food->stock_quantity
                        ];
                    })
                        ->values()
                        ->toArray()
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

                foreach (
                    $requestedFoodItems
                    as $foodData
                ) {
                    $foodId = (int) (
                        $foodData['id'] ?? 0
                    );

                    $quantity = (int) (
                        $foodData['qty']
                        ?? $foodData['quantity']
                        ?? 0
                    );

                    if (
                        $foodId <= 0
                        || $quantity <= 0
                    ) {
                        continue;
                    }

                    $food = DoAn::lockForUpdate()
                        ->find($foodId);

                    if (!$food) {
                        throw new \RuntimeException(
                            'Một món ăn không tồn tại.'
                        );
                    }

                    if (
                        isset($food->is_active)
                        && !$food->is_active
                    ) {
                        throw new \RuntimeException(
                            'Món '
                                . $food->name
                                . ' đã ngừng bán.'
                        );
                    }

                    $stockQuantity = (int) (
                        $food->stock_quantity ?? 0
                    );

                    if (
                        $stockQuantity
                        < $quantity
                    ) {
                        throw new \RuntimeException(
                            'Món '
                                . $food->name
                                . ' chỉ còn '
                                . $stockQuantity
                                . ' sản phẩm.'
                        );
                    }

                    /*
                 * Không dùng giá frontend gửi lên.
                 */
                    $unitPrice = (float) (
                        $food->price ?? 0
                    );

                    $foodTotal +=
                        $unitPrice * $quantity;

                    $verifiedFoodItems->push([
                        'id' => $food->id,
                        'name' => $food->name,
                        'image' => $food->image,
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

                foreach ($verifiedFoodItems as $foodItem) {
                    $food = DoAn::with([
                        'category',
                        'variants',
                        'comboItems.variant',
                    ])
                        ->lockForUpdate()
                        ->find($foodItem['id']);

                    if (!$food) {
                        throw new \RuntimeException(
                            'Không tìm thấy món ' . $foodItem['name'] . '.'
                        );
                    }

                    $soldQuantity = (int) $foodItem['qty'];

                    /*
    |--------------------------------------------------------------------------
    | Trừ kho combo
    |--------------------------------------------------------------------------
    |
    | Mỗi combo gồm nhiều biến thể. Khi bán combo phải trừ tồn kho
    | của từng biến thể thành phần.
    |
    */
                    if ($food->isCombo()) {
                        if ($food->comboItems->isEmpty()) {
                            throw new \RuntimeException(
                                'Combo ' . $food->name . ' chưa có thành phần.'
                            );
                        }

                        foreach ($food->comboItems as $comboItem) {
                            $variantId = $comboItem->variant_id
                                ?? $comboItem->food_variant_id
                                ?? $comboItem->variant?->id;

                            $quantityPerCombo = max(
                                (int) ($comboItem->quantity ?? 1),
                                1
                            );

                            $quantityToDeduct =
                                $quantityPerCombo * $soldQuantity;

                            if (!$variantId) {
                                throw new \RuntimeException(
                                    'Một thành phần của combo '
                                        . $food->name
                                        . ' không có biến thể hợp lệ.'
                                );
                            }

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

                            if ($updatedRows === 0) {
                                throw new \RuntimeException(
                                    'Không đủ tồn kho thành phần của combo '
                                        . $food->name
                                        . '.'
                                );
                            }
                        }

                        continue;
                    }

                    /*
    |--------------------------------------------------------------------------
    | Trừ kho món đơn
    |--------------------------------------------------------------------------
    */
                    $variant = $food->saleVariant();

                    if (!$variant) {
                        throw new \RuntimeException(
                            'Món '
                                . $food->name
                                . ' chưa có biến thể đang bán.'
                        );
                    }

                    $updatedRows = BienTheDoAn::where(
                        'id',
                        $variant->id
                    )
                        ->where(
                            'stock_quantity',
                            '>=',
                            $soldQuantity
                        )
                        ->decrement(
                            'stock_quantity',
                            $soldQuantity
                        );

                    if ($updatedRows === 0) {
                        throw new \RuntimeException(
                            'Món '
                                . $food->name
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
                    'staff_food_cart_'
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
     * Mẫu vé khổ 80 mm.
     */
    public function printTicket(int $id)
    {
        $ve = $this->findPrintableTicket($id);

        return view('staff.ban-ve.print-ticket', compact('ve'));
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