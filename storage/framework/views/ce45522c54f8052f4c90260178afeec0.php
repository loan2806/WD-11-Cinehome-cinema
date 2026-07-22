<?php $__env->startSection('page-title', 'Sửa quốc gia'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $movieCount = $quocGia->phims_count ?? 0;
        $statusValue = (string) old('trang_thai', $quocGia->trang_thai);
        $previewName = old('ten_quoc_gia', $quocGia->ten_quoc_gia);
        $previewCode = old('ma_quoc_gia', $quocGia->ma_quoc_gia);
        $previewInitials = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($previewCode ?: $previewName, 0, 2));
    ?>

    <form action="<?php echo e(route('admin.quoc-gias.update', $quocGia)); ?>" method="POST" class="country-form-page" novalidate>
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <section class="country-form-hero">
            <div class="country-form-hero-copy">
                <span class="country-kicker">
                    <i class="fa-solid fa-earth-asia"></i>
                    Cập nhật quốc gia phim
                </span>
                <h1>Sửa quốc gia</h1>
                <p>
                    Cập nhật tên, mã quốc gia và trạng thái sử dụng cho
                    <strong><?php echo e($quocGia->ten_quoc_gia); ?></strong>. Các phim đã liên kết vẫn được giữ nguyên.
                </p>

                <div class="country-form-meta">
                    <span><i class="fa-solid fa-hashtag"></i> ID <?php echo e($quocGia->id); ?></span>
                    <span><i class="fa-solid fa-film"></i> <?php echo e(number_format($movieCount)); ?> phim liên kết</span>
                    <span><i class="fa-solid fa-code"></i> <?php echo e(\Illuminate\Support\Str::upper($quocGia->ma_quoc_gia ?: '--')); ?></span>
                </div>
            </div>

            <div class="country-form-hero-actions">
                <a href="<?php echo e(route('admin.quoc-gias.index')); ?>" class="movie-action-btn is-ghost">
                    <i class="fa-solid fa-arrow-left"></i>
                    Quay lại
                </a>
                <button type="submit" class="movie-action-btn is-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Lưu thay đổi
                </button>
            </div>
        </section>

        <div class="country-form-layout">
            <main class="country-form-main">
                <section class="country-form-panel">
                    <div class="country-form-panel-head">
                        <span><i class="fa-solid fa-globe"></i></span>
                        <div>
                            <h2>Thông tin quốc gia</h2>
                            <p>Đặt tên và mã ngắn gọn để quản trị viên lọc phim nhanh hơn.</p>
                        </div>
                    </div>

                    <div class="country-form-grid">
                        <label class="country-form-field">
                            <span>Tên quốc gia <b>*</b></span>
                            <input
                                type="text"
                                name="ten_quoc_gia"
                                id="ten_quoc_gia"
                                value="<?php echo e(old('ten_quoc_gia', $quocGia->ten_quoc_gia)); ?>"
                                placeholder="Ví dụ: Việt Nam, United States, Japan..."
                                maxlength="255"
                                required
                                class="<?php echo e($errors->has('ten_quoc_gia') ? 'is-invalid' : ''); ?>"
                            >
                            <?php $__errorArgs = ['ten_quoc_gia'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="country-form-error"><?php echo e($message); ?></small>
                            <?php else: ?>
                                <small>Tên quốc gia sẽ xuất hiện trong bộ lọc phim và trang quản trị.</small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </label>

                        <label class="country-form-field">
                            <span>Mã quốc gia <b>*</b></span>
                            <input
                                type="text"
                                name="ma_quoc_gia"
                                id="ma_quoc_gia"
                                value="<?php echo e(old('ma_quoc_gia', $quocGia->ma_quoc_gia)); ?>"
                                placeholder="Ví dụ: VN, US, JP..."
                                maxlength="10"
                                required
                                class="<?php echo e($errors->has('ma_quoc_gia') ? 'is-invalid' : ''); ?>"
                            >
                            <?php $__errorArgs = ['ma_quoc_gia'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="country-form-error"><?php echo e($message); ?></small>
                            <?php else: ?>
                                <small>Dùng mã ngắn, dễ nhận diện khi hiển thị trong bảng dữ liệu.</small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </label>
                    </div>
                </section>

                <section class="country-form-panel">
                    <div class="country-form-panel-head">
                        <span><i class="fa-solid fa-toggle-on"></i></span>
                        <div>
                            <h2>Trạng thái sử dụng</h2>
                            <p>Quốc gia đang dùng sẽ xuất hiện trong các luồng chọn và lọc phim.</p>
                        </div>
                    </div>

                    <div class="country-status-choice-grid <?php echo e($errors->has('trang_thai') ? 'is-invalid' : ''); ?>">
                        <label class="country-status-choice">
                            <input type="radio" name="trang_thai" value="1" <?php if($statusValue === '1'): echo 'checked'; endif; ?>>
                            <span class="country-status-choice-card is-active">
                                <i class="fa-solid fa-circle-check"></i>
                                <strong>Đang sử dụng</strong>
                                <small>Cho phép chọn quốc gia này khi thêm hoặc sửa phim.</small>
                            </span>
                        </label>

                        <label class="country-status-choice">
                            <input type="radio" name="trang_thai" value="0" <?php if($statusValue === '0'): echo 'checked'; endif; ?>>
                            <span class="country-status-choice-card is-inactive">
                                <i class="fa-solid fa-circle-pause"></i>
                                <strong>Tạm ẩn</strong>
                                <small>Giữ dữ liệu cũ nhưng hạn chế dùng cho phim mới.</small>
                            </span>
                        </label>
                    </div>

                    <?php $__errorArgs = ['trang_thai'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="country-form-error"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </section>
            </main>

            <aside class="country-form-side">
                <section class="country-preview-card">
                    <span class="country-preview-avatar"><?php echo e($previewInitials ?: '--'); ?></span>
                    <small>Xem nhanh</small>
                    <h2><?php echo e($previewName ?: 'Tên quốc gia'); ?></h2>
                    <p>Mã quốc gia: <strong><?php echo e(\Illuminate\Support\Str::upper($previewCode ?: '--')); ?></strong></p>

                    <div class="country-preview-badges">
                        <?php if($statusValue === '1'): ?>
                            <span class="country-status is-active">
                                <i class="fa-solid fa-circle-check"></i>
                                Đang dùng
                            </span>
                        <?php else: ?>
                            <span class="country-status is-inactive">
                                <i class="fa-solid fa-circle-pause"></i>
                                Tạm ẩn
                            </span>
                        <?php endif; ?>

                        <span class="country-movie-count <?php echo e($movieCount > 0 ? 'has-movies' : ''); ?>">
                            <i class="fa-solid fa-film"></i>
                            <?php echo e(number_format($movieCount)); ?> phim
                        </span>
                    </div>
                </section>

                <section class="country-help-card">
                    <div class="country-help-head">
                        <i class="fa-solid fa-shield-halved"></i>
                        <div>
                            <strong>Lưu ý khi chỉnh sửa</strong>
                            <span>Thông tin quốc gia ảnh hưởng đến bộ lọc và dữ liệu phim liên quan.</span>
                        </div>
                    </div>

                    <ul class="country-help-list">
                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            Tên và mã quốc gia không được trùng với bản ghi khác.
                        </li>
                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            Quốc gia có phim liên kết vẫn có thể đổi trạng thái.
                        </li>
                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            Nên dùng mã ngắn như VN, US, JP để bảng dễ quét.
                        </li>
                    </ul>
                </section>
            </aside>
        </div>

        <div class="country-form-savebar">
            <div>
                <strong><?php echo e($quocGia->ten_quoc_gia); ?></strong>
                <span>Kiểm tra tên, mã và trạng thái trước khi lưu.</span>
            </div>

            <div class="country-form-save-actions">
                <a href="<?php echo e(route('admin.quoc-gias.index')); ?>" class="movie-action-btn is-ghost">
                    Hủy
                </a>
                <button type="submit" class="movie-action-btn is-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Cập nhật quốc gia
                </button>
            </div>
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/admin/quoc-gias/edit.blade.php ENDPATH**/ ?>