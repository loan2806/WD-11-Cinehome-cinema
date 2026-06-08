@extends('layouts.admin')

@section('page-title', 'Thêm Thể Loại Phim')

@section('content')

    <div class="admin-panel">

        <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            <div>

                <h5 class="text-2xl font-black text-white">
                    Thêm thể loại phim mới
                </h5>

                <small class="text-gray-400">
                    Điền thông tin để thêm thể loại vào hệ thống
                </small>

            </div>

            <a href="{{ route('admin.the-loais.index') }}"
                class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">

                <i class="fa-solid fa-arrow-left"></i>

                Quay lại

            </a>

        </div>

        {{-- FORM --}}
        <form action="{{ route('admin.the-loais.store') }}" method="POST" class="mt-6 space-y-6" novalidate>

            @csrf

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                {{-- TÊN THỂ LOẠI --}}
                <div class="space-y-2 md:col-span-1">

                    <label class="block text-sm font-semibold text-gray-300">
                        Tên Thể Loại <span class="text-red-400">*</span>
                    </label>

                    <input type="text" name="ten_the_loai" id="ten_the_loai" value="{{ old('ten_the_loai') }}"
                        placeholder="VD: Hành động, Kinh dị, Tình cảm..." maxlength="255" required
                        class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32] {{ $errors->has('ten_the_loai') ? 'border-red-500' : '' }}">

                    @error('ten_the_loai')
                        <p class="text-xs text-red-400">{{ $message }}</p>
                    @enderror

                </div>

            </div>

            {{-- MÔ TẢ --}}
            <div class="space-y-2">

                <label class="block text-sm font-semibold text-gray-300">
                    Mô Tả
                </label>

                <textarea name="mo_ta" id="mo_ta" rows="5" placeholder="Nhập mô tả chi tiết về thể loại này..."
                    class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">{{ old('mo_ta') }}</textarea>

                <small class="text-xs text-gray-500">Tối đa 500 ký tự</small>

                @error('mo_ta')
                    <p class="text-xs text-red-400">{{ $message }}</p>
                @enderror

            </div>

            {{-- ACTIONS --}}
            <div class="flex items-center justify-end gap-4 border-t border-white/10 pt-6">

                <a href="{{ route('admin.the-loais.index') }}"
                    class="rounded-2xl border border-white/10 bg-white/5 px-6 py-3 font-medium text-white transition hover:bg-white/10">

                    Hủy

                </a>

                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-8 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">

                    <i class="fa-solid fa-save"></i>

                    Thêm Thể Loại

                </button>

            </div>

        </form>

    </div>

@endsection
