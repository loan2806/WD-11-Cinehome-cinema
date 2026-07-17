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
            'menu' => $menu
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


        $gheList = GheNgoi::where(
            'phong_chieu_id',
            $suatChieu->phong_chieu_id
        )
            ->whereIn('ma_ghe', $seats)
            ->get();


        $seatTotal = 0;

        foreach ($gheList as $ghe) {
            $seatTotal += $ghe->gia ?? $suatChieu->gia_ve;
        }


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
        $request->validate([
            'seats' => 'required|string'
        ]);


        DB::beginTransaction();


        try {


            $selectedSeats = collect(explode(',', $request->seats))
                ->map(fn($seat) => strtoupper(trim($seat)))
                ->filter()
                ->unique()
                ->values();



            $blockedSeats = VeXemPhim::where(
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
                        ->map(fn($code) => strtoupper(trim($code)));
                })
                ->intersect($selectedSeats);



            if ($blockedSeats->isNotEmpty()) {

                throw new \Exception(
                    'Ghế ' . $blockedSeats->implode(', ') . ' đã được bán.'
                );
            }



            $gheList = GheNgoi::where(
                'phong_chieu_id',
                $suatChieu->phong_chieu_id
            )
                ->whereIn('ma_ghe', $selectedSeats)
                ->get();



            $seatTotal = 0;


            foreach ($gheList as $ghe) {

                $seatTotal += $ghe->gia ?? $suatChieu->gia_ve;
            }



            $foodCart = [];


            if ($request->filled('food_cart')) {

                $foodCart = json_decode(
                    $request->food_cart,
                    true
                ) ?? [];
            }



            $foodItems = collect($foodCart)
                ->filter(fn($item) => is_array($item));


            $foodTotal = 0;


            foreach ($foodItems as $food) {


                $item = DoAn::lockForUpdate()
                    ->find($food['id']);


                if (!$item) {
                    continue;
                }


                $qty = (int)($food['qty'] ?? 0);



                /*
     |--------------------------------------------------------------------------
     | Nếu bảng đồ ăn có quản lý tồn kho
     |--------------------------------------------------------------------------
     */


                if (isset($item->so_luong)) {


                    if ($item->so_luong < $qty) {

                        throw new \Exception(
                            'Đồ ăn ' . $item->ten_do_an . ' không đủ số lượng.'
                        );
                    }


                    $item->decrement(
                        'so_luong',
                        $qty
                    );
                }



                $foodTotal +=
                    ($food['price'] ?? $item->gia)
                    * $qty;
            }



            $tongTien = $seatTotal + $foodTotal;



            $ve = VeXemPhim::create([

                'nguoi_dung_id' => null,

                'nhan_vien_id' => auth()->id(),

                'suat_chieu_id' => $suatChieu->id,

                'ma_ve' =>
                'OFF-'
                    . now()->format('YmdHis')
                    . '-'
                    . strtoupper(Str::random(5)),


                'ten_phim' => $suatChieu->phim->ten_phim,

                'ten_rap' => $suatChieu->rapChieuPhim->ten_rap,

                'ten_phong' => $suatChieu->phongChieu->ten_phong,

                'ma_ghe' => $selectedSeats->implode(','),

                'thoi_gian_chieu' => $suatChieu->thoi_gian_chieu,

                'tong_tien' => $tongTien,

                'tien_hoan' => 0,

                'loai_ve' => 'tai_quay',

                'trang_thai' => 'da_thanh_toan'

            ]);



            DB::commit();

            session()->flash(
                'clear_food_cart_key',
                'staff_food_cart_' . auth()->id() . '_' . $suatChieu->id
            );


            return redirect()
                ->route('staff.ban-ve.index')
                ->with([
                    'success' => 'Bán vé thành công. Mã vé: ' . $ve->ma_ve
                ]);
        } catch (\Exception $e) {

            DB::rollBack();

            dd($e->getMessage(), $e->getLine());
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
}
