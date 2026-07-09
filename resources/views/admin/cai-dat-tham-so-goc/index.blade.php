@extends('layouts.admin')

@section('page-title', 'Cài Đặt Tham Số Gốc')
@section('page-subtitle', 'Tổng bảng điều khiển tối cao các thông số hạt nhân vận hành hệ thống CineHome')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap gap-2 border-b border-white/10 pb-3">
            <button type="button" data-target="tab-cinema" class="tab-btn px-4 py-2.5 rounded-xl text-sm font-black transition duration-200 bg-[#d99a32] text-[#2b1208] border-0 cursor-pointer flex items-center gap-2">
                <i class="fa-solid fa-building"></i> Nhóm Rạp Phim
            </button>
            <button type="button" data-target="tab-booking" class="tab-btn px-4 py-2.5 rounded-xl text-sm font-bold transition duration-200 bg-white/5 text-gray-400 border-0 cursor-pointer flex items-center gap-2 hover:bg-white/10 hover:text-white">
                <i class="fa-solid fa-ticket"></i> Nhóm Đặt Vé
            </button>
            <button type="button" data-target="tab-showtime" class="tab-btn px-4 py-2.5 rounded-xl text-sm font-bold transition duration-200 bg-white/5 text-gray-400 border-0 cursor-pointer flex items-center gap-2 hover:bg-white/10 hover:text-white">
                <i class="fa-solid fa-clapperboard"></i> Nhóm Suất Chiếu
            </button>
            <button type="button" data-target="tab-pricing" class="tab-btn px-4 py-2.5 rounded-xl text-sm font-bold transition duration-200 bg-white/5 text-gray-400 border-0 cursor-pointer flex items-center gap-2 hover:bg-white/10 hover:text-white">
                <i class="fa-solid fa-tags"></i> Nhóm Giá Vé
            </button>
            <button type="button" data-target="tab-payment" class="tab-btn px-4 py-2.5 rounded-xl text-sm font-bold transition duration-200 bg-white/5 text-gray-400 border-0 cursor-pointer flex items-center gap-2 hover:bg-white/10 hover:text-white">
                <i class="fa-solid fa-credit-card"></i> Cổng Thanh Toán
            </button>
        </div>

        <form action="{{ route('admin.system-settings.update') }}" method="POST" enctype="multipart/form-data" class="m-0 space-y-6">
            @csrf
            @method('PATCH')

            @if ($errors->any())
                <div class="rounded-xl border border-red-500/20 bg-red-500/10 p-4">
                    <ul class="list-inside list-disc text-red-400 m-0 text-sm font-semibold space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- TAB 1: NHÓM RẠP PHIM --}}
            <div id="tab-cinema" class="tab-panel block space-y-5">
                <div class="p-6 rounded-2xl border border-white/5 bg-[#121212]/60 space-y-5">
                    <h4 class="text-lg font-black text-[#f4c56a] uppercase tracking-wider m-0 border-b border-white/5 pb-3">
                        <i class="fa-solid fa-building mr-1.5"></i> Cấu Hình Nhãn Hiệu & Thông Tin Liên Hệ
                    </h4>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
                        <div class="space-y-4 flex flex-col">
                            <div class="flex flex-col items-center p-4 rounded-xl border border-white/5 bg-[#151515]">
                                <label class="text-xs text-gray-400 font-bold uppercase tracking-wide">Logo Rạp Hiện Tại</label>
                                <div class="my-3 h-24 w-24 rounded-2xl bg-white p-1 flex items-center justify-center overflow-hidden shadow-inner">
                                    <img id="logo_preview" src="{{ $settings->logo ? asset('storage/' . $settings->logo) : asset('assets/images/logo.png') }}" class="object-contain max-h-full max-w-full">
                                </div>
                                <input type="file" name="logo" id="logo_input" accept="image/*" class="hidden">
                                <button type="button" onclick="document.getElementById('logo_input').click()" class="px-3 py-1.5 rounded-lg bg-white/10 text-xs font-bold text-gray-200 hover:bg-[#d99a32] hover:text-[#2b1208] transition border-0 cursor-pointer">
                                    Tải Logo Mới
                                </button>
                            </div>

                            <div class="flex flex-col items-center p-4 rounded-xl border border-white/5 bg-[#151515]">
                                <label class="text-xs text-gray-400 font-bold uppercase tracking-wide">Poster Nền Trang Đăng Nhập</label>
                                <div class="my-3 h-28 w-full rounded-xl bg-[#222] p-1 flex items-center justify-center overflow-hidden border border-white/10 relative group">
                                    <img id="bg_login_preview" src="{{ $settings->anh_nen_login ? asset('storage/' . $settings->anh_nen_login) : 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=1025' }}" class="object-cover w-full h-full brightness-75 rounded-lg">
                                </div>
                                <input type="file" name="anh_nen_login" id="bg_login_input" accept="image/*" class="hidden">
                                <button type="button" onclick="document.getElementById('bg_login_input').click()" class="px-3 py-1.5 rounded-lg bg-white/10 text-xs font-bold text-gray-200 hover:bg-[#d99a32] hover:text-[#2b1208] transition border-0 cursor-pointer">
                                    Thay Poster Nền
                                </button>
                            </div>
                        </div>

                        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs text-gray-400 font-bold">Tên Rạp Phim <span class="text-red-400">*</span></label>
                                <input type="text" name="ten_rap" value="{{ old('ten_rap', $settings->ten_rap) }}" required class="h-11 w-full rounded-xl border border-white/10 bg-[#151515] px-4 text-sm text-white outline-none focus:border-[#d99a32] transition">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs text-gray-400 font-bold">Hotline Tổng Đài <span class="text-red-400">*</span></label>
                                <input type="text" name="hotline" value="{{ old('hotline', $settings->hotline) }}" required class="h-11 w-full rounded-xl border border-white/10 bg-[#151515] px-4 text-sm text-white outline-none focus:border-[#d99a32] transition">
                            </div>
                            <div class="space-y-1.5 sm:col-span-2">
                                <label class="text-xs text-gray-400 font-bold">Email Hỗ Trợ Khách Hàng <span class="text-red-400">*</span></label>
                                <input type="email" name="email" value="{{ old('email', $settings->email) }}" required class="h-11 w-full rounded-xl border border-white/10 bg-[#151515] px-4 text-sm text-white outline-none focus:border-[#d99a32] transition">
                            </div>
                            <div class="space-y-1.5 sm:col-span-2">
                                <label class="text-xs text-gray-400 font-bold">Địa Chỉ Cơ Sở Chính <span class="text-red-400">*</span></label>
                                <input type="text" name="dia_chi" value="{{ old('dia_chi', $settings->dia_chi) }}" required class="h-11 w-full rounded-xl border border-white/10 bg-[#151515] px-4 text-sm text-white outline-none focus:border-[#d99a32] transition">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 2: NHÓM ĐẶT VÉ --}}
            <div id="tab-booking" class="tab-panel hidden space-y-5">
                <div class="p-6 rounded-2xl border border-white/5 bg-[#121212]/60 space-y-5">
                    <h4 class="text-lg font-black text-[#f4c56a] uppercase tracking-wider m-0 border-b border-white/5 pb-3">
                        <i class="fa-solid fa-ticket mr-1.5"></i> Tham Số Giới Hạn Phiên Đặt Vé
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="text-xs text-gray-400 font-bold">Thời Gian Giữ Ghế Tạm Temporarily (Phút) <span class="text-red-400">*</span></label>
                            <input type="number" name="thoi_gian_giu_ghe" value="{{ old('thoi_gian_giu_ghe', $settings->thoi_gian_giu_ghe) }}" required min="1" class="h-11 w-full rounded-xl border border-white/10 bg-[#151515] px-4 text-sm text-white outline-none focus:border-[#d99a32] transition">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs text-gray-400 font-bold">Số Lượng Vé Tối Đa Trên Mỗi Đơn Hàng <span class="text-red-400">*</span></label>
                            <input type="number" name="so_ve_toi_da_don" value="{{ old('so_ve_toi_da_don', $settings->so_ve_toi_da_don) }}" required min="1" class="h-11 w-full rounded-xl border border-white/10 bg-[#151515] px-4 text-sm text-white outline-none focus:border-[#d99a32] transition">
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 3: NHÓM SUẤT CHIẾU --}}
            <div id="tab-showtime" class="tab-panel hidden space-y-5">
                <div class="p-6 rounded-2xl border border-white/5 bg-[#121212]/60 space-y-5">
                    <h4 class="text-lg font-black text-[#f4c56a] uppercase tracking-wider m-0 border-b border-white/5 pb-3">
                        <i class="fa-solid fa-clapperboard mr-1.5"></i> Quy Tắc Điều Phối Suất Chiếu
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="text-xs text-gray-400 font-bold">Khoảng Cách Thời Gian Dọn Phòng (Phút) <span class="text-red-400">*</span></label>
                            <input type="number" name="thoi_gian_don_phong" value="{{ old('thoi_gian_don_phong', $settings->thoi_gian_don_phong) }}" required min="0" class="h-11 w-full rounded-xl border border-white/10 bg-[#151515] px-4 text-sm text-white outline-none focus:border-[#d99a32] transition">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs text-gray-400 font-bold">Số Ngày Tối Đa Cho Khách Đặt Vé Trước <span class="text-red-400">*</span></label>
                            <input type="number" name="so_ngay_duoc_dat_ve_truoc" value="{{ old('so_ngay_duoc_dat_ve_truoc', $settings->so_ngay_duoc_dat_ve_truoc) }}" required min="1" class="h-11 w-full rounded-xl border border-white/10 bg-[#151515] px-4 text-sm text-white outline-none focus:border-[#d99a32] transition">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mt-4">
                        <div class="space-y-1.5">
                            <label class="text-xs text-gray-400 font-bold">Thời gian chuyển sang "Đang chiếu" (Phút) <span class="text-red-400">*</span></label>
                            <input type="number" name="so_phut_truoc_chieu_dang_chieu" value="{{ old('so_phut_truoc_chieu_dang_chieu', $settings->so_phut_truoc_chieu_dang_chieu) }}" required class="h-11 w-full rounded-xl border border-white/10 bg-[#151515] px-4 text-sm text-white outline-none focus:border-[#d99a32] transition">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs text-gray-400 font-bold">Mốc thời gian coi là Phim "Sắp ra mắt" (Ngày) <span class="text-red-400">*</span></label>
                            <input type="number" name="so_ngay_truoc_chieu_sap_ra_mat" value="{{ old('so_ngay_truoc_chieu_sap_ra_mat', $settings->so_ngay_truoc_chieu_sap_ra_mat) }}" required class="h-11 w-full rounded-xl border border-white/10 bg-[#151515] px-4 text-sm text-white outline-none focus:border-[#d99a32] transition">
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 4: NHÓM GIÁ VÉ --}}
            <div id="tab-pricing" class="tab-panel hidden space-y-5">
                <div class="p-6 rounded-2xl border border-white/5 bg-[#121212]/60 space-y-5">
                    <h4 class="text-lg font-black text-[#f4c56a] uppercase tracking-wider m-0 border-b border-white/5 pb-3">
                        <i class="fa-solid fa-tags mr-1.5"></i> Ma Trận Giá Sàn Hệ Thống (VND)
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div class="space-y-1.5">
                            <label class="text-xs text-gray-400 font-bold">Giá Vé Sàn Ngày Thường</label>
                            <input type="number" name="gia_ngay_thuong" value="{{ old('gia_ngay_thuong', (int)$settings->gia_ngay_thuong) }}" required class="h-11 w-full rounded-xl border border-white/10 bg-[#151515] px-4 text-sm text-[#f4c56a] font-bold outline-none focus:border-[#d99a32] transition">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs text-gray-400 font-bold">Giá Vé Sàn Cuối Tuần</label>
                            <input type="number" name="gia_cuoi_tuan" value="{{ old('gia_cuoi_tuan', (int)$settings->gia_cuoi_tuan) }}" required class="h-11 w-full rounded-xl border border-white/10 bg-[#151515] px-4 text-sm text-[#f4c56a] font-bold outline-none focus:border-[#d99a32] transition">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs text-gray-400 font-bold">Mức Phụ Thu Ghế VIP</label>
                            <input type="number" name="phu_thu_ghe_vip" value="{{ old('phu_thu_ghe_vip', (int)$settings->phu_thu_ghe_vip) }}" required class="h-11 w-full rounded-xl border border-white/10 bg-[#151515] px-4 text-sm text-[#f4c56a] font-bold outline-none focus:border-[#d99a32] transition">
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 5: CỔNG THANH TOÁN --}}
            <div id="tab-payment" class="tab-panel hidden space-y-5">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="p-6 rounded-2xl border border-white/5 bg-[#121212]/60 space-y-4">
                        <div class="flex items-center justify-between border-b border-white/5 pb-3">
                            <h4 class="text-base font-black text-white uppercase tracking-wider m-0 flex items-center gap-2">
                                <i class="fa-solid fa-wallet text-blue-400"></i> Cổng VNPay
                            </h4>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="bat_tat_vnpay" value="1" {{ old('bat_tat_vnpay', $settings->bat_tat_vnpay) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-white/10 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-gray-400 peer-checked:after:bg-[#d99a32] after:border-transparent after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#d99a32]/20 border border-white/10"></div>
                            </label>
                        </div>
                        <div class="space-y-3">
                            <div class="space-y-1.5">
                                <label class="text-xs text-gray-400 font-semibold">vnp_TmnCode</label>
                                <input type="text" name="vnpay_tmn_code" value="{{ old('vnpay_tmn_code', $settings->vnpay_tmn_code) }}" class="h-10 w-full rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs text-gray-400 font-semibold">vnp_HashSecret</label>
                                <input type="password" name="vnpay_hash_secret" value="{{ old('vnpay_hash_secret', $settings->vnpay_hash_secret) }}" class="h-10 w-full rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition">
                            </div>
                        </div>
                    </div>

                    <div class="p-6 rounded-2xl border border-white/5 bg-[#121212]/60 space-y-4">
                        <div class="flex items-center justify-between border-b border-white/5 pb-3">
                            <h4 class="text-base font-black text-white uppercase tracking-wider m-0 flex items-center gap-2">
                                <i class="fa-solid fa-wallet text-pink-500"></i> Ví MoMo
                            </h4>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="bat_tat_momo" value="1" {{ old('bat_tat_momo', $settings->bat_tat_momo) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-white/10 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-gray-400 peer-checked:after:bg-[#d99a32] after:border-transparent after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#d99a32]/20 border border-white/10"></div>
                            </label>
                        </div>
                        <div class="space-y-3">
                            <div class="space-y-1.5">
                                <label class="text-xs text-gray-400 font-semibold">Partner Code</label>
                                <input type="text" name="momo_partner_code" value="{{ old('momo_partner_code', $settings->momo_partner_code) }}" class="h-10 w-full rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs text-gray-400 font-semibold">Access Key</label>
                                <input type="text" name="momo_access_key" value="{{ old('momo_access_key', $settings->momo_access_key) }}" class="h-10 w-full rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs text-gray-400 font-semibold">Secret Key</label>
                                <input type="password" name="momo_secret_key" value="{{ old('momo_secret_key', $settings->momo_secret_key) }}" class="h-10 w-full rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end border-t border-white/10 pt-5">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-8 h-12 text-sm font-black text-white border-0 cursor-pointer transition hover:scale-[1.01] active:scale-95 duration-150">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Lưu Toàn Bộ Cấu Hình Hệ Thống
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabPanels = document.querySelectorAll('.tab-panel');

            tabButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const target = this.dataset.target;
                    tabButtons.forEach(b => {
                        b.classList.remove('bg-[#d99a32]', 'text-[#2b1208]');
                        b.classList.add('bg-white/5', 'text-gray-400', 'font-bold');
                    });
                    this.classList.remove('bg-white/5', 'text-gray-400', 'font-bold');
                    this.classList.add('bg-[#d99a32]', 'text-[#2b1208]', 'font-black');

                    tabPanels.forEach(panel => {
                        panel.classList.toggle('hidden', panel.id !== target);
                        panel.classList.toggle('block', panel.id === target);
                    });
                });
            });

            const bindPreview = (inputId, previewId) => {
                const input = document.getElementById(inputId);
                const preview = document.getElementById(previewId);
                if (input && preview) {
                    input.addEventListener('change', function() {
                        const file = this.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = e => preview.src = e.target.result;
                            reader.readAsDataURL(file);
                        }
                    });
                }
            };
            bindPreview('logo_input', 'logo_preview');
            bindPreview('bg_login_input', 'bg_login_preview');
        });
    </script>
@endpush