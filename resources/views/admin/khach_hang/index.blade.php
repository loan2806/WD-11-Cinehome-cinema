@extends('layouts.admin')

@section('title', 'Tài khoản khách hàng')
@section('page-title', 'Tài khoản khách hàng')
@section('page-subtitle', 'Quản lý hồ sơ, trạng thái và hoạt động tài khoản khách hàng')

@section('content')
<div class="space-y-6">

    {{-- THỐNG KÊ NHANH --}}
    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
        @foreach([
        ['Tổng khách hàng', $tongKhachHang, 'fa-users'],
        ['Đang hoạt động', $tongDangHoatDong, 'fa-user-check'],
        ['Bị khóa', $tongBiKhoa, 'fa-user-lock'],
        ] as $card)
        <div
            class="rounded-3xl border border-white/10 bg-[#121212] p-5 shadow-xl transition duration-300 hover:-translate-y-1 hover:border-[#d99a32]/50">
            <div
                class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32]">
                <i class="fa-solid {{ $card[2] }} text-white"></i>
            </div>
            <p class="text-sm text-gray-400">{{ $card[0] }}</p>
            <h3 class="mt-2 text-3xl font-black text-white">{{ number_format($card[1]) }}</h3>
        </div>
        @endforeach
    </div>

    {{-- BỘ LỌC --}}
    <div class="rounded-3xl border border-white/10 bg-[#121212] p-5">
        <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <input type="text" name="tim_kiem" value="{{ request('tim_kiem') }}"
                placeholder="Tìm tên hoặc email khách hàng..."
                class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white outline-none focus:border-[#d99a32]">

            <select name="trang_thai"
                class="rounded-2xl border border-white/10 bg-[#1a1a1a] px-4 py-3 text-white outline-none focus:border-[#d99a32]">
                <option value="">Tất cả trạng thái</option>
                <option value="1" @selected(request('trang_thai')==='1' )>Đang hoạt động</option>
                <option value="0" @selected(request('trang_thai')==='0' )>Bị khóa</option>
            </select>

            <button
                class="rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 font-black text-white transition hover:-translate-y-1">
                <i class="fa-solid fa-filter mr-2"></i>
                Lọc dữ liệu
            </button>
        </form>
    </div>

    {{-- DANH SÁCH KHÁCH HÀNG --}}
    <div class="overflow-hidden rounded-3xl border border-white/10 bg-[#121212] shadow-2xl">
        <div class="flex items-center justify-between border-b border-white/10 px-6 py-5">
            <div>
                <h2 class="text-xl font-black text-white">Danh sách tài khoản</h2>
                <p class="mt-1 text-sm text-gray-400">
                    Theo dõi hồ sơ, trạng thái và lịch sử hoạt động khách hàng
                </p>
            </div>

            <a href="{{ route('admin.khach-hang.create') }}"
                class="rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-black text-white no-underline transition hover:-translate-y-1">
                <i class="fa-solid fa-user-plus mr-2"></i>
                Thêm khách hàng
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1200px] table-fixed text-left">
                <colgroup>
                    <col class="w-[15%]">
                    <col class="w-[18%]">
                    <col class="w-[12%]">
                    <col class="w-[11%]">
                    <col class="w-[10%]">
                    <col class="w-[8%]">
                    <col class="w-[12%]">
                    <col class="w-[14%]">
                </colgroup>

                <thead class="bg-white/[0.04] text-xs uppercase tracking-wider text-gray-400">
                    <tr>
                        <th class="px-6 py-4 text-left">Khách hàng</th>
                        <th class="px-6 py-4 text-left">Email</th>
                        <th class="px-6 py-4 text-left">SĐT</th>
                        <th class="px-6 py-4 text-center">Ngày sinh</th>
                        <th class="px-6 py-4 text-center">Hạng TV</th>
                        <th class="px-6 py-4 text-center">Số vé</th>
                        <th class="px-6 py-4 text-center">Trạng thái</th>
                        <th class="px-6 py-4 text-right">Thao tác</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-white/10">
                    @forelse($khachHangs as $item)
                    <tr class="transition duration-300 hover:bg-white/[0.04]">
                        <td class="px-6 py-5 align-middle">
                            <div class="truncate font-bold text-white">
                                {{ $item->ho_ten }}
                            </div>
                        </td>

                        <td class="px-6 py-5 align-middle">
                            <div class="truncate text-gray-400">
                                {{ $item->email }}
                            </div>
                        </td>

                        <td class="px-6 py-5 align-middle text-gray-400">
                            {{ $item->so_dien_thoai ?? '---' }}
                        </td>

                        <td class="px-6 py-5 text-center align-middle text-gray-400">
                            {{ $item->ngay_sinh?->format('d/m/Y') ?? '---' }}
                        </td>

                        <td class="px-6 py-5 text-center align-middle">
                            <span
                                class="inline-flex items-center justify-center rounded-full bg-[#d99a32]/15 px-3 py-1 text-xs font-black text-[#f4c56a]">
                                {{ strtoupper($item->thanhVien->ten_hang ?? 'Chưa có') }}
                            </span>
                        </td>

                        <td class="px-6 py-5 text-center align-middle font-bold text-white">
                            {{ number_format($item->ve_xem_phims_count) }}
                        </td>

                        <td class="px-6 py-5 text-center align-middle">
                            @if($item->trang_thai_hoat_dong)
                            <span
                                class="inline-flex items-center justify-center rounded-full bg-green-500/15 px-3 py-1 text-xs font-bold text-green-400">
                                Hoạt động
                            </span>
                            @else
                            <span
                                class="inline-flex items-center justify-center rounded-full bg-red-500/15 px-3 py-1 text-xs font-bold text-red-400">
                                Bị khóa
                            </span>
                            @endif
                        </td>

                        <td class="px-6 py-5 text-right align-middle">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.khach-hang.show', $item) }}"
                                    class="rounded-xl bg-[#d99a32] px-4 py-2 text-xs font-black text-black no-underline transition hover:bg-[#f4c56a]">
                                    Chi tiết
                                </a>

                                <form method="POST" action="{{ route('admin.khach-hang.toggle-status', $item) }}"
                                    onsubmit="return confirm('Bạn có chắc muốn thay đổi trạng thái tài khoản này?')">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" class="rounded-xl px-4 py-2 text-xs font-black transition
                                        {{ $item->trang_thai_hoat_dong
                                            ? 'bg-red-500/15 text-red-400 hover:bg-red-500 hover:text-white'
                                            : 'bg-green-500/15 text-green-400 hover:bg-green-500 hover:text-white' }}">
                                        {{ $item->trang_thai_hoat_dong ? 'Khóa' : 'Mở khóa' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                            Chưa có tài khoản khách hàng nào.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-white/10 px-6 py-4">
            {{ $khachHangs->links() }}
        </div>
    </div>
</div>
@endsection