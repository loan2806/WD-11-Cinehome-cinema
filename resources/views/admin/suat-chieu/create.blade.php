@extends('layouts.admin')

@section('title', 'Them suat chieu')
@section('page-title', 'Them suat chieu')
@section('page-subtitle', 'Moi rap khong duoc trung khung gio chieu')

@section('content')
<form method="POST" action="{{ route('admin.suat-chieu.store') }}" class="admin-panel max-w-4xl">
    @csrf

    <div class="grid gap-4">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">Phim</label>
                <select name="phim_id" class="w-full rounded-xl border border-white/10 bg-[#111] px-4 py-3 text-white" required>
                    <option value="">Chon phim</option>
                    @foreach($phims as $phim)
                        <option value="{{ $phim->id }}" @selected((int) old('phim_id') === $phim->id)>
                            {{ $phim->ten_phim }} - {{ $phim->thoi_luong }} phut
                        </option>
                    @endforeach
                </select>
                @error('phim_id')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">Rap</label>
                <select name="rap_chieu_phim_id" class="w-full rounded-xl border border-white/10 bg-[#111] px-4 py-3 text-white" required>
                    <option value="">Chon rap</option>
                    @foreach($raps as $rap)
                        <option value="{{ $rap->id }}" @selected((int) old('rap_chieu_phim_id') === $rap->id)>
                            {{ $rap->ten_rap }} - {{ $rap->thanh_pho }}
                        </option>
                    @endforeach
                </select>
                @error('rap_chieu_phim_id')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">Ngay chieu</label>
                <input type="date" name="ngay_chieu" value="{{ old('ngay_chieu', now('Asia/Ho_Chi_Minh')->toDateString()) }}" min="{{ now('Asia/Ho_Chi_Minh')->toDateString() }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white" required>
                @error('ngay_chieu')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">Gio bat dau</label>
                <input type="time" name="gio_chieu" value="{{ old('gio_chieu') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white" required>
                @error('gio_chieu')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">Gia ve</label>
                <input type="number" name="gia_ve" value="{{ old('gia_ve', 90000) }}" min="1000" max="500000" step="1000" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white" required>
                @error('gia_ve')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="rounded-xl border border-[#d99a32]/25 bg-[#d99a32]/10 px-4 py-3 text-sm text-[#f4c56a]">
            He thong tu kiem tra trung lich trong cung rap. Thoi gian ket thuc tinh bang thoi luong phim cong 15 phut don rap.
        </div>

        <div class="flex flex-wrap gap-3">
            <button class="btn-admin"><i class="fa-solid fa-calendar-plus"></i> Luu suat chieu</button>
            <a href="{{ route('admin.suat-chieu.index') }}" class="btn-admin-outline">Huy</a>
        </div>
    </div>
</form>
@endsection
