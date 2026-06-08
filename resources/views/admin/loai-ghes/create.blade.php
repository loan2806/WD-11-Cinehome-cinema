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

            </div>

            <div class="space-y-2">

                <label class="text-sm text-gray-400">
                    Mô tả
                </label>

                <textarea name="mo_ta" id="mo_ta" rows="4" placeholder="Nhập mô tả..."
                    class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">{{ old('mo_ta') }}</textarea>

            </div>

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
