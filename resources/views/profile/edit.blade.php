<x-app-layout>
    {{-- ĐỒNG BỘ BACKGROUND DARK-MODE TOÀN TRANG PROFILE --}}
    <div class="min-h-screen bg-[#0f0f0f] py-12 text-gray-200">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            {{-- TIÊU ĐỀ TRANG --}}
            <div class="border-b border-white/5 pb-4 mb-6">
                <h2 class="text-2xl font-black tracking-tight text-white flex items-center gap-2 m-0">
                    <i class="fa-solid fa-user-gear text-[#d99a32]"></i> Cấu Hình Tài Khoản Cá Nhân
                </h2>
                <p class="text-sm text-gray-400 mt-1">Cập nhật thông tin định danh, thiết lập mật khẩu bảo mật và quản lý quyền riêng tư của bạn trên hệ thống CineHome.</p>
            </div>

            {{-- KHỐI 1: THÔNG TIN HỒ SƠ (ĐÃ ĐỔI TÊN FILE) --}}
            <div class="p-6 sm:p-8 bg-[#151515] border border-white/10 shadow-2xl rounded-2xl transition duration-300 hover:border-white/20">
                <div class="max-w-xl">
                    @include('profile.partials.cap_nhat_thong_tin_form')
                </div>
            </div>

            {{-- KHỐI 2: ĐỔI MẬT KHẨU (ĐÃ ĐỔI TÊN FILE) --}}
            <div class="p-6 sm:p-8 bg-[#151515] border border-white/10 shadow-2xl rounded-2xl transition duration-300 hover:border-white/20">
                <div class="max-w-xl">
                    @include('profile.partials.doi_mat_khau_form')
                </div>
            </div>

            {{-- KHỐI 3: XÓA TÀI KHOẢN (ĐÃ ĐỔI TÊN FILE) --}}
            <div class="p-6 sm:p-8 bg-[#151515] border border-red-500/10 shadow-2xl rounded-2xl bg-gradient-to-br from-[#151515] to-red-950/5">
                <div class="max-w-xl">
                    @include('profile.partials.xoa_tai_khoan_form')
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>