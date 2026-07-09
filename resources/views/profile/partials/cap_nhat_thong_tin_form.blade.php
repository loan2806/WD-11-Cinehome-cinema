<section class="space-y-6">
    <header>
        <h3 class="text-lg font-black text-[#e8d2bb] uppercase tracking-wide m-0 flex items-center gap-2">
            <i class="fa-solid fa-id-card text-[#d99a32] text-sm"></i> Thông tin cá nhân
        </h3>
        <p class="mt-1 text-sm text-gray-400">Thay đổi họ tên hiển thị, ngày sinh nhận ưu đãi và hòm thư điện tử của bạn.</p>
    </header>

    <form id="send-verification" method="POST" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="POST" action="{{ route('profile.update') }}" class="space-y-5 m-0">
        @csrf
        @method('patch')

        {{-- HỌ VÀ TÊN --}}
        <div class="space-y-2">
            <label for="ho_ten" class="block text-sm font-bold uppercase tracking-wide text-gray-300">Họ và tên thành viên</label>
            <input id="ho_ten" name="ho_ten" type="text" class="block h-12 w-full rounded-xl border border-[#8a4a21] bg-[#2a2a2a]/60 px-4 text-base text-white outline-none focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32] transition shadow-inner" value="{{ old('ho_ten', $user->ho_ten) }}" required autofocus autocomplete="name" />
            @if($errors->has('ho_ten')) <div class="text-sm text-red-400 font-medium mt-1">{{ $errors->first('ho_ten') }}</div> @endif
        </div>

        {{-- NGÀY SINH (CHẶN ĐỔI NHIỀU LẦN ĐỂ LẤY VOUCHER) --}}
        <div class="space-y-2">
            <label for="ngay_sinh" class="block text-sm font-bold uppercase tracking-wide text-gray-300">Ngày sinh nhận quà</label>
            <input id="ngay_sinh" 
                   name="ngay_sinh" 
                   type="date" 
                   class="block h-12 w-full rounded-xl border border-[#8a4a21] bg-[#2a2a2a]/60 px-4 text-base text-white outline-none focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32] transition shadow-inner disabled:opacity-50 disabled:cursor-not-allowed" 
                   value="{{ old('ngay_sinh', $user->ngay_sinh ? \Carbon\Carbon::parse($user->ngay_sinh)->format('Y-m-d') : '') }}"
                   {{ $user->ngay_sinh ? 'disabled' : '' }} />
            
            @if($user->ngay_sinh)
                <p class="text-xs font-semibold text-amber-500 m-0 flex items-center gap-1.5 pt-1">
                    <i class="fa-solid fa-lock text-[10px]"></i> Ngày sinh đã được khóa cố định nhằm bảo mật quyền lợi nhận Voucher sinh nhật.
                </p>
            @else
                <p class="text-xs text-gray-500 m-0 pt-1">Lưu ý: Ngày sinh chỉ được thiết lập duy nhất 1 lần và không thể tự thay đổi.</p>
            @endif
            @if($errors->has('ngay_sinh')) <div class="text-sm text-red-400 font-medium mt-1">{{ $errors->first('ngay_sinh') }}</div> @endif
        </div>

        {{-- ĐỊA CHỈ EMAIL --}}
        <div class="space-y-2">
            <label for="email" class="block text-sm font-bold uppercase tracking-wide text-gray-300">Địa chỉ Email liên hệ</label>
            <input id="email" name="email" type="email" class="block h-12 w-full rounded-xl border border-[#8a4a21] bg-[#2a2a2a]/60 px-4 text-base text-white outline-none focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32] transition shadow-inner" value="{{ old('email', $user->email) }}" required autocomplete="username" />
            @if($errors->has('email')) <div class="text-sm text-red-400 font-medium mt-1">{{ $errors->first('email') }}</div> @endif
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="h-11 px-6 rounded-xl font-bold text-sm bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white border-0 cursor-pointer hover:from-[#d99a32] hover:to-[#8a4a21] transition flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi
            </button>
            @if (session('status') === 'profile-updated')
                <p class="text-sm font-semibold text-green-400 m-0 flex items-center gap-1"><i class="fa-solid fa-circle-check"></i> Đã cập nhật thành công hồ sơ!</p>
            @endif
        </div>
    </form>
</section>