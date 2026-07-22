<?php $__env->startSection('title', 'Quản lý vé - CineHome'); ?>
<?php $__env->startSection('page-title', 'Quản lý vé'); ?>
<?php $__env->startSection('page-subtitle', 'Theo dõi vé online, vé tại quầy và trạng thái sử dụng vé'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $summary = $summary ?? [
            'total' => $totalTickets ?? 0,
            'online' => $onlineTickets ?? 0,
            'counter' => $counterTickets ?? 0,
            'paid' => $paidTickets ?? 0,
            'used' => $usedTickets ?? 0,
            'cancelled' => $cancelledTickets ?? 0,
            'revenue' => 0,
        ];

        $statusLabels = [
            'da_thanh_toan' => 'Đã thanh toán',
            'da_su_dung' => 'Đã sử dụng',
            'da_huy' => 'Đã hủy',
        ];

        $typeLabels = [
            'truc_tuyen' => 'Online',
            'tai_quay' => 'Tại quầy',
        ];
    ?>

    <div class="ticket-page">
        <?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <section class="ticket-hero">
            <div class="ticket-hero-copy">
                <span class="ticket-kicker">
                    <i class="fa-solid fa-ticket"></i>
                    Trung tâm quản lý vé
                </span>
                <h1>Quản lý vé xem phim</h1>
                <p>
                    Tra cứu vé, kiểm tra suất chiếu, cập nhật trạng thái sử dụng và xử lý hủy vé trong một màn hình.
                    Giao diện được tối ưu để nhân sự vận hành đọc nhanh, thao tác chắc tay hơn.
                </p>

                <div class="ticket-hero-metrics">
                    <span><i class="fa-solid fa-sack-dollar"></i> <?php echo e(number_format((float) $summary['revenue'], 0, ',', '.')); ?>đ doanh thu hợp lệ</span>
                    <span><i class="fa-solid fa-filter"></i> <?php echo e(number_format($tickets->total())); ?> kết quả đang hiển thị</span>
                </div>
            </div>

            <div class="ticket-hero-actions">
                <a href="<?php echo e(route('admin.soat-ve.index')); ?>" class="movie-action-btn is-soft">
                    <i class="fa-solid fa-qrcode"></i>
                    Soát vé QR
                </a>
                <a href="<?php echo e(route('admin.ve-xem-phims.index')); ?>" class="movie-action-btn is-ghost">
                    <i class="fa-solid fa-rotate-left"></i>
                    Làm mới
                </a>
            </div>
        </section>

        <section class="ticket-stat-grid" aria-label="Thống kê vé">
            <article class="ticket-stat-card accent-red">
                <span class="ticket-stat-icon"><i class="fa-solid fa-ticket"></i></span>
                <div>
                    <small>Tổng vé</small>
                    <strong><?php echo e(number_format($summary['total'])); ?></strong>
                </div>
            </article>

            <article class="ticket-stat-card accent-green">
                <span class="ticket-stat-icon"><i class="fa-solid fa-circle-check"></i></span>
                <div>
                    <small>Đã thanh toán</small>
                    <strong><?php echo e(number_format($summary['paid'])); ?></strong>
                </div>
            </article>

            <article class="ticket-stat-card accent-blue">
                <span class="ticket-stat-icon"><i class="fa-solid fa-door-open"></i></span>
                <div>
                    <small>Đã sử dụng</small>
                    <strong><?php echo e(number_format($summary['used'])); ?></strong>
                </div>
            </article>

            <article class="ticket-stat-card accent-gold">
                <span class="ticket-stat-icon"><i class="fa-solid fa-globe"></i></span>
                <div>
                    <small>Online</small>
                    <strong><?php echo e(number_format($summary['online'])); ?></strong>
                </div>
            </article>

            <article class="ticket-stat-card accent-purple">
                <span class="ticket-stat-icon"><i class="fa-solid fa-store"></i></span>
                <div>
                    <small>Tại quầy</small>
                    <strong><?php echo e(number_format($summary['counter'])); ?></strong>
                </div>
            </article>

            <article class="ticket-stat-card accent-neutral">
                <span class="ticket-stat-icon"><i class="fa-solid fa-ban"></i></span>
                <div>
                    <small>Đã hủy</small>
                    <strong><?php echo e(number_format($summary['cancelled'])); ?></strong>
                </div>
            </article>
        </section>

        <section class="ticket-panel">
            <div class="ticket-panel-header">
                <div>
                    <span class="ticket-kicker">
                        <i class="fa-solid fa-list-check"></i>
                        Danh sách vận hành
                    </span>
                    <h2>Danh sách vé xem phim</h2>
                    <p>Tra cứu theo mã vé, phim, rạp, phòng hoặc ghế và cập nhật trạng thái ngay trên từng dòng.</p>
                </div>
            </div>

            <form method="GET" action="<?php echo e(route('admin.ve-xem-phims.index')); ?>" class="ticket-filter">
                <label class="ticket-filter-field is-search">
                    <span>Tìm kiếm</span>
                    <div class="ticket-filter-control">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input
                            type="text"
                            name="tim_kiem"
                            value="<?php echo e(request('tim_kiem')); ?>"
                            placeholder="Mã vé, tên phim, rạp, phòng, ghế..."
                        >
                    </div>
                </label>

                <label class="ticket-filter-field">
                    <span>Trạng thái</span>
                    <div class="ticket-filter-control">
                        <i class="fa-solid fa-sliders"></i>
                        <select name="trang_thai">
                            <option value="">Tất cả trạng thái</option>
                            <?php $__currentLoopData = $statusLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>" <?php if(request('trang_thai') === $value): echo 'selected'; endif; ?>>
                                    <?php echo e($label); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </label>

                <label class="ticket-filter-field">
                    <span>Loại vé</span>
                    <div class="ticket-filter-control">
                        <i class="fa-solid fa-layer-group"></i>
                        <select name="loai_ve">
                            <option value="">Tất cả loại vé</option>
                            <?php $__currentLoopData = $typeLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>" <?php if(request('loai_ve') === $value): echo 'selected'; endif; ?>>
                                    <?php echo e($label); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </label>

                <div class="ticket-filter-actions">
                    <button type="submit" class="movie-action-btn is-primary">
                        <i class="fa-solid fa-filter"></i>
                        Lọc vé
                    </button>
                    <a href="<?php echo e(route('admin.ve-xem-phims.index')); ?>" class="movie-action-btn is-soft">
                        <i class="fa-solid fa-rotate-left"></i>
                        Đặt lại
                    </a>
                </div>
            </form>

            <div class="ticket-table-wrap">
                <table class="ticket-table">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Mã vé</th>
                            <th>Phim & khách</th>
                            <th>Ghế</th>
                            <th>Suất chiếu</th>
                            <th>Loại</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th class="is-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $buyerName = $ticket->nguoiDung?->ho_ten
                                    ?? $ticket->nguoiDung?->name
                                    ?? $ticket->nguoiDung?->email
                                    ?? 'Khách vãng lai';
                                $sellerName = $ticket->nhanVien?->ho_ten
                                    ?? $ticket->nhanVien?->name
                                    ?? $ticket->nhanVien?->email;
                            ?>

                            <tr>
                                <td class="ticket-index">#<?php echo e(($tickets->firstItem() ?? 0) + $key); ?></td>
                                <td>
                                    <span class="ticket-code"><?php echo e($ticket->ma_ve); ?></span>
                                </td>
                                <td>
                                    <div class="ticket-movie">
                                        <strong><?php echo e($ticket->ten_phim ?? 'Chưa có tên phim'); ?></strong>
                                        <small>
                                            <i class="fa-solid fa-user"></i>
                                            <?php echo e($buyerName); ?>

                                        </small>
                                        <small>
                                            <i class="fa-solid fa-location-dot"></i>
                                            <?php echo e($ticket->ten_rap ?? 'Chưa có rạp'); ?> · Phòng <?php echo e($ticket->ten_phong ?? '--'); ?>

                                        </small>
                                        <?php if($sellerName): ?>
                                            <small>
                                                <i class="fa-solid fa-user-tie"></i>
                                                Nhân viên: <?php echo e($sellerName); ?>

                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="ticket-seat">
                                        <i class="fa-solid fa-couch"></i>
                                        <?php echo e($ticket->ma_ghe ?? '-'); ?>

                                    </span>
                                </td>
                                <td>
                                    <span class="ticket-time">
                                        <i class="fa-regular fa-clock"></i>
                                        <?php echo e($ticket->thoi_gian_chieu?->format('d/m/Y H:i') ?? '-'); ?>

                                    </span>
                                </td>
                                <td>
                                    <span class="ticket-type is-<?php echo e($ticket->loai_ve === 'tai_quay' ? 'counter' : 'online'); ?>">
                                        <i class="fa-solid <?php echo e($ticket->loai_ve === 'tai_quay' ? 'fa-store' : 'fa-globe'); ?>"></i>
                                        <?php echo e($typeLabels[$ticket->loai_ve] ?? 'Không rõ'); ?>

                                    </span>
                                </td>
                                <td>
                                    <strong class="ticket-money">
                                        <?php echo e(number_format((float) $ticket->tong_tien, 0, ',', '.')); ?>đ
                                    </strong>
                                </td>
                                <td>
                                    <form method="POST" action="<?php echo e(route('admin.ve-xem-phims.cap-nhat-trang-thai', $ticket)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>

                                        <select
                                            name="trang_thai"
                                            onchange="confirmTicketStatus(this)"
                                            data-current="<?php echo e($ticket->trang_thai); ?>"
                                            class="ticket-status-select status-<?php echo e($ticket->trang_thai); ?>"
                                            aria-label="Cập nhật trạng thái vé <?php echo e($ticket->ma_ve); ?>"
                                        >
                                            <?php $__currentLoopData = $statusLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($value); ?>" <?php if($ticket->trang_thai === $value): echo 'selected'; endif; ?>>
                                                    <?php echo e($label); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <div class="ticket-actions">
                                        <a
                                            href="<?php echo e(route('admin.ve-xem-phims.show', $ticket)); ?>"
                                            class="ticket-action-btn view"
                                            title="Xem chi tiết"
                                            aria-label="Xem chi tiết vé <?php echo e($ticket->ma_ve); ?>"
                                        >
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        <?php if($ticket->trang_thai === 'da_thanh_toan'): ?>
                                            <form method="POST" action="<?php echo e(route('admin.ve-xem-phims.su-dung', $ticket)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <button
                                                    type="submit"
                                                    onclick="return confirm('Xác nhận vé này đã sử dụng?')"
                                                    class="ticket-action-btn success"
                                                    title="Đánh dấu đã sử dụng"
                                                    aria-label="Đánh dấu vé <?php echo e($ticket->ma_ve); ?> đã sử dụng"
                                                >
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                            </form>

                                            <form method="POST" action="<?php echo e(route('admin.ve-xem-phims.huy', $ticket)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <button
                                                    type="submit"
                                                    onclick="return confirm('Bạn có chắc muốn hủy vé này?')"
                                                    class="ticket-action-btn danger"
                                                    title="Hủy vé"
                                                    aria-label="Hủy vé <?php echo e($ticket->ma_ve); ?>"
                                                >
                                                    <i class="fa-solid fa-ban"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="ticket-action-note">Đã khóa</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="9">
                                    <div class="ticket-empty">
                                        <span><i class="fa-solid fa-ticket"></i></span>
                                        <h3>Chưa có vé phù hợp</h3>
                                        <p>Thử đổi từ khóa, bỏ bộ lọc hoặc kiểm tra lại trạng thái vé.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="ticket-pagination">
                <?php echo $__env->make('components.admin-pagination', ['paginator' => $tickets], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmTicketStatus(select) {
            const oldValue = select.dataset.current;
            const newValue = select.value;

            if (oldValue === newValue) {
                return;
            }

            const statusMap = {
                da_thanh_toan: 'Đã thanh toán',
                da_su_dung: 'Đã sử dụng',
                da_huy: 'Đã hủy'
            };

            const submitChange = () => {
                select.dataset.current = newValue;
                select.form.submit();
            };

            if (window.Swal) {
                Swal.fire({
                    title: 'Xác nhận cập nhật',
                    text: `Bạn có chắc muốn chuyển vé sang "${statusMap[newValue] || newValue}" không?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Xác nhận',
                    cancelButtonText: 'Hủy',
                    confirmButtonColor: '#ff2f45',
                    cancelButtonColor: '#6b7280',
                    background: '#151923',
                    color: '#ffffff'
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitChange();
                    } else {
                        select.value = oldValue;
                    }
                });
            } else if (confirm(`Chuyển vé sang "${statusMap[newValue] || newValue}"?`)) {
                submitChange();
            } else {
                select.value = oldValue;
            }
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/admin/ve-xem-phims/index.blade.php ENDPATH**/ ?>