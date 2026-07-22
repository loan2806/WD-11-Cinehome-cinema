<?php if (isset($component)) { $__componentOriginalf59bbabc84de9a059788bce88cd9445a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf59bbabc84de9a059788bce88cd9445a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.profile-layout','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('profile-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php
        $displayName = $user->ho_ten ?? 'Thành viên CineHome';
        $initial = mb_substr($displayName, 0, 1);
        $roleLabel = $user->vai_tro === 'admin'
            ? 'Quản trị viên'
            : ($user->vai_tro === 'nhan_vien' ? 'Nhân viên rạp' : 'Thành viên CineHome');
        $birthDate = $user->ngay_sinh ? \Carbon\Carbon::parse($user->ngay_sinh)->format('d/m/Y') : 'Chưa thiết lập';
        $joinedAt = $user->created_at ? $user->created_at->format('d/m/Y') : 'Đang cập nhật';
    ?>

    <section class="profile-page">
        <div class="profile-hero">
            <div class="profile-hero-copy">
                <span class="profile-eyebrow">
                    <i class="fa-solid fa-id-card-clip"></i>
                    Tài khoản CineHome
                </span>
                <h1>Quản lý hồ sơ cá nhân của bạn.</h1>
                <p>Cập nhật thông tin liên hệ, đổi mật khẩu bảo mật và kiểm soát tài khoản tại một nơi gọn gàng.</p>
            </div>

            <div class="profile-hero-card">
                <div class="profile-avatar-lg"><?php echo e($initial); ?></div>
                <div>
                    <strong><?php echo e($displayName); ?></strong>
                    <span><?php echo e($roleLabel); ?></span>
                    <small><?php echo e($user->email); ?></small>
                </div>
            </div>
        </div>

        <div class="profile-grid">
            <aside class="profile-summary-card">
                <div class="profile-summary-head">
                    <div class="profile-avatar-xl"><?php echo e($initial); ?></div>
                    <h2><?php echo e($displayName); ?></h2>
                    <p><?php echo e($roleLabel); ?></p>
                </div>

                <div class="profile-summary-list">
                    <div>
                        <i class="fa-solid fa-envelope"></i>
                        <span>Email</span>
                        <strong><?php echo e($user->email); ?></strong>
                    </div>
                    <div>
                        <i class="fa-solid fa-cake-candles"></i>
                        <span>Ngày sinh</span>
                        <strong><?php echo e($birthDate); ?></strong>
                    </div>
                    <div>
                        <i class="fa-solid fa-calendar-check"></i>
                        <span>Tham gia</span>
                        <strong><?php echo e($joinedAt); ?></strong>
                    </div>
                </div>

                <a href="<?php echo e(route('user.ve_xem_phim.index')); ?>" class="profile-summary-link">
                    <i class="fa-solid fa-ticket"></i>
                    Vé đã đặt
                </a>
            </aside>

            <div class="profile-content">
                <?php if(session('status') === 'profile-updated'): ?>
                    <div class="profile-toast is-success">
                        <i class="fa-solid fa-circle-check"></i>
                        Hồ sơ đã được cập nhật thành công.
                    </div>
                <?php endif; ?>

                <?php if(session('status') === 'password-updated'): ?>
                    <div class="profile-toast is-success">
                        <i class="fa-solid fa-shield-halved"></i>
                        Mật khẩu đã được thay đổi an toàn.
                    </div>
                <?php endif; ?>

                <div class="profile-card">
                    <?php echo $__env->make('profile.partials.cap_nhat_thong_tin_form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

                <div class="profile-card">
                    <?php echo $__env->make('profile.partials.doi_mat_khau_form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

                <div class="profile-card profile-card-danger">
                    <?php echo $__env->make('profile.partials.xoa_tai_khoan_form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>
        </div>
    </section>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf59bbabc84de9a059788bce88cd9445a)): ?>
<?php $attributes = $__attributesOriginalf59bbabc84de9a059788bce88cd9445a; ?>
<?php unset($__attributesOriginalf59bbabc84de9a059788bce88cd9445a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf59bbabc84de9a059788bce88cd9445a)): ?>
<?php $component = $__componentOriginalf59bbabc84de9a059788bce88cd9445a; ?>
<?php unset($__componentOriginalf59bbabc84de9a059788bce88cd9445a); ?>
<?php endif; ?>
<?php /**PATH E:\laragon\www\Cinema\WD-11-Cinehome-cinema\resources\views/profile/edit.blade.php ENDPATH**/ ?>