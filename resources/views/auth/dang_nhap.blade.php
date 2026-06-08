<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if (session('error'))
    <div class="alert alert-danger mb-3 p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-500 text-sm">
        {{ session('error') }}
    </div>
    @endif
    
    <form method="POST" action="{{ route('login') }}" id="loginForm" class="auth-form auth-form-animate block">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium !text-[#e8d2bb]">Email</label>
            <input id="email" 
                   type="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   required 
                   autofocus 
                   autocomplete="username"
                   class="block mt-1 w-full rounded-xl border !border-[#8a4a21] !bg-[#2a2a2a] px-4 py-3 !text-white focus:!border-[#d99a32] focus:!ring-[#d99a32] transition-colors" />
            
            @if($errors->has('email'))
                <div class="text-sm text-red-500 mt-2">{{ $errors->first('email') }}</div>
            @endif
        </div>

        <div class="mt-4">
            <label for="mat_khau" class="block text-sm font-medium !text-[#e8d2bb]">Mật khẩu</label>
            <input id="mat_khau" 
                   type="password" 
                   name="mat_khau" 
                   required 
                   autocomplete="current-password"
                   class="block mt-1 w-full rounded-xl border !border-[#8a4a21] !bg-[#2a2a2a] px-4 py-3 !text-white focus:!border-[#d99a32] focus:!ring-[#d99a32] transition-colors" />
            
            @if($errors->has('mat_khau'))
                <div class="text-sm text-red-500 mt-2">{{ $errors->first('mat_khau') }}</div>
            @endif
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded !bg-[#2a2a2a] !border-[#8a4a21] !text-[#d99a32] shadow-sm focus:!ring-[#d99a32]" name="remember">
                <span class="ms-2 text-sm !text-[#e8d2bb]">Ghi nhớ đăng nhập</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm !text-[#e8d2bb] hover:!text-[#d99a32] rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:!ring-[#d99a32]" href="{{ route('password.request') }}">
                    Quên mật khẩu?
                </a>
            @endif

            <button type="submit" class="ms-3 rounded-xl px-6 py-3 font-medium transition-all !bg-gradient-to-r !from-[#8a4a21] !to-[#d99a32] !text-white hover:!from-[#d99a32] hover:!to-[#8a4a21]">
                Đăng nhập
            </button>
        </div>
    </form>
</x-guest-layout>