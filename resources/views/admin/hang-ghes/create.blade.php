@extends('layouts.admin')

@section('page-title', 'Thêm Hàng Ghế')

@section('content')

    <div class="admin-panel">

        <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            <div>

                <h5 class="text-2xl font-black text-white">
                    Thêm hàng ghế mới
                </h5>

                <small class="text-gray-400">
                    Điền thông tin để thêm hàng ghế vào hệ thống
                </small>

            </div>

            <a href="{{ route('admin.hang-ghes.index') }}"
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

        <form action="{{ route('admin.hang-ghes.store') }}" method="POST" class="mt-6 space-y-6">

            @csrf

            <div class="space-y-2">

                <label class="text-sm text-gray-400">
                    Phòng Chiếu <span class="text-red-400">*</span>
                </label>

                <select name="phong_chieu_id" id="phong_chieu_id" required
                    class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                    <option value="">-- Chọn Phòng Chiếu --</option>

                    @foreach ($phongChieus as $phong)
                        <option value="{{ $phong->id }}"
                            {{ old('phong_chieu_id', $phongChieuId) == $phong->id ? 'selected' : '' }}>
                            {{ $phong->ten_phong }} - {{ $phong->rapChieuPhim->ten_rap ?? '' }}
                        </option>
                    @endforeach

                </select>

            </div>

            <div class="space-y-2">

                <label class="text-sm text-gray-400">
                    Tên Hàng Ghế <span class="text-red-400">*</span>
                </label>

                <input type="text" name="ten_hang" id="ten_hang" value="{{ old('ten_hang') }}" placeholder="VD: A, B, C..."
                    maxlength="10" required
                    class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                <small class="text-xs text-gray-500">VD: A, B, C, D... (tối đa 10 ký tự)</small>

            </div>

            <div class="flex items-center justify-end gap-4 border-t border-white/10 pt-6">

                <a href="{{ route('admin.hang-ghes.index') }}"
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
