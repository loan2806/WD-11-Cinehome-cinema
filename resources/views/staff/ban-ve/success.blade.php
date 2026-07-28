@extends('layouts.admin')

@section('title', 'Thanh toán thành công')
@section('page-title', 'Kết quả bán vé')
@section('page-subtitle', 'Vé đã được phát hành và sẵn sàng để in')

@php
    $foods = collect(
        $ve->food_items
        ?? $ve->foods_list
        ?? []
    );

    $foodTotal = (float) ($ve->food_total ?? 0);

    if ($foodTotal <= 0) {
        $foodTotal = $foods->sum(function ($food) {
            $price = (float) (
                $food['price']
                ?? $food['don_gia']
                ?? 0
            );

            $quantity = (int) (
                $food['qty']
                ?? $food['quantity']
                ?? $food['so_luong']
                ?? 1
            );

            return $price * $quantity;
        });
    }

    $seatTotal = (float) ($ve->seat_total ?? 0);

    if ($seatTotal <= 0) {
        $seatTotal = max(
            (float) $ve->tong_tien - $foodTotal,
            0
        );
    }

    $staffName = $ve->nhanVien->ho_ten
        ?? $ve->nhanVien->ten
        ?? 'Nhân viên';

    $staffId = (int) (
        $ve->nhan_vien_id
        ?? auth()->id()
        ?? 0
    );

    $showtimeId = (int) ($ve->suat_chieu_id ?? 0);

    $seatCount = $ve->gheVes?->count() ?? 0;

    if ($seatCount <= 0) {
        $seatCount = count(
            array_filter(
                array_map(
                    'trim',
                    explode(',', $ve->ma_ghe ?? '')
                )
            )
        );
    }
@endphp

@section('content')

<div class="min-h-screen bg-[#080808] py-8 text-white">

    <div class="mx-auto max-w-6xl">

        {{-- THÔNG BÁO THÀNH CÔNG --}}
        <section
            class="rounded-3xl border border-emerald-500/30
                   bg-gradient-to-br from-emerald-500/15 to-[#141414]
                   p-8 shadow-2xl"
        >
            <div
                class="flex flex-col items-center gap-5 text-center
                       sm:flex-row sm:text-left"
            >

                <div
                    class="flex h-20 w-20 shrink-0 items-center
                           justify-center rounded-full bg-emerald-500
                           text-4xl shadow-lg"
                >
                    <i class="fa-solid fa-check"></i>
                </div>

                <div class="flex-1">

                    <p
                        class="text-xs font-black uppercase
                               tracking-[0.35em] text-emerald-300"
                    >
                        Giao dịch hoàn tất
                    </p>

                    <h1 class="mt-2 text-4xl font-black">
                        Thanh toán thành công
                    </h1>

                    <p class="mt-2 text-gray-300">
                        Vé

                        <strong class="text-yellow-400">
                            {{ $ve->ma_ve }}
                        </strong>

                        đã được phát hành.
                    </p>

                </div>

                <div
                    class="rounded-2xl border border-white/10
                           bg-black/25 px-5 py-4"
                >
                    <p
                        class="text-xs uppercase tracking-widest
                               text-gray-500"
                    >
                        Tổng thanh toán
                    </p>

                    <strong
                        class="mt-1 block text-3xl text-yellow-400"
                    >
                        {{ number_format((float) $ve->tong_tien, 0, ',', '.') }}đ
                    </strong>
                </div>

            </div>
        </section>


        {{-- THÔNG TIN VÉ + THANH TOÁN --}}
        <div class="mt-6 grid gap-6 lg:grid-cols-2">

            {{-- THÔNG TIN VÉ --}}
            <section
                class="rounded-3xl border border-white/10
                       bg-[#141414] p-6"
            >

                <h2
                    class="mb-5 text-xl font-black
                           text-yellow-400"
                >
                    <i class="fa-solid fa-ticket mr-2"></i>
                    Thông tin vé
                </h2>

                @foreach ([
                    ['Mã vé', $ve->ma_ve],
                    ['Tên phim', $ve->ten_phim],
                    ['Rạp', $ve->ten_rap],
                    ['Phòng', $ve->ten_phong],
                    [
                        'Suất chiếu',
                        optional($ve->thoi_gian_chieu)
                            ->format('d/m/Y H:i')
                    ],
                    [
                        'Ghế',
                        str_replace(',', ', ', $ve->ma_ghe)
                    ],
                    ['Nhân viên', $staffName],
                    [
                        'Trạng thái',
                        match ($ve->trang_thai) {
                            'da_thanh_toan' => 'Đã thanh toán',
                            'da_in' => 'Đã in',
                            'da_su_dung' => 'Đã sử dụng',
                            'da_huy' => 'Đã hủy',
                            default => $ve->trang_thai ?: '---',
                        }
                    ],
                ] as [$label, $value])

                    <div
                        class="flex justify-between gap-5
                               border-b border-white/10 py-3"
                    >

                        <span class="text-gray-400">
                            {{ $label }}
                        </span>

                        <strong class="text-right">
                            {{ $value ?: '---' }}
                        </strong>

                    </div>

                @endforeach

            </section>


            {{-- THANH TOÁN --}}
            <section
                class="rounded-3xl border border-white/10
                       bg-[#141414] p-6"
            >

                <h2
                    class="mb-5 text-xl font-black
                           text-yellow-400"
                >
                    <i class="fa-solid fa-receipt mr-2"></i>
                    Thanh toán
                </h2>


                <div
                    class="flex justify-between
                           border-b border-white/10 py-3"
                >
                    <span class="text-gray-400">
                        Tiền vé
                    </span>

                    <strong>
                        {{ number_format($seatTotal, 0, ',', '.') }}đ
                    </strong>
                </div>


                <div
                    class="flex justify-between
                           border-b border-white/10 py-3"
                >
                    <span class="text-gray-400">
                        Đồ ăn
                    </span>

                    <strong>
                        {{ number_format($foodTotal, 0, ',', '.') }}đ
                    </strong>
                </div>


                <div
                    class="flex justify-between
                           border-b border-white/10 py-3"
                >
                    <span class="text-gray-400">
                        Phương thức
                    </span>

                    <strong>
                        {{ $ve->payment_method === 'vietqr'
                            ? 'VietQR'
                            : 'Tiền mặt'
                        }}
                    </strong>
                </div>


                @if ($ve->payment_method !== 'vietqr')

                    <div
                        class="flex justify-between
                               border-b border-white/10 py-3"
                    >
                        <span class="text-gray-400">
                            Khách đưa
                        </span>

                        <strong>
                            {{
                                number_format(
                                    (float) ($ve->received_amount ?? 0),
                                    0,
                                    ',',
                                    '.'
                                )
                            }}đ
                        </strong>
                    </div>


                    <div
                        class="flex justify-between
                               border-b border-white/10 py-3"
                    >
                        <span class="text-gray-400">
                            Tiền thừa
                        </span>

                        <strong class="text-emerald-400">
                            {{
                                number_format(
                                    (float) ($ve->change_amount ?? 0),
                                    0,
                                    ',',
                                    '.'
                                )
                            }}đ
                        </strong>
                    </div>

                @endif


                <div
                    class="flex justify-between
                           pt-5 text-2xl font-black"
                >
                    <span>
                        Tổng cộng
                    </span>

                    <strong class="text-yellow-400">
                        {{
                            number_format(
                                (float) $ve->tong_tien,
                                0,
                                ',',
                                '.'
                            )
                        }}đ
                    </strong>
                </div>

            </section>

        </div>


        {{-- ĐỒ ĂN --}}
        <section
            class="mt-6 rounded-3xl border border-white/10
                   bg-[#141414] p-6"
        >

            <h2
                class="mb-5 text-xl font-black
                       text-yellow-400"
            >
                <i class="fa-solid fa-burger mr-2"></i>
                Đồ ăn & combo
            </h2>


            @forelse ($foods as $food)

                @php
                    $foodName = $food['name']
                        ?? $food['ten_mon']
                        ?? 'Đồ ăn';

                    $foodPrice = (float) (
                        $food['price']
                        ?? $food['don_gia']
                        ?? 0
                    );

                    $foodQuantity = (int) (
                        $food['qty']
                        ?? $food['quantity']
                        ?? $food['so_luong']
                        ?? 1
                    );
                @endphp


                <div
                    class="flex justify-between
                           border-b border-white/10
                           py-3 last:border-0"
                >

                    <div>

                        <strong>
                            {{ $foodName }}
                        </strong>

                        <p class="text-sm text-gray-500">
                            {{
                                number_format(
                                    $foodPrice,
                                    0,
                                    ',',
                                    '.'
                                )
                            }}đ / sản phẩm
                        </p>

                    </div>


                    <div class="text-right">

                        <strong>
                            x{{ $foodQuantity }}
                        </strong>

                        <p class="text-sm text-yellow-400">
                            {{
                                number_format(
                                    $foodPrice * $foodQuantity,
                                    0,
                                    ',',
                                    '.'
                                )
                            }}đ
                        </p>

                    </div>

                </div>

            @empty

                <p
                    class="rounded-2xl border
                           border-dashed border-white/10
                           p-5 text-center text-gray-500"
                >
                    Không có đồ ăn hoặc combo.
                </p>

            @endforelse

        </section>


        {{-- CÁC NÚT THAO TÁC --}}
        <section
            class="mt-6 grid gap-3
                   sm:grid-cols-2 xl:grid-cols-3"
        >

            {{-- IN VÉ --}}
            <button
                type="button"
                id="btnPrintTicket"
                onclick="printTicket()"
                class="flex cursor-pointer items-center
                       justify-center gap-2 rounded-2xl
                       border-0 bg-yellow-400 px-5 py-4
                       font-black text-black transition
                       hover:bg-yellow-300"
            >
                <i class="fa-solid fa-print"></i>

                <span id="printButtonText">
                    In {{ $seatCount }} vé & hóa đơn
                </span>
            </button>


            {{-- BÁN VÉ MỚI --}}
            <a
                href="{{ route('staff.ban-ve.index') }}"
                onclick="clearCurrentFoodCart()"
                class="flex items-center justify-center
                       gap-2 rounded-2xl bg-emerald-600
                       px-5 py-4 font-black text-white
                       no-underline transition
                       hover:bg-emerald-500"
            >
                <i class="fa-solid fa-plus"></i>
                Bán vé mới
            </a>


            {{-- LỊCH SỬ --}}
            <a
                href="{{ route('staff.lich-su-ve.index') }}"
                class="flex items-center justify-center
                       gap-2 rounded-2xl border
                       border-white/15 bg-white/5
                       px-5 py-4 font-black text-white
                       no-underline transition
                       hover:bg-white/10"
            >
                <i class="fa-solid fa-clock-rotate-left"></i>
                Lịch sử vé
            </a>

        </section>

    </div>

</div>


{{-- ========================================================= --}}
{{-- IFRAME ẨN DÙNG ĐỂ IN - KHÔNG CHUYỂN TRANG                --}}
{{-- ========================================================= --}}

<iframe
    id="ticketPrintFrame"
    title="In vé"
    style="
        position: fixed;
        width: 1px;
        height: 1px;
        right: 0;
        bottom: 0;
        border: 0;
        opacity: 0;
        pointer-events: none;
    "
></iframe>


<script>
    let isPrinting = false;


    /**
     * Mở hộp thoại in mà không rời khỏi
     * trang kết quả bán vé.
     */
    async function printTicket() {

        if (isPrinting) {
            return;
        }

        const frame = document.getElementById('ticketPrintFrame');
        const button = document.getElementById('btnPrintTicket');
        const buttonText = document.getElementById('printButtonText');

        isPrinting = true;

        button.disabled = true;
        button.classList.add('opacity-60', 'cursor-wait');
        buttonText.textContent = 'Đang chuẩn bị vé...';

        try {

            /*
             * Đánh dấu "Đã in" tại thời điểm nhân viên chủ động
             * bấm nút In vé. Trình duyệt không cung cấp API tin cậy
             * để biết người dùng bấm Print hay Cancel trong hộp thoại.
             */
            const response = await fetch(
                @json(
                    route(
                        'staff.ban-ve.mark-printed',
                        ['id' => $ve->id]
                    )
                ),
                {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN':
                            document.querySelector(
                                'meta[name="csrf-token"]'
                            )?.getAttribute('content') ?? '',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                }
            );

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message
                    ?? 'Không thể cập nhật trạng thái đã in.'
                );
            }

            frame.onload = function () {

                setTimeout(function () {

                    try {

                        const printWindow = frame.contentWindow;

                        printWindow.focus();
                        printWindow.print();

                    } catch (error) {

                        console.error(
                            'Không thể mở hộp thoại in:',
                            error
                        );

                        alert(
                            'Không thể mở hộp thoại in. ' +
                            'Vui lòng thử lại.'
                        );

                    } finally {

                        isPrinting = false;

                        button.disabled = false;

                        button.classList.remove(
                            'opacity-60',
                            'cursor-wait'
                        );

                        buttonText.textContent =
                            'In lại {{ $seatCount }} vé & hóa đơn';

                    }

                }, 500);

            };

            frame.src =
                @json(
                    route(
                        'staff.ban-ve.print-ticket',
                        ['id' => $ve->id]
                    )
                )
                + '?embedded=1&t='
                + Date.now();

        } catch (error) {

            console.error(error);

            alert(
                error.message
                ?? 'Không thể chuẩn bị vé để in.'
            );

            isPrinting = false;

            button.disabled = false;

            button.classList.remove(
                'opacity-60',
                'cursor-wait'
            );

            buttonText.textContent =
                'In {{ $seatCount }} vé & hóa đơn';
        }
    }



    /**
     * Xóa giỏ đồ ăn của giao dịch vừa hoàn thành.
     */
    function clearCurrentFoodCart() {

        const staffId = {{ $staffId }};
        const showtimeId = {{ $showtimeId }};

        const sessionCartKey =
            @json(session('clear_food_cart_key'));


        localStorage.removeItem(
            `staff_food_cart_v2_${staffId}_${showtimeId}`
        );

        localStorage.removeItem(
            `staff_food_cart_${staffId}_${showtimeId}`
        );


        if (sessionCartKey) {
            localStorage.removeItem(sessionCartKey);
        }


        Object.keys(localStorage)
            .filter(function (key) {

                return (
                    key.startsWith('staff_food_cart_')
                    &&
                    key.endsWith(`_${showtimeId}`)
                );

            })
            .forEach(function (key) {

                localStorage.removeItem(key);

            });
    }


    document.addEventListener(
        'DOMContentLoaded',
        function () {

            clearCurrentFoodCart();

        }
    );
</script>

@endsection