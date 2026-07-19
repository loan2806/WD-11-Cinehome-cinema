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
@endphp

@section('content')

<div class="booking-checkout-page min-h-screen bg-[#080808] pt-24 pb-12 text-white" lang="vi" spellcheck="false">
    <div class="booking-checkout-container mx-auto max-w-7xl px-4 lg:px-6">

        {{-- ================= HEADER ================= --}}
        <div class="mb-8">
            <p class="text-xs uppercase tracking-[0.45em] text-yellow-400">
                Staff Checkout
            </p>

            <h1 class="mt-2 text-4xl font-black">
                THANH TOÁN TẠI QUẦY
            </h1>

            <p class="mt-2 text-gray-400">
                Kiểm tra thông tin trước khi xác nhận bán vé.
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
        <div class="overflow-hidden rounded-3xl border border-white/10 bg-[#141414] shadow-2xl">


            {{-- ================= MAIN LAYOUT ================= --}}
            <div class="mt-6 grid lg:grid-cols-[380px_1fr]">

                {{-- ================= LEFT SIDE ================= --}}
                <div class="border-white/10 p-8 lg:border-r">

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
                <div class="p-8">

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
                                $foodImagePath = '';

                                if (!empty($item['image'])) {
                                $foodImagePath = trim((string) $item['image']);

                                if (
                                $foodImagePath !== '' &&
                                strpos($foodImagePath, 'foods/') !== 0
                                ) {
                                $foodImagePath = 'foods/' . $foodImagePath;
                                }
                                }

                                $foodName = $item['name'] ?? 'Sản phẩm';
                                $foodQuantity = (int) ($item['qty'] ?? 0);
                                $foodPrice = (int) ($item['price'] ?? 0);
                                $foodLineTotal = $foodPrice * $foodQuantity;
                                @endphp

                                <div class="flex items-center justify-between gap-4">

                                    <div class="flex min-w-0 items-center gap-3">

                                        @if ($foodImagePath !== '')
                                        <img src="{{ asset('storage/' . $foodImagePath) }}" alt="{{ $foodName }}" class="h-16 w-16 rounded-lg bg-white/5 object-contain">
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

                        <div class="mt-5 flex justify-between border-t border-white/10 pt-5 text-2xl font-black text-yellow-400">

                            <span>
                                Tổng
                            </span>

                            <span id="grandTotal">
                                {{ number_format($total, 0, ',', '.') }}đ
                            </span>

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

                            <input type="hidden" name="clear_cart_key" value="staff_food_cart_{{ $staffId }}_{{ $suatChieu->id }}">

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
                                        Bội 100.000đ
                                    </button>

                                    <button type="button" data-cash-round="200000" class="cash-round-button rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm transition hover:border-yellow-400/50 hover:bg-yellow-400/10">
                                        Bội 200.000đ
                                    </button>

                                    <button type="button" data-cash-round="500000" class="cash-round-button rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm transition hover:border-yellow-400/50 hover:bg-yellow-400/10">
                                        Bội 500.000đ
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
                                            Sau khi xác nhận, hệ thống sẽ tạo giao dịch và hiển thị mã QR để khách quét thanh toán.
                                        </p>

                                    </div>

                                </div>

                                <div class="mt-4 flex justify-between rounded-xl border border-white/10 bg-zinc-900 p-4">

                                    <span class="text-gray-400">
                                        Số tiền chuyển khoản
                                    </span>

                                    <strong class="text-yellow-400">
                                        {{ number_format($total, 0, ',', '.') }}đ
                                    </strong>

                                </div>

                                <p class="mt-3 text-xs text-orange-400">
                                    Vé chỉ được phát hành sau khi giao dịch được xác nhận đã thanh toán.
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
(function () {
    const totalAmount = @json((int) $total);

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
                'bg-gray-700',
                'text-gray-400',
                'cursor-not-allowed'
            );

            submitButton.classList.add(
                'bg-yellow-400',
                'text-black',
                'hover:bg-yellow-300',
                'cursor-pointer',
                'shadow-lg',
                'shadow-yellow-400/10'
            );
        } else {
            submitButton.classList.add(
                'bg-gray-700',
                'text-gray-400',
                'cursor-not-allowed'
            );

            submitButton.classList.remove(
                'bg-yellow-400',
                'text-black',
                'hover:bg-yellow-300',
                'cursor-pointer',
                'shadow-lg',
                'shadow-yellow-400/10'
            );
        }
    }

    function updateCashCalculation() {
        const receivedAmount = Number(
            receivedAmountInput.value || 0
        );

        if (receivedAmount <= 0) {
            changeAmountElement.textContent = '0đ';

            changeAmountElement.classList.remove(
                'text-green-400',
                'text-red-400'
            );
            changeAmountElement.classList.add(
                'text-gray-400'
            );

            cashMessageElement.textContent =
                'Nhập số tiền khách đưa';

            cashMessageElement.classList.remove(
                'text-green-400',
                'text-red-400'
            );
            cashMessageElement.classList.add(
                'text-gray-500'
            );

            setSubmitEnabled(
                false,
                'NHẬP SỐ TIỀN KHÁCH ĐƯA'
            );

            return;
        }

        if (receivedAmount < totalAmount) {
            const missingAmount = totalAmount - receivedAmount;

            changeAmountElement.textContent = '0đ';

            changeAmountElement.classList.remove(
                'text-green-400',
                'text-gray-400'
            );
            changeAmountElement.classList.add(
                'text-red-400'
            );

            cashMessageElement.textContent =
                'Khách còn thiếu ' + formatMoney(missingAmount);

            cashMessageElement.classList.remove(
                'text-green-400',
                'text-gray-500'
            );
            cashMessageElement.classList.add(
                'text-red-400'
            );

            setSubmitEnabled(
                false,
                'SỐ TIỀN KHÁCH ĐƯA CHƯA ĐỦ'
            );

            return;
        }

        const changeAmount = receivedAmount - totalAmount;

        changeAmountElement.textContent =
            formatMoney(changeAmount);

        changeAmountElement.classList.remove(
            'text-red-400',
            'text-gray-400'
        );
        changeAmountElement.classList.add(
            'text-green-400'
        );

        cashMessageElement.textContent =
            changeAmount > 0
                ? 'Số tiền cần trả lại khách'
                : 'Khách đã đưa đúng số tiền';

        cashMessageElement.classList.remove(
            'text-red-400',
            'text-gray-500'
        );
        cashMessageElement.classList.add(
            'text-green-400'
        );

        setSubmitEnabled(
            true,
            'XÁC NHẬN THANH TOÁN VÀ IN VÉ'
        );
    }

    function updatePaymentLabels() {
        paymentLabels.forEach(function (label) {
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
                    'border-yellow-400',
                    'bg-yellow-400/10'
                );
                label.classList.remove(
                    'border-white/10',
                    'bg-zinc-900/30'
                );

                if (title) {
                    title.classList.add('text-gray-100');
                    title.classList.remove('text-gray-200');
                }
            } else {
                label.classList.remove(
                    'border-yellow-400',
                    'bg-yellow-400/10'
                );
                label.classList.add(
                    'border-white/10',
                    'bg-zinc-900/30'
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
                true,
                'TẠO MÃ THANH TOÁN VIETQR'
            );
        }
    }

    paymentRadios.forEach(function (radio) {
        radio.addEventListener(
            'change',
            updatePaymentMethod
        );
    });

    receivedAmountInput.addEventListener(
        'input',
        updateCashCalculation
    );

    document
        .querySelectorAll('.cash-exact-button')
        .forEach(function (button) {
            button.addEventListener('click', function () {
                receivedAmountInput.value = totalAmount;
                updateCashCalculation();
            });
        });

    document
        .querySelectorAll('.cash-round-button')
        .forEach(function (button) {
            button.addEventListener('click', function () {
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

    paymentForm.addEventListener('submit', function (event) {
        if (submitting) {
            event.preventDefault();
            return;
        }

        const paymentMethod = getPaymentMethod();

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
            'opacity-60',
            'cursor-not-allowed'
        );

        submitText.textContent =
            paymentMethod === 'cash'
                ? 'ĐANG XỬ LÝ THANH TOÁN...'
                : 'ĐANG KHỞI TẠO VIETQR...';
    });

    updatePaymentMethod();
})();
</script>

@endsection