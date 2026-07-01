@extends('layouts.user')

@section('title', 'Thẻ thành viên & Điểm')

@section('content')
<div class="min-h-screen bg-[#080808] px-6 pt-28 pb-16 text-white">

    <div class="mx-auto max-w-6xl">

        {{-- TIÊU ĐỀ --}}
        <div class="mb-8">
            <p class="mb-2 text-sm font-bold uppercase tracking-[0.25em] text-[#d99a32]">
                CineHome Loyalty
            </p>

            <h1 class="text-4xl font-black">
                Thẻ thành viên
                <span class="text-[#d99a32]">& Điểm thưởng</span>
            </h1>

            <p class="mt-3 max-w-2xl text-gray-400">
                Theo dõi hạng thành viên, điểm tích lũy và lịch sử cộng/trừ điểm sau mỗi lần đặt vé.
            </p>
        </div>

        {{-- THẺ THÀNH VIÊN --}}
        <div
            class="relative mb-8 overflow-hidden rounded-[28px] border border-[#d99a32]/30 bg-gradient-to-br from-[#1b0d05] via-[#141414] to-[#070707] p-7 shadow-2xl shadow-[#d99a32]/10 transition duration-500 hover:-translate-y-1 hover:shadow-[#d99a32]/20">

            {{-- HIỆU ỨNG NỀN --}}
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-[#d99a32]/20 blur-3xl"></div>
            <div class="absolute -bottom-24 -left-16 h-64 w-64 rounded-full bg-[#8a4a21]/20 blur-3xl"></div>

            <div class="relative z-10 grid grid-cols-1 gap-8 lg:grid-cols-3">

                {{-- BÊN TRÁI --}}
                <div class="lg:col-span-1">
                    <div class="mb-6 flex items-center gap-4">
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] shadow-lg shadow-[#d99a32]/30">
                            <i class="fa-solid fa-crown text-2xl text-white"></i>
                        </div>

                        <div>
                            <p class="text-sm text-gray-400">
                                Mã thành viên
                            </p>

                            <h2 class="text-2xl font-black tracking-wide text-[#f4c56a]">
                                {{ $thanhVien->ma_thanh_vien }}
                            </h2>
                        </div>
                    </div>

                    <div class="mt-4 rounded-2xl border border-[#d99a32]/30 bg-[#d99a32]/10 p-4">

                        <p class="text-sm text-gray-400">
                            Mã giới thiệu của bạn
                        </p>

                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-xl font-black text-[#f4c56a]">
                                {{ $thanhVien->ma_gioi_thieu ?? 'Chưa có' }}
                            </span>

                            <i class="fa-solid fa-share-nodes text-[#d99a32]"></i>
                        </div>

                        <p class="mt-2 text-xs text-gray-400">
                            Chia sẻ mã này cho bạn bè để nhận thưởng.
                        </p>

                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                        <p class="text-sm text-gray-400">
                            Hạng hiện tại
                        </p>

                        <div
                            class="mt-2 inline-flex items-center gap-2 rounded-full bg-[#d99a32]/15 px-4 py-2 text-sm font-black text-[#f4c56a]">
                            <i class="fa-solid fa-medal"></i>
                            {{ strtoupper($thanhVien->ten_hang) }}
                        </div>
                    </div>
                </div>

                {{-- BÊN PHẢI --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 lg:col-span-2">

                    <div
                        class="group rounded-2xl border border-white/10 bg-white/[0.06] p-5 transition duration-300 hover:-translate-y-1 hover:border-[#d99a32]/50 hover:bg-[#d99a32]/10">
                        <div
                            class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-[#d99a32]/15 text-[#d99a32] transition group-hover:scale-110">
                            <i class="fa-solid fa-star"></i>
                        </div>

                        <p class="text-sm text-gray-400">
                            Điểm hiện tại
                        </p>

                        <h3 class="mt-2 text-3xl font-black text-white">
                            {{ number_format($thanhVien->diem_hien_tai) }}
                        </h3>
                    </div>

                    <div
                        class="group rounded-2xl border border-white/10 bg-white/[0.06] p-5 transition duration-300 hover:-translate-y-1 hover:border-[#d99a32]/50 hover:bg-[#d99a32]/10">
                        <div
                            class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-[#d99a32]/15 text-[#d99a32] transition group-hover:scale-110">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>

                        <p class="text-sm text-gray-400">
                            Tổng tích lũy
                        </p>

                        <h3 class="mt-2 text-3xl font-black text-white">
                            {{ number_format($thanhVien->tong_diem_tich_luy) }}
                        </h3>
                    </div>

                    <div
                        class="group rounded-2xl border border-white/10 bg-white/[0.06] p-5 transition duration-300 hover:-translate-y-1 hover:border-[#d99a32]/50 hover:bg-[#d99a32]/10">
                        <div
                            class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-[#d99a32]/15 text-[#d99a32] transition group-hover:scale-110">
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>

                        <p class="text-sm text-gray-400">
                            Ngày tham gia
                        </p>

                        <h3 class="mt-2 text-xl font-black text-white">
                            {{ $thanhVien->ngay_tham_gia?->format('d/m/Y') }}
                        </h3>
                    </div>

                </div>
            </div>
        </div>

        {{-- QUYỀN LỢI HẠNG --}}
        <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-4">

            @foreach([
            ['Member', '0 - 499 điểm', 'Tích điểm cơ bản'],
            ['Silver', '500 - 999 điểm', 'Ưu đãi tốt hơn'],
            ['Gold', '1000 - 1999 điểm', 'Nhận khuyến mãi riêng'],
            ['Platinum', 'Từ 2000 điểm', 'Quyền lợi cao nhất'],
            ] as $rank)
            <div
                class="rounded-2xl border border-white/10 bg-[#121212] p-5 transition duration-300 hover:-translate-y-1 hover:border-[#d99a32]/50 hover:bg-[#18110a]">
                <h4 class="font-black text-[#d99a32]">
                    {{ $rank[0] }}
                </h4>

                <p class="mt-2 text-sm text-gray-400">
                    {{ $rank[1] }}
                </p>

                <p class="mt-3 text-sm text-gray-300">
                    {{ $rank[2] }}
                </p>
            </div>
            @endforeach

        </div>

        {{-- GIỚI THIỆU THÀNH VIÊN --}}
        <div class="mb-8 rounded-[24px] border border-[#d99a32]/30 bg-[#121212] p-6 shadow-xl">

            <div class="mb-5">
                <h2 class="text-2xl font-black text-white">
                    Giới thiệu thành viên
                </h2>

                <p class="mt-2 text-gray-400">
                    Chia sẻ mã giới thiệu để nhận thêm điểm thưởng.
                </p>
            </div>


            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">


                {{-- MÃ GIỚI THIỆU --}}
                <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-5">

                    <p class="text-sm text-gray-400">
                        Mã giới thiệu
                    </p>

                    <div class="mt-3 flex items-center justify-between">

                        <h3 class="text-2xl font-black text-[#d99a32]">
                            {{ $thanhVien->ma_gioi_thieu }}
                        </h3>


                        <button onclick="navigator.clipboard.writeText('{{ $thanhVien->ma_gioi_thieu }}')"
                            class="rounded-xl bg-[#d99a32] px-3 py-2 text-xs font-black text-black">

                            Copy

                        </button>

                    </div>

                </div>



                {{-- SỐ NGƯỜI --}}
                <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-5">

                    <p class="text-sm text-gray-400">
                        Người đã giới thiệu
                    </p>


                    <h3 class="mt-3 text-3xl font-black text-white">

                        {{ $nguoiDaGioiThieu->count() }}

                    </h3>

                </div>



                {{-- ĐIỂM --}}
                <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-5">

                    <p class="text-sm text-gray-400">
                        Điểm nhận từ giới thiệu
                    </p>


                    <h3 class="mt-3 text-3xl font-black text-green-400">

                        +{{ $thanhVien->gioiThieus->sum('diem_thuong') }}

                    </h3>

                </div>


            </div>


        </div>

        {{-- LỊCH SỬ ĐIỂM --}}
        <div class="overflow-hidden rounded-[24px] border border-white/10 bg-[#121212] shadow-xl">

            <div class="flex items-center justify-between border-b border-white/10 px-6 py-5">
                <div>
                    <h2 class="text-xl font-black">
                        Lịch sử điểm
                    </h2>

                    <p class="mt-1 text-sm text-gray-400">
                        Các giao dịch cộng/trừ điểm của tài khoản
                    </p>
                </div>

                <div class="hidden rounded-full bg-[#d99a32]/15 px-4 py-2 text-sm font-bold text-[#d99a32] sm:block">
                    {{ $lichSuDiem->total() }} giao dịch
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-white/[0.04] text-xs uppercase tracking-wider text-gray-400">
                        <tr>
                            <th class="px-6 py-4">Ngày</th>
                            <th class="px-6 py-4">Loại</th>
                            <th class="px-6 py-4">Điểm</th>
                            <th class="px-6 py-4">Nội dung</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-white/10">
                        @forelse($lichSuDiem as $item)
                        <tr class="transition duration-300 hover:bg-white/[0.04]">

                            <td class="px-6 py-4 text-sm text-gray-300">
                                {{ $item->created_at->format('d/m/Y H:i') }}
                            </td>

                            <td class="px-6 py-4">
                                @if($item->loai_giao_dich === 'cong_diem')
                                <span
                                    class="inline-flex items-center gap-2 rounded-full bg-green-500/15 px-3 py-1 text-xs font-bold text-green-400">
                                    <i class="fa-solid fa-plus"></i>
                                    Cộng điểm
                                </span>
                                @else
                                <span
                                    class="inline-flex items-center gap-2 rounded-full bg-red-500/15 px-3 py-1 text-xs font-bold text-red-400">
                                    <i class="fa-solid fa-minus"></i>
                                    Trừ điểm
                                </span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                @if($item->loai_giao_dich === 'cong_diem')
                                <span class="font-black text-green-400">
                                    +{{ $item->so_diem }}
                                </span>
                                @else
                                <span class="font-black text-red-400">
                                    -{{ $item->so_diem }}
                                </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-300">
                                {{ $item->noi_dung }}
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div
                                    class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-white/5 text-gray-500">
                                    <i class="fa-solid fa-receipt text-2xl"></i>
                                </div>

                                <p class="font-bold text-gray-300">
                                    Chưa có lịch sử điểm
                                </p>

                                <p class="mt-1 text-sm text-gray-500">
                                    Hãy đặt vé để bắt đầu tích điểm thành viên.
                                </p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($lichSuDiem->hasPages())
            <div class="border-t border-white/10 px-6 py-4">
                {{ $lichSuDiem->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
@endsection