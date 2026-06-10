@extends('layouts.admin')

@section('page-title', 'Sửa Quốc Gia')

@section('content')

<div class="admin-panel">

    {{-- HEADER --}}
    <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

        <div>
            <h5 class="text-2xl font-black text-white">Sửa quốc gia</h5>
            <small class="text-gray-400">Cập nhật thông tin quốc gia</small>
        </div>

        <a href="{{ route('admin.quoc-gias.index') }}"
           class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-white">
            <i class="fa-solid fa-arrow-left"></i>
            Quay lại
        </a>

    </div>

    {{-- FORM --}}
    <form action="{{ route('admin.quoc-gias.update', $quocGia) }}" method="POST" class="mt-6 space-y-6">

        @csrf
        @method('PUT')

        {{-- TÊN QUỐC GIA --}}
        <div>
            <label class="text-sm text-gray-300">Tên quốc gia</label>

            <input type="text"
                   name="ten_quoc_gia"
                   value="{{ old('ten_quoc_gia', $quocGia->ten_quoc_gia) }}"
                   class="w-full rounded-xl border px-4 py-3 text-white bg-[#151515]
                   {{ $errors->has('ten_quoc_gia') ? 'border-red-500' : 'border-white/10' }}">

            @error('ten_quoc_gia')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- MÃ QUỐC GIA --}}
        <div>
            <label class="text-sm text-gray-300">Mã quốc gia</label>

            <input type="text"
                   name="ma_quoc_gia"
                   value="{{ old('ma_quoc_gia', $quocGia->ma_quoc_gia) }}"
                   class="w-full rounded-xl border px-4 py-3 text-white bg-[#151515]
                   {{ $errors->has('ma_quoc_gia') ? 'border-red-500' : 'border-white/10' }}">

            @error('ma_quoc_gia')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- BUTTON --}}
        <div class="flex justify-end gap-3">

            <a href="{{ route('admin.quoc-gias.index') }}"
               class="rounded-xl border border-white/10 bg-white/5 px-6 py-3 text-white">
                Hủy
            </a>

            <button type="submit"
                class="rounded-xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-8 py-3 font-bold text-white">
                Cập nhật
            </button>

        </div>

    </form>

</div>

@endsection