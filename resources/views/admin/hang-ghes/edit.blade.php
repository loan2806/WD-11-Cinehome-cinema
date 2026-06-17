@extends('layouts.admin')

@section('page-title', 'Sửa Hàng Ghế')

@section('content')

    <div class="admin-panel">

        <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            <div>

                <h5 class="text-2xl font-black text-white">
                    Sửa hàng ghế
                </h5>

                <small class="text-gray-400">
                    Cập nhật thông tin hàng ghế
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

        <form action="{{ route('admin.hang-ghes.update', $hangGhe) }}" method="POST" class="mt-6 space-y-6">

            @csrf

            @method('PUT')

            <div class="space-y-2">

                <label class="text-sm text-gray-400">
                    Tên Hàng Ghế <span class="text-red-400">*</span>
                </label>

                <input type="text" name="ten_hang" id="ten_hang" value="{{ old('ten_hang', $hangGhe->ten_hang) }}"
                    maxlength="10" required
                    class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">

            </div>

            <div class="space-y-2">

                <label class="text-sm text-gray-400">
                    Loại ghế mặc định cho hàng
                </label>

                <select name="loai_ghe_mac_dinh_id" id="loai_ghe_mac_dinh_id"
                    class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                    <option value="">-- Không đặt --</option>

                    @foreach ($loaiGhes as $loai)
                        <option value="{{ $loai->id }}"
                            {{ old('loai_ghe_mac_dinh_id', $hangGhe->loai_ghe_mac_dinh_id) == $loai->id ? 'selected' : '' }}>
                            {{ $loai->ten_loai }} (+{{ number_format($loai->phu_thu) }}đ)
                        </option>
                    @endforeach

                </select>

            </div>

            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-white/10 bg-[#0f0f0f] p-4">

                <input type="checkbox" name="la_hang_couple" value="1"
                    {{ old('la_hang_couple', $hangGhe->la_hang_couple) ? 'checked' : '' }}
                    class="mt-1 h-4 w-4 rounded border-white/20 bg-[#151515] text-[#d99a32] focus:ring-[#d99a32]">

                <div>

                    <div class="text-sm font-semibold text-white">
                        Đây là hàng ghép đôi (Couple)
                    </div>

                    <div class="mt-1 text-xs text-gray-400">
                        Bật nếu hàng này có ghế couple. Hệ thống sẽ tự động ghép 2 ghế liền kề thành 1 cặp khi tạo ghế.
                    </div>

                </div>

            </label>

            <div class="flex items-center justify-end gap-4 border-t border-white/10 pt-6">

                <a href="{{ route('admin.hang-ghes.index') }}"
                    class="rounded-2xl border border-white/10 bg-white/5 px-6 py-3 font-medium text-white transition hover:bg-white/10">

                    Hủy

                </a>

                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-8 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">

                    <i class="fa-solid fa-save"></i>

                    Cập nhật

                </button>

            </div>

        </form>

    </div>

@endsection
