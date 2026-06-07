@extends('layouts.admin')

@section('page-title', 'Sửa Suất Chiếu')

@section('content')

    <div class="admin-panel">

        <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            <div>

                <h5 class="text-2xl font-black text-white">
                    Sửa suất chiếu
                </h5>

                <small class="text-gray-400">
                    Cập nhật thông tin suất chiếu
                </small>

            </div>

            <a href="{{ route('admin.suat-chieus.index') }}"
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

        <form action="{{ route('admin.suat-chieus.update', $suatChieu) }}" method="POST" class="mt-6 space-y-6">

            @csrf

            @method('PUT')

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <div class="space-y-2">

                    <label class="text-sm text-gray-400">
                        Phim <span class="text-red-400">*</span>
                    </label>

                    <select name="phim_id" id="phim_id" required
                        class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                        @foreach ($phims as $phim)
                            <option value="{{ $phim->id }}" data-thoi-luong="{{ $phim->thoi_luong }}"
                                {{ $suatChieu->phim_id == $phim->id ? 'selected' : '' }}>
                                {{ $phim->ten_phim }} ({{ $phim->thoi_luong ?? 120 }} phút)
                            </option>
                        @endforeach

                    </select>

                </div>

                <div class="space-y-2">

                    <label class="text-sm text-gray-400">
                        Rạp Chiếu <span class="text-red-400">*</span>
                    </label>

                    <select name="rap_chieu_phim_id" id="rap_chieu_phim_id" required
                        class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                        @foreach ($rapChieuPhims as $rap)
                            <option value="{{ $rap->id }}"
                                {{ $suatChieu->rap_chieu_phim_id == $rap->id ? 'selected' : '' }}>
                                {{ $rap->ten_rap }}
                            </option>
                        @endforeach

                    </select>

                </div>

                <div class="space-y-2">

                    <label class="text-sm text-gray-400">
                        Phòng Chiếu <span class="text-red-400">*</span>
                    </label>

                    <select name="phong_chieu_id" id="phong_chieu_id" required
                        class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                        @foreach ($phongChieus ?? [] as $phong)
                            <option value="{{ $phong->id }}"
                                {{ $suatChieu->phong_chieu_id == $phong->id ? 'selected' : '' }}>
                                {{ $phong->ten_phong }}
                            </option>
                        @endforeach

                    </select>

                </div>

                <div class="space-y-2">

                    <label class="text-sm text-gray-400">
                        Giá Vé (VNĐ) <span class="text-red-400">*</span>
                    </label>

                    <input type="number" name="gia_ve" value="{{ old('gia_ve', $suatChieu->gia_ve) }}" min="0" step="1000" required
                        class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                </div>

                <div class="space-y-2">

                    <label class="text-sm text-gray-400">
                        Ngày & Giờ Chiếu <span class="text-red-400">*</span>
                    </label>

                    <input type="datetime-local" name="thoi_gian_chieu" value="{{ old('thoi_gian_chieu', $suatChieu->thoi_gian_chieu->format('Y-m-d\TH:i')) }}" required
                        class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                </div>

                <div class="space-y-2">

                    <label class="text-sm text-gray-400">
                        Trạng Thái <span class="text-red-400">*</span>
                    </label>

                    <select name="trang_thai" required
                        class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                        @foreach (\App\Models\SuatChieu::TRANG_THAI_LIST as $value => $label)
                            <option value="{{ $value }}" {{ old('trang_thai', $suatChieu->trang_thai) == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach

                    </select>

                </div>

            </div>

            <div class="flex items-center justify-end gap-4 border-t border-white/10 pt-6">

                <a href="{{ route('admin.suat-chieus.index') }}"
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
