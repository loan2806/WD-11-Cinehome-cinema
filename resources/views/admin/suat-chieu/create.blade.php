@extends('layouts.admin')

@section('title', 'Thêm suất chiếu')
@section('page-title', 'Thêm suất chiếu')
@section('page-subtitle', 'Mỗi phòng chiếu chỉ nhận một phim trong cùng khung giờ')

@section('content')
<form method="POST" action="{{ route('admin.suat-chieu.store') }}" class="admin-panel max-w-4xl">
    @csrf

    <div class="grid gap-4">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">Phim</label>
                <select name="movie_id" class="w-full rounded-xl border border-white/10 bg-[#111] px-4 py-3 text-white" required>
                    <option value="">Chọn phim</option>
                    @foreach($phims as $phim)
                        <option value="{{ $phim->id }}" @selected((int) old('movie_id') === $phim->id)>
                            {{ $phim->title }} - {{ $phim->duration }} phút
                        </option>
                    @endforeach
                </select>
                @error('movie_id')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">Rạp</label>
                <select name="cinema_id" class="w-full rounded-xl border border-white/10 bg-[#111] px-4 py-3 text-white" required>
                    <option value="">Chọn rạp</option>
                    @foreach($raps as $rap)
                        <option value="{{ $rap->id }}" @selected((int) old('cinema_id') === $rap->id)>
                            {{ $rap->name }} - {{ $rap->city }}
                        </option>
                    @endforeach
                </select>
                @error('cinema_id')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">Phòng chiếu</label>
                <input
                    name="room_name"
                    value="{{ old('room_name', 'Phòng 1') }}"
                    list="phong-chieu-goi-y"
                    class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white"
                    required
                >
                <datalist id="phong-chieu-goi-y">
                    @foreach($phongChieuMacDinh as $phong)
                        <option value="{{ $phong }}">
                    @endforeach
                </datalist>
                @error('room_name')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">Giá vé</label>
                <input
                    type="number"
                    name="price"
                    value="{{ old('price', 90000) }}"
                    min="1000"
                    max="500000"
                    step="1000"
                    class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white"
                    required
                >
                @error('price')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">Ngày chiếu</label>
                <input
                    type="date"
                    name="show_date"
                    value="{{ old('show_date', now('Asia/Ho_Chi_Minh')->toDateString()) }}"
                    min="{{ now('Asia/Ho_Chi_Minh')->toDateString() }}"
                    class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white"
                    required
                >
                @error('show_date')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">Giờ bắt đầu</label>
                <input
                    type="time"
                    name="show_time"
                    value="{{ old('show_time') }}"
                    class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white"
                    required
                >
                @error('show_time')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="rounded-xl border border-[#d99a32]/25 bg-[#d99a32]/10 px-4 py-3 text-sm text-[#f4c56a]">
            Hệ thống tự kiểm tra trùng lịch theo cùng rạp, cùng phòng, cùng ngày. Mỗi suất được cộng thêm 15 phút để khách ra vào và dọn phòng.
        </div>

        <div class="flex flex-wrap gap-3">
            <button class="btn-admin">
                <i class="fa-solid fa-calendar-plus"></i> Lưu suất chiếu
            </button>
            <a href="{{ route('admin.suat-chieu.index') }}" class="btn-admin-outline">Hủy</a>
        </div>
    </div>
</form>
@endsection
