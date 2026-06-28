<x-guest-layout>
    @php
        // ĐỒNG BỘ HÌNH NỀN: Tự động gọi cấu hình poster nền từ module cài đặt tham số gốc của Admin
        $heThongSettings = \App\Models\CaiDatHeThong::first();
        $urlAnhNen = ($heThongSettings && $heThongSettings->anh_nen_login) 
            ? asset('storage/' . $heThongSettings->anh_nen_login) 
            : 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=1920';
    @endphp

    {{-- KHÓA CHỨA BACKGROUND TOÀN MÀN HÌNH CỐ ĐỊNH - CHỐNG CUỘN CHUỘT DỌC --}}
    <div class="fixed inset-0 z-0 h-screen w-screen overflow-hidden bg-cover bg-center bg-no-repeat transition-all duration-500" style="background-image: url('{{ $urlAnhNen }}');">
        <div class="absolute inset-0 bg-black/75 backdrop-blur-[3px]"></div>
    </div>

    {{-- ĐỊNH VỊ KHUNG FORM CHÍNH GIỮA MÀN HÌNH --}}
    <div class="fixed inset-0 z-10 flex h-screen w-screen overflow-hidden items-center justify-center px-4">
        <div class="w-full max-w-[480px] rounded-2xl border border-white/10 bg-[#151515]/95 p-10 shadow-2xl backdrop-blur-md">
            
            {{-- TIÊU ĐỀ THƯƠNG HIỆU CINEHOME --}}
            <div class="text-center mb-6">
                <h2 class="text-3xl font-black tracking-tight text-white m-0">
                    Cine<span class="text-[#d99a32]">Home</span>
                </h2>
                <p class="mt-1.5 text-xs text-gray-400 uppercase tracking-widest font-semibold">
                    Tạo tài khoản thành viên xem phim của bạn
                </p>
            </div>

            {{-- THANH ĐIỀU HƯỚNG DẠNG TAB: ĐÃ ĐỒNG BỘ (Với Đăng ký ở trạng thái Active) --}}
            <div class="flex rounded-xl bg-[#2a2a2a]/60 p-1 mb-6 border border-white/5">
                <a href="{{ route('login') }}" class="w-1/2 text-center py-2.5 text-xs font-bold text-gray-400 hover:text-white transition-colors text-decoration-none flex items-center justify-center">
                    Đăng nhập
                </a>
                <a href="{{ route('register') }}" class="w-1/2 text-center py-2.5 text-xs font-black rounded-lg bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white text-decoration-none shadow-md">
                    Đăng ký
                </a>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-4 m-0">
                @csrf

                {{-- TRƯỜNG 1: HỌ VÀ TÊN --}}
                <div class="space-y-1.5">
                    <label for="ho_ten" class="block text-xs font-bold uppercase tracking-wide text-[#e8d2bb]">Họ và tên thành viên</label>
                    <input id="ho_ten" 
                           type="text" 
                           name="ho_ten" {{-- ĐÃ ĐỒNG BỘ: Đổi từ 'name' sang 'ho_ten' để khớp với thuộc tính hiển thị ở navbar của bạn --}}
                           value="{{ old('ho_ten') }}" 
                           required 
                           autofocus 
                           autocomplete="name" 
                           placeholder="Nguyễn Văn A"
                           class="block h-11 w-full rounded-xl border border-[#8a4a21] bg-[#2a2a2a]/60 px-4 text-sm text-white outline-none focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32] transition-colors shadow-inner" />
                    
                    @if($errors->has('ho_ten'))
                        <div class="text-xs text-red-400 font-medium mt-1">{{ $errors->first('ho_ten') }}</div>
                    @endif
                </div>

                {{-- TRƯỜNG 2: ĐỊA CHỈ EMAIL --}}
                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-bold uppercase tracking-wide text-[#e8d2bb]">Địa chỉ Email</label>
                    <input id="email" 
                           type="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autocomplete="username" 
                           placeholder="khachhang@gmail.com"
                           class="block h-11 w-full rounded-xl border border-[#8a4a21] bg-[#2a2a2a]/60 px-4 text-sm text-white outline-none focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32] transition-colors shadow-inner" />
                    
                    @if($errors->has('email'))
                        <div class="text-xs text-red-400 font-medium mt-1">{{ $errors->first('email') }}</div>
                    @endif
                </div>

                {{-- TRƯỜNG 3: MẬT KHẨU --}}
                <div class="space-y-1.5">
                    <label for="mat_khau" class="block text-xs font-bold uppercase tracking-wide text-[#e8d2bb]">Mật khẩu bảo mật</label>
                    <input id="mat_khau" 
                           type="password" 
                           name="mat_khau" {{-- ĐÃ ĐỒNG BỘ: Chuyển từ 'password' sang 'mat_khau' theo chuẩn tiếng Việt hệ thống Auth của bạn --}}
                           required 
                           autocomplete="new-password" 
                           placeholder="••••••••"
                           class="block h-11 w-full rounded-xl border border-[#8a4a21] bg-[#2a2a2a]/60 px-4 text-sm text-white outline-none focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32] transition-colors shadow-inner" />
                    
                    @if($errors->has('mat_khau'))
                        <div class="text-xs text-red-400 font-medium mt-1">{{ $errors->first('mat_khau') }}</div>
                    @endif
                </div>

                {{-- TRƯỜNG 4: XÁC NHẬN MẬT KHẨU --}}
                <div class="space-y-1.5">
                    <label for="mat_khau_confirmation" class="block text-xs font-bold uppercase tracking-wide text-[#e8d2bb]">Xác nhận lại mật khẩu</label>
                    <input id="mat_khau_confirmation" 
                           type="password" 
                           name="mat_khau_confirmation" {{-- ĐÃ ĐỒNG BỘ: Chuyển đổi tương ứng theo biến mat_khau --}}
                           required 
                           autocomplete="new-password" 
                           placeholder="••••••••"
                           class="block h-11 w-full rounded-xl border border-[#8a4a21] bg-[#2a2a2a]/60 px-4 text-sm text-white outline-none focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32] transition-colors shadow-inner" />
                    
                    @if($errors->has('mat_khau_confirmation'))
                        <div class="text-xs text-red-400 font-medium mt-1">{{ $errors->first('mat_khau_confirmation') }}</div>
                    @endif
                </div>

                {{-- NÚT HÀNH ĐỘNG ĐĂNG KÝ --}}
                <div class="pt-3">
                    <button type="submit" class="w-full h-12 rounded-xl font-bold transition-all bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white shadow-lg border-0 cursor-pointer hover:from-[#d99a32] hover:to-[#8a4a21] active:scale-[0.99] flex items-center justify-center gap-2 text-sm">
                        Xác nhận Đăng ký thành viên <i class="fa-solid fa-user-plus text-xs"></i>
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-guest-layout>