<?php $__env->startSection('title', 'Chi tiết vé - CineHome'); ?>
<?php $__env->startSection('page-title', 'Chi tiết vé'); ?>
<?php $__env->startSection('page-subtitle', 'Thông tin chi tiết vé xem phim'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $statusLabels = [
            'da_thanh_toan' => 'Đã thanh toán',
            'da_su_dung' => 'Đã sử dụng',
            'da_huy' => 'Đã hủy',
        ];

        $foodItems = collect($foodItems ?? ($veXemPhim->foods_list ?? []));

        if ($foodItems->isEmpty() && $veXemPhim->relationLoaded('foods') && $veXemPhim->foods->isNotEmpty()) {
            $foodItems = $veXemPhim->foods->map(function ($food) {
                return [
                    'name' => $food->ten_do_an ?? $food->ten_mon ?? $food->name ?? 'Đồ ăn',
                    'qty' => $food->pivot->so_luong ?? 1,
                ];
            });
        }

        $typeLabels = [
            'truc_tuyen' => 'Online',
            'tai_quay' => 'Tại quầy',
        ];

        $buyerName = $veXemPhim->nguoiDung?->ho_ten
            ?? $veXemPhim->nguoiDung?->name
            ?? $veXemPhim->nguoiDung?->email
            ?? 'Khách mua tại quầy';

        $sellerName = $veXemPhim->nhanVien?->ho_ten
            ?? $veXemPhim->nhanVien?->name
            ?? $veXemPhim->nhanVien?->email
            ?? 'Chưa có nhân viên';

        $statusLabel = $statusLabels[$veXemPhim->trang_thai] ?? 'Không rõ';
        $typeLabel = $typeLabels[$veXemPhim->loai_ve] ?? 'Không rõ';
    ?>

    <div class="ticket-detail-page">
        <?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <section class="ticket-detail-hero">
            <div class="ticket-detail-hero-copy">
                <span class="ticket-kicker">
                    <i class="fa-solid fa-ticket"></i>
                    Hồ sơ vé CineHome
                </span>
                <h1><?php echo e($veXemPhim->ma_ve); ?></h1>
                <p>
                    Chi tiết giao dịch vé, thông tin khách hàng, suất chiếu và trạng thái sử dụng.
                    Dùng màn hình này để đối soát nhanh trước khi xác nhận khách vào phòng hoặc xử lý hủy vé.
                </p>

                <div class="ticket-detail-hero-meta">
                    <span><i class="fa-solid fa-film"></i> <?php echo e($veXemPhim->ten_phim ?? 'Chưa có tên phim'); ?></span>
                    <span><i class="fa-solid fa-location-dot"></i> <?php echo e($veXemPhim->ten_rap ?? 'Chưa có rạp'); ?></span>
                    <span><i class="fa-regular fa-clock"></i> <?php echo e($veXemPhim->thoi_gian_chieu?->format('d/m/Y H:i') ?? 'Chưa có suất chiếu'); ?></span>
                </div>
            </div>

            <div class="ticket-detail-hero-actions">
                <a href="<?php echo e(route('admin.ve-xem-phims.index')); ?>" class="movie-action-btn is-ghost">
                    <i class="fa-solid fa-arrow-left"></i>
                    Quay lại
                </a>
                <a href="<?php echo e(route('admin.ve-xem-phims.edit', $veXemPhim)); ?>" class="movie-action-btn is-soft">
                    <i class="fa-solid fa-pen"></i>
                    Sửa trạng thái
                </a>
            </div>
        </section>

        <section class="ticket-detail-summary">
            <article>
                <span class="ticket-detail-summary-icon is-money"><i class="fa-solid fa-sack-dollar"></i></span>
                <div>
                    <small>Tổng tiền</small>
                    <strong><?php echo e(number_format((float) $veXemPhim->tong_tien, 0, ',', '.')); ?>đ</strong>
                </div>
            </article>

            <article>
                <span class="ticket-detail-summary-icon is-refund"><i class="fa-solid fa-rotate-left"></i></span>
                <div>
                    <small>Tiền hoàn</small>
                    <strong><?php echo e(number_format((float) $veXemPhim->tien_hoan, 0, ',', '.')); ?>đ</strong>
                </div>
            </article>

            <article>
                <span class="ticket-detail-summary-icon is-seat"><i class="fa-solid fa-couch"></i></span>
                <div>
                    <small>Ghế</small>
                    <strong><?php echo e($veXemPhim->ma_ghe ?? '--'); ?></strong>
                </div>
            </article>

            <article>
                <span class="ticket-detail-summary-icon is-status"><i class="fa-solid fa-circle-info"></i></span>
                <div>
                    <small>Trạng thái</small>
                    <strong><?php echo e($statusLabel); ?></strong>
                </div>
            </article>
        </section>

        <div class="ticket-detail-layout">
            <main class="ticket-detail-main">
                <section class="ticket-detail-panel ticket-receipt-card">
                    <div class="ticket-detail-panel-head">
                        <span><i class="fa-solid fa-receipt"></i></span>
                        <div>
                            <h2>Thông tin vé</h2>
                            <p>Mã vé, loại vé, trạng thái và dữ liệu thanh toán chính.</p>
                        </div>
                    </div>

                    <div class="ticket-detail-grid">
                        <div class="ticket-detail-item is-wide">
                            <span>Mã vé</span>
                            <strong class="ticket-detail-code"><?php echo e($veXemPhim->ma_ve); ?></strong>
                        </div>
                        <div class="ticket-detail-item">
                            <span>Loại vé</span>
                            <strong><?php echo e($typeLabel); ?></strong>
                        </div>
                        <div class="ticket-detail-item">
                            <span>Trạng thái</span>
                            <strong>
                                <em class="ticket-detail-status status-<?php echo e($veXemPhim->trang_thai); ?>">
                                    <?php echo e($statusLabel); ?>

                                </em>
                            </strong>
                        </div>
                        <div class="ticket-detail-item">
                            <span>Tổng tiền</span>
                            <strong><?php echo e(number_format((float) $veXemPhim->tong_tien, 0, ',', '.')); ?>đ</strong>
                        </div>
                        <div class="ticket-detail-item">
                            <span>Tiền hoàn</span>
                            <strong><?php echo e(number_format((float) $veXemPhim->tien_hoan, 0, ',', '.')); ?>đ</strong>
                        </div>
                    </div>
                </section>

                <section class="ticket-detail-panel">
                    <div class="ticket-detail-panel-head">
                        <span><i class="fa-solid fa-user"></i></span>
                        <div>
                            <h2>Người mua & nhân sự</h2>
                            <p>Thông tin khách đặt vé và nhân viên bán vé nếu phát sinh tại quầy.</p>
                        </div>
                    </div>

                    <div class="ticket-detail-grid">
                        <div class="ticket-detail-item">
                            <span>Khách hàng</span>
                            <strong><?php echo e($buyerName); ?></strong>
                        </div>
                        <div class="ticket-detail-item">
                            <span>Email</span>
                            <strong><?php echo e($veXemPhim->nguoiDung?->email ?? '-'); ?></strong>
                        </div>
                        <div class="ticket-detail-item">
                            <span>Nhân viên bán</span>
                            <strong><?php echo e($sellerName); ?></strong>
                        </div>
                        <div class="ticket-detail-item">
                            <span>Hình thức</span>
                            <strong><?php echo e($typeLabel); ?></strong>
                        </div>
                    </div>
                </section>

                <section class="ticket-detail-panel">
                    <div class="ticket-detail-panel-head">
                        <span><i class="fa-solid fa-film"></i></span>
                        <div>
                            <h2>Phim, rạp & ghế</h2>
                            <p>Thông tin dùng để đối chiếu khi khách đến rạp.</p>
                        </div>
                    </div>

                    <div class="ticket-detail-grid">
                        <div class="ticket-detail-item is-wide">
                            <span>Tên phim</span>
                            <strong><?php echo e($veXemPhim->ten_phim ?? 'Chưa có tên phim'); ?></strong>
                        </div>
                        <div class="ticket-detail-item">
                            <span>Rạp</span>
                            <strong><?php echo e($veXemPhim->ten_rap ?? '-'); ?></strong>
                        </div>
                        <div class="ticket-detail-item">
                            <span>Phòng</span>
                            <strong><?php echo e($veXemPhim->ten_phong ?? '-'); ?></strong>
                        </div>
                        <div class="ticket-detail-item">
                            <span>Ghế</span>
                            <strong><?php echo e($veXemPhim->ma_ghe ?? '-'); ?></strong>
                        </div>
                        <div class="ticket-detail-item">
                            <span>Mã suất chiếu</span>
                            <strong>#<?php echo e($veXemPhim->suat_chieu_id ?? '--'); ?></strong>
                        </div>
                    </div>
                </section>

                <section class="ticket-detail-panel ticket-detail-food-panel">
                    <div class="ticket-detail-panel-head">
                        <span><i class="fa-solid fa-cookie-bite"></i></span>
                        <div>
                            <h2>Đồ ăn & Combo kèm theo</h2>
                            <p>Danh sách bắp nước hoặc combo đã đặt cùng vé.</p>
                        </div>
                    </div>

                    <?php if($foodItems->isNotEmpty()): ?>
                        <div class="ticket-detail-food-list">
                            <?php $__currentLoopData = $foodItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $tenMon = is_array($item) ? ($item['name'] ?? $item['ten_mon'] ?? 'Đồ ăn') : ($item->name ?? $item->ten_mon ?? $item->ten_do_an ?? 'Đồ ăn');
                                    $soLuong = is_array($item) ? ($item['qty'] ?? $item['quantity'] ?? $item['so_luong'] ?? 1) : ($item->qty ?? $item->quantity ?? $item->so_luong ?? 1);
                                ?>

                                <article class="ticket-detail-food-item">
                                    <span><?php echo e($tenMon); ?></span>
                                    <strong>x<?php echo e($soLuong); ?></strong>
                                </article>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="ticket-detail-empty-note">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>Không có đồ ăn hoặc combo nào được đặt kèm theo vé này.</span>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="ticket-detail-panel">
                    <div class="ticket-detail-panel-head">
                        <span><i class="fa-solid fa-clock"></i></span>
                        <div>
                            <h2>Mốc thời gian</h2>
                            <p>Thời điểm chiếu, thời điểm tạo vé và lần cập nhật gần nhất.</p>
                        </div>
                    </div>

                    <div class="ticket-detail-timeline">
                        <article>
                            <i class="fa-solid fa-calendar-day"></i>
                            <div>
                                <span>Suất chiếu</span>
                                <strong><?php echo e($veXemPhim->thoi_gian_chieu?->format('d/m/Y H:i') ?? '-'); ?></strong>
                            </div>
                        </article>
                        <article>
                            <i class="fa-solid fa-plus"></i>
                            <div>
                                <span>Ngày tạo vé</span>
                                <strong><?php echo e($veXemPhim->created_at?->format('d/m/Y H:i') ?? '-'); ?></strong>
                            </div>
                        </article>
                        <article>
                            <i class="fa-solid fa-rotate"></i>
                            <div>
                                <span>Cập nhật cuối</span>
                                <strong><?php echo e($veXemPhim->updated_at?->format('d/m/Y H:i') ?? '-'); ?></strong>
                            </div>
                        </article>
                    </div>
                </section>
            </main>

            <aside class="ticket-detail-side">
                <section class="ticket-detail-pass">
                    <span class="ticket-detail-pass-icon">
                        <i class="fa-solid fa-ticket"></i>
                    </span>
                    <small>Vé điện tử</small>
                    <h2><?php echo e($veXemPhim->ma_ve); ?></h2>
                    <p><?php echo e($veXemPhim->ten_phim ?? 'Chưa có tên phim'); ?></p>

                    <div class="ticket-detail-pass-row">
                        <span>Ghế</span>
                        <strong><?php echo e($veXemPhim->ma_ghe ?? '--'); ?></strong>
                    </div>
                    <div class="ticket-detail-pass-row">
                        <span>Phòng</span>
                        <strong><?php echo e($veXemPhim->ten_phong ?? '--'); ?></strong>
                    </div>
                    <div class="ticket-detail-pass-row">
                        <span>Trạng thái</span>
                        <strong><?php echo e($statusLabel); ?></strong>
                    </div>
                </section>

                <section class="ticket-detail-panel ticket-detail-actions-panel">
                    <div class="ticket-detail-panel-head">
                        <span><i class="fa-solid fa-bolt"></i></span>
                        <div>
                            <h2>Thao tác nhanh</h2>
                            <p>Cập nhật trạng thái hoặc xử lý vé ngay tại đây.</p>
                        </div>
                    </div>

                    <form method="POST" action="<?php echo e(route('admin.ve-xem-phims.cap-nhat-trang-thai', $veXemPhim)); ?>" class="ticket-detail-status-form">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PATCH'); ?>

                        <label>
                            <span>Trạng thái vé</span>
                            <select name="trang_thai">
                                <?php $__currentLoopData = $statusLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($value); ?>" <?php if($veXemPhim->trang_thai === $value): echo 'selected'; endif; ?>>
                                        <?php echo e($label); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </label>

                        <button type="submit" class="movie-action-btn is-primary" onclick="return confirm('Xác nhận cập nhật trạng thái vé này?')">
                            <i class="fa-solid fa-floppy-disk"></i>
                            Cập nhật trạng thái
                        </button>
                    </form>

                    <?php if($veXemPhim->trang_thai === 'da_thanh_toan'): ?>
                        <div class="ticket-detail-quick-actions">
                            <form method="POST" action="<?php echo e(route('admin.ve-xem-phims.su-dung', $veXemPhim)); ?>">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>
                                <button type="submit" onclick="return confirm('Xác nhận vé này đã sử dụng?')" class="ticket-detail-action-btn is-success">
                                    <i class="fa-solid fa-check"></i>
                                    Đánh dấu đã sử dụng
                                </button>
                            </form>

                            <form method="POST" action="<?php echo e(route('admin.ve-xem-phims.huy', $veXemPhim)); ?>">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>
                                <button type="submit" onclick="return confirm('Bạn có chắc muốn hủy vé này?')" class="ticket-detail-action-btn is-danger">
                                    <i class="fa-solid fa-ban"></i>
                                    Hủy vé
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="ticket-detail-locked-note">
                            <i class="fa-solid fa-lock"></i>
                            <span>Vé hiện không còn thao tác sử dụng/hủy nhanh.</span>
                        </div>
                    <?php endif; ?>
                </section>
            </aside>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\DATN\WD-11-Cinehome-cinema\resources\views/admin/ve-xem-phims/show.blade.php ENDPATH**/ ?>