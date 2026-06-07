@extends('layouts.admin')

@section('title','Thêm nhân viên')
@section('page-title','Thêm nhân viên')
@section('page-subtitle','Tạo tài khoản nhân viên mới')

@section('content')

<div class="mx-auto max-w-4xl">

    <div class="rounded-2xl border border-white/10 bg-[#151515] p-8">

        <form action="{{ route('admin.nhanviens.store') }}" method="POST">

            @csrf

            <div class="grid gap-6 md:grid-cols-2">

                <div>
                    <label class="mb-2 block font-semibold">
                        Họ tên
                    </label>

                    <input
                        type="text"
                        name="ho_ten"
                        class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white">
                </div>

                <div>
                    <label class="mb-2 block font-semibold">
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white">
                </div>

            </div>

            <div class="mt-6">

                <label class="mb-2 block font-semibold">
                    Mật khẩu
                </label>

                <input
                    type="password"
                    name="mat_khau"
                    class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white">

            </div>

            <div class="mt-8 flex gap-3">

                <button
                    class="rounded-xl bg-[#d99a32] px-6 py-3 font-bold text-[#2b1208]">

                    <i class="fa-solid fa-floppy-disk mr-2"></i>
                    Lưu nhân viên

                </button>

                <a href="{{ route('admin.nhanviens.index') }}"
                   class="rounded-xl border border-white/10 px-6 py-3">

                    Quay lại

                </a>

            </div>

        </form>

    </div>

</div>

@endsection