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
            <div class="text-center mb-8">
                <h2 class="text-3xl font-black text-white m-0">Khôi Phục <span class="text-[#d99a32]">Mật Khẩu</span></h2>
                <p class="mt-3 text-sm text-gray-400 leading-relaxed font-normal tracking-wide text-center">
                    Hãy cung cấp địa chỉ Email đã đăng ký trên hệ thống. <br class="hidden sm:block">
                    CineHome sẽ gửi một liên kết xác thực để bạn thiết lập lại mật khẩu mới.
                </p>
            </div>

            @if (session('status'))
                <div class="mb-5 p-4 rounded-xl text-sm font-semibold text-green-400 bg-green-500/10 border border-green-500/20">
                    <i class="fa-solid fa-circle-check mr-1.5"></i> {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-6 m-0">
                @csrf
                <div class="space-y-2">
                    <label for="email" class="block text-sm font-bold uppercase tracking-wide text-[#e8d2bb]">Địa chỉ Email của bạn</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="your-email@cinehome.vn" class="block h-12 w-full rounded-xl border border-[#8a4a21] bg-[#2a2a2a]/60 px-4 text-base text-white outline-none focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32]" />
                    @if($errors->has('email')) <div class="text-sm text-red-400 font-medium mt-1">{{ $errors->first('email') }}</div> @endif
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full h-12 rounded-xl font-bold text-base bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white border-0 cursor-pointer hover:from-[#d99a32] hover:to-[#8a4a21] flex items-center justify-center gap-2">
                        Gửi liên kết xác nhận <i class="fa-solid fa-paper-plane text-xs"></i>
                    </button>
                </div>

                <div class="text-center pt-2">
                    <a href="{{ route('login') }}" class="text-sm text-gray-400 hover:text-[#d99a32] transition-colors text-decoration-none inline-flex items-center gap-2 font-semibold">
                        <i class="fa-solid fa-arrow-left text-xs"></i> Quay lại trang Đăng nhập
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>