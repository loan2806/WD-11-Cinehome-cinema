@extends('layouts.admin')

@section('title', 'Cập nhật nhân viên')
@section('page-title', 'Cập nhật nhân viên')
@section('page-subtitle', 'Chỉnh sửa thông tin nhân viên')

@section('content')

<div class="mx-auto max-w-4xl">

    <div class="rounded-3xl border border-white/10 bg-[#151515] p-8 shadow-xl">

        <div class="mb-8 flex items-center gap-4">

            <div
                class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32]">

                <i class="fa-solid fa-user-pen text-2xl text-white"></i>

            </div>

            <div>

                <h2 class="text-2xl font-black text-white">
                    Chỉnh sửa nhân viên
                </h2>

                <p class="text-gray-400">
                    Cập nhật thông tin tài khoản nhân viên
                </p>

            </div>

        </div>

        <form
            action="{{ route('admin.nhanviens.update', $nhanvien) }}"
            method="POST">

            @csrf
            @method('PUT')

            <div class="grid gap-6 md:grid-cols-2">

                {{-- HỌ TÊN --}}
                <div>

                    <label class="mb-2 block text-sm font-bold text-white">
                        Họ và tên
                    </label>

                    <input
                        type="text"
                        name="ho_ten"
                        value="{{ old('ho_ten', $nhanvien->ho_ten) }}"
                        class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white outline-none transition focus:border-[#d99a32]"
                        required>

                    @error('ho_ten')
                        <p class="mt-2 text-sm text-red-400">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                {{-- EMAIL --}}
                <div>

                    <label class="mb-2 block text-sm font-bold text-white">
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        value="{{ old('email', $nhanvien->email) }}"
                        class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white outline-none transition focus:border-[#d99a32]"
                        required>

                    @error('email')
                        <p class="mt-2 text-sm text-red-400">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

            </div>

            {{-- THÔNG TIN TRẠNG THÁI --}}
            <div class="mt-8 rounded-2xl border border-white/10 bg-[#101010] p-5">

                <div class="flex items-center justify-between">

                    <div>

                        <h5 class="font-bold text-white">
                            Trạng thái tài khoản
                        </h5>

                        <p class="text-sm text-gray-400">
                            Theo dõi tình trạng hoạt động của nhân viên
                        </p>

                    </div>

                    @if($nhanvien->trang_thai_hoat_dong)

                        <span
                            class="rounded-full bg-green-500/20 px-4 py-2 text-sm font-bold text-green-400">

                            Đang hoạt động

                        </span>

                    @else

                        <span
                            class="rounded-full bg-red-500/20 px-4 py-2 text-sm font-bold text-red-400">

                            Đã khóa

                        </span>

                    @endif

                </div>

            </div>

            {{-- ACTION BUTTON --}}
            <div class="mt-8 flex flex-wrap gap-3">

                <button
                    type="submit"
                    class="rounded-xl bg-[#d99a32] px-6 py-3 font-bold text-[#2b1208] transition hover:scale-105">

                    <i class="fa-solid fa-floppy-disk mr-2"></i>

                    Cập nhật nhân viên

                </button>

                <a
                    href="{{ route('admin.nhanviens.index') }}"
                    class="rounded-xl border border-white/10 px-6 py-3 font-bold text-white transition hover:bg-white/10">

                    <i class="fa-solid fa-arrow-left mr-2"></i>

                    Quay lại

                </a>

            </div>

        </form>

    </div>

</div>

@endsection