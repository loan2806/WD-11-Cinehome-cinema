<?php

namespace App\Http\Controllers\DatVe;

use App\Http\Controllers\Controller;
use App\Models\Food;
use App\Models\RapChieuPhim;
use App\Models\SuatChieu;
use App\Services\DatVeXemPhimService;
use Illuminate\Support\Facades\Auth;
use App\Models\NguoiDungVoucher;
use Carbon\Carbon;
use App\Models\GheNgoi;
use App\Models\Phims;
use Illuminate\Support\Facades\Cache;

class DatVeController extends Controller
{
    public function chonRap()
    {
        return redirect()->route('dat_ve.chon_phim', [
            'rap_id' => $this->rapDatVeMacDinh()->id,
        ]);
    }

    public function chonPhim($rap_id = null)
    {
        $rap = $rap_id
            ? RapChieuPhim::findOrFail($rap_id)
            : $this->rapDatVeMacDinh();

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
                $tongGhe = $suat->phongChieu
                    ->gheNgois
                    ->where('trang_thai', 'hoat_dong')
                    ->count();

                $gheDaDat = $suat->veXemPhims
                    ->whereIn('trang_thai', [
                        'da_dat',
                        'da_thanh_toan',
                    ])
                    ->count();

                $suat->tong_ghe = $tongGhe;
                $suat->ghe_da_dat = $gheDaDat;
                $suat->ghe_trong = max(0, $tongGhe - $gheDaDat);
            }
        }

        return view('user.dat_ve.chon_phim', compact('rap', 'suatChieuTheoPhim', 'dateOptions', 'selectedDate'));
    }

    public function chonGhe(Phims $movie, DatVeXemPhimService $datVeXemPhimService)
    {
        $suatChieu = SuatChieu::with(['phim', 'rapChieuPhim', 'phongChieu'])
            ->where('phim_id', $movie->id)
            ->where('thoi_gian_chieu', '>=', now('Asia/Ho_Chi_Minh'))
            ->orderBy('thoi_gian_chieu')
            ->firstOrFail();

        abort_if($suatChieu->thoi_gian_chieu->lt(now('Asia/Ho_Chi_Minh')), 404);

        session([
            'reservation_expires_at' => now('Asia/Ho_Chi_Minh')->addMinutes(7)->timestamp
        ]);

        $duLieuChonGhe = $datVeXemPhimService->duLieuChonGhe($suatChieu);

        $duLieuChonGhe['vouchers'] = Auth::check()
            ? NguoiDungVoucher::with('voucher')
            ->where('nguoi_dung_id', Auth::id())
            ->where('da_su_dung', false)
            ->where(function ($query) {
                $query->whereNull('ngay_het_han')
                    ->orWhere('ngay_het_han', '>=', now());
            })
            ->whereHas('voucher', function ($query) {
                $query->where('trang_thai', true)
                    ->whereDate('ngay_het_han', '>=', today());
            })
            ->get()
            : collect();

        return view('user.dat_ve.chon_ghe', $duLieuChonGhe);
    }

    public function chonDoAn($suat_chieu_id)
    {
        $suatChieu = SuatChieu::with(['phim', 'rapChieuPhim', 'phongChieu'])
            ->findOrFail($suat_chieu_id);

        $identifier = Auth::id() ?? session()->getId();

        $selectedSeats = collect(explode(',', request('ghe')))
            ->map(fn($seat) => strtoupper(trim($seat)))
            ->filter()
            ->unique()
            ->values();

        foreach ($selectedSeats as $seat) {

            $lock = Cache::get("seat_lock:suat:{$suat_chieu_id}:seat:{$seat}");

            foreach ($selectedSeats as $seat) {

                $lock = Cache::get("seat_lock:suat:{$suat_chieu_id}:seat:{$seat}");

                if (
                    !$lock ||
                    ($lock['identifier'] ?? null) != $identifier ||
                    ($lock['expires_at'] ?? 0) < now()->timestamp
                ) {

                    return redirect()
                        ->route('dat_ve.chon_ghe', $suatChieu->phim->slug)
                        ->with('error', 'Ghế đã hết thời gian giữ, vui lòng chọn lại.');
                }
            }
        }

        abort_if(
            $suatChieu->thoi_gian_chieu->lt(now('Asia/Ho_Chi_Minh')),
            404
        );

        $foods = Food::active()
            ->with([
                'category',
                'variants' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('price');
                },
                'comboItems.variant',
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // ================================
        // FIX: GROUP CATEGORY CHUẨN HOÁ
        // ================================
        $menu = $foods
            ->groupBy(function ($food) {
                return trim($food->category?->name ?? 'Khác');
            })
            ->map(function ($items, $category) {

                return [
                    'category' => $category,
                    'foods' => $items->values()->map(function (Food $food) {

                        // ================= COMBO =================
                        $isCombo = strcasecmp(trim($food->category?->name ?? ''), 'Combo') === 0;

                        if ($isCombo) {

                            $comboItems = $food->comboItems->map(function ($comboItem) {

                                return [
                                    'variant_id' => $comboItem->food_variant_id,
                                    'name' => $comboItem->variant?->food->name ?? $comboItem->variant?->value,
                                    'variant' => $comboItem->variant?->value,
                                    'price' => (float) ($comboItem->variant?->price ?? 0),
                                    'quantity' => (int) ($comboItem->quantity ?? 1),
                                    'stock' => (int) ($comboItem->variant?->stock_quantity ?? 0),
                                ];
                            });

                            $price = $comboItems->sum(fn($item) => $item['price'] * $item['quantity']);

                            $available = $comboItems
                                ->map(
                                    fn($item) =>
                                    $item['quantity'] > 0
                                        ? intdiv($item['stock'], $item['quantity'])
                                        : 0
                                )
                                ->min() ?? 0;

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

                        // ================= VARIANTS =================
                        $variants = $food->variants->map(function ($variant) use ($food) {
                            return [
                                'id' => $variant->id,
                                'food_id' => $food->id,
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
            })
            ->values();

        return view('user.dat_ve.chon_do_an', [
            'suatChieu' => $suatChieu,
            'selectedSeats' => $selectedSeats,
            'menu' => $menu,
        ]);
    }

    private function rapDatVeMacDinh(): RapChieuPhim
    {
        return RapChieuPhim::whereHas('suatChieus', function ($query) {
            $query->where('thoi_gian_chieu', '>=', now('Asia/Ho_Chi_Minh'));
        })
            ->orderBy('ten_rap')
            ->first()
            ?? RapChieuPhim::orderBy('ten_rap')->firstOrFail();
    }
    public function checkout($suat_chieu_id)
    {
        $suatChieu = SuatChieu::with([
            'phim',
            'rapChieuPhim',
            'phongChieu'
        ])->findOrFail($suat_chieu_id);

        $identifier = Auth::id() ?? session()->getId();

        $selectedSeats = collect(explode(',', request('ghe')))
            ->map(fn($seat) => strtoupper(trim($seat)))
            ->filter()
            ->unique()
            ->values();

        foreach ($selectedSeats as $seat) {

            $lock = Cache::get("seat_lock:suat:{$suat_chieu_id}:seat:{$seat}");

            if (
                !$lock ||
                ($lock['identifier'] ?? null) != $identifier ||
                ($lock['expires_at'] ?? 0) < now()->timestamp
            ) {

                return redirect()
                    ->route('dat_ve.chon_ghe', $suatChieu->phim->slug)
                    ->with('error', 'Ghế đã hết thời gian giữ, vui lòng chọn lại.');
            }
        }


        $seatModels = GheNgoi::with('loaiGhe')
            ->where('phong_chieu_id', $suatChieu->phong_chieu_id)
            ->whereIn('ma_ghe', $selectedSeats)
            ->get();

        $seatTotal = $selectedSeats->count();

        $seatTotalPrice = $seatModels->sum(function ($seat) use ($suatChieu) {

            $price = $suatChieu->gia_ve + ($seat->loaiGhe->phu_thu ?? 0);

            if ($seat->loaiGhe?->la_couple) {
                $price = ($suatChieu->gia_ve * 2) + ($seat->loaiGhe->phu_thu ?? 0);
            }

            return $price;
        });

        // ================= FOOD (FIX HERE) =================
        $foodItems = collect(json_decode(request('food_cart', '[]'), true));

        $foodTotal = $foodItems->sum(function ($item) {
            return ($item['price'] ?? 0) * ($item['qty'] ?? 0);
        });

        // ================= GRAND TOTAL =================
        $grandTotal = $seatTotalPrice + $foodTotal;

        return view('user.dat_ve.checkout', compact(
            'suatChieu',
            'selectedSeats',
            'seatTotal',
            'seatTotalPrice',
            'foodItems',
            'foodTotal',
            'grandTotal'
        ));
    }
}
