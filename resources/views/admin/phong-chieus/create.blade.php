@extends('layouts.admin')

@section('page-title', 'Thêm Phòng Chiếu')

@section('content')

<div class="admin-panel">

    <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

        <div>

            <h5 class="text-2xl font-black text-white">
                Thêm phòng chiếu mới
            </h5>

            <small class="text-gray-400">
                Điền thông tin để tạo phòng chiếu mới
            </small>

        </div>

        <a href="{{ route('admin.phong-chieus.index') }}"
            class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">

            <i class="fa-solid fa-arrow-left"></i>

            Quay lại

        </a>

    </div>

    @if ($errors->any())
        <div class="mt-6 rounded-xl border border-red-500/30 bg-red-500/10 p-4">
            <ul class="list-inside list-disc text-sm text-red-400">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.phong-chieus.store') }}" method="POST" class="mt-6">

        @csrf

        <div class="grid gap-5 lg:grid-cols-2">

            {{-- RAP CHIEU --}}
            <div>

                <label for="rap_chieu_phim_id" class="mb-2 block text-sm font-medium text-gray-300">
                    Rạp Chiếu Phim <span class="text-red-400">*</span>
                </label>

                <select name="rap_chieu_phim_id" id="rap_chieu_phim_id"
                    class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none transition focus:border-[#d99a32]"
                    required>

                    <option value="">-- Chọn Rạp --</option>

                    @foreach($rapChieuPhims as $rap)
                        <option value="{{ $rap->id }}" {{ old('rap_chieu_phim_id') == $rap->id ? 'selected' : '' }}>
                            {{ $rap->ten_rap }}
                        </option>
                    @endforeach

                </select>

            </div>

            {{-- TEN PHONG --}}
            <div>

                <label for="ten_phong" class="mb-2 block text-sm font-medium text-gray-300">
                    Tên Phòng Chiếu <span class="text-red-400">*</span>
                </label>

                <input type="text" name="ten_phong" id="ten_phong"
                    value="{{ old('ten_phong') }}"
                    placeholder="VD: Phòng 1, Phòng VIP"
                    class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none transition focus:border-[#d99a32]"
                    required>

            </div>

            {{-- LOAI PHONG --}}
            <div>

                <label for="loai_phong" class="mb-2 block text-sm font-medium text-gray-300">
                    Loại Phòng Chiếu <span class="text-red-400">*</span>
                </label>

                <select name="loai_phong" id="loai_phong"
                    class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none transition focus:border-[#d99a32]"
                    required>

                    <option value="">-- Chọn Loại --</option>

                    <option value="2d" {{ old('loai_phong') == '2d' ? 'selected' : '' }}>2D</option>

                    <option value="3d" {{ old('loai_phong') == '3d' ? 'selected' : '' }}>3D</option>

                    <option value="imax" {{ old('loai_phong') == 'imax' ? 'selected' : '' }}>IMAX</option>

                    <option value="4dx" {{ old('loai_phong') == '4dx' ? 'selected' : '' }}>4DX</option>

                </select>

            </div>

            {{-- SUC CHUA --}}
            <div>

                <label for="suc_chua" class="mb-2 block text-sm font-medium text-gray-300">
                    Sức Chứa (ghế) <span class="text-red-400">*</span>
                </label>

                <input type="number" name="suc_chua" id="suc_chua"
                    value="{{ old('suc_chua', 80) }}"
                    min="1" max="500"
                    class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none transition focus:border-[#d99a32]"
                    required>

                <small class="mt-1 block text-xs text-gray-500">Tổng số ghế trong phòng</small>

            </div>

            {{-- PHU THU VE THEO LOAI PHONG (chi doc, quan ly theo LOAI) --}}
            <div>

                <label class="mb-2 block text-sm font-medium text-gray-300">
                    Phụ Thu Vé (VNĐ)
                </label>

                <div class="flex h-[50px] w-full items-center rounded-2xl border border-white/10 bg-[#151515]/60 px-4 text-[#f4c56a] font-bold">
                    <span id="phuThuHienThi">{{ number_format($phuThuTheoLoai[old('loai_phong', '2d')] ?? 0) }}đ</span>
                </div>

                <small class="mt-1 block text-xs text-gray-500">Theo đúng mức đang cấu hình cho loại phòng đã chọn ở trên. Muốn đổi, vào "Giá theo phòng chiếu" — áp dụng cho mọi phòng cùng loại.</small>

            </div>

            {{-- TRANG THAI --}}
            <div class="lg:col-span-2">

                <label for="trang_thai" class="mb-2 block text-sm font-medium text-gray-300">
                    Trạng Thái <span class="text-red-400">*</span>
                </label>

                <select name="trang_thai" id="trang_thai"
                    class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none transition focus:border-[#d99a32]"
                    required>

                    <option value="hoat_dong" {{ old('trang_thai', 'hoat_dong') == 'hoat_dong' ? 'selected' : '' }}>
                        Hoạt động
                    </option>

                    <option value="bao_tri" {{ old('trang_thai') == 'bao_tri' ? 'selected' : '' }}>
                        Bảo trì
                    </option>

                    <option value="ngung_hoat_dong" {{ old('trang_thai') == 'ngung_hoat_dong' ? 'selected' : '' }}>
                        Ngừng hoạt động
                    </option>

                </select>

            </div>

        </div>

        <div class="mt-6 flex justify-end gap-3">

            <a href="{{ route('admin.phong-chieus.index') }}"
                class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-6 py-3 text-sm font-bold text-white transition hover:bg-white/10">

                <i class="fa-solid fa-times"></i>

                Hủy

            </a>

            <button type="submit"
                class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-6 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">

                <i class="fa-solid fa-save"></i>

                Lưu phòng chiếu

            </button>

        </div>

    </form>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const loaiPhongSelect = document.getElementById('loai_phong');
    const phuThuHienThi = document.getElementById('phuThuHienThi');
    if (!loaiPhongSelect || !phuThuHienThi) return;

    // Phụ thu HIỆN TẠI đang cấu hình cho từng loại phòng (trang "Giá theo phòng chiếu")
    const phuThuTheoLoai = @json($phuThuTheoLoai);

    loaiPhongSelect.addEventListener('change', function () {
        const gia = phuThuTheoLoai[loaiPhongSelect.value];
        phuThuHienThi.textContent = (gia !== undefined ? Number(gia) : 0).toLocaleString('vi-VN') + 'đ';
    });
});
</script>
@endpush

@endsection
