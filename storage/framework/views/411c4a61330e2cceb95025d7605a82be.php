<section class="profile-section profile-danger-section">
    <header class="profile-section-head">
        <span>
            <i class="fa-solid fa-triangle-exclamation"></i>
        </span>
        <div>
            <h2>Vùng nguy hiểm</h2>
            <p>Xóa tài khoản sẽ hủy vĩnh viễn lịch sử giao dịch, vé đã lưu và dữ liệu tích lũy của bạn trên CineHome.</p>
        </div>
    </header>

    <details class="profile-danger-details" <?php echo e($errors->userDeletion->isNotEmpty() ? 'open' : ''); ?>>
        <summary>
            <i class="fa-solid fa-user-xmark"></i>
            Mở xác nhận xóa tài khoản
        </summary>

        <form method="POST" action="<?php echo e(route('profile.destroy')); ?>" class="profile-form profile-danger-form">
            <?php echo csrf_field(); ?>
            <?php echo method_field('delete'); ?>

            <p class="profile-warning-copy">
                Hành động này không thể hoàn tác. Vui lòng nhập mật khẩu hiện tại để xác nhận bạn là chủ sở hữu tài khoản.
            </p>

            <label class="profile-field" for="mat_khau_xoa">
                <span>Mật khẩu xác nhận</span>
                <div class="profile-input-wrap is-danger">
                    <i class="fa-solid fa-lock"></i>
                    <input id="mat_khau_xoa" name="mat_khau" type="password" placeholder="Nhập mật khẩu hiện tại" required>
                    <button type="button" data-toggle-password="mat_khau_xoa" class="profile-password-toggle" aria-label="Hiện mật khẩu">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                <?php if($errors->userDeletion->has('mat_khau')): ?>
                    <small class="profile-error"><?php echo e($errors->userDeletion->first('mat_khau')); ?></small>
                <?php endif; ?>
            </label>

            <div class="profile-actions">
                <button type="submit" class="profile-danger-btn">
                    <i class="fa-solid fa-trash-can"></i>
                    Xóa tài khoản vĩnh viễn
                </button>
            </div>
        </form>
    </details>
</section>
<?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/profile/partials/xoa_tai_khoan_form.blade.php ENDPATH**/ ?>