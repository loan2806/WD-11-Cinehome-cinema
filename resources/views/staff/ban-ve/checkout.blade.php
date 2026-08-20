@extends('layouts.admin')

@section('title', 'Thanh toán tại quầy')
@section('page-title', 'Thanh toán tại quầy')

@php
$currentUser = auth()->user();

$staffName = 'Nhân viên';
$staffEmail = '';

if ($currentUser) {
if (!empty($currentUser->ho_ten)) {
$staffName = $currentUser->ho_ten;
} elseif (!empty($currentUser->ten)) {
$staffName = $currentUser->ten;
}

$staffEmail = $currentUser->email ?? '';
}

$posterPath = '';

if (!empty($suatChieu->phim->poster)) {
$posterPath = asset('storage/movies/' . $suatChieu->phim->poster);
}

$staffId = auth()->id();
$previousUrl = url()->previous();
$staffVoucherData = $staffVouchers->map(function ($voucher) {
    return [
        'code' => $voucher->ma_voucher,
        'name' => $voucher->ten_voucher,
        'type' => $voucher->kieu_giam,
        'value' => (float) $voucher->gia_tri_giam,
    ];
})->values();
@endphp

@section('content')

<style>
    /* ===== NGOÀI ĐEN - KHUNG TRONG XÁM ĐEN ===== */
    .booking-checkout-page {
        background: #080808 !important;
    }

    .booking-checkout-page .booking-checkout-shell {
        background: #1b1b1b !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
        box-shadow: 0 25px 70px rgba(0, 0, 0, 0.55) !important;
        backdrop-filter: blur(8px);
    }

    .booking-checkout-page .booking-checkout-info,
    .booking-checkout-page .booking-checkout-order {
        border-color: rgba(148, 163, 184, 0.14) !important;
    }

    .booking-checkout-page .payment-method-label {
        border-radius: 16px !important;
        background: #141414;
        transition: transform .2s ease, border-color .2s ease, background .2s ease, box-shadow .2s ease;
    }

    .booking-checkout-page .payment-method-label:hover {
        transform: translateY(-1px);
        border-color: rgba(250, 204, 21, .5) !important;
        box-shadow: 0 10px 28px rgba(2, 6, 23, .22);
    }

    .booking-checkout-page #cashPaymentSection,
    .booking-checkout-page #vietQrPaymentSection {
        border-radius: 16px !important;
        background: #141414 !important;
        border-color: rgba(148, 163, 184, 0.14) !important;
    }

    .booking-checkout-page input {
        border-radius: 12px !important;
        background: #111111 !important;
        border-color: rgba(148, 163, 184, 0.18) !important;
    }

    .booking-checkout-page input:focus {
        box-shadow: 0 0 0 3px rgba(250, 204, 21, .08);
    }

    .staff-voucher-scroll::-webkit-scrollbar {
        width: 4px;
    }
    .staff-voucher-scroll::-webkit-scrollbar-track {
        background: rgba(148, 163, 184, .06);
        border-radius: 4px;
    }
    .staff-voucher-scroll::-webkit-scrollbar-thumb {
        background: rgba(250, 204, 21, .65);
        border-radius: 4px;
    }
    .staff-voucher-scroll {
        scrollbar-width: thin;
        scrollbar-color: rgba(250, 204, 21, .65) rgba(148, 163, 184, .06);
    }

    .staff-voucher-card {
        transition: all .2s ease;
    }
    .staff-voucher-card:hover {
        transform: translateY(-1px);
        border-color: rgba(250, 204, 21, .5) !important;
        background: #242424 !important;
    }

    .booking-checkout-page .checkout-food-image {
        width: 72px !important;
        height: 72px !important;
        min-width: 72px !important;
        min-height: 72px !important;
        flex: 0 0 72px !important;
    }

    .booking-checkout-page #submitPaymentButton {
        border-radius: 16px !important;
    }
</style>


<div class="booking-checkout-page min-h-screen bg-[#080808] pt-24 pb-12 text-white" lang="vi" spellcheck="false">
    <div class="booking-checkout-container mx-auto max-w-7xl px-4 lg:px-6">

        {{-- ================= HEADER ================= --}}
        <div class="mb-8">
            <p class="text-xs uppercase tracking-[0.45em] text-yellow-400">
                Checkout
            </p>

            <h1 class="mt-2 text-4xl font-black">
                THANH TOÁN
            </h1>

            <p class="mt-2 text-gray-400">
                Kiểm tra thông tin trước khi hoàn tất giao dịch tại quầy.
            </p>
        </div>

        {{-- ================= THÔNG BÁO ================= --}}
        @if (session('success'))
        <div class="mb-6 rounded-2xl border border-green-500/30 bg-green-500/10 p-4 text-green-300">
            {{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="mb-6 rounded-2xl border border-red-500/30 bg-red-500/10 p-4 text-red-300">
            {{ session('error') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-500/30 bg-red-500/10 p-4 text-red-300">
            <ul class="space-y-1">
                @foreach ($errors->all() as $error)
                <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- ================= CHECKOUT BOX ================= --}}
        <div class="booking-checkout-shell overflow-hidden rounded-3xl border border-zinc-800 bg-[#141414] shadow-2xl">


            {{-- ================= MAIN LAYOUT ================= --}}
            <div class="mt-6 grid lg:grid-cols-[380px_1fr]">

                {{-- ================= LEFT SIDE ================= --}}
                <div class="booking-checkout-info border-r border-zinc-800/80 p-8">

                    {{-- NHÂN VIÊN --}}
                    <div class="pb-6">

                        <div class="mb-4 flex items-center gap-4">

                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-yellow-400 text-xl text-black">
                                👤
                            </div>

                            <div>
                                <h2 class="text-xl font-black text-yellow-400">
                                    Nhân viên bán vé
                                </h2>

                                <p class="text-sm text-gray-400">
                                    Tài khoản thực hiện giao dịch
                                </p>
                            </div>

                        </div>

                        <input type="text" readonly value="{{ $staffName }}" class="mb-3 w-full rounded-xl border border-white/10 bg-[#1d1d1d] px-4 py-3 text-white outline-none">

                        <input type="text" readonly value="{{ $staffEmail }}" class="w-full rounded-xl border border-white/10 bg-[#1d1d1d] px-4 py-3 text-white outline-none">

                    </div>

                    {{-- THÔNG TIN PHIM --}}
                    <div class="mt-6 border-t border-white/10 pt-6">

                        <h2 class="mb-3 text-lg font-black text-yellow-400">
                            Thông tin phim
                        </h2>

                        <div class="overflow-hidden rounded-xl border border-white/10 bg-black/20">

                            @if ($posterPath !== '')
                            <img src="{{ $posterPath }}" alt="{{ $suatChieu->phim->ten_phim }}" class="w-full object-cover">
                            @else
                            <div class="flex aspect-[2/3] items-center justify-center text-gray-500">
                                Không có poster
                            </div>
                            @endif

                        </div>

                        <div class="mt-4 space-y-3 text-sm text-gray-300">

                            <div>
                                <strong class="text-white">
                                    Rạp chiếu:
                                </strong>

                                {{ $suatChieu->rapChieuPhim->ten_rap }}
                            </div>

                            <div>
                                <strong class="text-white">
                                    Phòng chiếu:
                                </strong>

                                {{ $suatChieu->phongChieu->ten_phong }}
                            </div>

                            <div>
                                <strong class="text-white">
                                    Tên phim:
                                </strong>

                                {{ $suatChieu->phim->ten_phim }}
                            </div>

                            <div>
                                <strong class="text-white">
                                    Suất chiếu:
                                </strong>

                                {{ $suatChieu->thoi_gian_chieu->format('d/m/Y H:i') }}
                            </div>

                        </div>

                    </div>

                </div>

                {{-- ================= RIGHT SIDE ================= --}}
                <div class="booking-checkout-order p-8">

                    {{-- ================= DANH SÁCH ĐẶT ================= --}}
                    <div class="border-b border-white/10 pb-6">

                        <h2 class="mb-4 text-2xl font-black text-yellow-400">
                            Danh sách đặt
                        </h2>

                        {{-- DANH SÁCH GHẾ --}}
                        <div class="border-b border-white/10 py-6">

                            <div class="flex items-start justify-between gap-4">

                                <span class="text-gray-300">
                                    Số ghế
                                </span>

                                <div class="flex flex-wrap justify-end gap-2">

                                    @foreach ($seats as $seat)
                                    <span class="rounded-lg border border-yellow-400/20 bg-yellow-400/10 px-3 py-1 font-semibold text-yellow-400">
                                        {{ $seat }}
                                    </span>
                                    @endforeach

                                </div>

                            </div>

                            {{-- DANH SÁCH ĐỒ ĂN --}}
                            <div class="mt-6 space-y-4">

                                @forelse ($foodItems as $item)

                                @php
                                $foodImagePath = trim((string) (
                                    $item['image_url']
                                    ?? $item['image']
                                    ?? $item['image_path']
                                    ?? $item['hinh_anh']
                                    ?? $item['anh']
                                    ?? $item['food_image']
                                    ?? data_get($item, 'food.image_url')
                                    ?? data_get($item, 'food.image')
                                    ?? data_get($item, 'food.image_path')
                                    ?? ''
                                ));
                                $foodFallbackImage = asset('assets/images/LOGO copy.png');
                                $foodImageUrl = $foodFallbackImage;

                                // Hỗ trợ cả URL đầy đủ, /storage/foods/..., foods/... và tên file.
                                if ($foodImagePath !== '') {
                                    if (preg_match('/^https?:\/\//i', $foodImagePath)) {
                                        $foodImageUrl = $foodImagePath;
                                    } else {
                                        $normalizedFoodImage = ltrim(str_replace('\\', '/', $foodImagePath), '/');
                                        $normalizedFoodImage = preg_replace('#^public/#', '', $normalizedFoodImage);

                                        if (strpos($normalizedFoodImage, 'storage/') === 0) {
                                            $foodImageUrl = asset($normalizedFoodImage);
                                        } elseif (strpos($normalizedFoodImage, 'foods/') === 0) {
                                            $foodImageUrl = asset('storage/' . $normalizedFoodImage);
                                        } else {
                                            $foodImageUrl = asset('storage/foods/' . $normalizedFoodImage);
                                        }
                                    }
                                }

                                $foodName = $item['name'] ?? 'Sản phẩm';
                                $foodQuantity = (int) ($item['qty'] ?? 0);
                                $foodPrice = (int) ($item['price'] ?? 0);
                                $foodLineTotal = $foodPrice * $foodQuantity;
                                @endphp

                                <div class="flex items-center justify-between gap-4">

                                    <div class="flex min-w-0 items-center gap-3">

                                        @if ($foodImageUrl !== '')
                                        <div class="h-[64px] w-[64px] shrink-0 overflow-hidden rounded-lg border border-white/10 bg-[#111111]">
                                            <img
                                                src="{{ $foodImageUrl }}"
                                                alt="{{ $foodName }}"
                                                class="block h-full w-full object-cover object-center"
                                                loading="lazy"
                                                decoding="async"
                                                onerror="this.onerror=null;this.src='{{ $foodFallbackImage }}';"
                                            >
                                        </div>
                                        @endif

                                        <div class="min-w-0">

                                            <p class="truncate font-semibold text-gray-200">
                                                {{ $foodName }}
                                            </p>

                                            <p class="mt-1 text-sm text-gray-500">
                                                {{ number_format($foodPrice, 0, ',', '.') }}đ
                                            </p>

                                        </div>

                                    </div>

                                    <div class="text-right">

                                        <p class="font-semibold">
                                            x{{ $foodQuantity }}
                                        </p>

                                        <p class="mt-1 text-sm text-yellow-400">
                                            {{ number_format($foodLineTotal, 0, ',', '.') }}đ
                                        </p>

                                    </div>

                                </div>

                                @empty

                                <div class="flex justify-between text-gray-400">
                                    <span>Đồ ăn</span>
                                    <span>Không có</span>
                                </div>

                                @endforelse

                            </div>

                        </div>

                    </div>

                    {{-- ================= TỔNG TIỀN ================= --}}
                    <div class="border-b border-white/10 py-6">

                        <div class="flex justify-between">

                            <span class="text-gray-400">
                                Tiền ghế
                            </span>

                            <span class="font-semibold">
                                {{ number_format($seatTotal, 0, ',', '.') }}đ
                            </span>

                        </div>

                        <div class="mt-3 flex justify-between">

                            <span class="text-gray-400">
                                Tiền đồ ăn
                            </span>

                            <span class="font-semibold">
                                {{ number_format($foodTotal, 0, ',', '.') }}đ
                            </span>

                        </div>

                        <div id="discountRow" class="mt-4 hidden justify-between border-t border-white/10 pt-4 font-semibold text-green-400">
                            <span>Giảm giá (Voucher)</span>
                            <span id="discountAmount">-0đ</span>
                        </div>

                        <div class="mt-5 flex justify-between border-t border-white/10 pt-5 text-2xl font-black text-yellow-400">

                            <span>
                                Tổng
                            </span>

                            <span id="grandTotal">
                                {{ number_format($total, 0, ',', '.') }}đ
                            </span>

                        </div>

                    </div>

                    {{-- ================= VOUCHER ĐẶC BIỆT TẠI QUẦY ================= --}}
                    <div class="border-b border-white/10 py-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-xl font-black text-yellow-400">Mã giảm giá</h2>
                                <p class="mt-1 text-xs text-gray-400">
                                    Dùng voucher cho khách thuộc trường hợp đặc biệt.
                                </p>
                            </div>
                            <span class="rounded-full border border-yellow-400/30 bg-yellow-400/10 px-3 py-1 text-xs font-bold text-yellow-400">
                                {{ $staffVouchers->count() }} voucher khả dụng
                            </span>
                        </div>

                        {{-- DANH SÁCH VOUCHER ĐẶC BIỆT HIỆN SẴN --}}
                        <div class="staff-voucher-scroll mb-4 max-h-[245px] space-y-2 overflow-y-auto pr-1">
                            @forelse($staffVouchers as $voucher)
                                @php
                                    $voucherValue = (float) $voucher->gia_tri_giam;
                                    $voucherDisplay = $voucher->kieu_giam === 'phan_tram'
                                        ? rtrim(rtrim(number_format($voucherValue, 2, ',', '.'), '0'), ',') . '%'
                                        : number_format($voucherValue, 0, ',', '.') . 'đ';
                                @endphp

                                <div class="staff-voucher-card flex items-center gap-3 rounded-2xl border border-white/10 bg-[#161616] p-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-yellow-400 text-black">
                                        <span class="text-lg">🎟</span>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="truncate text-sm font-black text-white">
                                            {{ $voucher->ten_voucher }}
                                        </div>
                                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs">
                                            <span class="rounded-md border border-yellow-400/50 bg-yellow-400/10 px-2 py-0.5 font-bold text-yellow-300">
                                                {{ $voucher->ma_voucher }}
                                            </span>
                                            <span class="font-bold text-green-400">
                                                Giảm {{ $voucherDisplay }}
                                            </span>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        class="use-staff-voucher shrink-0 rounded-xl border border-yellow-400/60 px-3 py-2 text-xs font-black text-yellow-300 transition hover:bg-yellow-400 hover:text-black"
                                        data-voucher-code="{{ $voucher->ma_voucher }}"
                                    >
                                        Sử dụng
                                    </button>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-white/10 bg-[#161616] p-4 text-sm text-gray-400">
                                    Hiện chưa có voucher đặc biệt nào đang hoạt động tại quầy.
                                </div>
                            @endforelse
                        </div>

                        <div class="mb-3 text-xs text-gray-500">
                            Hoặc nhập mã voucher khác nếu khách cung cấp mã riêng.
                        </div>

                        <div class="flex gap-2">
                            <input
                                type="text"
                                id="voucherCode"
                                placeholder="HOẶC NHẬP MÃ VOUCHER KHÁC..."
                                autocomplete="off"
                                class="flex-1 rounded-2xl border border-white/10 bg-[#111111] px-4 py-3 text-sm uppercase tracking-wider text-white outline-none transition focus:border-yellow-400"
                            >

                            <button
                                type="button"
                                id="applyVoucherButton"
                                class="shrink-0 rounded-2xl bg-yellow-400 px-5 text-sm font-black text-black transition duration-200 hover:bg-yellow-300 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                Áp dụng
                            </button>
                        </div>

                        <div
                            id="voucherResult"
                            class="mt-3 hidden items-center justify-between rounded-2xl border border-yellow-400/30 bg-yellow-400/10 p-3.5 text-sm text-yellow-300"
                        >
                            <span id="voucherResultText">✔ Đã áp dụng voucher</span>
                            <button
                                type="button"
                                id="resetVoucherButton"
                                class="ml-2 text-xs font-bold text-red-400 underline transition hover:text-red-300"
                            >
                                Hủy dùng
                            </button>
                        </div>

                    </div>

                    {{-- ================= THANH TOÁN ================= --}}
                    <div class="pt-6">

                        <h2 class="mb-4 text-xl font-black text-yellow-400">
                            Chọn phương thức thanh toán
                        </h2>

                        <form id="paymentForm" action="{{ route('staff.ban-ve.store', $suatChieu->id) }}" method="POST">
                            @csrf

                            <input type="hidden" name="seats" value="{{ $seats->implode(',') }}">

                            <input type="hidden" name="food_cart" value="{{ json_encode($foodItems->values()->all()) }}">

                            <input type="hidden" name="clear_cart_key" value="staff_food_cart_v2_{{ $staffId }}_{{ $suatChieu->id }}">

                            <input type="hidden" id="submitVoucherCode" name="voucher_code" value="">

                            {{-- PHƯƠNG THỨC THANH TOÁN --}}
                            <div class="space-y-3">

                                {{-- TIỀN MẶT --}}
                                <label class="payment-method-label flex cursor-pointer items-center justify-between rounded-2xl border border-yellow-400 bg-yellow-400/10 p-4 transition duration-200">

                                    <div class="flex items-center gap-3">

                                        <input type="radio" checked name="payment_method" value="cash" class="h-5 w-5 accent-yellow-400">

                                        <div class="ml-1">

                                            <span class="payment-method-title block font-bold text-gray-100">
                                                Thanh toán bằng tiền mặt
                                            </span>

                                            <span class="mt-0.5 block text-xs text-gray-400">
                                                Nhập số tiền khách đưa và tính tiền thừa
                                            </span>

                                        </div>

                                    </div>

                                    <div class="flex h-10 w-16 items-center justify-center rounded-lg bg-white text-2xl shadow-sm">
                                        💵
                                    </div>

                                </label>

                                {{-- VIETQR --}}
                                <label class="payment-method-label flex cursor-pointer items-center justify-between rounded-2xl border border-white/10 bg-zinc-900/30 p-4 transition duration-200 hover:border-white/20">

                                    <div class="flex items-center gap-3">

                                        <input type="radio" name="payment_method" value="vietqr" class="h-5 w-5 accent-yellow-400">

                                        <div class="ml-1">

                                            <span class="payment-method-title block font-bold text-gray-200">
                                                Chuyển khoản nhanh VietQR
                                            </span>

                                            <span class="mt-0.5 block text-xs text-gray-400">
                                                Tạo mã QR đúng số tiền để khách chuyển khoản
                                            </span>

                                        </div>

                                    </div>

                                    <div class="flex items-center justify-center rounded-lg bg-white px-2 py-1 shadow-sm">

                                        <img src="{{ asset('assets/images/logo-vietqr.png') }}" class="h-6 w-16 object-contain" alt="VietQR Logo">

                                    </div>

                                </label>

                            </div>

                            {{-- ================= TIỀN MẶT ================= --}}
                            <div id="cashPaymentSection" class="mt-5 rounded-2xl border border-white/10 bg-black/20 p-5">
                                <label for="receivedAmount" class="mb-2 block text-sm font-bold text-gray-200">
                                    Số tiền khách đưa
                                </label>

                                <div class="relative">

                                    <input id="receivedAmount" type="number" name="received_amount" min="0" step="1000" value="{{ old('received_amount') }}" placeholder="Nhập số tiền khách đưa" autocomplete="off" class="w-full rounded-2xl border border-white/10 bg-[#1d1d1d] px-4 py-3 pr-12 text-white outline-none transition focus:border-yellow-400">

                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400">
                                        đ
                                    </span>

                                </div>

                                {{-- NÚT NHẬP NHANH --}}
                                <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">

                                    <button type="button" data-cash-value="{{ $total }}" class="cash-exact-button rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm transition hover:border-yellow-400/50 hover:bg-yellow-400/10">
                                        Đúng tiền
                                    </button>

                                    <button type="button" data-cash-round="100000" class="cash-round-button rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm transition hover:border-yellow-400/50 hover:bg-yellow-400/10">
                                        Làm Tròn 100.000đ
                                    </button>

                                    <button type="button" data-cash-round="200000" class="cash-round-button rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm transition hover:border-yellow-400/50 hover:bg-yellow-400/10">
                                        Làm Tròn 200.000đ
                                    </button>

                                    <button type="button" data-cash-round="500000" class="cash-round-button rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm transition hover:border-yellow-400/50 hover:bg-yellow-400/10">
                                        Làm Tròn 500.000đ
                                    </button>

                                </div>

                                {{-- TIỀN THỪA --}}
                                <div class="mt-5 flex items-center justify-between rounded-xl border border-white/10 bg-zinc-900 p-4">

                                    <div>

                                        <p class="text-xs uppercase tracking-[0.25em] text-gray-500">
                                            Tiền thừa
                                        </p>

                                        <p id="cashMessage" class="mt-1 text-xs text-gray-500">
                                            Nhập số tiền khách đưa
                                        </p>

                                    </div>

                                    <strong id="changeAmount" class="text-xl text-gray-400">
                                        0đ
                                    </strong>

                                </div>

                            </div>

                            {{-- ================= VIETQR ================= --}}
                            <div id="vietQrPaymentSection" class="mt-5 hidden rounded-2xl border border-white/10 bg-black/20 p-5">
                                <div class="flex items-start gap-4">

                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-yellow-400/10 text-2xl">
                                        📱
                                    </div>

                                    <div>

                                        <p class="font-bold text-yellow-400">
                                            Thanh toán VietQR
                                        </p>

                                        <p class="mt-1 text-sm leading-6 text-gray-400">
                                            Sau khi xác nhận, hệ thống tạo giao dịch VietQR và khóa các ghế đã chọn trong 7 phút để chờ khách thanh toán.
                                        </p>

                                    </div>

                                </div>

                                <div class="mt-4 flex justify-between rounded-xl border border-white/10 bg-zinc-900 p-4">

                                    <span class="text-gray-400">
                                        Số tiền chuyển khoản
                                    </span>

                                    <strong id="vietQrAmount" class="text-yellow-400">
                                        {{ number_format($total, 0, ',', '.') }}đ
                                    </strong>

                                </div>

                                <p class="mt-3 text-xs text-orange-400">
                                    Vé chỉ được phát hành sau khi PayOS xác nhận thanh toán thành công. Nếu hủy hoặc quá hạn, ghế sẽ được giải phóng.
                                </p>

                            </div>

                            {{-- ================= SUBMIT ================= --}}
                            <button id="submitPaymentButton" type="submit" disabled class="mt-6 w-full cursor-not-allowed rounded-2xl bg-gray-700 py-4 font-black text-gray-400 transition duration-200">
                                <span id="submitPaymentText">
                                    NHẬP SỐ TIỀN KHÁCH ĐƯA
                                </span>
                            </button>

                        </form>

                        {{-- ================= QUAY LẠI ================= --}}
                        <a href="{{ $previousUrl }}" class="mt-3 flex w-full items-center justify-center rounded-2xl border border-white/20 bg-white/5 py-4 font-semibold text-gray-300 transition hover:border-white/40 hover:bg-white/10 hover:text-white">
                            ← Quay lại
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>
</div>

<script>
    (function() {
        let totalAmount = @json((int) $total);
        const baseTotalAmount = totalAmount;
        let appliedVoucher = null;
        let voucherRequestRunning = false;

        const paymentForm = document.getElementById('paymentForm');
        const paymentRadios = document.querySelectorAll(
            'input[name="payment_method"]'
        );
        const paymentLabels = document.querySelectorAll(
            '.payment-method-label'
        );

        const cashSection = document.getElementById(
            'cashPaymentSection'
        );
        const vietQrSection = document.getElementById(
            'vietQrPaymentSection'
        );

        const receivedAmountInput = document.getElementById(
            'receivedAmount'
        );
        const changeAmountElement = document.getElementById(
            'changeAmount'
        );
        const cashMessageElement = document.getElementById(
            'cashMessage'
        );

        const submitButton = document.getElementById(
            'submitPaymentButton'
        );
        const submitText = document.getElementById(
            'submitPaymentText'
        );

        let submitting = false;

        if (
            !paymentForm ||
            !cashSection ||
            !vietQrSection ||
            !receivedAmountInput ||
            !changeAmountElement ||
            !cashMessageElement ||
            !submitButton ||
            !submitText
        ) {
            console.error(
                'Staff checkout: thiếu phần tử HTML cần thiết.'
            );
            return;
        }

        function formatMoney(value) {
            const number = Number(value);

            if (!Number.isFinite(number) || number < 0) {
                return '0đ';
            }

            return new Intl.NumberFormat('vi-VN').format(number) + 'đ';
        }

        function getPaymentMethod() {
            const checkedRadio = document.querySelector(
                'input[name="payment_method"]:checked'
            );

            return checkedRadio ? checkedRadio.value : 'cash';
        }

        function setSubmitEnabled(enabled, text) {
            submitText.textContent = text;
            submitButton.disabled = !enabled;

            if (enabled) {
                submitButton.classList.remove(
                    'bg-gray-700'
                    , 'text-gray-400'
                    , 'cursor-not-allowed'
                );

                submitButton.classList.add(
                    'bg-yellow-400'
                    , 'text-black'
                    , 'hover:bg-yellow-300'
                    , 'cursor-pointer'
                    , 'shadow-lg'
                    , 'shadow-yellow-400/10'
                );
            } else {
                submitButton.classList.add(
                    'bg-gray-700'
                    , 'text-gray-400'
                    , 'cursor-not-allowed'
                );

                submitButton.classList.remove(
                    'bg-yellow-400'
                    , 'text-black'
                    , 'hover:bg-yellow-300'
                    , 'cursor-pointer'
                    , 'shadow-lg'
                    , 'shadow-yellow-400/10'
                );
            }
        }

        function formatVoucherMoney(value) {
            const number = Number(value);
            if (!Number.isFinite(number) || number < 0) return '0đ';
            return new Intl.NumberFormat('vi-VN').format(Math.round(number)) + 'đ';
        }

        function showVoucherResult(message, isError = false) {
            const result = document.getElementById('voucherResult');
            const text = document.getElementById('voucherResultText');

            if (!result || !text) return;

            result.classList.remove(
                'hidden',
                'flex',
                'border-yellow-400/30',
                'bg-yellow-400/10',
                'text-yellow-300',
                'border-red-400/30',
                'bg-red-400/10',
                'text-red-300'
            );

            result.classList.add(
                'flex',
                isError ? 'border-red-400/30' : 'border-yellow-400/30',
                isError ? 'bg-red-400/10' : 'bg-yellow-400/10',
                isError ? 'text-red-300' : 'text-yellow-300'
            );

            text.textContent = message;
        }

        function updateStaffVoucherTotal() {
            const totalEl = document.getElementById('grandTotal');
            const discountRow = document.getElementById('discountRow');
            const discountAmount = document.getElementById('discountAmount');

            if (totalEl) {
                totalEl.textContent = formatVoucherMoney(totalAmount);
            }

            const vietQrAmount = document.getElementById('vietQrAmount');
            if (vietQrAmount) {
                vietQrAmount.textContent = formatVoucherMoney(totalAmount);
            }

            if (appliedVoucher && discountRow && discountAmount) {
                discountAmount.textContent = '-' + formatVoucherMoney(appliedVoucher.discount);
                discountRow.classList.remove('hidden');
                discountRow.classList.add('flex');
            }
        }

        function resetStaffVoucher(message = '') {
            appliedVoucher = null;
            totalAmount = baseTotalAmount;

            const codeInput = document.getElementById('voucherCode');
            const hiddenInput = document.getElementById('submitVoucherCode');
            const result = document.getElementById('voucherResult');
            const discountRow = document.getElementById('discountRow');

            if (codeInput) codeInput.value = '';
            if (hiddenInput) hiddenInput.value = '';

            if (result) {
                result.classList.add('hidden');
                result.classList.remove('flex');
            }

            if (discountRow) {
                discountRow.classList.add('hidden');
                discountRow.classList.remove('flex');
            }

            updateStaffVoucherTotal();

            if (message) {
                showVoucherResult(message, true);
            }

            updateCashCalculation();
        }

        async function applyStaffVoucher() {
            if (voucherRequestRunning) return;

            const codeInput = document.getElementById('voucherCode');
            const applyButton = document.getElementById('applyVoucherButton');
            const hiddenInput = document.getElementById('submitVoucherCode');

            const code = (codeInput?.value || '').trim().toUpperCase();

            if (!code) {
                showVoucherResult('Vui lòng nhập mã voucher.', true);
                return;
            }

            voucherRequestRunning = true;

            if (applyButton) {
                applyButton.disabled = true;
                applyButton.textContent = 'Đang kiểm tra...';
            }

            try {
                // Không gọi route riêng để tránh phụ thuộc endpoint chưa được khai báo.
                // Backend BanVeController::store vẫn xác thực và tính lại voucher khi thanh toán.
                const voucherList = @json($staffVoucherData);

                const voucher = voucherList.find(function(item) {
                    return String(item.code || '').trim().toUpperCase() === code;
                });

                if (!voucher) {
                    resetStaffVoucher('Voucher không tồn tại, đã hết hạn hoặc không dành cho bán vé tại quầy.');
                    return;
                }

                let discount = 0;
                const voucherValue = Number(voucher.value || 0);

                if (voucher.type === 'phan_tram') {
                    const percent = Math.min(Math.max(voucherValue, 0), 100);
                    discount = baseTotalAmount * (percent / 100);
                } else {
                    discount = voucherValue;
                }

                discount = Math.min(Math.max(discount, 0), baseTotalAmount);

                appliedVoucher = {
                    code: voucher.code,
                    discount: Math.round(discount),
                    finalTotal: Math.max(0, Math.round(baseTotalAmount - discount)),
                };

                totalAmount = Math.max(0, appliedVoucher.finalTotal);

                if (codeInput) codeInput.value = appliedVoucher.code;
                if (hiddenInput) hiddenInput.value = appliedVoucher.code;

                const result = document.getElementById('voucherResult');
                const text = document.getElementById('voucherResultText');

                if (result && text) {
                    result.classList.remove(
                        'hidden',
                        'border-red-400/30',
                        'bg-red-400/10',
                        'text-red-300'
                    );
                    result.classList.add(
                        'flex',
                        'border-yellow-400/30',
                        'bg-yellow-400/10',
                        'text-yellow-300'
                    );
                    text.innerHTML =
                        `✔ Đã áp dụng <b>${appliedVoucher.code}</b> (-${formatVoucherMoney(appliedVoucher.discount)})`;
                }

                updateStaffVoucherTotal();
                updateCashCalculation();
            } catch (error) {
                console.error('Staff checkout voucher:', error);
                resetStaffVoucher('Không thể kết nối máy chủ để kiểm tra voucher.');
            } finally {
                voucherRequestRunning = false;

                if (applyButton) {
                    applyButton.disabled = false;
                    applyButton.textContent = 'Áp dụng';
                }
            }
        }

        function updateCashCalculation() {
            const receivedAmount = Number(
                receivedAmountInput.value || 0
            );

            if (receivedAmount <= 0) {
                changeAmountElement.textContent = '0đ';

                changeAmountElement.classList.remove(
                    'text-green-400'
                    , 'text-red-400'
                );
                changeAmountElement.classList.add(
                    'text-gray-400'
                );

                cashMessageElement.textContent =
                    'Nhập số tiền khách đưa';

                cashMessageElement.classList.remove(
                    'text-green-400'
                    , 'text-red-400'
                );
                cashMessageElement.classList.add(
                    'text-gray-500'
                );

                setSubmitEnabled(
                    false
                    , 'NHẬP SỐ TIỀN KHÁCH ĐƯA'
                );

                return;
            }

            if (receivedAmount < totalAmount) {
                const missingAmount = totalAmount - receivedAmount;

                changeAmountElement.textContent = '0đ';

                changeAmountElement.classList.remove(
                    'text-green-400'
                    , 'text-gray-400'
                );
                changeAmountElement.classList.add(
                    'text-red-400'
                );

                cashMessageElement.textContent =
                    'Khách còn thiếu ' + formatMoney(missingAmount);

                cashMessageElement.classList.remove(
                    'text-green-400'
                    , 'text-gray-500'
                );
                cashMessageElement.classList.add(
                    'text-red-400'
                );

                setSubmitEnabled(
                    false
                    , 'SỐ TIỀN KHÁCH ĐƯA CHƯA ĐỦ'
                );

                return;
            }

            const changeAmount = receivedAmount - totalAmount;

            changeAmountElement.textContent =
                formatMoney(changeAmount);

            changeAmountElement.classList.remove(
                'text-red-400'
                , 'text-gray-400'
            );
            changeAmountElement.classList.add(
                'text-green-400'
            );

            cashMessageElement.textContent =
                changeAmount > 0 ?
                'Số tiền cần trả lại khách' :
                'Khách đã đưa đúng số tiền';

            cashMessageElement.classList.remove(
                'text-red-400'
                , 'text-gray-500'
            );
            cashMessageElement.classList.add(
                'text-green-400'
            );

            setSubmitEnabled(
                true
                , 'XÁC NHẬN THANH TOÁN VÀ IN VÉ'
            );
        }

        function updatePaymentLabels() {
            paymentLabels.forEach(function(label) {
                const radio = label.querySelector(
                    'input[type="radio"]'
                );
                const title = label.querySelector(
                    '.payment-method-title'
                );

                if (!radio) {
                    return;
                }

                if (radio.checked) {
                    label.classList.add(
                        'border-yellow-400'
                        , 'bg-yellow-400/10'
                    );
                    label.classList.remove(
                        'border-white/10'
                        , 'bg-zinc-900/30'
                    );

                    if (title) {
                        title.classList.add('text-gray-100');
                        title.classList.remove('text-gray-200');
                    }
                } else {
                    label.classList.remove(
                        'border-yellow-400'
                        , 'bg-yellow-400/10'
                    );
                    label.classList.add(
                        'border-white/10'
                        , 'bg-zinc-900/30'
                    );

                    if (title) {
                        title.classList.remove('text-gray-100');
                        title.classList.add('text-gray-200');
                    }
                }
            });
        }

        function updatePaymentMethod() {
            const paymentMethod = getPaymentMethod();

            updatePaymentLabels();

            if (paymentMethod === 'cash') {
                cashSection.classList.remove('hidden');
                vietQrSection.classList.add('hidden');

                receivedAmountInput.required = true;
                updateCashCalculation();
            } else {
                cashSection.classList.add('hidden');
                vietQrSection.classList.remove('hidden');

                receivedAmountInput.required = false;
                receivedAmountInput.value = '';

                setSubmitEnabled(
                    true
                    , 'TẠO MÃ THANH TOÁN VIETQR'
                );
            }
        }

        paymentRadios.forEach(function(radio) {
            radio.addEventListener(
                'change'
                , updatePaymentMethod
            );
        });

        receivedAmountInput.addEventListener(
            'input'
            , updateCashCalculation
        );

        document
            .querySelectorAll('.cash-exact-button')
            .forEach(function(button) {
                button.addEventListener('click', function() {
                    receivedAmountInput.value = totalAmount;
                    updateCashCalculation();
                });
            });

        document
            .querySelectorAll('.cash-round-button')
            .forEach(function(button) {
                button.addEventListener('click', function() {
                    const unit = Number(
                        this.dataset.cashRound || 0
                    );

                    if (unit <= 0) {
                        return;
                    }

                    receivedAmountInput.value =
                        Math.ceil(totalAmount / unit) * unit;

                    updateCashCalculation();
                });
            });

        document.querySelectorAll('.use-staff-voucher').forEach(function(button) {
            button.addEventListener('click', function() {
                const code = (this.dataset.voucherCode || '').trim().toUpperCase();
                const input = document.getElementById('voucherCode');

                if (input) {
                    input.value = code;
                }

                applyStaffVoucher();
            });
        });

        const voucherCodeInput = document.getElementById('voucherCode');
        const applyVoucherButton = document.getElementById('applyVoucherButton');
        const resetVoucherButton = document.getElementById('resetVoucherButton');

        if (applyVoucherButton) {
            applyVoucherButton.addEventListener('click', applyStaffVoucher);
        }

        if (resetVoucherButton) {
            resetVoucherButton.addEventListener('click', function() {
                resetStaffVoucher();
            });
        }

        if (voucherCodeInput) {
            voucherCodeInput.addEventListener('input', function() {
                const typedCode = this.value.trim().toUpperCase();

                if (appliedVoucher && typedCode !== appliedVoucher.code) {
                    resetStaffVoucher();
                }
            });

            voucherCodeInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    applyStaffVoucher();
                }
            });
        }

        paymentForm.addEventListener('submit', function(event) {
            if (submitting) {
                event.preventDefault();
                return;
            }

            const paymentMethod = getPaymentMethod();

            const typedVoucherCode = (
                document.getElementById('voucherCode')?.value || ''
            ).trim().toUpperCase();

            if (
                typedVoucherCode &&
                (!appliedVoucher || appliedVoucher.code !== typedVoucherCode)
            ) {
                event.preventDefault();
                showVoucherResult(
                    'Bạn phải bấm “Áp dụng” và xác thực voucher trước khi thanh toán.',
                    true
                );
                document.getElementById('voucherCode')?.focus();
                return;
            }

            if (paymentMethod === 'cash') {
                const receivedAmount = Number(
                    receivedAmountInput.value || 0
                );

                if (receivedAmount < totalAmount) {
                    event.preventDefault();

                    alert(
                        'Số tiền khách đưa chưa đủ để thanh toán.'
                    );

                    receivedAmountInput.focus();
                    return;
                }
            }

            submitting = true;
            submitButton.disabled = true;

            submitButton.classList.add(
                'opacity-60'
                , 'cursor-not-allowed'
            );

            submitText.textContent =
                paymentMethod === 'cash' ?
                'ĐANG XỬ LÝ THANH TOÁN...' :
                'ĐANG KHỞI TẠO VIETQR...';
        });

        updatePaymentMethod();
    })();

</script>

@endsection