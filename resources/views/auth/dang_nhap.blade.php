<x-guest-layout>
    @php
        $heThongSettings = \App\Models\CaiDatHeThong::first();
        $urlAnhNen = ($heThongSettings && $heThongSettings->anh_nen_login) 
            ? asset('storage/' . $heThongSettings->anh_nen_login) 
            : 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=1920';
    @endphp

    <div class="fixed inset-0 z-0 h-screen w-screen overflow-hidden bg-cover bg-center bg-no-repeat" style="background-image: url('{{ $urlAnhNen }}');">
        <div class="absolute inset-0 bg-black/75 backdrop-blur-[3px]"></div>
    </div>

    <div class="fixed inset-0 z-10 flex h-screen w-screen overflow-hidden items-center justify-center px-4">
        <div class="w-full max-w-[500px] rounded-2xl border border-white/10 bg-[#151515]/95 p-10 shadow-2xl backdrop-blur-md">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-black tracking-tight text-white m-0">Cine<span class="text-[#d99a32]">Home</span></h2>
                <p class="mt-2 text-sm text-gray-400 uppercase tracking-widest font-semibold">Hệ thống quản lý rạp phim</p>
            </div>

            <div class="flex rounded-xl bg-[#2a2a2a]/60 p-1.5 mb-6 border border-white/5">
                <a href="{{ route('login') }}" class="w-1/2 text-center py-2.5 text-sm font-black rounded-lg bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white text-decoration-none shadow-md">Đăng nhập</a>
                <a href="{{ route('register') }}" class="w-1/2 text-center py-2.5 text-sm font-bold text-gray-400 hover:text-white transition-colors text-decoration-none flex items-center justify-center">Đăng ký</a>
            </div>

            <x-auth-session-status class="mb-4 text-base" :status="session('status')" />
            @if (session('error'))
                <div class="mb-4 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-base font-semibold">
                    <i class="fa-solid fa-circle-exclamation mr-1.5"></i> {{ session('error') }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('login') }}" id="loginForm" class="space-y-6 m-0">
                @csrf
                <div class="space-y-2">
                    <label for="email" class="block text-sm font-bold uppercase tracking-wide text-[#e8d2bb]">Địa chỉ Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="block h-12 w-full rounded-xl border border-[#8a4a21] bg-[#2a2a2a]/60 px-4 text-base text-white outline-none focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32] transition-colors" />
                    @if($errors->has('email')) <div class="text-sm text-red-400 font-medium mt-1">{{ $errors->first('email') }}</div> @endif
                </div>

                <div class="space-y-2">
                    <label for="mat_khau" class="block text-sm font-bold uppercase tracking-wide text-[#e8d2bb]">Mật khẩu bảo mật</label>
                    <input id="mat_khau" type="password" name="mat_khau" required class="block h-12 w-full rounded-xl border border-[#8a4a21] bg-[#2a2a2a]/60 px-4 text-base text-white outline-none focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32] transition-colors" />
                    @if($errors->has('mat_khau')) <div class="text-sm text-red-400 font-medium mt-1">{{ $errors->first('mat_khau') }}</div> @endif
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                        <input id="remember_me" type="checkbox" class="rounded h-4 w-4 bg-[#2a2a2a] border-[#8a4a21] text-[#d99a32] shadow-sm focus:ring-[#d99a32]" name="remember">
                        <span class="ms-2 text-sm text-gray-400 hover:text-white transition-colors">Ghi nhớ đăng nhập</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a class="text-sm text-gray-400 hover:text-[#d99a32] text-decoration-none font-medium" href="{{ route('password.request') }}">Quên mật khẩu?</a>
                    @endif
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full h-12 rounded-xl font-bold text-base bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white border-0 cursor-pointer hover:from-[#d99a32] hover:to-[#8a4a21] flex items-center justify-center gap-2">
                        Xác nhận Đăng nhập <i class="fa-solid fa-right-to-bracket text-sm"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>