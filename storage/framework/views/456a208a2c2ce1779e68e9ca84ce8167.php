<?php if (isset($component)) { $__componentOriginal69dc84650370d1d4dc1b42d016d7226b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b = $attributes; } ?>
<?php $component = App\View\Components\GuestLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('guest-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\GuestLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php
        $heThongSettings = \App\Models\CaiDatHeThong::first();
        $urlAnhNen = ($heThongSettings && $heThongSettings->anh_nen_login)
            ? asset('storage/' . $heThongSettings->anh_nen_login)
            : 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=1920&auto=format&fit=crop';
    ?>

    <main class="auth-page" style="--auth-bg: url('<?php echo e($urlAnhNen); ?>')" lang="vi" spellcheck="false">
        <section class="auth-page-shell">
            <aside class="auth-page-visual">
                <a href="<?php echo e(route('home')); ?>" class="auth-page-brand">
                    <span class="cinehome-logo-sparkle">
                        <img src="<?php echo e(asset('assets/images/LOGO copy.png')); ?>" alt="CineHome" class="cinehome-logo-img">
                    </span>
                    Cine<span>Home</span>
                </a>

                <div>
                    <span class="auth-side-kicker">Gia nhập CineHome</span>
                    <h1>Tạo tài khoản để xem phim tiện hơn.</h1>
                    <p>Nhận điểm chào mừng, lưu ưu đãi và đặt vé nhanh trong các lần xem phim tiếp theo.</p>
                </div>

                <div class="auth-page-benefits">
                    <span><i class="fa-solid fa-star"></i> Tặng điểm thành viên mới</span>
                    <span><i class="fa-solid fa-ticket"></i> Đặt vé chỉ trong vài bước</span>
                    <span><i class="fa-solid fa-bell"></i> Cập nhật ưu đãi mới</span>
                </div>
            </aside>

            <section class="auth-page-card">
                <div class="auth-brand-block">
                    <div class="auth-logo-mark cinehome-logo-sparkle">
                        <img src="<?php echo e(asset('assets/images/LOGO copy.png')); ?>" alt="CineHome" class="cinehome-logo-img">
                    </div>
                    <div>
                        <h2>Cine<span>Home</span></h2>
                        <p>Tạo tài khoản thành viên CineHome</p>
                    </div>
                </div>

                <div class="auth-tab-switch">
                    <a href="<?php echo e(route('login')); ?>" class="auth-tab-btn">Đăng nhập</a>
                    <a href="<?php echo e(route('register')); ?>" class="auth-tab-btn is-active">Đăng ký</a>
                </div>

                <?php if($errors->any()): ?>
                    <div class="auth-alert">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div><?php echo e($error); ?></div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('register')); ?>" class="auth-form auth-form-animate">
                    <?php echo csrf_field(); ?>

                    <label class="auth-field">
                        <span>Họ và tên</span>
                        <div class="auth-input-wrap">
                            <i class="fa-solid fa-user"></i>
                            <input
                                id="ho_ten"
                                type="text"
                                name="ho_ten"
                                value="<?php echo e(old('ho_ten')); ?>"
                                required
                                autofocus
                                placeholder="Nhập họ tên"
                            >
                        </div>
                    </label>

                    <label class="auth-field">
                        <span>Email</span>
                        <div class="auth-input-wrap">
                            <i class="fa-solid fa-envelope"></i>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="<?php echo e(old('email')); ?>"
                                required
                                placeholder="Nhập email"
                            >
                        </div>
                    </label>

                    <label class="auth-field">
                        <span>Mật khẩu</span>
                        <div class="auth-input-wrap">
                            <i class="fa-solid fa-lock"></i>
                            <input
                                id="registerPagePassword"
                                type="password"
                                name="mat_khau"
                                required
                                placeholder="Tạo mật khẩu"
                            >
                            <button type="button" data-toggle-password="registerPagePassword" class="auth-password-toggle" aria-label="Hiện mật khẩu">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </label>

                    <label class="auth-field">
                        <span>Xác nhận mật khẩu</span>
                        <div class="auth-input-wrap">
                            <i class="fa-solid fa-shield-halved"></i>
                            <input
                                id="registerPagePasswordConfirm"
                                type="password"
                                name="mat_khau_confirmation"
                                required
                                placeholder="Nhập lại mật khẩu"
                            >
                            <button type="button" data-toggle-password="registerPagePasswordConfirm" class="auth-password-toggle" aria-label="Hiện mật khẩu">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </label>

                    <button type="submit" class="auth-submit-btn">
                        Tạo tài khoản
                        <i class="fa-solid fa-user-plus"></i>
                    </button>
                </form>
            </section>
        </section>
    </main>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $attributes = $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $component = $__componentOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php /**PATH E:\laragon\www\Cinema\WD-11-Cinehome-cinema\resources\views/auth/dang_ky.blade.php ENDPATH**/ ?>