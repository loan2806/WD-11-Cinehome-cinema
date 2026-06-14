@extends('layouts.admin')

@section('page-title', 'Thêm Loại Ghế')

@section('content')

    <div class="admin-panel">

        <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            <div>

                <h5 class="text-2xl font-black text-white">
                    Thêm loại ghế mới
                </h5>

                <small class="text-gray-400">
                    Điền thông tin để thêm loại ghế vào hệ thống
                </small>

            </div>

            <a href="{{ route('admin.loai-ghes.index') }}"
                class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">

                <i class="fa-solid fa-arrow-left"></i>

                Quay lại

            </a>

        </div>

        @if ($errors->any())
            <div class="mt-6 rounded-2xl border border-red-500/30 bg-red-500/10 p-4">
                <ul class="list-inside list-disc text-red-400">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.loai-ghes.store') }}" method="POST" class="mt-6 space-y-6">

            @csrf

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <div class="space-y-2">

                    <label class="text-sm text-gray-400">
                        Tên Loại Ghế <span class="text-red-400">*</span>
                    </label>

                    <input type="text" name="ten_loai" id="ten_loai" value="{{ old('ten_loai') }}"
                        placeholder="VD: Thường, VIP, Couple" maxlength="50" required
                        class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                </div>

                <div class="space-y-2">

                    <label class="text-sm text-gray-400">
                        Phụ Thu (VNĐ) <span class="text-red-400">*</span>
                    </label>

                    <input type="number" name="phu_thu" id="phu_thu" value="{{ old('phu_thu', 0) }}" min="0" step="1000" required
                        class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                    <small class="text-xs text-gray-500">Phụ thu cho loại ghế này (0 = không phụ thu)</small>

                </div>

                <div class="space-y-2">

                    <label class="text-sm text-gray-400">
                        Màu sắc hiển thị
                    </label>

                    <div class="flex items-center gap-3">
                        <input type="color" name="mau_sac" id="mau_sac" value="{{ old('mau_sac', '#666666') }}"
                            class="h-12 w-20 cursor-pointer rounded-xl border border-white/10 bg-transparent">
                        <input type="text" id="mau_sac_hex" value="{{ old('mau_sac', '#666666') }}"
                            placeholder="#666666" maxlength="7"
                            class="flex-1 rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">
                    </div>

                    <small class="text-xs text-gray-500">Màu hiển thị trên sơ đồ ghế</small>

                </div>

            </div>

            <div class="space-y-2">

                <label class="text-sm text-gray-400">
                    Mô tả
                </label>

                <textarea name="mo_ta" id="mo_ta" rows="4" placeholder="Nhập mô tả..."
                    class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">{{ old('mo_ta') }}</textarea>

            </div>

            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-white/10 bg-[#0f0f0f] p-4">

                <input type="checkbox" name="la_couple" value="1"
                    {{ old('la_couple') ? 'checked' : '' }}
                    class="mt-1 h-4 w-4 rounded border-white/20 bg-[#151515] text-[#d99a32] focus:ring-[#d99a32]">

                <div>

                    <div class="text-sm font-semibold text-white">
                        Đây là loại ghế ghép đôi (Couple)
                    </div>

                    <div class="mt-1 text-xs text-gray-400">
                        Ghế thuộc loại này sẽ được tự động ghép thành từng cặp 2 ghế liền kề trên sơ đồ phòng.
                    </div>

                </div>

            </label>

            <div class="flex items-center justify-end gap-4 border-t border-white/10 pt-6">

                <a href="{{ route('admin.loai-ghes.index') }}"
                    class="rounded-2xl border border-white/10 bg-white/5 px-6 py-3 font-medium text-white transition hover:bg-white/10">

                    Hủy

                </a>

                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-8 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">

                    <i class="fa-solid fa-save"></i>

                    Lưu

                </button>

            </div>

        </form>

    </div>

@endsection
