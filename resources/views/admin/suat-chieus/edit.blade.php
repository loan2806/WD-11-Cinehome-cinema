@extends('layouts.admin')

@section('page-title', 'Chỉnh Sửa Suất Chiếu')

@section('content')

    <div class="admin-panel space-y-6">

        {{-- HEADER CONTROL --}}
        <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-[#110c06]/30 p-5 rounded-2xl border border-[#d99a32]/10 backdrop-blur-md">
            <div>
                <h5 class="text-2xl font-black text-white m-0 flex items-center gap-2">
                    <i class="fa-solid fa-calendar-check text-[#d99a32]"></i>
                    Cập nhật thông tin suất chiếu #{{ $suatChieu->id }}
                </h5>
                <small class="text-gray-400 block mt-1">
                    Điều chỉnh tham số vận hành, biểu giá hoặc thiết lập trạng thái đóng/hủy suất chiếu khẩn cấp.
                </small>
            </div>

            <a href="{{ route('admin.suat-chieus.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-white/10 no-underline cursor-pointer">
                <i class="fa-solid fa-arrow-left"></i>
                Quay lại
            </a>
        </div>

        {{-- KIỂM TRA NGHIỆP VỤ: CẢNH BÁO KHÓA DỮ LIỆU NẾU PHIM ĐÃ HOẶC ĐANG CHIẾU --}}
        @php
            $isLocked = in_array($suatChieu->trang_thai, ['dang_chieu', 'dung_nhan_ve', 'da_chieu']);
        @endphp

        @if($isLocked)
            <div class="rounded-2xl border border-yellow-500/30 bg-yellow-500/10 p-4 flex items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation text-yellow-500 text-xl mt-0.5"></i>
                <div>
                    <h6 class="text-yellow-500 font-bold m-0 text-sm">Khóa dữ liệu cốt lõi do Suất chiếu đang vận hành!</h6>
                    <p class="text-xs text-gray-400 m-0 mt-1 leading-relaxed">
                        Suất chiếu này đã bước vào giai đoạn mở cổng đón khách hoặc đã kết thúc. Hệ thống đã tự động đóng băng các trường thông tin: <span class="text-white font-bold">Phim, Rạp, Phòng chiếu, Ngày & Giờ</span> để bảo toàn dữ liệu vé của khách hàng. Bạn chỉ có thể điều chỉnh Biểu giá hoặc chọn trạng thái Hủy suất nếu có sự cố phòng chiếu.
                    </p>
                </div>
            </div>
        @endif

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

        <form action="{{ route('admin.suat-chieus.update', $suatChieu->id) }}" method="POST" class="space-y-6 m-0">
            @csrf
            @method('PUT')

            {{-- PHÂN KHU 1: THÔNG TIN GỐC CỐ ĐỊNH (TỰ ĐỘNG KHÓA NẾU DÍNH LUẬT BẢO VỆ) --}}
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <div class="space-y-2">
                    <label class="text-sm text-gray-400 font-bold block">Phim Trình Chiếu</label>
                    <select name="phim_id" id="phim_id" required {{ $isLocked ? 'disabled' : '' }}
                        class="w-full h-11 rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition disabled:opacity-50 disabled:cursor-not-allowed">
                        @foreach ($phims as $phim)
                            <option value="{{ $phim->id }}" data-thoi-luong="{{ $phim->thoi_luong }}"
                                {{ old('phim_id', $suatChieu->phim_id) == $phim->id ? 'selected' : '' }}>
                                {{ $phim->ten_phim }} ({{ $phim->thoi_luong ?? 90 }} phút)
                            </option>
                        @endforeach
                    </select>
                    @if($isLocked) <input type="hidden" name="phim_id" value="{{ $suatChieu->phim_id }}"> @endif
                </div>

                <div class="space-y-2">
                    <label class="text-sm text-gray-400 font-bold block">Cơ Sở Rạp Phim</label>
                    <select name="rap_chieu_phim_id" id="rap_chieu_phim_id" required {{ $isLocked ? 'disabled' : '' }}
                        class="w-full h-11 rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition disabled:opacity-50 disabled:cursor-not-allowed">
                        @foreach ($rapChieuPhims as $rap)
                            <option value="{{ $rap->id }}" {{ old('rap_chieu_phim_id', $suatChieu->rap_chieu_phim_id) == $rap->id ? 'selected' : '' }}>
                                {{ $rap->ten_rap }}
                            </option>
                        @endforeach
                    </select>
                    @if($isLocked) <input type="hidden" name="rap_chieu_phim_id" value="{{ $suatChieu->rap_chieu_phim_id }}"> @endif
                </div>

                <div class="space-y-2">
                    <label class="text-sm text-gray-400 font-bold block">Phòng Chiếu Mục Tiêu</label>
                    <select name="phong_chieu_id" id="phong_chieu_id" required {{ $isLocked ? 'disabled' : '' }}
                        class="w-full h-11 rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition disabled:opacity-50 disabled:cursor-not-allowed">
                        @foreach ($phongChieus as $phong)
                            <option value="{{ $phong->id }}" {{ old('phong_chieu_id', $suatChieu->phong_chieu_id) == $phong->id ? 'selected' : '' }}>
                                {{ $phong->ten_phong }} ({{ strtoupper($phong->loai_phong) }})
                            </option>
                        @endforeach
                    </select>
                    @if($isLocked) <input type="hidden" name="phong_chieu_id" value="{{ $suatChieu->phong_chieu_id }}"> @endif
                </div>
            </div>

            {{-- PHÂN KHU 2: THỜI GIAN ĐÃ TÁCH BIỆT CHUẨN ĐỊNH DẠNG HỆ THỐNG --}}
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div class="space-y-2">
                    <label class="text-sm text-gray-400 font-bold block">Ngày Chiếu <span class="text-red-400">*</span></label>
                    <input type="date" name="ngay_chieu" id="ngay_chieu" required {{ $isLocked ? 'disabled' : '' }}
                        value="{{ old('ngay_chieu', \Carbon\Carbon::parse($suatChieu->thoi_gian_chieu)->format('Y-m-d')) }}"
                        class="w-full h-11 rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed [&::-webkit-calendar-picker-indicator]:invert">
                    @if($isLocked) <input type="hidden" name="ngay_chieu" value="{{ \Carbon\Carbon::parse($suatChieu->thoi_gian_chieu)->format('Y-m-d') }}"> @endif
                </div>

                <div class="space-y-2">
                    <label class="text-sm text-gray-400 font-bold block">Giờ Khởi Chiếu <span class="text-red-400">*</span></label>
                    <input type="time" name="gio_chieu" id="gio_chieu" required {{ $isLocked ? 'disabled' : '' }}
                        value="{{ old('gio_chieu', \Carbon\Carbon::parse($suatChieu->thoi_gian_chieu)->format('H:i')) }}"
                        class="w-full h-11 rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed [&::-webkit-calendar-picker-indicator]:invert">
                    @if($isLocked) <input type="hidden" name="gio_chieu" value="{{ \Carbon\Carbon::parse($suatChieu->thoi_gian_chieu)->format('H:i') }}"> @endif
                </div>
            </div>

            {{-- PHÂN KHU 3: ĐIỀU CHỈNH BIỂU GIÁ & THIẾT LẬP TRẠNG THÁI KHẨN CẤP --}}
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                {{-- CẤU HÌNH BIỂU GIÁ THỦ CÔNG --}}
                <div class="p-5 rounded-2xl border border-white/5 bg-white/5 space-y-3">
                    <label class="text-sm text-[#f4c56a] font-black uppercase tracking-wider block m-0">
                        Ghi Đè Biểu Giá Suất Chiếu Tùy Chỉnh
                    </label>
                    <div class="relative flex items-center">
                        <input type="number" name="gia_ve_tuy_chinh" id="gia_ve_tuy_chinh" placeholder="Dùng biểu giá ma trận tự động..."
                            value="{{ old('gia_ve_tuy_chinh', $suatChieu->gia_ve_tu_dong ? '' : $suatChieu->gia_ve) }}"
                            class="h-11 w-full rounded-xl border border-white/10 bg-[#151515] pl-4 pr-12 text-sm text-[#f4c56a] font-black outline-none focus:border-[#d99a32] transition [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                        <span class="absolute right-4 text-xs text-gray-500 font-bold select-none">VND</span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <small class="text-gray-500 block leading-relaxed text-[11px]">
                            @if ($suatChieu->gia_ve_tu_dong)
                                Đang ở chế độ <b>tự động</b>: giá cuối cùng hiện tại là <b>{{ number_format($suatChieu->gia_ve_cuoi_cung) }}đ</b> (giá ngày thường/cuối tuần + phụ thu phòng). Để trống ô này và Lưu để giữ chế độ tự động — mỗi khi phòng đổi phụ thu, giá vé sẽ tự cập nhật ngay, không cần vào sửa lại.
                            @else
                                Suất này đang dùng <b>giá cố định thủ công</b> ({{ number_format($suatChieu->gia_ve) }}đ), sẽ KHÔNG tự cộng thêm phụ thu phòng dù phòng có đổi giá. Muốn quay lại tự động, bấm nút bên cạnh rồi Lưu.
                            @endif
                        </small>
                        <button type="button" id="btnDungGiaTuDong"
                            class="shrink-0 rounded-lg border border-[#d99a32]/40 bg-[#d99a32]/10 px-3 py-1.5 text-[11px] font-bold text-[#f4c56a] hover:bg-[#d99a32]/20 transition whitespace-nowrap">
                            <i class="fa-solid fa-rotate mr-1"></i>Dùng giá tự động
                        </button>
                    </div>
                </div>

                {{-- 💡 LOGIC TRẠNG THÁI MỚI: Chỉ cho phép chọn chạy tự động hoặc hủy suất --}}
                <div class="p-5 rounded-2xl border border-white/5 bg-white/5 space-y-3">
                    <label class="text-sm text-[#f4c56a] font-black uppercase tracking-wider block m-0">
                        Quản Trị Quy Trình Trạng Thái
                    </label>
                    <select name="trang_thai" required
                        class="w-full h-11 rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition cursor-pointer">
                        <option value="sap_chieu" {{ $suatChieu->trang_thai !== 'huy' ? 'selected' : '' }}>
                            🔄 Kích hoạt chế độ quét tự động Real-time (Khuyên dùng)
                        </option>
                        <option value="huy" {{ $suatChieu->trang_thai === 'huy' ? 'selected' : '' }}>
                            🚫 Hủy suất chiếu khẩn cấp (Sự cố phòng máy/Thiết bị)
                        </option>
                    </select>
                    <small class="text-gray-500 block leading-relaxed text-[11px]">
                        Giao diện đã loại bỏ tính năng gán cứng trạng thái Đang chiếu/Đã chiếu bằng tay để nhường quyền xử lý cho Lõi thuật toán gối đầu của CineHome.
                    </small>
                </div>
            </div>

            {{-- MONITOR MONITORING --}}
            <div class="p-5 rounded-2xl border border-white/5 bg-[#121212]/90 space-y-2">
                <label class="text-xs text-gray-400 font-black uppercase tracking-widest block m-0">
                    <i class="fa-solid fa-desktop text-[#d99a32] mr-1"></i> Bảng tính toán dòng thời gian chiếm dụng phòng máy mới
                </label>
                <input type="text" id="thoi_luong_preview" class="w-full rounded-xl border border-0 bg-white/5 px-4 py-2.5 text-[#f4c56a] font-black outline-none text-sm shadow-inner"
                       value="" readonly>
            </div>

            {{-- FOOTER ĐIỀU HƯỚNG LỆNH --}}
            <div class="flex items-center justify-end gap-3 border-t border-white/10 pt-5">
                <a href="{{ route('admin.suat-chieus.index') }}"
                    class="rounded-xl border border-white/10 bg-white/5 px-5 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-white/10 hover:text-white no-underline">
                    Hủy bỏ
                </a>

                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-6 py-2.5 text-sm font-black text-white shadow-lg border-0 cursor-pointer transition hover:opacity-95 duration-200">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    Lưu lại thay đổi
                </button>
            </div>
        </form>
    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const phimSelect = document.getElementById('phim_id');
    const ngayChieuInput = document.getElementById('ngay_chieu');
    const gioChieuInput = document.getElementById('gio_chieu');
    const thoiLuongPreview = document.getElementById('thoi_luong_preview');

    const giaVeTuyChinhInput = document.getElementById('gia_ve_tuy_chinh');
    const btnDungGiaTuDong = document.getElementById('btnDungGiaTuDong');
    if (btnDungGiaTuDong && giaVeTuyChinhInput) {
        btnDungGiaTuDong.addEventListener('click', function () {
            giaVeTuyChinhInput.value = '';
            giaVeTuyChinhInput.focus();
        });
    }

    // Đọc tham số động giãn cách dọn rạp từ controller truyền xuống
    const thoiGianDonPhong = {{ $thoiGianDonPhong }};

    function updateTimePreview() {
        const selectedOption = phimSelect.options[phimSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            thoiLuongPreview.value = "Thông tin phim không hợp lệ.";
            return;
        }
        
        const thoiLuong = parseInt(selectedOption.dataset.thoiLuong) || 90;
        
        if (ngayChieuInput.value && gioChieuInput.value) {
            const start = new Date(`${ngayChieuInput.value}T${gioChieuInput.value}`);
            const end = new Date(start.getTime() + (thoiLuong + thoiGianDonPhong) * 60000);
            
            const formatTime = (d) => d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
            thoiLuongPreview.value = `Dòng thời gian sau căn chỉnh: ${formatTime(start)} - ${formatTime(end)} (Phòng máy chiếm dụng tổng cộng ${thoiLuong + thoiGianDonPhong} phút tính cả vệ sinh phòng chiếu).`;
        } else {
            thoiLuongPreview.value = `Thời lượng phim: ${thoiLuong} phút (+ ${thoiGianDonPhong} phút giãn cách).`;
        }
    }

    phimSelect.addEventListener('change', updateTimePreview);
    ngayChieuInput.addEventListener('change', updateTimePreview);
    gioChieuInput.addEventListener('change', updateTimePreview);
    
    // Khởi chạy tính toán ngay khi tải trang
    updateTimePreview();
});
</script>
@endpush