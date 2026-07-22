<section class="profile-section">
    <header class="profile-section-head">
        <span>
            <i class="fa-solid fa-shield-halved"></i>
        </span>
        <div>
            <h2>Đổi mật khẩu bảo mật</h2>
            <p>Dùng mật khẩu mạnh để bảo vệ vé điện tử, voucher và thông tin thanh toán của bạn.</p>
        </div>
    </header>

    <form method="POST" action="<?php echo e(route('password.update')); ?>" class="profile-form">
        <?php echo csrf_field(); ?>
        <?php echo method_field('put'); ?>

        <label class="profile-field" for="update_password_current_password">
            <span>Mật khẩu hiện tại</span>
            <div class="profile-input-wrap">
                <i class="fa-solid fa-lock"></i>
                <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" placeholder="Nhập mật khẩu hiện tại">
                <button type="button" data-toggle-password="update_password_current_password" class="profile-password-toggle" aria-label="Hiện mật khẩu">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
            <?php if($errors->updatePassword->has('current_password')): ?>
                <small class="profile-error"><?php echo e($errors->updatePassword->first('current_password')); ?></small>
            <?php endif; ?>
        </label>

        <label class="profile-field" for="update_password_password">
            <span>Mật khẩu mới</span>
            <div class="profile-input-wrap">
                <i class="fa-solid fa-key"></i>
                <input id="update_password_password" name="password" type="password" autocomplete="new-password" placeholder="Tối thiểu 8 ký tự">
                <button type="button" data-toggle-password="update_password_password" class="profile-password-toggle" aria-label="Hiện mật khẩu">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
            <?php if($errors->updatePassword->has('password')): ?>
                <small class="profile-error"><?php echo e($errors->updatePassword->first('password')); ?></small>
            <?php endif; ?>
        </label>

        <label class="profile-field" for="update_password_password_confirmation">
            <span>Xác nhận mật khẩu mới</span>
            <div class="profile-input-wrap">
                <i class="fa-solid fa-circle-check"></i>
                <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" placeholder="Nhập lại mật khẩu mới">
                <button type="button" data-toggle-password="update_password_password_confirmation" class="profile-password-toggle" aria-label="Hiện mật khẩu">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
        </label>

        <div class="profile-actions">
            <button type="submit" class="profile-primary-btn">
                <i class="fa-solid fa-shield"></i>
                Cập nhật mật khẩu
            </button>
        </div>
    </form>
</section>
<?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/profile/partials/doi_mat_khau_form.blade.php ENDPATH**/ ?>