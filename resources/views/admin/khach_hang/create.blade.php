@extends('layouts.admin')

@section('title', 'Thêm khách hàng')

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="rounded-3xl border border-white/10 bg-[#121212] p-6">

        <h2 class="mb-6 text-2xl font-black text-white">
            Thêm khách hàng mới
        </h2>

        <form method="POST" action="{{ route('admin.khach-hang.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-bold text-gray-300 mb-2">
                    Họ tên
                </label>

                <input type="text"
                    name="ho_ten"
                    class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-300 mb-2">
                    Email
                </label>

                <input type="email"
                    name="email"
                    class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-300 mb-2">
                    Số điện thoại
                </label>

                <input type="text"
                    name="so_dien_thoai"
                    class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-300 mb-2">
                    Ngày sinh
                </label>

                <input type="date"
                    name="ngay_sinh"
                    class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-300 mb-2">
                    Mật khẩu
                </label>

                <input type="password"
                    name="mat_khau"
                    class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white">
            </div>

            <button type="submit"
                class="rounded-2xl bg-[#d99a32] px-6 py-3 font-bold text-white">
                Thêm khách hàng
            </button>
        </form>

    </div>
</div>
@endsection