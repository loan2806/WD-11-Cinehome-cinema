<?php $__env->startSection('title', 'Chọn ghế - ' . $suatChieu->phim->ten_phim); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $poster = $suatChieu->phim->poster ?? null;
        $posterUrl = $poster
            ? (\Illuminate\Support\Str::startsWith($poster, ['http://', 'https://'])
                ? $poster
                : asset('storage/movies/' . $poster))
            : 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=1200&auto=format&fit=crop';

        $flatSeats = collect($gheTheoHang ?? [])->flatten(1)->map(function ($seat) {
            $seat['loai_ghe_norm'] = mb_strtolower($seat['loai_ghe'] ?? '');
            return $seat;
        });

        $normalSeat = $flatSeats->first(fn($seat) => str_contains($seat['loai_ghe_norm'] ?? '', 'thường') || ($seat['loai_ghe_norm'] ?? '') === 'normal');
        $vipSeat = $flatSeats->first(fn($seat) => str_contains($seat['loai_ghe_norm'] ?? '', 'vip'));
        $doubleSeat = $flatSeats->first(fn($seat) => str_contains($seat['loai_ghe_norm'] ?? '', 'couple') || str_contains($seat['loai_ghe_norm'] ?? '', 'doi') || str_contains($seat['loai_ghe_norm'] ?? '', 'đôi') || str_contains($seat['loai_ghe_norm'] ?? '', 'double'));

        $hasNormal = !is_null($normalSeat);
        $hasVip = !is_null($vipSeat);
        $hasDouble = !is_null($doubleSeat);

        $normalPrice = $normalSeat['gia'] ?? ($suatChieu->gia_ve ?? 0);
        $vipPrice = $vipSeat['gia'] ?? $normalPrice;
        $doublePrice = ($doubleSeat['gia'] ?? (($suatChieu->gia_ve ?? 0) * 2)) + ($doubleSeat['phu_thu'] ?? 0);

        $totalSeats = $flatSeats->count();
        $availableSeats = $flatSeats->where('chon_duoc', true)->count();
        $unavailableSeats = $flatSeats->filter(fn($seat) => ($seat['da_dat'] ?? false) || ($seat['bao_tri'] ?? false))->count();
        $weekdayLabels = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
        $showDate = $suatChieu->thoi_gian_chieu;
        $showDateLabel = ($weekdayLabels[$showDate->dayOfWeek] ?? $showDate->format('D')) . ', ' . $showDate->format('d/m/Y');
    ?>

    <div class="dat-ve-page booking-seat-page" lang="vi" spellcheck="false">
        <section class="booking-seat-hero">
            <div class="booking-flow-hero-copy">
                <span class="booking-eyebrow">
                    <i class="fa-solid fa-couch"></i>
                    Bước 2 trong 4
                </span>
                <h1>Chọn ghế đẹp cho suất chiếu của bạn.</h1>
                <p>
                    <strong><?php echo e($suatChieu->phim->ten_phim); ?></strong> chiếu lúc
                    <strong><?php echo e($suatChieu->thoi_gian_chieu->format('H:i')); ?></strong> tại
                    <strong><?php echo e($suatChieu->rapChieuPhim->ten_rap); ?></strong>.
                    Ghế được giữ trong 7 phút sau khi chọn.
                </p>
                <div class="booking-seat-mini-stats" aria-label="Tổng quan ghế">
                    <div>
                        <strong><?php echo e($availableSeats); ?></strong>
                        <span>Ghế trống</span>
                    </div>
                    <div>
                        <strong><?php echo e($unavailableSeats); ?></strong>
                        <span>Đã đặt / giữ</span>
                    </div>
                    <div>
                        <strong><?php echo e(number_format($suatChieu->gia_ve, 0, ',', '.')); ?>đ</strong>
                        <span>Giá từ</span>
                    </div>
                </div>
            </div>

            <div class="booking-stepper" aria-label="Tiến trình đặt vé">
                <div class="booking-step is-done">
                    <span><i class="fa-solid fa-check"></i></span>
                    <strong>Chọn phim</strong>
                </div>
                <div class="booking-step is-active">
                    <span>2</span>
                    <strong>Chọn ghế</strong>
                </div>
                <div class="booking-step">
                    <span>3</span>
                    <strong>Đồ ăn</strong>
                </div>
                <div class="booking-step">
                    <span>4</span>
                    <strong>Thanh toán</strong>
                </div>
            </div>
        </section>

        <div class="booking-seat-layout">
            <aside class="booking-seat-movie-card">
                <div class="booking-seat-poster-wrap">
                    <img src="<?php echo e($posterUrl); ?>" alt="<?php echo e($suatChieu->phim->ten_phim); ?>">
                    <div class="booking-seat-poster-overlay">
                        <span>CineHome</span>
                        <strong><?php echo e($suatChieu->phim->ten_phim); ?></strong>
                    </div>
                </div>

                <div class="booking-seat-info-card">
                    <h2><?php echo e($suatChieu->phim->ten_phim); ?></h2>
                    <dl>
                        <div>
                            <dt><i class="fa-solid fa-location-dot"></i> Rạp</dt>
                            <dd><?php echo e($suatChieu->rapChieuPhim->ten_rap); ?></dd>
                        </div>
                        <div>
                            <dt><i class="fa-solid fa-door-open"></i> Phòng</dt>
                            <dd><?php echo e($suatChieu->phongChieu->ten_phong ?? 'Phòng chiếu'); ?></dd>
                        </div>
                        <div>
                            <dt><i class="fa-solid fa-calendar-days"></i> Ngày chiếu</dt>
                            <dd><?php echo e($showDateLabel); ?></dd>
                        </div>
                        <div>
                            <dt><i class="fa-solid fa-clock"></i> Giờ chiếu</dt>
                            <dd><?php echo e($suatChieu->thoi_gian_chieu->format('H:i')); ?></dd>
                        </div>
                    </dl>
                </div>

                <a href="<?php echo e(route('dat_ve.chon_phim')); ?>" class="booking-seat-back-link">
                    <i class="fa-solid fa-arrow-left"></i>
                    Đổi phim hoặc suất chiếu
                </a>
            </aside>

            <section class="booking-seat-map-panel" aria-label="Sơ đồ chọn ghế">
                <div class="booking-seat-toolbar">
                    <div>
                        <span class="booking-eyebrow">
                            <i class="fa-solid fa-ticket"></i>
                            Sơ đồ phòng chiếu
                        </span>
                        <h2>Chọn vị trí yêu thích</h2>
                        <p><?php echo e($availableSeats); ?> ghế còn trống / <?php echo e($totalSeats); ?> ghế. Tối đa 8 ghế mỗi lần đặt.</p>
                    </div>

                    <div class="booking-seat-timer" aria-live="polite">
                        <span><i class="fa-regular fa-clock"></i> Thời gian giữ ghế</span>
                        <strong id="countdown">07:00</strong>
                    </div>
                </div>

                <?php if(session('error')): ?>
                    <div class="booking-seat-alert" style="background: rgba(239, 68, 68, 0.15) !important; border: 1px solid #ef4444 !important; color: #f87171 !important; padding: 16px !important; border-radius: 12px !important; margin: 15px 0 !important; display: flex !important; align-items: center !important; gap: 12px !important; font-weight: 600 !important; font-size: 14px !important; position: relative !important; z-index: 99 !important;">
                        <i class="fa-solid fa-circle-exclamation" style="color: #ef4444 !important; font-size: 18px !important;"></i>
                        <span><?php echo e(session('error')); ?></span>
                    </div>
                <?php endif; ?>

                <div class="booking-theater">
                    <div class="booking-screen-wrap" aria-hidden="true">
                        <div class="screen-line"></div>
                        <span>Màn hình</span>
                    </div>

                    <div class="booking-seat-scroll">
                        <div class="booking-seat-grid">
                            <?php $__currentLoopData = $gheTheoHang ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hang => $cacGhe): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="seat-row">
                                    <span class="row-label"><?php echo e($hang); ?></span>

                                    <?php
                                        $merged = [];
                                        $skip = false;

                                        for ($i = 0; $i < count($cacGhe); $i++) {
                                            if ($skip) {
                                                $skip = false;
                                                continue;
                                            }

                                            $ghe = $cacGhe[$i];
                                            $type = mb_strtolower($ghe['loai_ghe'] ?? '');
                                            $isCouple = str_contains($type, 'couple') || str_contains($type, 'đôi') || str_contains($type, 'doi');

                                            if ($isCouple && isset($cacGhe[$i + 1]) && mb_strtolower($cacGhe[$i + 1]['loai_ghe'] ?? '') === $type) {
                                                $ghe2 = $cacGhe[$i + 1];
                                                $merged[] = [
                                                    'ma_ghe' => $ghe['ma_ghe'] . ' | ' . $ghe2['ma_ghe'],
                                                    'seat_codes' => $ghe['ma_ghe'] . ',' . $ghe2['ma_ghe'],
                                                    'loai_ghe' => $ghe['loai_ghe'],
                                                    'gia' => $ghe['gia'] + ($ghe['phu_thu'] ?? 0),
                                                    'mau_sac' => $ghe['mau_sac'],
                                                    'da_dat' => $ghe['da_dat'] || $ghe2['da_dat'],
                                                    'bao_tri' => $ghe['bao_tri'] || $ghe2['bao_tri'],
                                                    'chon_duoc' => $ghe['chon_duoc'] && $ghe2['chon_duoc'],
                                                    'is_couple' => true,
                                                ];
                                                $skip = true;
                                            } else {
                                                $merged[] = [
                                                    'ma_ghe' => $ghe['ma_ghe'],
                                                    'seat_codes' => $ghe['ma_ghe'],
                                                    'loai_ghe' => $ghe['loai_ghe'],
                                                    'gia' => $ghe['gia'],
                                                    'mau_sac' => $ghe['mau_sac'],
                                                    'da_dat' => $ghe['da_dat'],
                                                    'bao_tri' => $ghe['bao_tri'],
                                                    'chon_duoc' => $ghe['chon_duoc'],
                                                    'is_couple' => false,
                                                ];
                                            }
                                        }
                                    ?>

                                    <?php $__currentLoopData = $merged; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ghe): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $disabled = !($ghe['chon_duoc'] ?? false);
                                            $isBooked = $ghe['da_dat'];
                                            $isMaintenance = $ghe['bao_tri'];
                                            $seatCodes = $ghe['seat_codes'];
                                            $seatLabel = $ghe['ma_ghe'];
                                            $isCouple = $ghe['is_couple'];
                                            $codes = explode(',', $seatCodes);
                                            $typeText = $ghe['loai_ghe'] ?? 'Ghế';
                                        ?>

                                        <div class="seat-wrapper">
                                            <?php $__currentLoopData = $codes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $seat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <input type="checkbox" class="js-seat sr-only" value="<?php echo e(trim($seat)); ?>" <?php echo e($disabled ? 'disabled' : ''); ?>>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                            <button
                                                type="button"
                                                class="seat-button <?php echo e($isCouple ? 'seat-button--couple' : ''); ?> <?php echo e($isBooked ? 'booked' : ''); ?> <?php echo e($isMaintenance ? 'maintenance' : ''); ?>"
                                                style="--seat-color: <?php echo e($ghe['mau_sac']); ?>"
                                                data-seat="<?php echo e($seatLabel); ?>"
                                                data-seat-codes="<?php echo e($seatCodes); ?>"
                                                data-price="<?php echo e($ghe['gia']); ?>"
                                                data-type="<?php echo e($typeText); ?>"
                                                data-static-disabled="<?php echo e($disabled ? '1' : '0'); ?>"
                                                aria-label="Ghế <?php echo e($seatLabel); ?> - <?php echo e($typeText); ?> - <?php echo e(number_format($ghe['gia'], 0, ',', '.')); ?>đ"
                                                aria-pressed="false"
                                                <?php if($disabled): echo 'disabled'; endif; ?>
                                            >
                                                <?php if($isCouple): ?>
                                                    <span class="seat-couple-label"><?php echo e(trim($codes[0])); ?> | <?php echo e(trim($codes[1])); ?></span>
                                                <?php else: ?>
                                                    <span><?php echo e($seatLabel); ?></span>
                                                <?php endif; ?>
                                            </button>

                                            <div class="seat-tooltip">
                                                <strong><?php echo e($seatLabel); ?></strong>
                                                <span><?php echo e($typeText); ?></span>
                                                <small><?php echo e(number_format($ghe['gia'], 0, ',', '.')); ?>đ</small>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                    <span class="row-label"><?php echo e($hang); ?></span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <div class="booking-seat-legend" aria-label="Chú thích ghế">
                        <span><i class="seat-swatch is-empty"></i> Ghế trống</span>
                        <span><i class="seat-swatch is-vip"></i> Ghế VIP</span>
                        <span><i class="seat-swatch is-selected"></i> Đang chọn</span>
                        <span><i class="seat-swatch is-locked"></i> Đã đặt / đang giữ</span>
                    </div>
                </div>
            </section>

            <aside class="booking-seat-order-card" aria-label="Tóm tắt vé">
                <div class="booking-order-card-head">
                    <span>Tóm tắt đặt vé</span>
                    <h2>Ghế của bạn</h2>
                </div>

                <div class="booking-order-section">
                    <h3>Loại ghế</h3>
                    <div class="booking-seat-price-list">
                        <?php if($hasNormal): ?>
                            <div>
                                <span><i class="seat-swatch is-empty"></i> Thường</span>
                                <strong><?php echo e(number_format($normalPrice, 0, ',', '.')); ?>đ</strong>
                            </div>
                        <?php endif; ?>
                        <?php if($hasVip): ?>
                            <div>
                                <span><i class="seat-swatch is-vip"></i> VIP</span>
                                <strong><?php echo e(number_format($vipPrice, 0, ',', '.')); ?>đ</strong>
                            </div>
                        <?php endif; ?>
                        <?php if($hasDouble): ?>
                            <div>
                                <span><i class="seat-swatch is-couple"></i> Đôi</span>
                                <strong><?php echo e(number_format($doublePrice, 0, ',', '.')); ?>đ/cặp</strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="booking-order-section">
                    <h3>Vé đã chọn</h3>
                    <div class="booking-order-line">
                        <span>Số ghế</span>
                        <strong id="seatCount">0 ghế</strong>
                    </div>
                    <div class="booking-order-line">
                        <span>Vị trí</span>
                        <strong id="seatLabels">—</strong>
                    </div>
                    <div id="selected-list" class="booking-selected-list">
                        <span class="booking-seat-empty-selection">Chưa chọn ghế nào</span>
                    </div>
                </div>

                <div class="booking-seat-total-card">
                    <span>Tổng thanh toán</span>
                    <strong id="totalPrice">0đ</strong>
                    <small>Chưa bao gồm đồ ăn và voucher</small>
                </div>

                <button type="button" id="btnFood" disabled class="booking-seat-primary-cta">
                    Tiếp tục chọn đồ ăn
                    <i class="fa-solid fa-arrow-right"></i>
                </button>

                <button type="button" id="btnResetSeats" class="booking-seat-secondary-link" disabled>
                    <i class="fa-solid fa-rotate-left"></i>
                    Chọn lại ghế
                </button>

                <a href="<?php echo e(route('home')); ?>" class="booking-seat-secondary-link">
                    <i class="fa-solid fa-house"></i>
                    Về trang chủ
                </a>
            </aside>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script>
        const csrf = "<?php echo e(csrf_token()); ?>";
        const showtimeId = "<?php echo e($suatChieu->id); ?>";
        const pendingTicketId = "<?php echo e($pendingTicketId ?? ''); ?>";
        const pendingDeadline = "<?php echo e($pendingDeadline ?? ''); ?>";
        const initialSelectedSeats = <?php echo json_encode($selectedSeats->values()->all(), 15, 512) ?>;

        document.addEventListener("DOMContentLoaded", function() {
            const maxSeatCount = 8;
            const seatButtons = Array.from(document.querySelectorAll(".seat-button"));
            const btnFood = document.getElementById("btnFood");
            const btnResetSeats = document.getElementById("btnResetSeats");
            const seatLabels = document.getElementById("seatLabels");
            const seatCountEl = document.getElementById("seatCount");
            const totalPriceEl = document.getElementById("totalPrice");
            const selectedList = document.getElementById("selected-list");
            const countdownEl = document.getElementById("countdown");

            const storageKey = "booking_deadline_" + showtimeId;
            let countdownTimerInterval = null;
            let selectedSeats = [];
            let lockedSeats = new Set();
            let isProceedingToFood = false; // Flag kiểm tra người dùng có đang chuyển tiếp sang bước chọn đồ ăn không

            const params = new URLSearchParams(window.location.search);
            const seatParam = params.get("ghe");
            if (seatParam) {
                selectedSeats = seatParam.split(",").map(normalizeSeat).filter(Boolean);
            } else if (Array.isArray(initialSelectedSeats) && initialSelectedSeats.length > 0) {
                selectedSeats = initialSelectedSeats.map(normalizeSeat).filter(Boolean);
            }

            function normalizeSeat(seat) {
                return String(seat || "").trim().toUpperCase();
            }

            function money(value) {
                return Number(value || 0).toLocaleString("vi-VN") + "đ";
            }

            function getSeatsFromButton(btn) {
                return (btn.dataset.seatCodes || btn.dataset.seat)
                    .split(",")
                    .map(normalizeSeat)
                    .filter(Boolean);
            }

            function buttonIsSelected(btn) {
                return getSeatsFromButton(btn).some(code => selectedSeats.includes(code));
            }

            function buttonIsLocked(btn) {
                const codes = getSeatsFromButton(btn);
                const selected = codes.some(code => selectedSeats.includes(code));
                return !selected && codes.some(code => lockedSeats.has(code));
            }

            function syncButtonStates() {
                seatButtons.forEach(btn => {
                    const selected = buttonIsSelected(btn);
                    const locked = buttonIsLocked(btn);
                    const staticDisabled = btn.dataset.staticDisabled === "1";

                    btn.classList.toggle("selected", selected);
                    btn.classList.toggle("locked", locked);
                    btn.disabled = staticDisabled || locked;
                    btn.setAttribute("aria-pressed", selected ? "true" : "false");
                    btn.setAttribute("aria-disabled", btn.disabled ? "true" : "false");
                });
            }

            async function loadLockedSeats() {
                try {
                    const res = await fetch(`/dat-ve/seat-locks/${showtimeId}`);
                    if (!res.ok) return;

                    const data = await res.json();
                    lockedSeats = new Set(Object.keys(data.locked || {}).map(normalizeSeat));
                    syncButtonStates();
                } catch (e) {
                    console.error("Lỗi quét ghế đang giữ:", e);
                }
            }

            async function releaseSeats(seats) {
                for (const seat of seats) {
                    try {
                        await fetch(`/dat-ve/seat-locks/${showtimeId}/${encodeURIComponent(seat)}`, {
                            method: "DELETE",
                            headers: { "X-CSRF-TOKEN": csrf }
                        });
                    } catch (err) {
                        console.error("Lỗi hủy giữ ghế:", err);
                    }
                }
            }

            async function releaseAllSeats() {
                await releaseSeats(selectedSeats);
                selectedSeats = [];
                localStorage.removeItem(storageKey);
                updateUI();
            }

            // 🌟 GIẢI PHÁP TRIỆT ĐỂ: Tự động gửi request nhả ghế ngầm khi thoát / chuyển trang
            function releaseAllSeatsBeacon() {
                if (isProceedingToFood || selectedSeats.length === 0) return;

                const url = `/dat-ve/seat-locks/${showtimeId}/release-all`;
                const formData = new FormData();
                formData.append("_token", csrf);

                if (navigator.sendBeacon) {
                    navigator.sendBeacon(url, formData);
                } else {
                    fetch(url, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": csrf,
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({}),
                        keepalive: true
                    });
                }
                localStorage.removeItem(storageKey);
            }

            // 1. Nhả ghế khi người dùng Tắt Tab, Reload, hoặc rời trang
            window.addEventListener("pagehide", releaseAllSeatsBeacon);

            // 2. Nhả ghế khi người dùng bấm vào bất kỳ Link chuyển trang nào (Trang chủ, Đặt vé, Đổi suất chiếu...)
            document.addEventListener("click", function(e) {
                const link = e.target.closest("a");
                if (link && link.href) {
                    if (!link.href.includes("chon-do-an") && !isProceedingToFood) {
                        releaseAllSeatsBeacon();
                    }
                }
            });

            function updateCountdown(deadline) {
                const remain = deadline - Date.now();

                if (remain <= 0) {
                    clearInterval(countdownTimerInterval);
                    countdownTimerInterval = null;
                    if (countdownEl) countdownEl.innerText = "00:00";
                    releaseAllSeats();
                    window.location.href = "<?php echo e(route('home')); ?>";
                    return;
                }

                const minute = Math.floor(remain / 60000);
                const second = Math.floor((remain % 60000) / 1000);

                if (countdownEl) {
                    countdownEl.innerText = String(minute).padStart(2, "0") + ":" + String(second).padStart(2, "0");
                    countdownEl.classList.toggle("animate-pulse", remain <= 60000);
                }
            }

            function checkTimerState() {
                if (!countdownEl) return;

                let deadline = Number(localStorage.getItem(storageKey));
                const serverDeadline = Number(pendingDeadline) || null;
                const validStoredDeadline = deadline && deadline > Date.now() ? deadline : null;
                const validServerDeadline = serverDeadline && serverDeadline > Date.now() ? serverDeadline : null;

                if (validStoredDeadline && validServerDeadline) {
                    deadline = Math.min(validStoredDeadline, validServerDeadline);
                } else if (validServerDeadline) {
                    deadline = validServerDeadline;
                } else if (validStoredDeadline) {
                    deadline = validStoredDeadline;
                } else if (selectedSeats.length > 0) {
                    deadline = Date.now() + (7 * 60 * 1000);
                } else {
                    if (countdownTimerInterval) {
                        clearInterval(countdownTimerInterval);
                        countdownTimerInterval = null;
                    }

                    localStorage.removeItem(storageKey);
                    countdownEl.innerText = "07:00";
                    countdownEl.classList.remove("animate-pulse");
                    return;
                }

                localStorage.setItem(storageKey, deadline);

                if (!countdownTimerInterval) {
                    updateCountdown(deadline);
                    countdownTimerInterval = setInterval(() => updateCountdown(deadline), 1000);
                }
            }

            function validateSeatsAdjacentJS(seats) {
                if (seats.length <= 1) {
                    return true;
                }

                const parsedSeats = [];
                const seatRegex = /^([A-Z]+)([0-9]+)$/;

                for (const seat of seats) {
                    const upperSeat = String(seat || "").toUpperCase().trim();
                    const match = upperSeat.match(seatRegex);
                    if (match) {
                        parsedSeats.push({
                            row: match[1],
                            num: parseInt(match[2], 10)
                        });
                    } else {
                        if (upperSeat.includes('-') || upperSeat.includes('|')) {
                            const subSeats = upperSeat.split(/[-|]/);
                            for (const sub of subSeats) {
                                const subMatch = sub.trim().match(seatRegex);
                                if (subMatch) {
                                    parsedSeats.push({
                                        row: subMatch[1],
                                        num: parseInt(subMatch[2], 10)
                                    });
                                }
                            }
                        }
                    }
                }

                if (parsedSeats.length === 0) {
                    return true;
                }

                const grouped = {};
                for (const s of parsedSeats) {
                    if (!grouped[s.row]) {
                        grouped[s.row] = [];
                    }
                    grouped[s.row].push(s.num);
                }

                for (const row in grouped) {
                    const nums = [...new Set(grouped[row])].sort((a, b) => a - b);
                    
                    for (let i = 0; i < nums.length - 1; i++) {
                        const start = nums[i];
                        const end = nums[i + 1];
                        
                        if (end - start > 1) {
                            for (let middleNum = start + 1; middleNum < end; middleNum++) {
                                const middleSeatCode = `${row}${middleNum}`;
                                
                                const middleBtn = seatButtons.find(btn => {
                                    const codes = getSeatsFromButton(btn);
                                    return codes.includes(middleSeatCode);
                                });
                                
                                if (middleBtn) {
                                    const isBooked = middleBtn.classList.contains('booked');
                                    const isLocked = middleBtn.classList.contains('locked');
                                    const isMaintenance = middleBtn.classList.contains('maintenance');
                                    const isStaticDisabled = middleBtn.dataset.staticDisabled === "1";
                                    const isSelectedByMe = selectedSeats.includes(middleSeatCode);
                                    
                                    if (!isBooked && !isLocked && !isMaintenance && !isStaticDisabled && !isSelectedByMe) {
                                        return false;
                                    }
                                }
                            }
                        }
                    }
                }

                return true;
            }

            function showSeatErrorJS(message) {
                let alertEl = document.querySelector(".booking-seat-alert");
                if (!alertEl) {
                    alertEl = document.createElement("div");
                    alertEl.className = "booking-seat-alert";
                    alertEl.style.cssText = "background: rgba(239, 68, 68, 0.15) !important; border: 1px solid #ef4444 !important; color: #f87171 !important; padding: 16px !important; border-radius: 12px !important; margin: 15px 0 !important; display: flex !important; align-items: center !important; gap: 12px !important; font-weight: 600 !important; font-size: 14px !important; position: relative !important; z-index: 99 !important;";
                    
                    const toolbar = document.querySelector(".booking-seat-toolbar");
                    if (toolbar) {
                        toolbar.parentNode.insertBefore(alertEl, toolbar.nextSibling);
                    }
                }
                alertEl.innerHTML = `
                    <i class="fa-solid fa-circle-exclamation" style="color: #ef4444 !important; font-size: 18px !important; margin-right: 8px;"></i>
                    <span>${message}</span>
                `;
                alertEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            function clearSeatErrorJS() {
                const alertEl = document.querySelector(".booking-seat-alert");
                if (alertEl) {
                    alertEl.remove();
                }
            }

            function updateUI() {
                selectedSeats = Array.from(new Set(selectedSeats.map(normalizeSeat).filter(Boolean)));
                syncButtonStates();

                if (validateSeatsAdjacentJS(selectedSeats)) {
                    clearSeatErrorJS();
                }

                if (seatCountEl) {
                    seatCountEl.innerText = selectedSeats.length + " ghế";
                }

                if (seatLabels) {
                    seatLabels.innerText = selectedSeats.length ? selectedSeats.join(", ") : "—";
                }

                if (selectedList) {
                    if (selectedSeats.length === 0) {
                        selectedList.innerHTML = '<span class="booking-seat-empty-selection">Chưa chọn ghế nào</span>';
                    } else {
                        selectedList.innerHTML = selectedSeats.map(seat => `
                            <button type="button" data-remove-seat="${seat}" class="booking-seat-chip" aria-label="Bỏ ghế ${seat}">
                                ${seat}
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        `).join("");
                    }
                }

                let total = 0;
                seatButtons.forEach(btn => {
                    if (buttonIsSelected(btn)) {
                        total += Number(btn.dataset.price || 0);
                    }
                });

                if (totalPriceEl) {
                    totalPriceEl.innerText = money(total);
                }

                if (btnFood) {
                    const hasSeat = selectedSeats.length > 0;
                    btnFood.disabled = !hasSeat;
                    btnFood.classList.toggle("is-enabled", hasSeat);

                    if (btnResetSeats) {
                        btnResetSeats.disabled = !hasSeat;
                        btnResetSeats.classList.toggle("is-disabled", !hasSeat);
                    }
                }

                checkTimerState();
            }

            seatButtons.forEach(btn => {
                btn.addEventListener("click", async function() {
                    if (btn.disabled || btn.dataset.staticDisabled === "1") return;

                    const codes = getSeatsFromButton(btn);
                    const isSelected = codes.every(code => selectedSeats.includes(code));
                    const previousSelectedSeats = [...selectedSeats];

                    if (isSelected) {
                        selectedSeats = selectedSeats.filter(seat => !codes.includes(seat));
                        updateUI();
                        await releaseSeats(codes);
                    } else {
                        const newCodes = codes.filter(code => !selectedSeats.includes(code));
                        if (selectedSeats.length + newCodes.length > maxSeatCount) {
                            alert("Bạn chỉ có thể chọn tối đa " + maxSeatCount + " ghế trong một lần đặt.");
                            return;
                        }

                        const reservedNow = [];
                        selectedSeats = Array.from(new Set([...selectedSeats, ...codes]));
                        updateUI();

                        for (const seat of codes) {
                            try {
                                const res = await fetch(`/dat-ve/seat-locks/${showtimeId}/${encodeURIComponent(seat)}`, {
                                    method: "POST",
                                    headers: { "X-CSRF-TOKEN": csrf }
                                });

                                if (!res.ok) {
                                    throw new Error("seat_locked");
                                }

                                reservedNow.push(seat);
                            } catch (e) {
                                alert("Ghế " + seat + " vừa được người khác chọn.");
                                await releaseSeats(reservedNow);
                                selectedSeats = previousSelectedSeats;
                                updateUI();
                                await loadLockedSeats();
                                return;
                            }
                        }
                    }

                    updateUI();
                    await loadLockedSeats();
                });
            });

            if (selectedList) {
                selectedList.addEventListener("click", async function(e) {
                    const removeBtn = e.target.closest("[data-remove-seat]");
                    if (!removeBtn) return;

                    const seat = normalizeSeat(removeBtn.dataset.removeSeat);
                    const ownerButton = seatButtons.find(btn => getSeatsFromButton(btn).includes(seat));
                    const seatsToRemove = ownerButton ? getSeatsFromButton(ownerButton) : [seat];

                    selectedSeats = selectedSeats.filter(value => !seatsToRemove.includes(value));
                    updateUI();
                    await releaseSeats(seatsToRemove);
                    await loadLockedSeats();
                });
            }

            if (btnFood) {
                btnFood.addEventListener("click", function(e) {
                    e.preventDefault();
                    if (selectedSeats.length === 0) return;

                    if (!validateSeatsAdjacentJS(selectedSeats)) {
                        showSeatErrorJS("Các ghế bạn chọn phải cạnh nhau trong cùng một hàng!");
                        return;
                    }

                    isProceedingToFood = true; // Bật cờ không giải phóng ghế khi chuyển hướng tiếp

                    const seats = encodeURIComponent(selectedSeats.join(","));
                    let url = `<?php echo e(route('dat_ve.chon_do_an', ['suat_chieu_id' => $suatChieu->id])); ?>?ghe=${seats}`;
                    if (pendingTicketId) {
                        url += `&pending_ticket_id=${encodeURIComponent(pendingTicketId)}`;
                    }

                    window.location.href = url;
                });
            }

            if (btnResetSeats) {
                btnResetSeats.addEventListener("click", async function() {
                    if (selectedSeats.length === 0) return;
                    await releaseAllSeats();
                    clearSeatErrorJS();
                    if (countdownEl) {
                        countdownEl.innerText = "07:00";
                    }
                    alert('Đã hủy chọn ghế cũ. Vui lòng chọn ghế mới.');
                });
            }

            updateUI();
            loadLockedSeats();
            setInterval(loadLockedSeats, 3000);
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/user/dat_ve/chon_ghe.blade.php ENDPATH**/ ?>