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
            
            {{-- THÔNG BÁO BẢO MẬT --}}
            <div class="text-center mb-6">
                <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-yellow-500/10 text-[#d99a32] mb-3 border border-[#d99a32]/20">
                    <i class="fa-solid fa-shield-halved text-xl"></i>
                </div>
                <h2 class="text-2xl font-black text-white m-0">Xác Minh Quyền Truy Cập</h2>
                <p class="mt-3 text-sm text-gray-400 leading-relaxed font-normal tracking-wide text-center">
                    Đây là khu vực bảo mật tối cao của ứng dụng. Vui lòng xác nhận lại chính xác mật khẩu của bạn trước khi tiếp tục thao tác.
                </p>
            </div>

            <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6 m-0">
                @csrf

                {{-- Ô NHẬP MẬT KHẨU XÁC MINH --}}
                <div class="space-y-2">
                    <label for="mat_khau" class="block text-sm font-bold uppercase tracking-wide text-[#e8d2bb]">Mật khẩu của bạn</label>
                    <input id="mat_khau" type="password" name="mat_khau" required autocomplete="current-password" placeholder="••••••••" class="block h-12 w-full rounded-xl border border-[#8a4a21] bg-[#2a2a2a]/60 px-4 text-base text-white outline-none focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32] transition-colors shadow-inner" />
                    @if($errors->has('mat_khau'))
                        <div class="text-sm text-red-400 font-medium mt-1">{{ $errors->first('mat_khau') }}</div>
                    @endif
                </div>

                {{-- HÀNH ĐỘNG KÍCH HOẠT --}}
                <div class="pt-2">
                    <button type="submit" class="w-full h-12 rounded-xl font-bold text-base transition-all bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white shadow-lg border-0 cursor-pointer hover:from-[#d99a32] hover:to-[#8a4a21] active:scale-[0.99] flex items-center justify-center gap-2">
                        Xác nhận danh tính <i class="fa-solid fa-circle-check text-sm"></i>
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-guest-layout>