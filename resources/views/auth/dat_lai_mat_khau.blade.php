<x-guest-layout>
    @php
        $heThongSettings = \App\Models\CaiDatHeThong::first();
        $urlAnhNen = ($heThongSettings && $heThongSettings->anh_nen_login) 
            ? asset('storage/' . $heThongSettings->anh_nen_login) 
            : 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=1920';
    @endphp

    {{-- KHÓA CỨNG TOÀN MÀN HÌNH CHỐNG CUỘN CHUỘT --}}
    <div class="fixed inset-0 z-0 h-screen w-screen overflow-hidden bg-cover bg-center bg-no-repeat" style="background-image: url('{{ $urlAnhNen }}');">
        <div class="absolute inset-0 bg-black/75 backdrop-blur-[3px]"></div>
    </div>

    {{-- ĐỊNH VỊ KHUNG FORM CHÍNH GIỮA MÀN HÌNH --}}
    <div class="fixed inset-0 z-10 flex h-screen w-screen overflow-hidden items-center justify-center px-4">
        <div class="w-full max-w-[500px] rounded-2xl border border-white/10 bg-[#151515]/95 p-10 shadow-2xl backdrop-blur-md">
            
            {{-- TIÊU ĐỀ THƯƠNG HIỆU --}}
            <div class="text-center mb-6">
                <h2 class="text-3xl font-black tracking-tight text-white m-0">
                    Cine<span class="text-[#d99a32]">Home</span>
                </h2>
                <p class="mt-2 text-sm text-gray-400 uppercase tracking-widest font-semibold">
                    Cập nhật mật khẩu mới cho tài khoản
                </p>
            </div>

            <form method="POST" action="{{ route('password.store') }}" class="space-y-5 m-0">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                {{-- TÀI KHOẢN EMAIL --}}
                <div class="space-y-2">
                    <label for="email" class="block text-sm font-bold uppercase tracking-wide text-[#e8d2bb]">Địa chỉ Email xác nhận</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" class="block h-12 w-full rounded-xl border border-[#8a4a21] bg-[#2a2a2a]/60 px-4 text-base text-white outline-none focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32] transition-colors shadow-inner" />
                    @if($errors->has('email'))
                        <div class="text-sm text-red-400 font-medium mt-1">{{ $errors->first('email') }}</div>
                    @endif
                </div>

                {{-- MẬT KHẨU MỚI --}}
                <div class="space-y-2">
                    <label for="mat_khau" class="block text-sm font-bold uppercase tracking-wide text-[#e8d2bb]">Mật khẩu mới</label>
                    <input id="mat_khau" type="password" name="mat_khau" required autocomplete="new-password" placeholder="Tối thiểu 8 ký tự" class="block h-12 w-full rounded-xl border border-[#8a4a21] bg-[#2a2a2a]/60 px-4 text-base text-white outline-none focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32] transition-colors shadow-inner" />
                    @if($errors->has('mat_khau'))
                        <div class="text-sm text-red-400 font-medium mt-1">{{ $errors->first('mat_khau') }}</div>
                    @endif
                </div>

                {{-- NHẬP LẠI MẬT KHẨU MỚI --}}
                <div class="space-y-2">
                    <label for="mat_khau_confirmation" class="block text-sm font-bold uppercase tracking-wide text-[#e8d2bb]">Xác nhận lại mật khẩu mới</label>
                    <input id="mat_khau_confirmation" type="password" name="mat_khau_confirmation" required autocomplete="new-password" placeholder="••••••••" class="block h-12 w-full rounded-xl border border-[#8a4a21] bg-[#2a2a2a]/60 px-4 text-base text-white outline-none focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32] transition-colors shadow-inner" />
                    @if($errors->has('mat_khau_confirmation'))
                        <div class="text-sm text-red-400 font-medium mt-1">{{ $errors->first('mat_khau_confirmation') }}</div>
                    @endif
                </div>

                {{-- NÚT XÁC NHẬN --}}
                <div class="pt-2">
                    <button type="submit" class="w-full h-12 rounded-xl font-bold text-base transition-all bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white shadow-lg border-0 cursor-pointer hover:from-[#d99a32] hover:to-[#8a4a21] active:scale-[0.99] flex items-center justify-center gap-2">
                        Tiến hành Đặt lại mật khẩu <i class="fa-solid fa-key text-xs"></i>
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-guest-layout>