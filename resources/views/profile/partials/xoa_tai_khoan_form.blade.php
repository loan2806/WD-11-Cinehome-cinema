<section class="space-y-6">
    <header>
        <h3 class="text-lg font-black text-red-400 uppercase tracking-wide m-0 flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation text-sm"></i> Vùng nguy hiểm: Xóa tài khoản
        </h3>
        <p class="mt-1 text-sm text-gray-400">Một khi tài khoản đã bị xóa, toàn bộ lịch sử giao dịch và dữ liệu điểm tích lũy đổi vé xem phim của bạn sẽ bị hủy bỏ vĩnh viễn.</p>
    </header>

    <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')" class="h-11 px-5 rounded-xl font-bold text-sm text-red-400 border border-red-500/20 bg-red-500/5 hover:bg-red-500/15 hover:border-red-500/30 transition cursor-pointer flex items-center gap-2">
        <i class="fa-solid fa-user-xmark text-xs"></i> Yêu cầu hủy tài khoản hệ thống
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="POST" action="{{ route('profile.destroy') }}" class="p-8 bg-[#151515] text-gray-200 border border-white/10 rounded-2xl m-0 space-y-5">
            @csrf
            @method('delete')

            <h2 class="text-xl font-black text-white m-0 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-red-500"></i> Bạn có chắc chắn muốn thực hiện hành động này?
            </h2>

            <p class="text-sm text-gray-400 leading-relaxed m-0">
                Vui lòng điền chính xác mật khẩu hiện tại của bạn để chứng minh quyền sở hữu tối cao đối với tài khoản trước khi hệ thống kích hoạt lệnh xóa vĩnh viễn dữ liệu.
            </p>

            {{-- ĐỒNG BỘ: Biến nhập mat_khau tiếng Việt --}}
            <div class="space-y-2">
                <label for="mat_khau_xoa" class="block text-xs font-bold uppercase tracking-wide text-gray-400">Mật khẩu xác nhận danh tính</label>
                <input id="mat_khau_xoa" name="mat_khau" type="password" class="block h-12 w-full rounded-xl border border-red-500/20 bg-[#2a2a2a]/60 px-4 text-base text-white outline-none focus:border-red-500 transition shadow-inner" placeholder="••••••••" required />
                @if($errors->userDeletion->has('mat_khau'))
                    <div class="text-sm text-red-400 font-medium mt-1">{{ $errors->userDeletion->first('mat_khau') }}</div>
                @endif
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-white/5">
                <button type="button" x-on:click="$dispatch('close')" class="h-11 px-5 rounded-xl text-sm font-bold text-gray-400 hover:text-white bg-white/5 border border-white/10 cursor-pointer transition">
                    Hủy thao tác
                </button>
                <button type="submit" class="h-11 px-5 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 border-0 cursor-pointer transition flex items-center gap-1.5">
                    Xóa tài khoản <i class="fa-solid fa-trash-can text-xs"></i>
                </button>
            </div>
        </form>
    </x-modal>
</section>