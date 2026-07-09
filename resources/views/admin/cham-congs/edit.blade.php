@extends('layouts.admin')

@section('title', 'Chỉnh sửa chấm công')
@section('page-title', 'Chỉnh sửa chấm công')
@section('page-subtitle', 'Cập nhật bản ghi chấm công nhân viên')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.cham-congs.index') }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-[#151515] text-white transition hover:bg-white/5">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-2xl font-black text-white">Chỉnh sửa chấm công</h2>
            <p class="text-gray-400">Cập nhật thông tin chấm công của nhân viên ngày {{ $chamCong->ngay->format('d/m/Y') }}</p>
        </div>
    </div>

    @if(session('error'))
        <div class="rounded-xl border border-red-500/20 bg-red-500/10 p-4 text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-2xl border border-white/10 bg-[#151515] p-6">
        <form method="POST" action="{{ route('admin.cham-congs.update', $chamCong) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Tên nhân viên (Chỉ đọc) -->
                <div>
                    <label class="mb-2 block text-sm font-bold text-gray-300">Nhân viên</label>
                    <input type="text" readonly value="{{ $chamCong->nguoiDung->ho_ten }} ({{ $chamCong->nguoiDung->email }})"
                           class="w-full rounded-xl border border-white/10 bg-[#1f1f1f] px-4 py-3 text-gray-400 focus:outline-none cursor-not-allowed">
                </div>

                <!-- Ngày chấm công (Chỉ đọc) -->
                <div>
                    <label class="mb-2 block text-sm font-bold text-gray-300">Ngày chấm công</label>
                    <input type="text" readonly value="{{ $chamCong->ngay->format('d/m/Y') }}"
                           class="w-full rounded-xl border border-white/10 bg-[#1f1f1f] px-4 py-3 text-gray-400 focus:outline-none cursor-not-allowed">
                </div>
            </div>

            <!-- Loại chấm công -->
            @php
                $loai = 'di_lam';
                if ($chamCong->nghi_phep) {
                    $loai = 'nghi_phep';
                } elseif ($chamCong->nghi_khong_phep) {
                    $loai = 'nghi_khong_phep';
                }
            @endphp
            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">Trạng thái chấm công <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-3 gap-4">
                    <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-white/10 bg-[#101010] p-4 text-white hover:border-[#d99a32] transition">
                        <input type="radio" name="loai_cham_cong" value="di_lam" class="text-[#d99a32] focus:ring-0" 
                               {{ old('loai_cham_cong', $loai) === 'di_lam' ? 'checked' : '' }} onchange="toggleWorkTimeFields(true)">
                        <span>Đi làm</span>
                    </label>
                    <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-white/10 bg-[#101010] p-4 text-white hover:border-[#d99a32] transition">
                        <input type="radio" name="loai_cham_cong" value="nghi_phep" class="text-[#d99a32] focus:ring-0"
                               {{ old('loai_cham_cong', $loai) === 'nghi_phep' ? 'checked' : '' }} onchange="toggleWorkTimeFields(false)">
                        <span>Nghỉ phép</span>
                    </label>
                    <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-white/10 bg-[#101010] p-4 text-white hover:border-[#d99a32] transition">
                        <input type="radio" name="loai_cham_cong" value="nghi_khong_phep" class="text-[#d99a32] focus:ring-0"
                               {{ old('loai_cham_cong', $loai) === 'nghi_khong_phep' ? 'checked' : '' }} onchange="toggleWorkTimeFields(false)">
                        <span>Không phép</span>
                    </label>
                </div>
                @error('loai_cham_cong')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Khung nhập giờ làm việc (Chỉ hiện khi chọn đi làm) -->
            <div id="work-time-fields" class="space-y-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div>
                        <label for="gio_vao" class="mb-2 block text-sm font-bold text-gray-300">Giờ vào <span class="text-red-500">*</span></label>
                        <input type="time" name="gio_vao" id="gio_vao" 
                               value="{{ old('gio_vao', $chamCong->gio_vao ? \Carbon\Carbon::parse($chamCong->gio_vao)->format('H:i') : '08:00') }}"
                               class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white focus:border-[#d99a32] focus:outline-none cursor-pointer" style="color-scheme: dark;" onclick="this.showPicker()">
                        @error('gio_vao')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="gio_ra" class="mb-2 block text-sm font-bold text-gray-300">Giờ ra <span class="text-red-500">*</span></label>
                        <input type="time" name="gio_ra" id="gio_ra" 
                               value="{{ old('gio_ra', $chamCong->gio_ra ? \Carbon\Carbon::parse($chamCong->gio_ra)->format('H:i') : '17:00') }}"
                               class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white focus:border-[#d99a32] focus:outline-none cursor-pointer" style="color-scheme: dark;" onclick="this.showPicker()">
                        @error('gio_ra')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="so_gio_tang_ca" class="mb-2 block text-sm font-bold text-gray-300">Giờ tăng ca</label>
                        <input type="number" step="0.5" name="so_gio_tang_ca" id="so_gio_tang_ca" 
                               value="{{ old('so_gio_tang_ca', $chamCong->so_gio_tang_ca) }}"
                               placeholder="Để trống tự động tính"
                               class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white focus:border-[#d99a32] focus:outline-none">
                        @error('so_gio_tang_ca')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Ghi chú -->
            <div>
                <label for="ghi_chu" class="mb-2 block text-sm font-bold text-gray-300">Ghi chú</label>
                <textarea name="ghi_chu" id="ghi_chu" rows="3"
                          class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white focus:border-[#d99a32] focus:outline-none">{{ old('ghi_chu', $chamCong->ghi_chu) }}</textarea>
                @error('ghi_chu')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nút gửi -->
            <div class="flex justify-end gap-3 border-t border-white/10 pt-6">
                <a href="{{ route('admin.cham-congs.index') }}" class="rounded-xl border border-white/10 bg-[#1f1f1f] px-6 py-3 font-bold text-white transition hover:bg-white/5">
                    Hủy bỏ
                </a>
                <button type="submit" class="rounded-xl bg-[#d99a32] px-6 py-3 font-bold text-[#2b1208] transition hover:bg-[#d99a32]/85">
                    Cập nhật chấm công
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleWorkTimeFields(show) {
        const fields = document.getElementById('work-time-fields');
        const gioVao = document.getElementById('gio_vao');
        const gioRa = document.getElementById('gio_ra');
        
        if (show) {
            fields.style.display = 'block';
            gioVao.required = true;
            gioRa.required = true;
        } else {
            fields.style.display = 'none';
            gioVao.required = false;
            gioRa.required = false;
        }
    }

    // Khởi tạo trạng thái ban đầu dựa vào giá trị old
    document.addEventListener('DOMContentLoaded', function() {
        const checkedInput = document.querySelector('input[name="loai_cham_cong"]:checked');
        const isWorkSelected = checkedInput ? checkedInput.value === 'di_lam' : true;
        toggleWorkTimeFields(isWorkSelected);
    });
</script>
@endsection
