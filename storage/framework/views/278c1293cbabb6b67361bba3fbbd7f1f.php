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
                    <span class="auth-side-kicker">Thành viên CineHome</span>
                    <h1>Chào mừng bạn quay lại.</h1>
                    <p>Đăng nhập để tiếp tục đặt vé, quản lý vé đã mua và lưu các voucher đang có.</p>
                </div>

                <div class="auth-page-benefits">
                    <span><i class="fa-solid fa-couch"></i> Chọn ghế yêu thích</span>
                    <span><i class="fa-solid fa-qrcode"></i> Nhận vé điện tử nhanh</span>
                    <span><i class="fa-solid fa-gift"></i> Dùng voucher thành viên</span>
                </div>
            </aside>

            <section class="auth-page-card">
                <div class="auth-brand-block">
                    <div class="auth-logo-mark cinehome-logo-sparkle">
                        <img src="<?php echo e(asset('assets/images/LOGO copy.png')); ?>" alt="CineHome" class="cinehome-logo-img">
                    </div>
                    <div>
                        <h2>Cine<span>Home</span></h2>
                        <p>Đăng nhập để đặt vé và quản lý tài khoản</p>
                    </div>
                </div>

                <div class="auth-tab-switch">
                    <a href="<?php echo e(route('login')); ?>" class="auth-tab-btn is-active">Đăng nhập</a>
                    <a href="<?php echo e(route('register')); ?>" class="auth-tab-btn">Đăng ký</a>
                </div>

                <?php if (isset($component)) { $__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth-session-status','data' => ['class' => 'auth-alert is-success','status' => session('status')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('auth-session-status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'auth-alert is-success','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(session('status'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5)): ?>
<?php $attributes = $__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5; ?>
<?php unset($__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5)): ?>
<?php $component = $__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5; ?>
<?php unset($__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5); ?>
<?php endif; ?>

                <?php if(session('error')): ?>
                    <div class="auth-alert"><?php echo e(session('error')); ?></div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('login')); ?>" class="auth-form auth-form-animate">
                    <?php echo csrf_field(); ?>

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
                                autofocus
                                placeholder="Nhập email của bạn"
                            >
                        </div>
                        <?php if($errors->has('email')): ?>
                            <small class="auth-error"><?php echo e($errors->first('email')); ?></small>
                        <?php endif; ?>
                    </label>

                    <label class="auth-field">
                        <span>Mật khẩu</span>
                        <div class="auth-input-wrap">
                            <i class="fa-solid fa-lock"></i>
                            <input
                                id="loginPagePassword"
                                type="password"
                                name="mat_khau"
                                required
                                placeholder="Nhập mật khẩu"
                            >
                            <button type="button" data-toggle-password="loginPagePassword" class="auth-password-toggle" aria-label="Hiện mật khẩu">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <?php if($errors->has('mat_khau')): ?>
                            <small class="auth-error"><?php echo e($errors->first('mat_khau')); ?></small>
                        <?php endif; ?>
                    </label>

                    <div class="auth-row">
                        <label class="auth-check">
                            <input id="remember_me" type="checkbox" name="remember">
                            <span>Ghi nhớ đăng nhập</span>
                        </label>

                        <?php if(Route::has('password.request')): ?>
                            <a href="<?php echo e(route('password.request')); ?>">Quên mật khẩu?</a>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="auth-submit-btn">
                        Đăng nhập
                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
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
<?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/auth/dang_nhap.blade.php ENDPATH**/ ?>