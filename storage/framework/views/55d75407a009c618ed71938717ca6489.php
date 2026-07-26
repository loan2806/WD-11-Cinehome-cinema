<?php $__env->startSection('page-title', 'Thêm thể loại phim mới'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $statusValue = (string) old('trang_thai', '1');
        $previewName = old('ten_the_loai');
        $previewDescription = old('mo_ta');
    ?>

    <form action="<?php echo e(route('admin.the-loais.store')); ?>" method="POST" class="genre-form-page" novalidate>
        <?php echo csrf_field(); ?>

        <?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <section class="genre-form-hero">
            <div class="genre-form-hero-copy">
                <span class="genre-kicker">
                    <i class="fa-solid fa-layer-group"></i>
                    Tạo danh mục phim
                </span>
                <h1>Thêm thể loại phim mới</h1>
                <p>
                    Tạo nhóm nội dung mới để quản trị viên gắn phim, lọc dữ liệu và giúp người dùng tìm đúng gu phim nhanh hơn.
                    Nên đặt tên ngắn, rõ nghĩa và dễ nhận diện.
                </p>

                <div class="genre-form-meta">
                    <span><i class="fa-solid fa-wand-magic-sparkles"></i> Tạo mới</span>
                    <span><i class="fa-solid fa-toggle-on"></i> Mặc định đang bật</span>
                    <span><i class="fa-solid fa-pen"></i> Mô tả tối đa 500 ký tự</span>
                </div>
            </div>

            <div class="genre-form-hero-actions">
                <a href="<?php echo e(route('admin.the-loais.index')); ?>" class="movie-action-btn is-ghost">
                    <i class="fa-solid fa-arrow-left"></i>
                    Quay lại
                </a>
                <button type="submit" class="movie-action-btn is-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Lưu thể loại
                </button>
            </div>
        </section>

        <div class="genre-form-layout">
            <main class="genre-form-main">
                <section class="genre-form-panel">
                    <div class="genre-form-panel-head">
                        <span><i class="fa-solid fa-ticket"></i></span>
                        <div>
                            <h2>Thông tin thể loại</h2>
                            <p>Điền tên và mô tả ngắn để nhóm phim hiển thị rõ ràng trong hệ thống.</p>
                        </div>
                    </div>

                    <div class="genre-form-grid">
                        <label class="genre-form-field is-wide">
                            <span>Tên thể loại <b>*</b></span>
                            <input
                                type="text"
                                name="ten_the_loai"
                                id="ten_the_loai"
                                value="<?php echo e(old('ten_the_loai')); ?>"
                                placeholder="Ví dụ: Hành động, Kinh dị, Tình cảm..."
                                maxlength="255"
                                required
                                class="<?php echo e($errors->has('ten_the_loai') ? 'is-invalid' : ''); ?>"
                            >
                            <?php $__errorArgs = ['ten_the_loai'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="genre-form-error"><?php echo e($message); ?></small>
                            <?php else: ?>
                                <small>Tên thể loại không nên quá dài để bảng quản trị và bộ lọc dễ đọc.</small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </label>

                        <label class="genre-form-field is-wide">
                            <span>Mô tả</span>
                            <textarea
                                name="mo_ta"
                                id="mo_ta"
                                rows="6"
                                maxlength="500"
                                placeholder="Nhập mô tả ngắn về nhóm phim này..."
                                class="<?php echo e($errors->has('mo_ta') ? 'is-invalid' : ''); ?>"
                            ><?php echo e(old('mo_ta')); ?></textarea>
                            <?php $__errorArgs = ['mo_ta'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="genre-form-error"><?php echo e($message); ?></small>
                            <?php else: ?>
                                <small>Tối đa 500 ký tự. Phần này giúp đội vận hành hiểu nhanh phạm vi thể loại.</small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </label>
                    </div>
                </section>

                <section class="genre-form-panel">
                    <div class="genre-form-panel-head">
                        <span><i class="fa-solid fa-toggle-on"></i></span>
                        <div>
                            <h2>Trạng thái hiển thị</h2>
                            <p>Chọn trạng thái ban đầu cho thể loại khi thêm vào hệ thống.</p>
                        </div>
                    </div>

                    <div class="genre-status-choice-grid <?php echo e($errors->has('trang_thai') ? 'is-invalid' : ''); ?>">
                        <label class="genre-status-choice">
                            <input type="radio" name="trang_thai" value="1" <?php if($statusValue === '1'): echo 'checked'; endif; ?>>
                            <span class="genre-status-choice-card is-active">
                                <i class="fa-solid fa-circle-check"></i>
                                <strong>Đang kích hoạt</strong>
                                <small>Có thể dùng ngay khi thêm hoặc chỉnh sửa phim.</small>
                            </span>
                        </label>

                        <label class="genre-status-choice">
                            <input type="radio" name="trang_thai" value="0" <?php if($statusValue === '0'): echo 'checked'; endif; ?>>
                            <span class="genre-status-choice-card is-inactive">
                                <i class="fa-solid fa-circle-pause"></i>
                                <strong>Tạm ẩn</strong>
                                <small>Lưu dữ liệu trước, bật lại khi thể loại đã sẵn sàng sử dụng.</small>
                            </span>
                        </label>
                    </div>

                    <?php $__errorArgs = ['trang_thai'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="genre-form-error"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </section>
            </main>

            <aside class="genre-form-side">
                <section class="genre-preview-card">
                    <span class="genre-preview-icon">
                        <i class="fa-solid fa-ticket"></i>
                    </span>
                    <small>Xem nhanh</small>
                    <h2><?php echo e($previewName ?: 'Tên thể loại'); ?></h2>
                    <p><?php echo e($previewDescription ?: 'Mô tả ngắn sẽ giúp đội quản trị nhận diện nhóm phim dễ hơn.'); ?></p>

                    <div class="genre-preview-badges">
                        <?php if($statusValue === '1'): ?>
                            <span class="genre-status is-active">
                                <i class="fa-solid fa-circle-check"></i>
                                Đang bật
                            </span>
                        <?php else: ?>
                            <span class="genre-status is-inactive">
                                <i class="fa-solid fa-circle-pause"></i>
                                Tạm ẩn
                            </span>
                        <?php endif; ?>

                        <span class="genre-movie-count">
                            <i class="fa-solid fa-film"></i>
                            0 phim
                        </span>
                    </div>
                </section>

                <section class="genre-help-card">
                    <div class="genre-help-head">
                        <i class="fa-solid fa-lightbulb"></i>
                        <div>
                            <strong>Gợi ý nhập liệu</strong>
                            <span>Một thể loại tốt giúp nhân sự quản trị và khách hàng tìm phim nhanh hơn.</span>
                        </div>
                    </div>

                    <ul class="genre-help-list">
                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            Ưu tiên tên ngắn như Hành động, Tâm lý, Gia đình.
                        </li>
                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            Không tạo nhiều thể loại có ý nghĩa quá giống nhau.
                        </li>
                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            Có thể tạo ở trạng thái tạm ẩn nếu cần chuẩn bị dữ liệu trước.
                        </li>
                    </ul>
                </section>
            </aside>
        </div>

        <div class="genre-form-savebar">
            <div>
                <strong>Thêm thể loại mới</strong>
                <span>Kiểm tra tên, mô tả và trạng thái trước khi lưu.</span>
            </div>

            <div class="genre-form-save-actions">
                <a href="<?php echo e(route('admin.the-loais.index')); ?>" class="movie-action-btn is-ghost">
                    Hủy
                </a>
                <button type="submit" class="movie-action-btn is-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Lưu thể loại
                </button>
            </div>
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\DATN\WD-11-Cinehome-cinema\resources\views/admin/the-loais/create.blade.php ENDPATH**/ ?>