<?php
    $shouldOpenAuthModal = $errors->any() || session('error');
    $activeAuthTab = old('auth_modal', 'login');
?>

<div
    id="authModal"
    class="<?php echo e($shouldOpenAuthModal ? 'flex' : 'hidden'); ?> fixed inset-0 z-[9999] items-center justify-center bg-black/70 px-4 backdrop-blur-md auth-modal-overlay"
>
    
    <div id="authModalOverlay" class="absolute inset-0"></div>

    
    <div
        id="authModalBox"
        class="auth-modal-box auth-modal-scroll relative z-10 max-h-[92vh] w-full max-w-[420px] overflow-y-auto overflow-x-hidden rounded-[28px] border border-[#d99a32]/30 bg-[#121212] shadow-2xl"
    >

        
        <button
            type="button"
            id="closeAuthModal"
            class="absolute right-3 top-3 z-20 flex h-8 w-8 items-center justify-center rounded-full bg-white/10 text-sm text-white transition hover:bg-[#d99a32] hover:text-[#2b1208]"
        >
            <i class="fa-solid fa-xmark"></i>
        </button>

        
        <div class="px-7 pt-6 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white p-1.5 shadow-lg">
                <img src="<?php echo e(asset('assets/images/logo.png')); ?>" alt="CineHome" class="h-full w-full object-contain">
            </div>

            <h2 class="mt-3 text-[22px] font-black leading-tight text-white">
                Cine<span class="text-[#d99a32]">Home</span>
            </h2>

            <p class="mt-1 text-xs text-gray-400">
                Đăng nhập để đặt vé và quản lý vé của bạn
            </p>
        </div>

        
        <div class="mx-7 mt-5 grid grid-cols-2 rounded-2xl bg-white/5 p-1">
            <button
                type="button"
                data-auth-tab="login"
                class="auth-tab-btn rounded-xl px-4 py-2 text-sm font-bold transition <?php echo e($activeAuthTab === 'login' ? 'bg-[#d99a32] text-[#2b1208]' : 'text-gray-300 hover:text-white'); ?>"
            >
                Đăng nhập
            </button>

            <button
                type="button"
                data-auth-tab="register"
                class="auth-tab-btn rounded-xl px-4 py-2 text-sm font-bold transition <?php echo e($activeAuthTab === 'register' ? 'bg-[#d99a32] text-[#2b1208]' : 'text-gray-300 hover:text-white'); ?>"
            >
                Đăng ký
            </button>
        </div>

        
        <?php if($errors->any()): ?>
            <div class="mx-8 mt-5 rounded-2xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="mx-8 mt-5 rounded-2xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        
        <form
            id="loginForm"
            method="POST"
            action="<?php echo e(route('login')); ?>"
            class="auth-form auth-form-animate px-7 pb-6 pt-5 <?php echo e($activeAuthTab === 'login' ? 'block' : 'hidden'); ?>"
        >
            <?php echo csrf_field(); ?>

            <input type="hidden" name="auth_modal" value="login">

            <div class="mb-4">
                <label class="mb-2 block text-sm font-bold text-gray-300">
                    Email
                </label>
                <input
                    type="email"
                    name="email"
                    value="<?php echo e(old('auth_modal') === 'login' ? old('email') : ''); ?>"
                    required
                    placeholder="Nhập email"
                    class="w-full rounded-2xl border border-[#d99a32]/20 bg-white/5 px-4 py-2.5 text-white outline-none transition placeholder:text-gray-500 focus:border-[#d99a32] focus:bg-white/10"
                >
            </div>

            <div class="mb-4">
                <label class="mb-2 block text-sm font-bold text-gray-300">
                    Mật khẩu
                </label>
                <div class="relative">
                    <input
                        type="password"
                        name="mat_khau"
                        id="loginPassword"
                        required
                        placeholder="Nhập mật khẩu"
                        class="w-full rounded-2xl border border-[#d99a32]/20 bg-white/5 px-4 py-2.5 pr-12 text-white outline-none transition placeholder:text-gray-500 focus:border-[#d99a32] focus:bg-white/10"
                    >
                    <button
                        type="button"
                        data-toggle-password="loginPassword"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#d99a32]"
                    >
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="mb-5 flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gray-400 class-pointer">
                    <input
                        type="checkbox"
                        name="remember"
                        class="h-4 w-4 rounded border-white/20 bg-white/10 text-[#d99a32] focus:ring-[#d99a32]"
                    >
                    Ghi nhớ đăng nhập
                </label>

                <?php if(Route::has('password.request')): ?>
                    <a href="<?php echo e(route('password.request')); ?>" class="text-sm font-semibold text-[#d99a32] hover:underline">
                        Quên mật khẩu?
                    </a>
                <?php endif; ?>
            </div>

            <button
                type="submit"
                class="w-full rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 font-black text-white shadow-lg transition hover:scale-[1.01] hover:opacity-95"
            >
                Đăng nhập
            </button>
        </form>

        
        <form
            id="registerForm"
            method="POST"
            action="<?php echo e(route('register')); ?>"
            class="auth-form auth-form-animate px-7 pb-6 pt-5 <?php echo e($activeAuthTab === 'register' ? 'block' : 'hidden'); ?>"
        >
            <?php echo csrf_field(); ?>

            <input type="hidden" name="auth_modal" value="register">

            <div class="mb-3.5">
                <label class="mb-2 block text-sm font-bold text-gray-300">
                    Họ tên
                </label>
                <input
                    type="text"
                    name="ho_ten"
                    value="<?php echo e(old('auth_modal') === 'register' ? old('ho_ten') : ''); ?>"
                    required
                    placeholder="Nhập họ tên"
                    class="w-full rounded-2xl border border-[#d99a32]/20 bg-white/5 px-4 py-2.5 text-white outline-none transition placeholder:text-gray-500 focus:border-[#d99a32] focus:bg-white/10"
                >
            </div>

            <div class="mb-3.5">
                <label class="mb-2 block text-sm font-bold text-gray-300">
                    Email
                </label>
                <input
                    type="email"
                    name="email"
                    value="<?php echo e(old('auth_modal') === 'register' ? old('email') : ''); ?>"
                    required
                    placeholder="Nhập email"
                    class="w-full rounded-2xl border border-[#d99a32]/20 bg-white/5 px-4 py-2.5 text-white outline-none transition placeholder:text-gray-500 focus:border-[#d99a32] focus:bg-white/10"
                >
            </div>

            <div class="mb-3.5">
                <label class="mb-2 block text-sm font-bold text-gray-300">
                    Mật khẩu
                </label>
                <div class="relative">
                    <input
                        type="password"
                        name="mat_khau"
                        id="registerPassword"
                        required
                        placeholder="Nhập mật khẩu"
                        class="w-full rounded-2xl border border-[#d99a32]/20 bg-white/5 px-4 py-2.5 pr-12 text-white outline-none transition placeholder:text-gray-500 focus:border-[#d99a32] focus:bg-white/10"
                    >
                    <button
                        type="button"
                        data-toggle-password="registerPassword"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#d99a32]"
                    >
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="mb-5">
                <label class="mb-2 block text-sm font-bold text-gray-300">
                    Xác nhận mật khẩu
                </label>
                <div class="relative">
                    <input
                        type="password"
                        name="mat_khau_confirmation"
                        id="registerPasswordConfirm"
                        required
                        placeholder="Nhập lại mật khẩu"
                        class="w-full rounded-2xl border border-[#d99a32]/20 bg-white/5 px-4 py-2.5 pr-12 text-white outline-none transition placeholder:text-gray-500 focus:border-[#d99a32] focus:bg-white/10"
                    >
                    <button
                        type="button"
                        data-toggle-password="registerPasswordConfirm"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#d99a32]"
                    >
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <button
                type="submit"
                class="w-full rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-2.5 font-black text-white shadow-lg transition hover:scale-[1.01] hover:opacity-95"
            >
                Đăng ký
            </button>
        </form>
    </div>
</div><?php /**PATH C:\laragon\www\WD-11-Cinehome-cinema\resources\views/components/auth-modal.blade.php ENDPATH**/ ?>