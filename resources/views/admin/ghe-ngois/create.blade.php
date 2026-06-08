@extends('layouts.admin')

@section('page-title', 'Thêm Ghế Ngồi')

@section('content')

    <div class="admin-panel">

        <div class="panel-header flex items-center justify-between">

            <div>

                <h5 class="text-2xl font-black text-white">
                    Thêm ghế ngồi mới
                </h5>

                <small class="text-gray-400">
                    Điền thông tin để thêm ghế vào hệ thống
                </small>

            </div>

            <a href="{{ route('admin.ghe-ngois.index') }}"
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

        <form action="{{ route('admin.ghe-ngois.store') }}" method="POST" class="mt-6 space-y-6">

            @csrf

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <div class="space-y-2">

                    <label class="text-sm text-gray-400">
                        Phòng Chiếu <span class="text-red-400">*</span>
                    </label>

                    <select name="phong_chieu_id" id="phong_chieu_id" required
                        class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                        <option value="">-- Chọn Phòng --</option>

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
                        Hàng Ghế <span class="text-red-400">*</span>
                    </label>

                    <select name="hang_ghe_id" id="hang_ghe_id" required
                        class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                        <option value="">-- Chọn Hàng --</option>

                    </select>

                </div>

                <div class="space-y-2">

                    <label class="text-sm text-gray-400">
                        Loại Ghế <span class="text-red-400">*</span>
                    </label>

                    <select name="loai_ghe_id" id="loai_ghe_id" required
                        class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                        <option value="">-- Chọn Loại --</option>

                        @foreach ($loaiGhes as $loai)
                            <option value="{{ $loai->id }}"
                                {{ old('loai_ghe_id') == $loai->id ? 'selected' : '' }}>
                                {{ $loai->ten_loai }} (+{{ number_format($loai->phu_thu) }}đ)
                            </option>
                        @endforeach

                    </select>

                </div>

                <div class="space-y-2">

                    <label class="text-sm text-gray-400">
                        Trạng Thái <span class="text-red-400">*</span>
                    </label>

                    <select name="trang_thai" id="trang_thai" required
                        class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                        <option value="hoat_dong"
                            {{ old('trang_thai', 'hoat_dong') == 'hoat_dong' ? 'selected' : '' }}>
                            Hoạt động
                        </option>

                        <option value="bao_tri"
                            {{ old('trang_thai') == 'bao_tri' ? 'selected' : '' }}>
                            Bảo trì
                        </option>

                    </select>

                </div>

                <div class="space-y-2">

                    <label class="text-sm text-gray-400">
                        Mã Ghế <span class="text-red-400">*</span>
                    </label>

                    <input type="text" name="ma_ghe" id="ma_ghe" value="{{ old('ma_ghe') }}"
                        placeholder="VD: A1, B2" maxlength="10" required
                        class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                </div>

                <div class="space-y-2">

                    <label class="text-sm text-gray-400">
                        Cột <span class="text-red-400">*</span>
                    </label>

                    <input type="number" name="cot" id="cot" value="{{ old('cot', 1) }}" min="1" required
                        class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                </div>

            </div>

            <div class="flex items-center justify-end gap-4 border-t border-white/10 pt-6">

                <a href="{{ route('admin.ghe-ngois.index') }}"
                    class="rounded-2xl border border-white/10 bg-white/5 px-6 py-3 font-medium text-white transition hover:bg-white/10">

                    Hủy

                </a>

                <button type="submit"
                    class="rounded-2xl bg-gradient-to-r from-[#6b3a1e] via-[#a66a2b] to-[#d9a441] px-8 py-3 font-semibold text-white shadow-lg shadow-amber-900/30">

                    <i class="fa-solid fa-save mr-2"></i>

                    Lưu

                </button>

            </div>

        </form>

    </div>

@endsection
