<x-guest-layout>
    @php
        $heThongSettings = \App\Models\CaiDatHeThong::first();
        $urlAnhNen = ($heThongSettings && $heThongSettings->anh_nen_login) ? asset('storage/' . $heThongSettings->anh_nen_login) : 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=1920';
    @endphp

    <div class="fixed inset-0 z-0 h-screen w-screen overflow-hidden bg-cover bg-center bg-no-repeat" style="background-image: url('{{ $urlAnhNen }}');">
        <div class="absolute inset-0 bg-black/75 backdrop-blur-[3px]"></div>
    </div>

    <div class="fixed inset-0 z-10 flex h-screen w-screen overflow-hidden items-center justify-center px-4">
        <div class="w-full max-w-[500px] rounded-2xl border border-white/10 bg-[#151515]/95 p-10 shadow-2xl backdrop-blur-md">
            <div class="text-center mb-6">
                <h2 class="text-3xl font-black text-white m-0">Cine<span class="text-[#d99a32]">Home</span></h2>
                <p class="mt-1.5 text-sm text-gray-400 uppercase tracking-widest font-semibold">Tạo tài khoản thành viên</p>
            </div>

            <div class="flex rounded-xl bg-[#2a2a2a]/60 p-1.5 mb-6 border border-white/5">
                <a href="{{ route('login') }}" class="w-1/2 text-center py-2.5 text-sm font-bold text-gray-400 hover:text-white transition-colors text-decoration-none flex items-center justify-center">Đăng nhập</a>
                <a href="{{ route('register') }}" class="w-1/2 text-center py-2.5 text-sm font-black rounded-lg bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white text-decoration-none shadow-md">Đăng ký</a>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-4 m-0">
                @csrf
                <div class="space-y-1.5">
                    <label for="ho_ten" class="block text-sm font-bold uppercase tracking-wide text-[#e8d2bb]">Họ và tên</label>
                    <input id="ho_ten" type="text" name="ho_ten" value="{{ old('ho_ten') }}" required autofocus class="block h-11 w-full rounded-xl border border-[#8a4a21] bg-[#2a2a2a]/60 px-4 text-base text-white outline-none focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32]" />
                    @if($errors->has('ho_ten')) <div class="text-sm text-red-400 font-medium mt-1">{{ $errors->first('ho_ten') }}</div> @endif
                </div>

                <div class="space-y-1.5">
                    <label for="email" class="block text-sm font-bold uppercase tracking-wide text-[#e8d2bb]">Địa chỉ Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required class="block h-11 w-full rounded-xl border border-[#8a4a21] bg-[#2a2a2a]/60 px-4 text-base text-white outline-none focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32]" />
                    @if($errors->has('email')) <div class="text-sm text-red-400 font-medium mt-1">{{ $errors->first('email') }}</div> @endif
                </div>

                <div class="space-y-1.5">
                    <label for="mat_khau" class="block text-sm font-bold uppercase tracking-wide text-[#e8d2bb]">Mật khẩu bảo mật</label>
                    <input id="mat_khau" type="password" name="mat_khau" required class="block h-11 w-full rounded-xl border border-[#8a4a21] bg-[#2a2a2a]/60 px-4 text-base text-white outline-none focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32]" />
                    @if($errors->has('mat_khau')) <div class="text-sm text-red-400 font-medium mt-1">{{ $errors->first('mat_khau') }}</div> @endif
                </div>

                <div class="space-y-1.5">
                    <label for="mat_khau_confirmation" class="block text-sm font-bold uppercase tracking-wide text-[#e8d2bb]">Xác nhận mật khẩu</label>
                    <input id="mat_khau_confirmation" type="password" name="mat_khau_confirmation" required class="block h-11 w-full rounded-xl border border-[#8a4a21] bg-[#2a2a2a]/60 px-4 text-base text-white outline-none focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32]" />
                </div>

                <div class="pt-3">
                    <button type="submit" class="w-full h-12 rounded-xl font-bold transition-all bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white border-0 cursor-pointer hover:from-[#d99a32] hover:to-[#8a4a21] flex items-center justify-center gap-2 text-sm">
                        Xác nhận Đăng ký thành viên <i class="fa-solid fa-user-plus text-xs"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>