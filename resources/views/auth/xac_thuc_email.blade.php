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
                <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-blue-500/10 text-blue-400 mb-3 border border-blue-500/20">
                    <i class="fa-solid fa-envelope-open-text text-xl"></i>
                </div>
                <h2 class="text-2xl font-black text-white m-0">Xác Thực Tài Khoản</h2>
                <p class="mt-3 text-sm text-gray-400 leading-relaxed font-normal tracking-wide text-center">
                    Cảm ơn bạn đã đăng ký thành viên tại <b class="text-white">CineHome</b>! Vui lòng kích hoạt tài khoản bằng cách nhấn vào liên kết xác thực chúng tôi vừa gửi vào hộp thư Email của bạn.
                </p>
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-5 p-4 rounded-xl text-sm font-semibold text-green-400 bg-green-500/10 border border-green-500/20">
                    <i class="fa-solid fa-circle-check mr-1"></i> Một liên kết xác thực mới đã được gửi thành công tới địa chỉ Email đăng ký của bạn.
                </div>
            @endif

            <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-white/10 pt-5">
                <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto m-0">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto h-11 px-5 rounded-xl font-bold text-sm bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white border-0 cursor-pointer hover:from-[#d99a32] hover:to-[#8a4a21] flex items-center justify-center gap-2">
                        <i class="fa-solid fa-paper-plane text-xs"></i> Gửi lại thư xác thực
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto m-0">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto h-11 px-4 rounded-xl text-sm font-bold text-gray-400 hover:text-red-400 border border-white/10 bg-white/5 transition hover:bg-red-500/15 cursor-pointer flex items-center justify-center gap-1.5">
                        Đăng xuất <i class="fa-solid fa-right-from-bracket text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>