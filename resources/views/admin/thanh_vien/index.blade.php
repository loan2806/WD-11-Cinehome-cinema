@extends('layouts.admin')

@section('title', 'Thẻ thành viên & Điểm')
@section('page-title', 'Thẻ thành viên & Điểm')
@section('page-subtitle', 'Quản lý khách hàng thân thiết, điểm tích lũy và hạng thành viên')

@section('content')
<div class="space-y-6">

    {{-- THỐNG KÊ NHANH --}}
    <div class="grid grid-cols-1 gap-5 md:grid-cols-5">
        @foreach([
        ['Tổng thành viên', $tongThanhVien, 'fa-users'],
        ['Member', $tongMember, 'fa-user'],
        ['Silver', $tongSilver, 'fa-medal'],
        ['Gold', $tongGold, 'fa-crown'],
        ['Platinum', $tongPlatinum, 'fa-gem'],
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
                placeholder="Tìm mã thành viên, tên hoặc email..."
                class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white outline-none focus:border-[#d99a32]">

            <select name="hang_thanh_vien"
                class="rounded-2xl border border-white/10 bg-[#1a1a1a] px-4 py-3 text-white outline-none focus:border-[#d99a32]">
                <option value="">Tất cả hạng</option>
                <option value="member" @selected(request('hang_thanh_vien')==='member' )>Member</option>
                <option value="silver" @selected(request('hang_thanh_vien')==='silver' )>Silver</option>
                <option value="gold" @selected(request('hang_thanh_vien')==='gold' )>Gold</option>
                <option value="platinum" @selected(request('hang_thanh_vien')==='platinum' )>Platinum</option>
            </select>

            <button
                class="rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 font-black text-white transition hover:-translate-y-1">
                <i class="fa-solid fa-filter mr-2"></i>
                Lọc dữ liệu
            </button>
        </form>
    </div>

    {{-- DANH SÁCH THÀNH VIÊN --}}
    <div class="overflow-hidden rounded-3xl border border-white/10 bg-[#121212] shadow-2xl">
        <div class="border-b border-white/10 px-6 py-5">
            <h2 class="text-xl font-black text-white">Danh sách thành viên</h2>
            <p class="mt-1 text-sm text-gray-400">Theo dõi điểm, hạng và thông tin khách hàng thân thiết</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1150px] table-fixed text-left">
                <colgroup>
                    <col class="w-[13%]">
                    <col class="w-[18%]">
                    <col class="w-[14%]">
                    <col class="w-[11%]">
                    <col class="w-[13%]">
                    <col class="w-[12%]">
                    <col class="w-[12%]">
                    <col class="w-[7%]">
                </colgroup>

                <thead class="bg-white/[0.04] text-xs uppercase tracking-wider text-gray-400">
                    <tr>
                        <th class="px-6 py-4 text-left">Mã thành viên</th>
                        <th class="px-6 py-4 text-left">Khách hàng</th>
                        <th class="px-6 py-4 text-left">Số điện thoại</th>
                        <th class="px-6 py-4 text-center">Hạng</th>
                        <th class="px-6 py-4 text-center">Điểm hiện tại</th>
                        <th class="px-6 py-4 text-center">Tổng điểm</th>
                        <th class="px-6 py-4 text-center">Ngày tham gia</th>
                        <th class="px-6 py-4 text-right">Thao tác</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-white/10">
                    @forelse($thanhViens as $item)
                    <tr class="transition duration-300 hover:bg-white/[0.04]">
                        <td class="px-6 py-5 align-middle font-black text-[#d99a32]">
                            {{ $item->ma_thanh_vien }}
                        </td>

                        <td class="px-6 py-5 align-middle">
                            <div class="truncate font-bold text-white">
                                {{ $item->nguoiDung->ho_ten ?? 'Không xác định' }}
                            </div>
                            <div class="truncate text-sm text-gray-500">
                                {{ $item->nguoiDung->email ?? '---' }}
                            </div>
                        </td>

                        <td class="px-6 py-5 align-middle font-bold text-white">
                            {{ $item->nguoiDung->so_dien_thoai ?? '---' }}
                        </td>

                        <td class="px-6 py-5 text-center align-middle">
                            <span class="inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-black
                        @class([
                            'bg-gray-500/15 text-gray-300' => $item->hang_thanh_vien === 'member',
                            'bg-slate-400/15 text-slate-300' => $item->hang_thanh_vien === 'silver',
                            'bg-yellow-500/15 text-yellow-400' => $item->hang_thanh_vien === 'gold',
                            'bg-purple-500/15 text-purple-400' => $item->hang_thanh_vien === 'platinum',
                        ])">
                                {{ strtoupper($item->ten_hang) }}
                            </span>
                        </td>

                        <td class="px-6 py-5 text-center align-middle font-bold text-white">
                            {{ number_format($item->diem_hien_tai) }}
                        </td>

                        <td class="px-6 py-5 text-center align-middle font-bold text-[#f4c56a]">
                            {{ number_format($item->tong_diem_tich_luy) }}
                        </td>

                        <td class="px-6 py-5 text-center align-middle text-gray-400">
                            {{ $item->ngay_tham_gia?->format('d/m/Y') ?? '---' }}
                        </td>

                        <td class="px-6 py-5 text-right align-middle">
                            <a href="{{ route('admin.thanh-vien.show', $item) }}"
                                class="inline-flex rounded-xl bg-[#d99a32] px-4 py-2 text-xs font-black text-black no-underline transition hover:bg-[#f4c56a]">
                                Chi tiết
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                            Chưa có thành viên nào.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-white/10 px-6 py-4">
            {{ $thanhViens->links() }}
        </div>
    </div>
</div>
@endsection