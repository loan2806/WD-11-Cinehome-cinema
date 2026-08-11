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

            {{-- PHU THU VE THEO LOAI PHONG --}}
            <div>

                <label for="phu_thu" class="mb-2 block text-sm font-medium text-gray-300">
                    Phụ Thu Vé (VNĐ)
                </label>

                <input type="number" name="phu_thu" id="phu_thu"
                    value="{{ old('phu_thu', 0) }}"
                    min="0" step="1000"
                    class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none transition focus:border-[#d99a32]">

                <small class="mt-1 block text-xs text-gray-500">Tự gợi ý theo loại phòng đã chọn ở trên, có thể chỉnh lại. Cộng thêm vào giá vé ngày thường/cuối tuần khi tạo suất chiếu.</small>

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
    const phuThuInput = document.getElementById('phu_thu');
    if (!loaiPhongSelect || !phuThuInput) return;

    // Mức phụ thu gợi ý theo loại phòng — khớp với PhongChieu::PHU_THU_GOI_Y
    const phuThuGoiY = @json(\App\Models\PhongChieu::PHU_THU_GOI_Y);

    let daTuChinhPhuThu = false;
    phuThuInput.addEventListener('input', function () {
        daTuChinhPhuThu = true;
    });

    loaiPhongSelect.addEventListener('change', function () {
        // Chỉ tự điền nếu người dùng CHƯA tự tay sửa ô phụ thu, tránh ghi đè
        // giá trị họ vừa nhập tay.
        if (daTuChinhPhuThu) return;
        const goiY = phuThuGoiY[loaiPhongSelect.value];
        if (goiY !== undefined) {
            phuThuInput.value = goiY;
        }
    });
});
</script>
@endpush

@endsection
