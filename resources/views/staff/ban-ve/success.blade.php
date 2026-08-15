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

                        <strong
                            class="text-right"
                            @if ($label === 'Trạng thái') id="ticketStatusText" @endif
                        >
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
                href="{{ route('admin.ve-xem-phims.index') }}"
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
{{-- MODAL XÁC NHẬN SAU KHI ĐÓNG HỘP THOẠI IN                --}}
{{-- ========================================================= --}}
<div id="printConfirmModal"
     class="fixed inset-0 z-[99999] hidden items-center justify-center p-5">

    <div
        id="printConfirmBackdrop"
        class="absolute inset-0 bg-black/75 backdrop-blur-sm"
    ></div>

    <div
        class="relative w-full max-w-md overflow-hidden rounded-3xl
               border border-yellow-400/30
               bg-gradient-to-br from-[#1b1b1b] to-[#101010]
               shadow-[0_30px_90px_rgba(0,0,0,.65)]"
    >
        <div class="p-7 text-center">

            <div
                class="mx-auto flex h-16 w-16 items-center justify-center
                       rounded-2xl bg-gradient-to-br from-yellow-300
                       to-yellow-500 text-2xl text-black
                       shadow-[0_12px_30px_rgba(250,204,21,.25)]"
            >
                <i class="fa-solid fa-print"></i>
            </div>

            <h3 class="mt-5 text-2xl font-black text-white">
                Xác nhận in vé
            </h3>

            <p class="mt-2 text-sm text-gray-400">
                Vé và hóa đơn đã được in ra giấy thành công chưa?
            </p>

            <div
                class="mt-5 flex items-start gap-3 rounded-2xl
                       border border-yellow-400/15
                       bg-yellow-400/5 p-4 text-left"
            >
                <i class="fa-solid fa-circle-info mt-0.5 text-yellow-400"></i>

                <p class="text-xs leading-5 text-gray-400">
                    Chỉ khi xác nhận thành công, trạng thái vé mới chuyển sang
                    <strong class="text-yellow-300">Đã in</strong>.
                    Nếu bạn vừa bấm Hủy trong cửa sổ in, hãy chọn
                    <strong class="text-white">Chưa in</strong>.
                </p>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-3">

                <button
                    type="button"
                    id="btnPrintNotDone"
                    class="flex items-center justify-center gap-2
                           rounded-2xl border border-white/10
                           bg-white/5 px-4 py-3.5
                           font-black text-gray-200 transition
                           hover:bg-white/10"
                >
                    <i class="fa-solid fa-xmark"></i>
                    Chưa in
                </button>

                <button
                    type="button"
                    id="btnPrintDone"
                    class="flex items-center justify-center gap-2
                           rounded-2xl border-0
                           bg-gradient-to-r from-yellow-400 to-amber-500
                           px-4 py-3.5 font-black text-black transition
                           hover:from-yellow-300 hover:to-amber-400"
                >
                    <i class="fa-solid fa-check"></i>
                    Đã in thành công
                </button>

            </div>
        </div>
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
    let isConfirmingPrinted = false;

    function setPrintButtonReady() {
        const button = document.getElementById('btnPrintTicket');
        const buttonText = document.getElementById('printButtonText');

        isPrinting = false;

        if (!button || !buttonText) {
            return;
        }

        button.disabled = false;
        button.classList.remove('opacity-60', 'cursor-wait');

        const statusText =
            document.getElementById('ticketStatusText')
                ?.textContent.trim();

        buttonText.textContent =
            statusText === 'Đã in'
                ? 'In lại {{ $seatCount }} vé & hóa đơn'
                : 'In {{ $seatCount }} vé & hóa đơn';
    }

    function openPrintConfirmModal() {
        const modal = document.getElementById('printConfirmModal');

        if (!modal) {
            return;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closePrintConfirmModal() {
        const modal = document.getElementById('printConfirmModal');

        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    /**
     * Chỉ mở hộp thoại in.
     * KHÔNG cập nhật trạng thái trước khi nhân viên xác nhận.
     */
    function printTicket() {
        if (isPrinting) {
            return;
        }

        const frame = document.getElementById('ticketPrintFrame');
        const button = document.getElementById('btnPrintTicket');
        const buttonText = document.getElementById('printButtonText');

        if (!frame || !button || !buttonText) {
            return;
        }

        isPrinting = true;

        button.disabled = true;
        button.classList.add('opacity-60', 'cursor-wait');
        buttonText.textContent = 'Đang chuẩn bị vé...';

        frame.onload = function () {
            setTimeout(function () {
                try {
                    const printWindow = frame.contentWindow;

                    printWindow.focus();
                    printWindow.print();

                    /*
                     * Sau khi hộp thoại in đóng, hiện modal xác nhận đẹp.
                     * Nếu vé đã ở trạng thái Đã in thì đây chỉ là in lại,
                     * không cần xác nhận lần nữa.
                     */
                    const currentStatus =
                        document.getElementById('ticketStatusText')
                            ?.textContent.trim();

                    if (currentStatus !== 'Đã in') {
                        openPrintConfirmModal();
                    }

                } catch (error) {
                    console.error(
                        'Không thể mở hộp thoại in:',
                        error
                    );

                    alert(
                        'Không thể mở hộp thoại in. '
                        + 'Vui lòng thử lại.'
                    );
                } finally {
                    setPrintButtonReady();
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
    }

    /**
     * Chỉ cập nhật Đã in sau khi nhân viên chủ động xác nhận.
     */
    async function confirmPrintedSuccess() {
        if (isConfirmingPrinted) {
            return;
        }

        const confirmButton = document.getElementById('btnPrintDone');
        const notDoneButton = document.getElementById('btnPrintNotDone');

        isConfirmingPrinted = true;

        if (confirmButton) {
            confirmButton.disabled = true;
            confirmButton.innerHTML =
                '<i class="fa-solid fa-spinner fa-spin"></i>'
                + '<span>Đang xác nhận...</span>';
        }

        if (notDoneButton) {
            notDoneButton.disabled = true;
        }

        try {
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

            const statusText =
                document.getElementById('ticketStatusText');

            if (statusText) {
                statusText.textContent = 'Đã in';
            }

            const printButtonText =
                document.getElementById('printButtonText');

            if (printButtonText) {
                printButtonText.textContent =
                    'In lại {{ $seatCount }} vé & hóa đơn';
            }

            closePrintConfirmModal();

        } catch (error) {
            console.error(error);

            alert(
                error.message
                ?? 'Không thể cập nhật trạng thái Đã in.'
            );
        } finally {
            isConfirmingPrinted = false;

            if (confirmButton) {
                confirmButton.disabled = false;
                confirmButton.innerHTML =
                    '<i class="fa-solid fa-check"></i>'
                    + '<span>Đã in thành công</span>';
            }

            if (notDoneButton) {
                notDoneButton.disabled = false;
            }
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
                    && key.endsWith(`_${showtimeId}`)
                );
            })
            .forEach(function (key) {
                localStorage.removeItem(key);
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        clearCurrentFoodCart();

        document
            .getElementById('btnPrintNotDone')
            ?.addEventListener('click', closePrintConfirmModal);

        document
            .getElementById('printConfirmBackdrop')
            ?.addEventListener('click', closePrintConfirmModal);

        document
            .getElementById('btnPrintDone')
            ?.addEventListener('click', confirmPrintedSuccess);

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closePrintConfirmModal();
            }
        });
    });
</script>

@endsection