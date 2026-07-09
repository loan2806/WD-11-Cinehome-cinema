<section class="space-y-6">
    <header>
        <h3 class="text-lg font-black text-[#e8d2bb] uppercase tracking-wide m-0 flex items-center gap-2">
            <i class="fa-solid fa-lock text-[#d99a32] text-sm"></i> Đổi mật khẩu bảo mật
        </h3>
        <p class="mt-1 text-sm text-gray-400">Đảm bảo tài khoản của bạn đang sử dụng chuỗi mật khẩu mã hóa bảo mật cao.</p>
    </header>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-5 m-0">
        @csrf
        @method('put')

        {{-- MẬT KHẨU HIỆN TẠI --}}
        <div class="space-y-2">
            <label for="update_password_current_password" class="block text-sm font-bold uppercase tracking-wide text-gray-300">Mật khẩu hiện tại</label>
            <input id="update_password_current_password" name="current_password" type="password" class="block h-12 w-full rounded-xl border border-[#8a4a21] bg-[#2a2a2a]/60 px-4 text-base text-white outline-none focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32] transition shadow-inner" autocomplete="current-password" placeholder="••••••••" />
            @if($errors->updatePassword->has('current_password'))
                <div class="text-sm text-red-400 font-medium mt-1">{{ $errors->updatePassword->first('current_password') }}</div>
            @endif
        </div>

        {{-- MẬT KHẨU MỚI --}}
        <div class="space-y-2">
            <label for="update_password_password" class="block text-sm font-bold uppercase tracking-wide text-gray-300">Mật khẩu mới</label>
            <input id="update_password_password" name="password" type="password" class="block h-12 w-full rounded-xl border border-[#8a4a21] bg-[#2a2a2a]/60 px-4 text-base text-white outline-none focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32] transition shadow-inner" autocomplete="new-password" placeholder="Tối thiểu 8 ký tự" />
            @if($errors->updatePassword->has('password'))
                <div class="text-sm text-red-400 font-medium mt-1">{{ $errors->updatePassword->first('password') }}</div>
            @endif
        </div>

        {{-- XÁC NHẬN MẬT KHẨU MỚI --}}
        <div class="space-y-2">
            <label for="update_password_password_confirmation" class="block text-sm font-bold uppercase tracking-wide text-gray-300">Xác nhận lại mật khẩu mới</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="block h-12 w-full rounded-xl border border-[#8a4a21] bg-[#2a2a2a]/60 px-4 text-base text-white outline-none focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32] transition shadow-inner" autocomplete="new-password" placeholder="••••••••" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="h-11 px-6 rounded-xl font-bold text-sm bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white border-0 cursor-pointer hover:from-[#d99a32] hover:to-[#8a4a21] transition flex items-center gap-2">
                <i class="fa-solid fa-shield-halved"></i> Cập nhật mật khẩu
            </button>

            @if (session('status') === 'password-updated')
                <p class="text-sm font-semibold text-green-400 m-0 flex items-center gap-1"><i class="fa-solid fa-circle-check"></i> Mật khẩu bảo mật đã được thay đổi!</p>
            @endif
        </div>
    </form>
</section>