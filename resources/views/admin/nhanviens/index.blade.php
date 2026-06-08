@extends('layouts.admin')

@section('title', 'Quản lý nhân viên')
@section('page-title', 'Quản lý nhân viên')
@section('page-subtitle', 'Quản lý tài khoản nhân viên hệ thống')

@section('content')

<div class="space-y-6">

    <div class="flex items-center justify-between">

        <div>
            <h2 class="text-2xl font-black text-white">
                Danh sách nhân viên
            </h2>

            <p class="text-gray-400">
                Quản lý toàn bộ nhân viên trong hệ thống
            </p>
        </div>

        <a href="{{ route('admin.nhanviens.create') }}"
           class="rounded-xl bg-[#d99a32] px-5 py-3 font-bold text-[#2b1208] transition hover:scale-105">

            <i class="fa-solid fa-plus mr-2"></i>
            Thêm nhân viên

        </a>

    </div>

    <div class="rounded-2xl border border-white/10 bg-[#151515] p-5">

        <form method="GET">

            <div class="relative max-w-md">

                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>

                <input
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    placeholder="Tìm theo tên hoặc email..."
                    class="w-full rounded-xl border border-white/10 bg-[#101010] py-3 pl-11 pr-4 text-white focus:border-[#d99a32] focus:outline-none">

            </div>

        </form>

    </div>

    <div class="overflow-hidden rounded-2xl border border-white/10 bg-[#151515]">

        <div class="overflow-x-auto">

            <table class="w-full">

                <thead class="bg-[#1f1f1f]">

                <tr>

                    <th class="px-6 py-4 text-left">ID</th>
                    <th class="px-6 py-4 text-left">Nhân viên</th>
                    <th class="px-6 py-4 text-left">Email</th>
                    <th class="px-6 py-4 text-left">Trạng thái</th>
                    <th class="px-6 py-4 text-center">Thao tác</th>

                </tr>

                </thead>

                <tbody>

                @forelse($nhanViens as $nhanVien)

                    <tr class="border-t border-white/10 hover:bg-white/5">

                        <td class="px-6 py-4">
                            #{{ $nhanVien->id }}
                        </td>

                        <td class="px-6 py-4">

                            <div class="flex items-center gap-3">

                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-r from-[#8a4a21] to-[#d99a32]">

                                    <i class="fa-solid fa-user text-white"></i>

                                </div>

                                <div>

                                    <div class="font-bold">
                                        {{ $nhanVien->ho_ten }}
                                    </div>

                                    <div class="text-sm text-gray-400">
                                        Nhân viên hệ thống
                                    </div>

                                </div>

                            </div>

                        </td>

                        <td class="px-6 py-4">
                            {{ $nhanVien->email }}
                        </td>

                        <td class="px-6 py-4">

                            @if($nhanVien->trang_thai_hoat_dong)

                                <span class="rounded-full bg-green-500/20 px-3 py-1 text-sm font-bold text-green-400">
                                    Hoạt động
                                </span>

                            @else

                                <span class="rounded-full bg-red-500/20 px-3 py-1 text-sm font-bold text-red-400">
                                    Đã khóa
                                </span>

                            @endif

                        </td>

                        <td class="px-6 py-4">

                            <div class="flex justify-center gap-2">

                                <a href="{{ route('admin.nhanviens.edit',$nhanVien) }}"
                                   class="rounded-lg bg-blue-500 px-3 py-2 text-white">

                                    <i class="fa-solid fa-pen"></i>

                                </a>

                                <form method="POST"
                                      action="{{ route('admin.nhanviens.toggle-status',$nhanVien) }}">

                                    @csrf
                                    @method('PATCH')

                                    <button class="rounded-lg bg-yellow-500 px-3 py-2 text-white">

                                        <i class="fa-solid fa-lock"></i>

                                    </button>

                                </form>

                                <form method="POST"
                                      action="{{ route('admin.nhanviens.destroy',$nhanVien) }}"
                                      onsubmit="return confirm('Xóa nhân viên?')">

                                    @csrf
                                    @method('DELETE')

                                    <button class="rounded-lg bg-red-500 px-3 py-2 text-white">

                                        <i class="fa-solid fa-trash"></i>

                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="5" class="py-10 text-center text-gray-400">

                            Chưa có nhân viên nào

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

    {{ $nhanViens->links() }}

</div>

@endsection