<?php $__env->startSection('title', 'Checkout - ' . $suatChieu->phim->ten_phim); ?>

<?php $__env->startSection('content'); ?>

    <div class="booking-checkout-page min-h-screen bg-[#080808] pt-24 pb-12 text-white" lang="vi" spellcheck="false">

        <div class="booking-checkout-container mx-auto max-w-7xl px-4 lg:px-6">

            
            <div class="mb-8">
                <p class="text-xs uppercase tracking-[0.45em] text-yellow-400">Checkout</p>
                <h1 class="mt-2 text-4xl font-black">THANH TOÁN</h1>
                <p class="mt-2 text-gray-400">Kiểm tra thông tin trước khi hoàn tất đơn đặt vé.</p>
            </div>

            
            <div class="booking-checkout-shell overflow-hidden rounded-3xl border border-white/10 bg-[#141414] shadow-2xl">
                
                <div class="mb-8 flex items-center justify-between rounded-2xl border border-white/10 bg-zinc-900 p-4">

                    <div>
                        <p class="text-[10px] uppercase tracking-[0.35em] text-gray-500">
                            Thời gian hoàn tất thanh toán
                        </p>

                        <p class="mt-1 text-sm text-gray-400">
                            Đơn đặt vé sẽ tự động hủy khi hết thời gian.
                        </p>
                    </div>

                    <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/20 px-4 py-2">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-black/40 text-lg">⏰</div>
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.3em] text-gray-500">Thời gian còn lại</p>
                            <div id="countdown" class="text-2xl font-black tracking-[0.2em] text-red-400">07:00</div>
                        </div>
                    </div>

                </div>

                <div class="booking-checkout-layout grid lg:grid-cols-[380px_1fr]">

                    
                    <div class="booking-checkout-info border-r border-white/10 p-8">

                        
                        <div class="pb-6">

                            <div class="mb-4 flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-yellow-400 text-black text-xl">👤</div>
                                <div>
                                    <h2 class="text-xl font-black text-yellow-400">Thông tin người đặt</h2>
                                    <p class="text-sm text-gray-400">Tài khoản</p>
                                </div>
                            </div>

                            <input readonly value="<?php echo e(auth()->user()->ho_ten ?? (auth()->user()->ten ?? '')); ?>"
                                class="w-full mb-3 rounded-xl border border-white/10 bg-[#1d1d1d] px-4 py-3">

                            <input readonly value="<?php echo e(auth()->user()->email); ?>"
                                class="w-full rounded-xl border border-white/10 bg-[#1d1d1d] px-4 py-3">
                        </div>

                        
                        <div class="border-t border-white/10 pt-6 mt-6">

                            <h2 class="text-lg font-black text-yellow-400 mb-3">Thông tin Phim</h2>

                            <div class="rounded-xl overflow-hidden border border-white/10">
                                <img src="<?php echo e(asset('storage/movies/' . $suatChieu->phim->poster)); ?>"
                                    alt="<?php echo e($suatChieu->phim->ten_phim); ?>" class="w-full rounded-lg object-cover">
                            </div>

                            <div class="mt-4 text-sm space-y-2 text-gray-300">
                                <div><strong>Rạp chiếu:</strong> <?php echo e($suatChieu->rapChieuPhim->ten_rap); ?></div>
                                <div><strong>Tên Phim:</strong> <?php echo e($suatChieu->phim->ten_phim); ?></div>
                                <div><strong>Suất chiếu:</strong> <?php echo e($suatChieu->thoi_gian_chieu->format('d/m/Y H:i')); ?></div>
                            </div>

                        </div>

                    </div>

                    
                    <div class="booking-checkout-order p-8">

                        
                        <div class="border-b border-white/10 pb-6">

                            <h2 class="text-2xl font-black text-yellow-400 mb-4">Danh sách đặt</h2>

                            <div class="py-6 border-b border-white/10">

                                <div class="flex justify-between">
                                    <span>Số ghế</span>
                                    <span class="text-yellow-400 font-semibold">
                                        <?php echo e($selectedSeats->implode(', ')); ?>

                                    </span>
                                </div>

                                <?php $__empty_1 = true; $__currentLoopData = $foodItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php
                                        $foodImagePath = trim((string) ($item['image'] ?? ''));
                                        if ($foodImagePath !== '' && ! str_starts_with($foodImagePath, 'foods/')) {
                                            $foodImagePath = 'foods/' . $foodImagePath;
                                        }
                                    ?>
                                    <div class="flex justify-between items-center mt-3">
                                        <div class="flex items-center gap-3">
                                            <?php if($foodImagePath !== ''): ?>
                                                <img src="<?php echo e(asset('storage/' . $foodImagePath)); ?>"
                                                    class="w-16 h-16 object-contain rounded">
                                            <?php endif; ?>
                                            <span><?php echo e($item['name']); ?></span>
                                        </div>
                                        <span>x<?php echo e($item['qty']); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="flex justify-between mt-3">
                                        <span>Đồ ăn</span>
                                        <span>Không có</span>
                                    </div>
                                <?php endif; ?>

                            </div>

                        </div>

                        
                        <div class="py-6 border-b border-white/10">

                            <div class="flex justify-between">
                                <span>Tiền ghế</span>
                                <span><?php echo e(number_format($seatTotalPrice, 0, ',', '.')); ?>đ</span>
                            </div>

                            <div class="flex justify-between mt-2">
                                <span>Tiền đồ ăn</span>
                                <span><?php echo e(number_format($foodTotal, 0, ',', '.')); ?>đ</span>
                            </div>

                        </div>

                        
                        <div class="py-6 border-b border-white/10">

                            <h3 class="mb-4 text-lg font-black text-yellow-400">Mã giảm giá</h3>

                            <div class="flex gap-2">
                                <input type="text" id="voucherCode" placeholder="Nhập mã voucher..."
                                    class="flex-1 rounded-2xl border border-white/10 bg-[#1d1d1d] px-4 py-3 text-white outline-none focus:border-yellow-400">

                                <button type="button" id="applyVoucherButton" onclick="applyVoucher()"
                                    class="rounded-2xl bg-yellow-400 px-5 font-black text-black hover:bg-yellow-300">
                                    Áp dụng
                                </button>
                            </div>

                            <div id="voucherResult"
                                class="mt-3 hidden rounded-xl border border-yellow-400/30 bg-yellow-400/10 p-3 text-sm text-yellow-300">
                                ✔ Voucher đã áp dụng
                            </div>

                            <div class="border-t border-white/10 mt-4 pt-4 flex justify-between text-yellow-400 text-2xl font-black">
                                <span>Tổng</span>
                                <span id="grandTotal"><?php echo e(number_format($grandTotal, 0, ',', '.')); ?>đ</span>
                            </div>

                        </div>

                        
                        <div class="pt-6">

                            <h2 class="text-xl font-black text-yellow-400 mb-4">Chọn phương thức thanh toán</h2>

                            <form id="paymentForm" action="<?php echo e(route('dat_ve.xu_ly_thanh_toan', $suatChieu->id)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="ghe" value="<?php echo e($selectedSeats->implode(', ')); ?>">
                                <input type="hidden" name="food_cart" value="<?php echo e(json_encode($foodItems->toArray(), JSON_UNESCAPED_UNICODE)); ?>">
                                <input type="hidden" id="submitVoucherCode" name="voucher_code" value="">
                                <?php if(!empty($pendingTicketId)): ?>
                                    <input type="hidden" name="pending_ticket_id" value="<?php echo e($pendingTicketId); ?>">
                                <?php endif; ?>

                                <div class="space-y-3">
                                    
                                    <label class="payment-method-label flex items-center justify-between border border-yellow-400 bg-yellow-400/10 p-4 rounded-2xl cursor-pointer transition dynamics-duration-200">
                                        <div class="flex items-center gap-3">
                                            <input type="radio" checked name="payment_method" value="online" class="accent-yellow-400 w-5 h-5">
                                            <div class="ml-1">
                                                <span class="block font-bold text-gray-100">Cổng thanh toán VNPAY</span>
                                                <span class="block text-xs text-gray-400 mt-0.5">Thanh toán qua ATM, Thẻ quốc tế hoặc ứng dụng ngân hàng</span>
                                            </div>
                                        </div>
                                        <div class="bg-white px-2 py-1 rounded-lg flex items-center justify-center shadow-sm">
                                            <img src="<?php echo e(asset('assets/images/logo-vnpay.webp')); ?>" class="h-5 w-16 object-contain" alt="VNPAY Logo">
                                        </div>
                                    </label>

                                    
                                    <label class="payment-method-label flex items-center justify-between border border-white/10 bg-zinc-900/30 p-4 rounded-2xl cursor-pointer transition dynamics-duration-200 hover:border-white/20">
                                        <div class="flex items-center gap-3">
                                            <input type="radio" name="payment_method" value="vietqr" class="accent-yellow-400 w-5 h-5">
                                            <div class="ml-1">
                                                <span class="block font-bold text-gray-200">Chuyển khoản nhanh VietQR</span>
                                                <span class="block text-xs text-gray-400 mt-0.5">Tạo mã QR bốc động số tiền, quét để chuyển khoản an toàn</span>
                                            </div>
                                        </div>
                                        <div class="bg-white px-2 py-1 rounded-lg flex items-center justify-center shadow-sm">
                                            <img src="<?php echo e(asset('assets/images/logo-vietqr.png')); ?>" class="h-5 w-16 object-contain" alt="VietQR Logo">
                                        </div>
                                    </label>
                                </div>

                                <button type="submit"
                                    class="mt-6 w-full rounded-2xl bg-yellow-400 py-4 font-black text-black hover:bg-yellow-300 transition duration-200 shadow-lg shadow-yellow-400/10">
                                    THANH TOÁN NGAY
                                </button>
                            </form>

                            <?php
                                $backUrl = route('dat_ve.chon_do_an', ['suat_chieu_id' => $suatChieu->id]) . '?ghe=' . urlencode(request('ghe'));
                                if (request('food_cart')) {
                                    $backUrl .= '&food_cart=' . urlencode(request('food_cart'));
                                }
                                if (! empty($pendingTicketId)) {
                                    $backUrl .= '&pending_ticket_id=' . urlencode($pendingTicketId);
                                }
                            ?>

                            <a href="<?php echo e($backUrl); ?>"
                                class="mt-3 flex w-full items-center justify-center rounded-2xl border border-white/20 bg-white/5 py-4 font-semibold text-gray-300 transition hover:border-white/40 hover:bg-white/10 hover:text-white">
                                ← Quay lại chọn đồ ăn
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    const baseTotal = Number("<?php echo e((float) $grandTotal); ?>");
    let appliedVoucher = null;
    let voucherRequestRunning = false;

    const formatVnd = (value) =>
        new Intl.NumberFormat('vi-VN').format(Math.max(0, Number(value) || 0)) + 'đ';

    function resetVoucher(message = '') {
        appliedVoucher = null;

        const result = document.getElementById('voucherResult');
        const totalEl = document.getElementById('grandTotal');
        const submitVoucherInput = document.getElementById('submitVoucherCode');

        if (submitVoucherInput) {
            submitVoucherInput.value = '';
        }

        if (totalEl) {
            totalEl.innerText = formatVnd(baseTotal);
        }

        if (result) {
            if (message) {
                result.classList.remove('hidden');
                result.classList.remove(
                    'border-yellow-400/30',
                    'bg-yellow-400/10',
                    'text-yellow-300'
                );
                result.classList.add(
                    'border-red-400/30',
                    'bg-red-400/10',
                    'text-red-300'
                );
                result.textContent = message;
            } else {
                result.classList.add('hidden');
                result.textContent = '';
            }
        }
    }

    async function applyVoucher() {
        if (voucherRequestRunning) {
            return;
        }

        const codeInput = document.getElementById('voucherCode');
        const result = document.getElementById('voucherResult');
        const totalEl = document.getElementById('grandTotal');
        const submitVoucherInput = document.getElementById('submitVoucherCode');
        const applyButton = document.getElementById('applyVoucherButton');

        const code = (codeInput?.value || '').trim().toUpperCase();

        if (!code) {
            resetVoucher('Vui lòng nhập mã voucher.');
            codeInput?.focus();
            return;
        }

        voucherRequestRunning = true;

        if (applyButton) {
            applyButton.disabled = true;
            applyButton.textContent = 'Đang kiểm tra...';
        }

        try {
            const response = await fetch("<?php echo e(route('dat_ve.ap_dung_voucher')); ?>", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': "<?php echo e(csrf_token()); ?>",
                },
                body: JSON.stringify({
                    voucher_code: code,
                    subtotal: baseTotal,
                }),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                resetVoucher(
                    data.message ||
                    data.errors?.voucher_code?.[0] ||
                    'Voucher không hợp lệ.'
                );
                return;
            }

            appliedVoucher = {
                code: data.voucher_code,
                discount: Number(data.discount),
                finalTotal: Number(data.final_total),
            };

            codeInput.value = appliedVoucher.code;
            submitVoucherInput.value = appliedVoucher.code;

            result.classList.remove('hidden');
            result.classList.remove(
                'border-red-400/30',
                'bg-red-400/10',
                'text-red-300'
            );
            result.classList.add(
                'border-yellow-400/30',
                'bg-yellow-400/10',
                'text-yellow-300'
            );
            result.innerHTML =
                `✔ Đã áp dụng: <b>${appliedVoucher.code}</b> ` +
                `(-${formatVnd(appliedVoucher.discount)})`;

            totalEl.innerText = formatVnd(appliedVoucher.finalTotal);
        } catch (error) {
            console.error(error);
            resetVoucher('Không thể kiểm tra voucher. Vui lòng thử lại.');
        } finally {
            voucherRequestRunning = false;

            if (applyButton) {
                applyButton.disabled = false;
                applyButton.textContent = 'Áp dụng';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const codeInput = document.getElementById('voucherCode');
        const paymentForm = document.getElementById('paymentForm');

        codeInput?.addEventListener('input', function () {
            if (
                appliedVoucher &&
                this.value.trim().toUpperCase() !== appliedVoucher.code
            ) {
                resetVoucher();
            }
        });

        codeInput?.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                applyVoucher();
            }
        });

        paymentForm?.addEventListener('submit', function (event) {
            const typedCode = (codeInput?.value || '').trim().toUpperCase();

            if (typedCode && (!appliedVoucher || appliedVoucher.code !== typedCode)) {
                event.preventDefault();
                resetVoucher('Bạn phải bấm “Áp dụng” và xác thực voucher trước khi thanh toán.');
                codeInput?.focus();
            }
        });

        const paymentLabels = document.querySelectorAll('.payment-method-label');

        paymentLabels.forEach(label => {
            const radio = label.querySelector('input[type="radio"]');

            radio.addEventListener('change', function () {
                paymentLabels.forEach(item => {
                    item.classList.remove('border-yellow-400', 'bg-yellow-400/10');
                    item.classList.add('border-white/10', 'bg-zinc-900/30');

                    const text = item.querySelector('span');
                    text?.classList.remove('text-gray-100');
                    text?.classList.add('text-gray-200');
                });

                if (this.checked) {
                    label.classList.add('border-yellow-400', 'bg-yellow-400/10');
                    label.classList.remove('border-white/10', 'bg-zinc-900/30');

                    const text = label.querySelector('span');
                    text?.classList.add('text-gray-100');
                    text?.classList.remove('text-gray-200');
                }
            });
        });

        const countdownEl = document.getElementById('countdown');

        if (!countdownEl) {
            return;
        }

        const storageKey = "booking_deadline_<?php echo e($suatChieu->id); ?>";
        const pendingDeadlineValue = "<?php echo e($pendingDeadline ?? ''); ?>";
        const pendingDeadline = pendingDeadlineValue ? Number(pendingDeadlineValue) : null;

        function getStoredDeadline() {
            try {
                const value = Number(localStorage.getItem(storageKey));
                return Number.isFinite(value) ? value : null;
            } catch (error) {
                return null;
            }
        }

        function setStoredDeadline(deadline) {
            try {
                localStorage.setItem(storageKey, String(deadline));
            } catch (error) {
                console.error(error);
            }
        }

        function clearStoredDeadline() {
            try {
                localStorage.removeItem(storageKey);
            } catch (error) {
                console.error(error);
            }
        }

        const storedDeadline = getStoredDeadline();
        const validPendingDeadline = pendingDeadline && pendingDeadline > Date.now() ? pendingDeadline : null;
        const validStoredDeadline = storedDeadline && storedDeadline > Date.now() ? storedDeadline : null;
        let deadline = null;

        if (validPendingDeadline && validStoredDeadline) {
            deadline = Math.min(validPendingDeadline, validStoredDeadline);
        } else if (validPendingDeadline) {
            deadline = validPendingDeadline;
        } else if (validStoredDeadline) {
            deadline = validStoredDeadline;
        } else {
            deadline = Date.now() + 7 * 60 * 1000;
        }

        setStoredDeadline(deadline);

        function updateCountdown() {
            const remaining = deadline - Date.now();

            if (remaining <= 0) {
                clearStoredDeadline();
                countdownEl.innerText = '00:00';
                countdownEl.classList.add('animate-pulse');
                window.location.href = "<?php echo e(route('home')); ?>";
                return;
            }

            const minutes = Math.floor(remaining / 60000);
            const seconds = Math.floor((remaining % 60000) / 1000);

            countdownEl.innerText =
                String(minutes).padStart(2, '0') +
                ':' +
                String(seconds).padStart(2, '0');

            if (remaining <= 60000) {
                countdownEl.classList.add('animate-pulse');
            }
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\laragon\www\Cinema\WD-11-Cinehome-cinema\resources\views/user/dat_ve/checkout.blade.php ENDPATH**/ ?>