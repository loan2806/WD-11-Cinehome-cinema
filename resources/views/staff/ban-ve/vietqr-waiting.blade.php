@extends('layouts.admin')

@section('title', 'Chờ thanh toán VietQR')
@section('page-title', 'Thanh toán VietQR tại quầy')
@section('page-subtitle', 'Ghế đang được giữ trong 7 phút')

@php
    $expiresAtIso = optional($ve->thoi_gian_het_han)?->toIso8601String();
    $foods = collect($ve->food_items ?? []);
@endphp

@section('content')
<div class="min-h-screen bg-[#080808] py-8 text-white">
    <div class="mx-auto max-w-6xl px-4">
        <div class="grid gap-6 lg:grid-cols-[1fr_430px]">

            <section class="rounded-3xl border border-white/10 bg-[#141414] p-7 shadow-2xl">
                <p class="text-xs font-black uppercase tracking-[0.35em] text-yellow-400">
                    Giao dịch tại quầy
                </p>
                <h1 class="mt-2 text-3xl font-black">Đang chờ khách thanh toán</h1>
                <p class="mt-2 text-gray-400">
                    Ghế <strong class="text-white">{{ str_replace(',', ', ', $ve->ma_ghe) }}</strong>
                    đang được khóa cho giao dịch này. Không phát hành vé trước khi PayOS xác nhận đã thanh toán.
                </p>

                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    @foreach ([
                        ['Mã giao dịch', $ve->ma_ve],
                        ['Phim', $ve->ten_phim],
                        ['Rạp', $ve->ten_rap],
                        ['Phòng', $ve->ten_phong],
                        ['Ghế', str_replace(',', ', ', $ve->ma_ghe)],
                        ['Suất chiếu', optional($ve->thoi_gian_chieu)->format('d/m/Y H:i')],
                    ] as [$label, $value])
                        <div class="rounded-2xl border border-white/10 bg-black/25 p-4">
                            <span class="text-xs uppercase tracking-wider text-gray-500">{{ $label }}</span>
                            <strong class="mt-1 block">{{ $value ?: '---' }}</strong>
                        </div>
                    @endforeach
                </div>

                @if ($foods->isNotEmpty())
                    <div class="mt-6 rounded-2xl border border-white/10 bg-black/20 p-5">
                        <h2 class="font-black text-yellow-400">Đồ ăn & combo</h2>
                        <div class="mt-3 space-y-2">
                            @foreach ($foods as $food)
                                @php
                                    $qty = (int) ($food['qty'] ?? $food['quantity'] ?? 1);
                                    $price = (float) ($food['price'] ?? 0);
                                @endphp
                                <div class="flex justify-between gap-4 text-sm">
                                    <span class="text-gray-300">{{ $food['name'] ?? 'Sản phẩm' }} × {{ $qty }}</span>
                                    <strong>{{ number_format($price * $qty, 0, ',', '.') }}đ</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-6 rounded-2xl border border-yellow-400/25 bg-yellow-400/10 p-5">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-widest text-yellow-300">Thời gian giữ ghế còn lại</p>
                            <p id="statusText" class="mt-1 text-sm text-gray-300">Đang kiểm tra trạng thái PayOS...</p>
                        </div>
                        <strong id="countdown" class="font-mono text-3xl text-yellow-400">07:00</strong>
                    </div>
                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-black/40">
                        <div id="countdownBar" class="h-full w-full bg-yellow-400 transition-all duration-1000"></div>
                    </div>
                </div>
            </section>

            <aside class="rounded-3xl border border-yellow-400/25 bg-[#141414] p-7 text-center shadow-2xl">
                <p class="text-xs font-black uppercase tracking-[0.35em] text-yellow-400">VietQR</p>
                <h2 class="mt-2 text-2xl font-black">Quét mã để thanh toán</h2>

                <div class="mx-auto mt-6 flex w-fit items-center justify-center rounded-3xl bg-white p-5">
                    {!! $qrSvg !!}
                </div>

                <p class="mt-5 text-sm text-gray-400">Số tiền cần thanh toán</p>
                <strong class="mt-1 block text-4xl text-yellow-400">
                    {{ number_format((float) $ve->tong_tien, 0, ',', '.') }}đ
                </strong>

                <div class="mt-6 flex items-center justify-center gap-2 rounded-xl border border-amber-500/20 bg-amber-500/10 p-3 text-sm text-amber-300">
                    <span class="inline-block h-2.5 w-2.5 animate-pulse rounded-full bg-amber-400"></span>
                    <span id="paymentState">CHỜ THANH TOÁN</span>
                </div>

                @if ($ve->payos_checkout_url)
                    <a href="{{ $ve->payos_checkout_url }}" target="_blank" rel="noopener"
                       class="mt-4 flex w-full items-center justify-center rounded-2xl border border-white/15 bg-white/5 px-4 py-3 font-bold text-white no-underline transition hover:bg-white/10">
                        Mở trang thanh toán PayOS
                    </a>
                @endif

                <form id="cancelForm" method="POST" action="{{ route('staff.ban-ve.vietqr-cancel', ['id' => $ve->id]) }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full rounded-2xl border border-red-500/30 bg-red-500/10 px-4 py-3 font-black text-red-300 transition hover:bg-red-500/20">
                        Hủy giao dịch & giải phóng ghế
                    </button>
                </form>

                <a href="{{ route('staff.lich-su-ve.index', ['trang_thai' => 'cho_thanh_toan']) }}"
                   class="mt-3 flex w-full items-center justify-center rounded-2xl border border-white/10 px-4 py-3 font-semibold text-gray-300 no-underline hover:bg-white/5">
                    Xem giao dịch chờ thanh toán
                </a>
            </aside>
        </div>
    </div>
</div>

<script>
(() => {
    const expiresAt = new Date(@json($expiresAtIso)).getTime();
    const createdAt = new Date(@json(optional($ve->created_at)?->toIso8601String())).getTime();
    const totalDuration = Math.max(expiresAt - createdAt, 1);

    const countdown = document.getElementById('countdown');
    const countdownBar = document.getElementById('countdownBar');
    const statusText = document.getElementById('statusText');
    const paymentState = document.getElementById('paymentState');
    const cancelForm = document.getElementById('cancelForm');

    let finished = false;
    let checking = false;

    function renderCountdown() {
        if (finished) return;

        const remaining = Math.max(expiresAt - Date.now(), 0);
        const seconds = Math.ceil(remaining / 1000);
        const mins = String(Math.floor(seconds / 60)).padStart(2, '0');
        const secs = String(seconds % 60).padStart(2, '0');

        countdown.textContent = `${mins}:${secs}`;
        countdownBar.style.width = `${Math.max(0, Math.min(100, remaining / totalDuration * 100))}%`;

        if (remaining <= 0) {
            statusText.textContent = 'Đã hết thời gian giữ ghế. Đang giải phóng ghế...';
            checkStatus();
        }
    }

    async function checkStatus() {
        if (finished || checking) return;
        checking = true;

        try {
            const response = await fetch(
                @json(route('staff.ban-ve.vietqr-status', ['id' => $ve->id])),
                {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    cache: 'no-store'
                }
            );

            const data = await response.json();
            const status = String(data.status || 'PENDING').toUpperCase();

            if (status === 'PAID') {
                finished = true;
                paymentState.textContent = 'ĐÃ THANH TOÁN';
                statusText.textContent = 'PayOS đã xác nhận thanh toán. Đang phát hành vé...';
                window.location.href = data.redirect_url;
                return;
            }

            if (status === 'CANCELLED' || status === 'EXPIRED') {
                finished = true;
                paymentState.textContent = status === 'EXPIRED' ? 'HẾT HẠN' : 'ĐÃ HỦY';
                statusText.textContent = status === 'EXPIRED'
                    ? 'Giao dịch hết hạn. Ghế đã được giải phóng.'
                    : 'Giao dịch đã hủy. Ghế đã được giải phóng.';

                setTimeout(() => {
                    window.location.href = data.redirect_url;
                }, 1000);
                return;
            }

            paymentState.textContent = status === 'PROCESSING' ? 'ĐANG XỬ LÝ' : 'CHỜ THANH TOÁN';
            statusText.textContent = data.message || 'Ghế đang được giữ. Hệ thống tự kiểm tra PayOS mỗi 3 giây.';
        } catch (error) {
            statusText.textContent = 'Mất kết nối kiểm tra PayOS. Ghế vẫn được giữ đến hết thời gian.';
        } finally {
            checking = false;
        }
    }

    cancelForm.addEventListener('submit', (event) => {
        if (!confirm('Hủy giao dịch này và giải phóng các ghế đang giữ?')) {
            event.preventDefault();
        }
    });

    renderCountdown();
    checkStatus();
    setInterval(renderCountdown, 1000);
    setInterval(checkStatus, 3000);
})();
</script>
@endsection