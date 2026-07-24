<?php $__env->startSection('title', 'Khuyến mãi & Voucher'); ?>
<?php $__env->startSection('page-title', 'Khuyến mãi & Voucher'); ?>
<?php $__env->startSection('page-subtitle', 'Thiết lập voucher mẫu, điểm đổi, hạn dùng và cấp ưu đãi trực tiếp cho khách hàng'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $activeFilters = collect([
        request('q'),
        request('loai_voucher'),
        request('trang_thai'),
    ])->filter()->count();

    $issuedFilters = collect([
        request('issued_q'),
        request('issued_status'),
    ])->filter()->count();

    $typeIcons = [
        'giam_gia_ve' => 'fa-ticket',
        'giam_gia_do_an' => 'fa-burger',
        'giam_gia_ghe_vip' => 'fa-couch',
        'sinh_nhat' => 'fa-cake-candles',
        'khach_hang_than_thiet' => 'fa-crown',
    ];
?>

<div class="voucher-admin-page">
    <?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <section class="voucher-hero">
        <div>
            <span class="voucher-eyebrow">
                <i class="fa-solid fa-gift"></i>
                Ưu đãi khách hàng
            </span>
            <h2>Quản lý khuyến mãi & voucher</h2>
            <p>Tạo voucher mẫu, cấu hình điểm đổi, cấp mã cá nhân cho khách và theo dõi trạng thái sử dụng.</p>
        </div>
    </section>

    <section class="voucher-stats">
        <div class="voucher-stat">
            <span>Voucher mẫu</span>
            <strong><?php echo e($summary['total']); ?></strong>
        </div>
        <div class="voucher-stat is-good">
            <span>Đang hiệu lực</span>
            <strong><?php echo e($summary['active']); ?></strong>
        </div>
        <div class="voucher-stat is-warn">
            <span>Hết hạn</span>
            <strong><?php echo e($summary['expired']); ?></strong>
        </div>
        <div class="voucher-stat">
            <span>Đã cấp</span>
            <strong><?php echo e($summary['issued']); ?></strong>
        </div>
        <div class="voucher-stat is-muted">
            <span>Đã dùng</span>
            <strong><?php echo e($summary['used']); ?></strong>
        </div>
    </section>

    <div class="voucher-workspace">
        <aside class="voucher-sidebar">
            <form method="POST" action="<?php echo e(route('admin.vouchers.store')); ?>" class="voucher-panel">
                <?php echo csrf_field(); ?>
                <div class="voucher-panel-head">
                    <div>
                        <span class="voucher-eyebrow">Tạo mẫu</span>
                        <h3>Voucher mới</h3>
                        <p>Dùng cho đổi điểm hoặc cấp thủ công.</p>
                    </div>
                    <span class="voucher-panel-icon"><i class="fa-solid fa-ticket-simple"></i></span>
                </div>

                <div class="voucher-form-grid">
                    <label class="voucher-field is-wide">
                        <span>Mã voucher</span>
                        <input name="ma_voucher" value="<?php echo e(old('ma_voucher')); ?>" class="admin-input" placeholder="VD: FOOD20K" required>
                    </label>
                    <label class="voucher-field is-wide">
                        <span>Tên chương trình</span>
                        <input name="ten_voucher" value="<?php echo e(old('ten_voucher')); ?>" class="admin-input" placeholder="VD: Giảm 20.000đ combo bắp nước" required>
                    </label>
                    <label class="voucher-field is-wide">
                        <span>Loại voucher</span>
                        <select name="loai_voucher" class="admin-input" required>
                            <?php $__currentLoopData = $voucherTypeLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>" <?php if(old('loai_voucher', 'giam_gia_ve') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </label>
                    <label class="voucher-field">
                        <span>Giá trị giảm</span>
                        <input name="gia_tri_giam" type="number" min="0" value="<?php echo e(old('gia_tri_giam', 0)); ?>" class="admin-input" placeholder="0" required>
                    </label>
                    <label class="voucher-field">
                        <span>Điểm đổi</span>
                        <input name="diem_can_doi" type="number" min="0" value="<?php echo e(old('diem_can_doi', 0)); ?>" class="admin-input" placeholder="0" required>
                    </label>
                    <label class="voucher-field is-wide">
                        <span>Hạn dùng</span>
                        <input name="ngay_het_han" type="date" value="<?php echo e(old('ngay_het_han', now()->addMonth()->format('Y-m-d'))); ?>" class="admin-input" required>
                    </label>
                    <label class="voucher-switch is-wide">
                        <input type="checkbox" name="trang_thai" value="1" checked>
                        <span></span>
                        <div>
                            <strong>Bật voucher ngay</strong>
                            <small>Khách có thể đổi hoặc admin có thể cấp mã.</small>
                        </div>
                    </label>
                </div>

                <button type="submit" class="voucher-primary-btn">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Lưu voucher
                </button>
            </form>

            <form method="POST" action="<?php echo e(route('admin.vouchers.issue')); ?>" class="voucher-panel">
                <?php echo csrf_field(); ?>
                <div class="voucher-panel-head">
                    <div>
                        <span class="voucher-eyebrow">Cấp phát</span>
                        <h3>Tặng voucher</h3>
                        <p>Cấp voucher cá nhân, không trừ điểm khách hàng.</p>
                    </div>
                    <span class="voucher-panel-icon"><i class="fa-solid fa-hand-holding-heart"></i></span>
                </div>

                <div class="voucher-form-grid">
                    <label class="voucher-field is-wide">
                        <span>Voucher mẫu</span>
                        <select name="voucher_id" class="admin-input" required>
                            <option value="">Chọn voucher đang hiệu lực</option>
                            <?php $__currentLoopData = $activeVouchers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voucher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($voucher->id); ?>" <?php if(old('voucher_id') == $voucher->id): echo 'selected'; endif; ?>>
                                    <?php echo e($voucher->ma_voucher); ?> - <?php echo e($voucher->ten_voucher); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </label>
                    <label class="voucher-field is-wide">
                        <span>Khách hàng</span>
                        <select name="nguoi_dung_id" class="admin-input" required>
                            <option value="">Chọn khách hàng</option>
                            <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($customer->id); ?>" <?php if(old('nguoi_dung_id') == $customer->id): echo 'selected'; endif; ?>>
                                    <?php echo e($customer->ho_ten); ?> - <?php echo e($customer->email); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </label>
                    <label class="voucher-field">
                        <span>Số lượng</span>
                        <input name="quantity" type="number" min="1" max="20" value="<?php echo e(old('quantity', 1)); ?>" class="admin-input" required>
                    </label>
                    <label class="voucher-field">
                        <span>Hạn riêng</span>
                        <input name="ngay_het_han" type="date" value="<?php echo e(old('ngay_het_han')); ?>" class="admin-input">
                    </label>
                    <label class="voucher-field is-wide">
                        <span>Lý do cấp</span>
                        <select name="loai_cap_phat" class="admin-input" required>
                            <option value="admin_tang" <?php if(old('loai_cap_phat', 'admin_tang') === 'admin_tang'): echo 'selected'; endif; ?>>Admin tặng</option>
                            <option value="khach_hang_than_thiet" <?php if(old('loai_cap_phat') === 'khach_hang_than_thiet'): echo 'selected'; endif; ?>>Khách hàng thân thiết</option>
                        </select>
                    </label>
                </div>

                <button type="submit" class="voucher-primary-btn">
                    <i class="fa-solid fa-gift"></i>
                    Cấp voucher
                </button>
            </form>
        </aside>

        <main class="voucher-main">
            <section class="voucher-panel">
                <div class="voucher-panel-head">
                    <div>
                        <span class="voucher-eyebrow">Voucher mẫu</span>
                        <h3>Danh sách chương trình</h3>
                        <p><?php echo e($vouchers->total()); ?> voucher theo bộ lọc hiện tại.</p>
                    </div>
                </div>

                <form method="GET" action="<?php echo e(route('admin.vouchers.index')); ?>" class="voucher-filter">
                    <label class="voucher-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input name="q" value="<?php echo e(request('q')); ?>" placeholder="Tìm mã hoặc tên voucher...">
                    </label>
                    <select name="loai_voucher" class="admin-input">
                        <option value="">Tất cả loại</option>
                        <?php $__currentLoopData = $voucherTypeLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>" <?php if(request('loai_voucher') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <select name="trang_thai" class="admin-input">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" <?php if(request('trang_thai') === 'active'): echo 'selected'; endif; ?>>Đang hiệu lực</option>
                        <option value="inactive" <?php if(request('trang_thai') === 'inactive'): echo 'selected'; endif; ?>>Đang tắt</option>
                        <option value="expired" <?php if(request('trang_thai') === 'expired'): echo 'selected'; endif; ?>>Hết hạn</option>
                    </select>
                    <button class="voucher-filter-btn" type="submit">
                        <i class="fa-solid fa-filter"></i>
                        Lọc
                        <?php if($activeFilters): ?>
                            <span><?php echo e($activeFilters); ?></span>
                        <?php endif; ?>
                    </button>
                    <?php if($activeFilters): ?>
                        <a href="<?php echo e(route('admin.vouchers.index')); ?>" class="voucher-reset-btn" title="Xóa bộ lọc">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    <?php endif; ?>
                </form>

                <div class="voucher-list">
                    <?php $__empty_1 = true; $__currentLoopData = $vouchers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voucher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $expired = $voucher->ngay_het_han->lt(today());
                            $statusClass = $expired ? 'is-expired' : ($voucher->trang_thai ? 'is-active' : 'is-inactive');
                            $statusLabel = $expired ? 'Hết hạn' : ($voucher->trang_thai ? 'Đang hiệu lực' : 'Đang tắt');
                            $typeIcon = $typeIcons[$voucher->loai_voucher] ?? 'fa-ticket';
                        ?>

                        <article class="voucher-card">
                            <div class="voucher-card-top">
                                <div class="voucher-code-badge">
                                    <i class="fa-solid <?php echo e($typeIcon); ?>"></i>
                                    <strong><?php echo e($voucher->ma_voucher); ?></strong>
                                </div>
                                <span class="voucher-status <?php echo e($statusClass); ?>"><?php echo e($statusLabel); ?></span>
                            </div>

                            <div class="voucher-card-body">
                                <div class="voucher-card-title">
                                    <span><?php echo e($voucherTypeLabels[$voucher->loai_voucher] ?? $voucher->loai_voucher); ?></span>
                                    <h3><?php echo e($voucher->ten_voucher); ?></h3>
                                </div>

                                <div class="voucher-metrics">
                                    <div>
                                        <span>Giảm</span>
                                        <strong><?php echo e(number_format((float) $voucher->gia_tri_giam, 0, ',', '.')); ?>đ</strong>
                                    </div>
                                    <div>
                                        <span>Điểm đổi</span>
                                        <strong><?php echo e(number_format($voucher->diem_can_doi)); ?></strong>
                                    </div>
                                    <div>
                                        <span>Đã cấp</span>
                                        <strong><?php echo e($voucher->nguoi_dung_vouchers_count); ?></strong>
                                    </div>
                                    <div>
                                        <span>Đã dùng</span>
                                        <strong><?php echo e($voucher->used_count); ?></strong>
                                    </div>
                                </div>

                                <div class="voucher-expire">
                                    <i class="fa-solid fa-calendar-day"></i>
                                    Hết hạn <?php echo e($voucher->ngay_het_han->format('d/m/Y')); ?>

                                </div>
                            </div>

                            <div class="voucher-card-actions">
                                <form method="POST" action="<?php echo e(route('admin.vouchers.toggle-status', $voucher)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <button class="voucher-soft-btn" type="submit">
                                        <i class="fa-solid <?php echo e($voucher->trang_thai ? 'fa-toggle-off' : 'fa-toggle-on'); ?>"></i>
                                        <?php echo e($voucher->trang_thai ? 'Tắt' : 'Bật'); ?>

                                    </button>
                                </form>

                                <form method="POST" action="<?php echo e(route('admin.vouchers.destroy', $voucher)); ?>" onsubmit="return confirm('Xóa voucher <?php echo e($voucher->ma_voucher); ?>?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button class="voucher-delete-btn" type="submit" <?php if($voucher->nguoi_dung_vouchers_count > 0): echo 'disabled'; endif; ?>>
                                        <i class="fa-solid fa-trash"></i>
                                        Xóa
                                    </button>
                                </form>
                            </div>

                            <details class="voucher-edit-box">
                                <summary>
                                    <span>Sửa voucher</span>
                                    <i class="fa-solid fa-chevron-down"></i>
                                </summary>

                                <form method="POST" action="<?php echo e(route('admin.vouchers.update', $voucher)); ?>" class="voucher-edit-form">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <label class="voucher-field">
                                        <span>Mã voucher</span>
                                        <input name="ma_voucher" value="<?php echo e(old('ma_voucher', $voucher->ma_voucher)); ?>" class="admin-input" required>
                                    </label>
                                    <label class="voucher-field">
                                        <span>Tên voucher</span>
                                        <input name="ten_voucher" value="<?php echo e(old('ten_voucher', $voucher->ten_voucher)); ?>" class="admin-input" required>
                                    </label>
                                    <label class="voucher-field">
                                        <span>Loại voucher</span>
                                        <select name="loai_voucher" class="admin-input">
                                            <?php $__currentLoopData = $voucherTypeLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($value); ?>" <?php if(old('loai_voucher', $voucher->loai_voucher) === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </label>
                                    <label class="voucher-field">
                                        <span>Hạn dùng</span>
                                        <input name="ngay_het_han" type="date" value="<?php echo e(old('ngay_het_han', $voucher->ngay_het_han->format('Y-m-d'))); ?>" class="admin-input" required>
                                    </label>
                                    <label class="voucher-field">
                                        <span>Giá trị giảm</span>
                                        <input name="gia_tri_giam" type="number" min="0" value="<?php echo e(old('gia_tri_giam', (float) $voucher->gia_tri_giam)); ?>" class="admin-input" required>
                                    </label>
                                    <label class="voucher-field">
                                        <span>Điểm cần đổi</span>
                                        <input name="diem_can_doi" type="number" min="0" value="<?php echo e(old('diem_can_doi', $voucher->diem_can_doi)); ?>" class="admin-input" required>
                                    </label>
                                    <label class="voucher-switch is-wide">
                                        <input type="checkbox" name="trang_thai" value="1" <?php if($voucher->trang_thai): echo 'checked'; endif; ?>>
                                        <span></span>
                                        <div>
                                            <strong>Bật voucher</strong>
                                            <small>Cho phép khách đổi hoặc admin cấp mã.</small>
                                        </div>
                                    </label>
                                    <button class="voucher-primary-btn is-wide" type="submit">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        Cập nhật voucher
                                    </button>
                                </form>
                            </details>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="voucher-empty">
                            <i class="fa-solid fa-ticket-simple"></i>
                            <h3>Chưa có voucher phù hợp</h3>
                            <p>Thử đổi bộ lọc hoặc tạo một voucher mẫu mới ở khung bên trái.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="voucher-pagination">
                    <?php echo e($vouchers->links()); ?>

                </div>
            </section>

            <section class="voucher-panel">
                <div class="voucher-panel-head">
                    <div>
                        <span class="voucher-eyebrow">Đã cấp</span>
                        <h3>Voucher cá nhân</h3>
                        <p>Theo dõi mã cá nhân và thu hồi khi khách chưa sử dụng.</p>
                    </div>
                </div>

                <form method="GET" action="<?php echo e(route('admin.vouchers.index')); ?>" class="voucher-issued-filter">
                    <label class="voucher-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input name="issued_q" value="<?php echo e(request('issued_q')); ?>" placeholder="Tìm mã cá nhân, khách hàng...">
                    </label>
                    <select name="issued_status" class="admin-input">
                        <option value="">Tất cả trạng thái</option>
                        <option value="unused" <?php if(request('issued_status') === 'unused'): echo 'selected'; endif; ?>>Chưa dùng</option>
                        <option value="used" <?php if(request('issued_status') === 'used'): echo 'selected'; endif; ?>>Đã dùng</option>
                        <option value="expired" <?php if(request('issued_status') === 'expired'): echo 'selected'; endif; ?>>Hết hạn</option>
                    </select>
                    <button class="voucher-filter-btn" type="submit">
                        <i class="fa-solid fa-filter"></i>
                        Lọc
                        <?php if($issuedFilters): ?>
                            <span><?php echo e($issuedFilters); ?></span>
                        <?php endif; ?>
                    </button>
                </form>

                <div class="issued-voucher-list">
                    <?php $__empty_1 = true; $__currentLoopData = $issuedVouchers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $issuedExpired = $item->ngay_het_han && $item->ngay_het_han->lt(now());
                            $issuedStatusClass = $item->da_su_dung ? 'is-used' : ($issuedExpired ? 'is-expired' : 'is-unused');
                            $issuedStatusLabel = $item->da_su_dung ? 'Đã dùng' : ($issuedExpired ? 'Hết hạn' : 'Chưa dùng');
                        ?>

                        <article class="issued-voucher-card">
                            <div>
                                <span>Mã cá nhân</span>
                                <strong><?php echo e($item->ma_voucher_ca_nhan); ?></strong>
                                <small>Nhận <?php echo e($item->ngay_nhan?->format('d/m/Y H:i')); ?></small>
                            </div>
                            <div>
                                <span>Khách hàng</span>
                                <strong><?php echo e($item->nguoiDung?->ho_ten ?? 'Không rõ'); ?></strong>
                                <small><?php echo e($item->nguoiDung?->email); ?></small>
                            </div>
                            <div>
                                <span>Voucher mẫu</span>
                                <strong><?php echo e($item->voucher?->ma_voucher ?? 'Không rõ'); ?></strong>
                                <small><?php echo e($item->voucher?->ten_voucher); ?></small>
                            </div>
                            <div>
                                <span>Hạn dùng</span>
                                <strong><?php echo e($item->ngay_het_han?->format('d/m/Y') ?? 'Theo voucher mẫu'); ?></strong>
                            </div>
                            <span class="issued-status <?php echo e($issuedStatusClass); ?>"><?php echo e($issuedStatusLabel); ?></span>

                            <?php if(! $item->da_su_dung): ?>
                                <form method="POST" action="<?php echo e(route('admin.vouchers.issued.destroy', $item)); ?>" onsubmit="return confirm('Thu hồi voucher <?php echo e($item->ma_voucher_ca_nhan); ?>?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button class="voucher-delete-btn" type="submit">
                                        <i class="fa-solid fa-rotate-left"></i>
                                        Thu hồi
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="voucher-no-action">Đã sử dụng</span>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="voucher-empty">
                            <i class="fa-solid fa-user-tag"></i>
                            <h3>Chưa có voucher cá nhân phù hợp</h3>
                            <p>Thử đổi bộ lọc hoặc cấp voucher cho khách hàng ở khung bên trái.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="voucher-pagination">
                    <?php echo e($issuedVouchers->links()); ?>

                </div>
            </section>
        </main>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\DATN\WD-11-Cinehome-cinema\resources\views/admin/vouchers/index.blade.php ENDPATH**/ ?>