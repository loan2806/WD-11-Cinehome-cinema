

<?php $__env->startSection('title', 'Checkout - ' . $suatChieu->phim->ten_phim); ?>

<?php $__env->startSection('content'); ?>

    <div class="min-h-screen bg-[#080808] pt-24 pb-12 text-white">

        <div class="mx-auto max-w-7xl px-4 lg:px-6">

            
            <div class="mb-8">
                <p class="text-xs uppercase tracking-[0.45em] text-yellow-400">Checkout</p>
                <h1 class="mt-2 text-4xl font-black">THANH TOÁN</h1>
                <p class="mt-2 text-gray-400">Kiểm tra thông tin trước khi hoàn tất đơn đặt vé.</p>
            </div>

            
            <div class="overflow-hidden rounded-3xl border border-white/10 bg-[#141414] shadow-2xl">
                
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
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-black/40 text-lg">
                            ⏰</div>
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.3em] text-gray-500">Thời gian còn lại</p>
                            <div id="countdown" class="text-2xl font-black tracking-[0.2em] text-red-400">07:00</div>
                        </div>
                    </div>

                </div>

                <div class="grid lg:grid-cols-[380px_1fr]">

                    
                    <div class="border-r border-white/10 p-8">

                        
                        <div class="pb-6">

                            <div class="mb-4 flex items-center gap-4">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-yellow-400 text-black text-xl">
                                    👤</div>
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

                            <h2 class="text-lg font-black text-yellow-400 mb-3"> Thông tin Phim</h2>

                            <div class="rounded-xl overflow-hidden border border-white/10">
                                <img src="<?php echo e(asset('storage/movies/' . $suatChieu->phim->poster)); ?>"
                                    alt="<?php echo e($suatChieu->phim->ten_phim); ?>" class="w-full rounded-lg object-cover">
                            </div>

                            <div class="mt-4 text-sm space-y-2 text-gray-300">
                                <div>Tên rạp : <?php echo e($suatChieu->rapChieuPhim->ten_rap); ?></div>
                                <div> Tên Phim : <?php echo e($suatChieu->phim->ten_phim); ?></div>
                                <div>Suất chiếu: <?php echo e($suatChieu->thoi_gian_chieu->format('d/m/Y H:i')); ?></div>
                            </div>

                        </div>


                    </div>

                    
                    <div class="p-8">

                        
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
                                    <div class="flex justify-between items-center mt-3">
                                        <div class="flex items-center gap-3">

                                            <?php if(!empty($item['image'])): ?>
                                                <img src="<?php echo e(asset('storage/foods/' . $item['image'])); ?>"
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

                                <h3 class="mb-4 text-lg font-black text-yellow-400">
                                    Mã giảm giá
                                </h3>

                                <div class="flex gap-2">

                                    <input type="text" id="voucherCode" placeholder="Nhập mã voucher..."
                                        class="flex-1 rounded-2xl border border-white/10 bg-[#1d1d1d] px-4 py-3 text-white outline-none focus:border-yellow-400">

                                    <button type="button" onclick="applyVoucher()"
                                        class="rounded-2xl bg-yellow-400 px-5 font-black text-black hover:bg-yellow-300">
                                        Áp dụng
                                    </button>

                                </div>

                                <div id="voucherResult"
                                    class="mt-3 hidden rounded-xl border border-yellow-400/30 bg-yellow-400/10 p-3 text-sm text-yellow-300">
                                    ✔ Voucher đã áp dụng
                                </div>



                                <div
                                    class="border-t border-white/10 mt-4 pt-4 flex justify-between text-yellow-400 text-2xl font-black">
                                    <span>Tổng</span>
                                    <span id="grandTotal"><?php echo e(number_format($grandTotal, 0, ',', '.')); ?>đ</span>
                                </div>

                            </div>


                            
                            <div class="pt-6">

                                <h2 class="text-xl font-black text-yellow-400 mb-4">Thanh toán</h2>

                                <label
                                    class="flex items-center gap-3 border border-yellow-400 bg-yellow-400/10 p-4 rounded-2xl">
                                    <input type="radio" checked name="payment_method" class="accent-yellow-400">
                                    <span>Online (VNPay / MoMo)</span>
                                </label>

                                <button
                                    class="mt-6 w-full rounded-2xl bg-yellow-400 py-4 font-black text-black hover:bg-yellow-300">
                                    THANH TOÁN NGAY
                                </button>
                                <a href="<?php echo e(route('dat_ve.chon_do_an', ['suat_chieu_id' => $suatChieu->id])); ?>?ghe=<?php echo e(request('ghe')); ?>"
                                    class="mt-3 flex w-full items-center justify-center rounded-2xl border border-white/20 bg-white/5 py-4 font-semibold text-gray-300 transition hover:border-white/40 hover:bg-white/10 hover:text-white">
                                    ← Quay lại chọn đồ ăn
                                </a>

                            </div>

                        </div>

                    </div>
                </div>

            </div>

        <?php $__env->stopSection(); ?>

       <?php $__env->startSection('scripts'); ?>
<script>
    let appliedVoucher = null;
    let baseTotal = <?php echo e($grandTotal); ?>;

    function applyVoucher() {
        const code = document.getElementById('voucherCode').value.trim();
        const result = document.getElementById('voucherResult');
        const totalEl = document.getElementById('grandTotal');

        if (!code) {
            alert('Nhập mã voucher');
            return;
        }

        // Demo
        appliedVoucher = {
            code: code,
            discount: 20000
        };

        result.classList.remove('hidden');
        result.innerHTML =
            `✔ Đã áp dụng: <b>${code}</b> (-${appliedVoucher.discount.toLocaleString('vi-VN')}đ)`;

        const final = Math.max(0, baseTotal - appliedVoucher.discount);
        totalEl.innerText = final.toLocaleString('vi-VN') + 'đ';
    }

    document.addEventListener('DOMContentLoaded', function () {

        const countdownEl = document.getElementById('countdown');
        if (!countdownEl) return;

        const storageKey = 'booking_deadline_<?php echo e($suatChieu->id); ?>';

        function getStoredDeadline() {
            try {
                return Number(localStorage.getItem(storageKey)) || null;
            } catch (e) {
                return null;
            }
        }

        function setStoredDeadline(deadline) {
            try {
                localStorage.setItem(storageKey, String(deadline));
            } catch (e) {}
        }

        function clearStoredDeadline() {
            try {
                localStorage.removeItem(storageKey);
            } catch (e) {}
        }

        let deadline = getStoredDeadline();

        if (!deadline || deadline <= Date.now()) {
            deadline = Date.now() + 7 * 60 * 1000;
            setStoredDeadline(deadline);
        }

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

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\WD-11-Cinehome-cinema\resources\views/user/dat_ve/checkout.blade.php ENDPATH**/ ?>