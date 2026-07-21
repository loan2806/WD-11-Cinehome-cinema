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
            : 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=1920';
    ?>

    <div class="fixed inset-0 z-0 h-screen w-screen overflow-hidden bg-cover bg-center bg-no-repeat" style="background-image: url('<?php echo e($urlAnhNen); ?>');">
        <div class="absolute inset-0 bg-black/75 backdrop-blur-[3px]"></div>
    </div>

    <div class="fixed inset-0 z-10 flex h-screen w-screen overflow-hidden items-center justify-center px-4">
        <div class="w-full max-w-[500px] rounded-2xl border border-white/10 bg-[#151515]/95 p-10 shadow-2xl backdrop-blur-md">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-black tracking-tight text-white m-0">Cine<span class="text-[#d99a32]">Home</span></h2>
                <p class="mt-2 text-sm text-gray-400 uppercase tracking-widest font-semibold">Hệ thống quản lý rạp phim</p>
            </div>

            <div class="flex rounded-xl bg-[#2a2a2a]/60 p-1.5 mb-6 border border-white/5">
                <a href="<?php echo e(route('login')); ?>" class="w-1/2 text-center py-2.5 text-sm font-black rounded-lg bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white text-decoration-none shadow-md">Đăng nhập</a>
                <a href="<?php echo e(route('register')); ?>" class="w-1/2 text-center py-2.5 text-sm font-bold text-gray-400 hover:text-white transition-colors text-decoration-none flex items-center justify-center">Đăng ký</a>
            </div>

            <?php if (isset($component)) { $__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth-session-status','data' => ['class' => 'mb-4 text-base','status' => session('status')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('auth-session-status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4 text-base','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(session('status'))]); ?>
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
                <div class="mb-4 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-base font-semibold">
                    <i class="fa-solid fa-circle-exclamation mr-1.5"></i> <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo e(route('login')); ?>" id="loginForm" class="space-y-6 m-0">
                <?php echo csrf_field(); ?>
                <div class="space-y-2">
                    <label for="email" class="block text-sm font-bold uppercase tracking-wide text-[#e8d2bb]">Địa chỉ Email</label>
                    <input id="email" type="email" name="email" value="<?php echo e(old('email')); ?>" required autofocus class="block h-12 w-full rounded-xl border border-[#8a4a21] bg-[#2a2a2a]/60 px-4 text-base text-white outline-none focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32] transition-colors" />
                    <?php if($errors->has('email')): ?> <div class="text-sm text-red-400 font-medium mt-1"><?php echo e($errors->first('email')); ?></div> <?php endif; ?>
                </div>

                <div class="space-y-2">
                    <label for="mat_khau" class="block text-sm font-bold uppercase tracking-wide text-[#e8d2bb]">Mật khẩu bảo mật</label>
                    <input id="mat_khau" type="password" name="mat_khau" required class="block h-12 w-full rounded-xl border border-[#8a4a21] bg-[#2a2a2a]/60 px-4 text-base text-white outline-none focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32] transition-colors" />
                    <?php if($errors->has('mat_khau')): ?> <div class="text-sm text-red-400 font-medium mt-1"><?php echo e($errors->first('mat_khau')); ?></div> <?php endif; ?>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                        <input id="remember_me" type="checkbox" class="rounded h-4 w-4 bg-[#2a2a2a] border-[#8a4a21] text-[#d99a32] shadow-sm focus:ring-[#d99a32]" name="remember">
                        <span class="ms-2 text-sm text-gray-400 hover:text-white transition-colors">Ghi nhớ đăng nhập</span>
                    </label>
                    <?php if(Route::has('password.request')): ?>
                        <a class="text-sm text-gray-400 hover:text-[#d99a32] text-decoration-none font-medium" href="<?php echo e(route('password.request')); ?>">Quên mật khẩu?</a>
                    <?php endif; ?>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full h-12 rounded-xl font-bold text-base bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white border-0 cursor-pointer hover:from-[#d99a32] hover:to-[#8a4a21] flex items-center justify-center gap-2">
                        Xác nhận Đăng nhập <i class="fa-solid fa-right-to-bracket text-sm"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $attributes = $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $component = $__componentOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?><?php /**PATH C:\laragon\www\WD-11-Cinehome-cinema\resources\views/auth/dang_nhap.blade.php ENDPATH**/ ?>