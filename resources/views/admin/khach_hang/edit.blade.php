@extends('layouts.admin')

@section('title', 'Sửa khách hàng')
@section('page-title', 'Sửa khách hàng')
@section('page-subtitle', 'Cập nhật hồ sơ và trạng thái tài khoản khách hàng')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">

    <a href="{{ route('admin.khach-hang.show', $khachHang) }}"
        class="inline-flex items-center gap-2 text-sm font-bold text-[#d99a32] no-underline transition hover:translate-x-1">
        <i class="fa-solid fa-arrow-left"></i>
        Quay lại chi tiết khách hàng
    </a>

    <div class="rounded-3xl border border-white/10 bg-[#121212] p-6 shadow-2xl">
        <h2 class="mb-6 text-2xl font-black text-white">
            Thông tin khách hàng
        </h2>

        <form method="POST" action="{{ route('admin.khach-hang.update', $khachHang) }}" class="space-y-5">
            @csrf
            @method('PATCH')

            {{-- Họ tên --}}
            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">
                    Họ tên khách hàng
                </label>

                <input type="text" name="ho_ten" value="{{ old('ho_ten', $khachHang->ho_ten) }}"
                    class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                @error('ho_ten')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">
                    Email
                </label>

                <input type="email" name="email" value="{{ old('email', $khachHang->email) }}"
                    class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                @error('email')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Số điện thoại --}}
            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">
                    Số điện thoại
                </label>

                <input type="text" name="so_dien_thoai" value="{{ old('so_dien_thoai', $khachHang->so_dien_thoai) }}"
                    placeholder="Ví dụ: 0987654321"
                    class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                @error('so_dien_thoai')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Ngày sinh --}}
            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">
                    Ngày sinh
                </label>

                @if($khachHang->ngay_sinh)
                {{-- Đã có ngày sinh thì khóa lại, tránh đổi ngày sinh để nhận voucher nhiều lần --}}
                <input type="date" value="{{ $khachHang->ngay_sinh->format('Y-m-d') }}" disabled
                    class="w-full cursor-not-allowed rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-gray-400 outline-none">

                <input type="hidden" name="ngay_sinh" value="{{ $khachHang->ngay_sinh->format('Y-m-d') }}">

                <p class="mt-2 text-xs text-yellow-400">
                    Ngày sinh đã được khóa sau khi lưu để đảm bảo chính sách voucher sinh nhật.
                </p>
                @else
                {{-- Chưa có ngày sinh thì admin được nhập một lần --}}
                <input type="date" name="ngay_sinh" value="{{ old('ngay_sinh') }}"
                    class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white outline-none focus:border-[#d99a32]">

                <p class="mt-2 text-xs text-gray-500">
                    Ngày sinh chỉ được nhập một lần. Sau khi lưu sẽ không thể chỉnh sửa.
                </p>
                @endif

                @error('ngay_sinh')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Trạng thái hoạt động --}}
            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">
                    Trạng thái tài khoản
                </label>

                <select name="trang_thai_hoat_dong"
                    class="w-full rounded-2xl border border-white/10 bg-[#1a1a1a] px-4 py-3 text-white outline-none focus:border-[#d99a32]">
                    <option value="1" @selected(old('trang_thai_hoat_dong', $khachHang->trang_thai_hoat_dong) == 1)>
                        Đang hoạt động
                    </option>

                    <option value="0" @selected(old('trang_thai_hoat_dong', $khachHang->trang_thai_hoat_dong) == 0)>
                        Bị khóa
                    </option>
                </select>

                @error('trang_thai_hoat_dong')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-3 border-t border-white/10 pt-5">
                <a href="{{ route('admin.khach-hang.show', $khachHang) }}"
                    class="rounded-2xl bg-white/10 px-5 py-3 text-sm font-bold text-white no-underline transition hover:bg-white/15">
                    Hủy
                </a>

                <button type="submit"
                    class="rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-6 py-3 text-sm font-black text-white transition hover:-translate-y-1">
                    <i class="fa-solid fa-save mr-2"></i>
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection