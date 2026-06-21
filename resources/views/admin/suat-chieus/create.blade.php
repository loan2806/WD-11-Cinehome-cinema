@extends('layouts.admin')

@section('page-title', 'Thêm Suất Chiếu')

@section('content')

    <div class="admin-panel space-y-6">

        {{-- HEADER CONTROL --}}
        <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-[#110c06]/30 p-5 rounded-2xl border border-[#d99a32]/10 backdrop-blur-md">
            <div>
                <h5 class="text-2xl font-black text-white m-0 flex items-center gap-2">
                    <i class="fa-solid fa-calendar-plus text-[#d99a32]"></i>
                    Thêm suất chiếu mới
                </h5>
                <small class="text-gray-400 block mt-1">
                    Cấu hình lên lịch chiếu đơn lẻ hoặc rải chuỗi suất chiếu hàng loạt tự động hóa hệ thống.
                </small>
            </div>

            <a href="{{ route('admin.suat-chieus.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-white/10 no-underline cursor-pointer">
                <i class="fa-solid fa-arrow-left"></i>
                Quay lại
            </a>
        </div>

        {{-- ERROR VALIDATION REPORT --}}
        @if ($errors->any())
            <div class="rounded-2xl border border-red-500/30 bg-red-500/10 p-4">
                <ul class="list-inside list-disc text-red-400 m-0 p-0 text-sm font-semibold space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.suat-chieus.store') }}" method="POST" class="space-y-6 m-0">
            @csrf

            {{-- PHÂN KHU 1: THÔNG TIN GỐC CỐ ĐỊNH (PHIM - RẠP - PHÒNG) --}}
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <div class="space-y-2">
                    <label class="text-sm text-gray-400 font-bold block">
                        Phim Trình Chiếu <span class="text-red-400">*</span>
                    </label>
                    <select name="phim_id" id="phim_id" required
                        class="w-full h-11 rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition">
                        <option value="">-- Chọn Phim --</option>
                        @foreach ($phims as $phim)
                            <option value="{{ $phim->id }}" data-thoi-luong="{{ $phim->thoi_luong }}"
                                {{ old('phim_id') == $phim->id ? 'selected' : '' }}>
                                {{ $phim->ten_phim }} ({{ $phim->thoi_luong ?? 90 }} phút)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="text-sm text-gray-400 font-bold block">
                        Cơ Sở Rạp Phim <span class="text-red-400">*</span>
                    </label>
                    <select name="rap_chieu_phim_id" id="rap_chieu_phim_id" required
                        class="w-full h-11 rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition">
                        <option value="">-- Chọn Rạp --</option>
                        @foreach ($rapChieuPhims as $rap)
                            <option value="{{ $rap->id }}"
                                {{ old('rap_chieu_phim_id') == $rap->id ? 'selected' : '' }}>
                                {{ $rap->ten_rap }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="text-sm text-gray-400 font-bold block">
                        Phòng Chiếu Mục Tiêu <span class="text-red-400">*</span>
                    </label>
                    <select name="phong_chieu_id" id="phong_chieu_id" required
                        class="w-full h-11 rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition">
                        <option value="">-- Chọn Phòng --</option>
                        @foreach ($phongChieus ?? [] as $phong)
                            <option value="{{ $phong->id }}"
                                {{ old('phong_chieu_id') == $phong->id ? 'selected' : '' }}>
                                {{ $phong->ten_phong }} ({{ strtoupper($phong->loai_phong) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- PHÂN KHU CENTRAL: CHỌN PHƯƠNG THỨC LÊN LỊCH CHIẾU --}}
            <div class="p-5 rounded-2xl border border-[#d99a32]/20 bg-[#110b06]/40 grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                <div class="space-y-1">
                    <label class="text-base text-[#f4c56a] font-black uppercase tracking-wider block m-0">
                        Phương thức cấu hình lịch
                    </label>
                    <p class="text-xs text-gray-400 m-0">Chọn hình thức lên lịch đơn lẻ truyền thống hoặc rải chuỗi tự động theo ngày.</p>
                </div>
                <div>
                    <select name="loai_tao" id="loai_tao" required
                        class="w-full rounded-xl border-2 border-[#d99a32]/30 bg-[#1c120a] px-4 h-12 text-white font-bold outline-none focus:border-[#d99a32] transition cursor-pointer">
                        <option value="don_le" {{ old('loai_tao', 'don_le') === 'don_le' ? 'selected' : '' }}>Cấu hình 1 suất chiếu đơn lẻ</option>
                        <option value="hang_loat" {{ old('loai_tao') === 'hang_loat' ? 'selected' : '' }}>Rải chuỗi suất chiếu hàng loạt tự động (Khuyên dùng)</option>
                    </select>
                </div>
            </div>

            {{-- PHÂN KHU NHÁNH ĐƠN LẺ (💡 ĐÃ THU GỌN VỀ 2 CỘT DO TRẠNG THÁI TỰ ĐỘNG) --}}
            <div id="khu_don_le" class="grid grid-cols-1 gap-5 md:grid-cols-2 transition-all duration-300">
                <div class="space-y-2">
                    <label class="text-sm text-gray-400 font-bold block">
                        Chọn Ngày Chiếu <span class="text-red-400">*</span>
                    </label>
                    <input type="date" name="ngay_chieu_don_le" id="ngay_chieu_don_le" min="{{ date('Y-m-d') }}" value="{{ old('ngay_chieu_don_le') }}"
                        class="w-full h-11 rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition cursor-pointer [&::-webkit-calendar-picker-indicator]:invert">
                </div>

                <div class="space-y-2">
                    <label class="text-sm text-gray-400 font-bold block">
                        Chọn Giờ Khởi Chiếu <span class="text-red-400">*</span>
                    </label>
                    <input type="time" name="gio_chieu_don_le" id="gio_chieu_don_le" value="{{ old('gio_chieu_don_le') }}"
                        class="w-full h-11 rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition cursor-pointer [&::-webkit-calendar-picker-indicator]:invert">
                </div>
            </div>

            {{-- PHÂN KHU NHÁNH HÀNG LOẠT --}}
            <div id="khu_hang_loat" class="hidden space-y-5 transition-all duration-300">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-sm text-gray-400 font-bold block">Từ ngày <span class="text-red-400">*</span></label>
                        <input type="date" name="ngay_bat_dau" min="{{ date('Y-m-d') }}" value="{{ old('ngay_bat_dau') }}"
                            class="w-full h-11 rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition cursor-pointer [&::-webkit-calendar-picker-indicator]:invert">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm text-gray-400 font-bold block">Đến hết ngày <span class="text-red-400">*</span></label>
                        <input type="date" name="ngay_ket_thuc" min="{{ date('Y-m-d') }}" value="{{ old('ngay_ket_thuc') }}"
                            class="w-full h-11 rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition cursor-pointer [&::-webkit-calendar-picker-indicator]:invert">
                    </div>
                </div>

                <div class="p-5 rounded-2xl border border-white/5 bg-white/5 space-y-4">
                    <label class="text-sm text-[#f4c56a] font-black uppercase tracking-wider block">
                        Chọn các khung giờ chiếu trong ngày <span class="text-red-400">*</span>
                    </label>
                    
                    <div class="flex flex-wrap gap-3" id="khung_gio_checkboxes">
                        @php
                            $khungGioMacDinh = ['08:30', '11:00', '13:30', '16:00', '18:30', '21:00', '23:30'];
                        @endphp
                        @foreach($khungGioMacDinh as $gio)
                            <label class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-white/10 bg-[#1a1a1a] cursor-pointer select-none transition hover:border-[#d99a32]">
                                <input type="checkbox" name="khung_gio[]" value="{{ $gio }}" class="accent-[#d99a32]">
                                <span class="text-sm font-bold text-gray-200">{{ $gio }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="pt-4 border-t border-white/5 flex items-center gap-3 max-w-sm">
                        <input type="time" id="custom_time_input" class="h-10 rounded-xl border border-white/10 bg-[#151515] px-3 text-white outline-none text-sm cursor-pointer [&::-webkit-calendar-picker-indicator]:invert">
                        <button type="button" id="btn_add_custom_time" class="h-10 px-4 text-xs font-bold rounded-xl bg-gradient-to-r from-gray-800 to-gray-700 text-gray-300 hover:from-[#d99a32] hover:to-[#8a4a21] hover:text-white transition border-0 cursor-pointer">
                            + Chèn khung giờ khác
                        </button>
                    </div>
                </div>
            </div>

            {{-- CẤU HÌNH BIỂU GIÁ THỦ CÔNG --}}
            <div class="p-5 rounded-2xl border border-white/5 bg-white/5 space-y-3">
                <label class="text-sm text-[#f4c56a] font-black uppercase tracking-wider block m-0">
                    Ghi Đè Biểu Giá Suất Chiếu Tùy Chỉnh
                </label>
                <div class="max-w-md space-y-2">
                    <div class="relative flex items-center">
                        <input type="number" name="gia_ve_tuy_chinh" placeholder="Bỏ trống để dùng ma trận giá tự động..." value="{{ old('gia_ve_tuy_chinh') }}"
                            class="h-11 w-full rounded-xl border border-white/10 bg-[#151515] pl-4 pr-12 text-sm text-[#f4c56a] font-black outline-none focus:border-[#d99a32] transition [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                        <span class="absolute right-4 text-xs text-gray-500 font-bold select-none">VND</span>
                    </div>
                    <p class="text-xs text-gray-500 m-0 leading-relaxed">
                        💡 <span class="text-gray-400 font-bold">Lưu ý nghiệp vụ:</span> Hãy điền số tiền vào đây nếu muốn gán giá cố định cho các ngày <span class="text-white font-bold">Lễ/Tết</span> hoặc phim bom tấn. Nếu để trống, hệ thống tự động chạy ma trận giá sàn được lấy từ cấu hình tham số gốc.
                    </p>
                </div>
            </div>

            {{-- 💡 BẢNG MONITOR GIÁM SÁT TRẠNG THÁI VÀ DÒNG THỜI GIAN ĐỘNG --}}
            <div class="p-5 rounded-2xl border border-white/5 bg-[#121212]/90 space-y-3">
                <label class="text-xs text-gray-400 font-black uppercase tracking-widest block m-0">
                    <i class="fa-solid fa-desktop text-[#d99a32] mr-1"></i> Hệ Thống Monitor Suất Chiếu Tự Động Real-time
                </label>
                
                <div class="space-y-2">
                    <span class="text-xs text-gray-400 font-bold block">Bảng phân tích dòng thời gian chiếm dụng phòng:</span>
                    <input type="text" id="thoi_luong_preview" class="w-full rounded-xl border border-0 bg-white/5 px-4 py-2.5 text-[#f4c56a] font-black outline-none text-sm shadow-inner"
                           value="" readonly placeholder="Chọn thông số lịch để hiển thị bảng phân tích dòng thời gian...">
                </div>

                <div class="pt-3 border-t border-white/5 grid grid-cols-2 sm:grid-cols-4 gap-3 text-[11px] text-gray-400 font-semibold">
                    <div class="flex items-center gap-1.5"><i class="fa-solid fa-circle text-blue-500 text-[8px]"></i> Sắp ra mắt (>30 ngày trước chiếu)</div>
                    <div class="flex items-center gap-1.5"><i class="fa-solid fa-circle text-yellow-500 text-[8px]"></i> Sắp chiếu (Thuộc khung lịch bán)</div>
                    <div class="flex items-center gap-1.5"><i class="fa-solid fa-circle text-green-500 text-[8px]"></i> Đang chiếu (Khung giờ chiếu + gối đầu)</div>
                    <div class="flex items-center gap-1.5"><i class="fa-solid fa-circle text-gray-600 text-[8px]"></i> Đã chiếu (Quá giờ kết thúc phim)</div>
                </div>
            </div>

            {{-- FOOTER ĐIỀU HƯỚNG LỆNH --}}
            <div class="flex items-center justify-end gap-3 border-t border-white/10 pt-5">
                <a href="{{ route('admin.suat-chieus.index') }}"
                    class="rounded-xl border border-white/10 bg-white/5 px-5 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-white/10 hover:text-white no-underline">
                    Hủy bỏ
                </a>

                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-6 py-2.5 text-sm font-black text-white shadow-lg border-0 cursor-pointer transition hover:opacity-95 duration-200">
                    <i class="fa-solid fa-save"></i>
                    Xác nhận lên lịch chiếu
                </button>
            </div>
        </form>
    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const loaiParam = document.getElementById('loai_tao');
    const khuDonLe = document.getElementById('khu_don_le');
    const khuHangLoat = document.getElementById('khu_hang_loat');
    
    const phimSelect = document.getElementById('phim_id');
    const thoiLuongPreview = document.getElementById('thoi_luong_preview');
    
    const ngayChieuDonLe = document.getElementById('ngay_chieu_don_le');
    const gioChieuDonLe = document.getElementById('gio_chieu_don_le');

    // 💡 ĐỌC ĐỘNG THỜI GIAN DỌN PHÒNG TỪ CONTROLLER TRUYỀN XUỐNG
    const thoiGianDonPhong = {{ $thoiGianDonPhong }};

    function switchFormMode() {
        if (loaiParam.value === 'don_le') {
            khuDonLe.style.display = 'grid';
            khuHangLoat.style.display = 'none';
            ngayChieuDonLe.setAttribute('required', 'required');
            gioChieuDonLe.setAttribute('required', 'required');
            document.getElementsByName('ngay_bat_dau')[0].removeAttribute('required');
            document.getElementsByName('ngay_ket_thuc')[0].removeAttribute('required');
        } else {
            khuDonLe.style.display = 'none';
            khuHangLoat.style.display = 'block';
            ngayChieuDonLe.removeAttribute('required');
            gioChieuDonLe.removeAttribute('required');
            document.getElementsByName('ngay_bat_dau')[0].setAttribute('required', 'required');
            document.getElementsByName('ngay_ket_thuc')[0].setAttribute('required', 'required');
        }
        updateTimePreview();
    }
    
    loaiParam.addEventListener('change', switchFormMode);
    switchFormMode();

    function updateTimePreview() {
        const selectedOption = phimSelect.options[phimSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            thoiLuongPreview.value = "Chưa có phim nào được chọn để phân tích.";
            return;
        }
        
        const thoiLuong = parseInt(selectedOption.dataset.thoiLuong) || 90;
        
        if (loaiParam.value === 'don_le') {
            if (ngayChieuDonLe.value && gioChieuDonLe.value) {
                const start = new Date(`${ngayChieuDonLe.value}T${gioChieuDonLe.value}`);
                const end = new Date(start.getTime() + (thoiLuong + thoiGianDonPhong) * 60000);
                
                const formatTime = (d) => d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
                thoiLuongPreview.value = `Suất chiếu đơn lẻ dự kiến: ${formatTime(start)} - ${formatTime(end)} (Phòng chiếu bị chiếm dụng ${thoiLuong + thoiGianDonPhong} phút bao gồm dọn vệ sinh gốc).`;
            } else {
                thoiLuongPreview.value = `Phim đã chọn: Thời lượng gốc ${thoiLuong} phút (+ ${thoiGianDonPhong} phút giãn cách dọn phòng vệ sinh rạp động).`;
            }
        } else {
            thoiLuongPreview.value = `Chế độ hàng loạt: Tự động rải chuỗi suất chiếu thời lượng gốc ${thoiLuong} phút (+ ${thoiGianDonPhong} phút giãn cách gốc) vào các khung giờ được chọn.`;
        }
    }

    phimSelect.addEventListener('change', updateTimePreview);
    ngayChieuDonLe.addEventListener('change', updateTimePreview);
    gioChieuDonLe.addEventListener('change', updateTimePreview);

    const btnAddCustomTime = document.getElementById('btn_add_custom_time');
    const customTimeInput = document.getElementById('custom_time_input');
    const checkboxesContainer = document.getElementById('khung_gio_checkboxes');

    btnAddCustomTime.addEventListener('click', function() {
        const customTime = customTimeInput.value;
        if (!customTime) {
            alert('Vui lòng chọn mốc giờ hợp lệ trên thanh công cụ picker trước khi chèn!');
            return;
        }

        const existingCheckboxes = checkboxesContainer.querySelectorAll('input[type="checkbox"]');
        let isExist = false;
        existingCheckboxes.forEach(input => {
            if (input.value === customTime) isExist = true;
        });

        if (isExist) {
            alert('Khung giờ này đã có trong danh sách chọn lựa!');
            return;
        }

        const newLabel = document.createElement('label');
        newLabel.className = "flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#d99a32] bg-[#1a1a1a] cursor-pointer select-none transition";
        newLabel.innerHTML = `
            <input type="checkbox" name="khung_gio[]" value="${customTime}" checked class="accent-[#d99a32]">
            <span class="text-sm font-bold text-[#f4c56a]">${customTime}</span>
        `;
        
        checkboxesContainer.appendChild(newLabel);
        customTimeInput.value = '';
    });
});
</script>
@endpush