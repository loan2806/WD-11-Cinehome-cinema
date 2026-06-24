@extends('layouts.user')

@section('title', 'Vé của tôi')

@section('content')
<div class="min-h-screen bg-[#080808] px-6 pt-28 pb-16 text-white">

    <div class="mx-auto max-w-6xl">

        {{-- HEADER --}}
        <div class="mb-8 flex flex-col justify-between gap-5 md:flex-row md:items-end">

            <div>
                <p class="mb-2 text-sm font-bold uppercase tracking-[0.25em] text-[#d99a32]">
                    CineHome Tickets
                </p>

                <h1 class="text-4xl font-black">
                    Vé <span class="text-[#d99a32]">của tôi</span>
                </h1>

                <p class="mt-3 max-w-2xl text-gray-400">
                    Quản lý vé đã đặt, xem chi tiết suất chiếu và thực hiện hủy vé trong thời gian cho phép.
                </p>
            </div>

            <a href="{{ route('user.thanh-vien.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-black text-white no-underline shadow-lg shadow-[#d99a32]/20 transition duration-300 hover:-translate-y-1 hover:scale-[1.02] hover:shadow-[#d99a32]/35">
                <i class="fa-solid fa-crown"></i>
                Thẻ thành viên & Điểm
            </a>
        </div>

        {{-- THÔNG BÁO --}}
        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-green-500/30 bg-green-500/10 px-5 py-4 text-sm font-bold text-green-400">
                <i class="fa-solid fa-circle-check mr-2"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 rounded-2xl border border-red-500/30 bg-red-500/10 px-5 py-4 text-sm font-bold text-red-400">
                <i class="fa-solid fa-circle-xmark mr-2"></i>
                {{ session('error') }}
            </div>
        @endif

        {{-- BẢNG VÉ --}}
        <div class="overflow-hidden rounded-[28px] border border-white/10 bg-[#121212] shadow-2xl shadow-black/30">

            <div class="flex flex-col justify-between gap-4 border-b border-white/10 bg-white/[0.03] px-6 py-5 md:flex-row md:items-center">
                <div>
                    <h2 class="text-xl font-black">
                        Danh sách vé đã đặt
                    </h2>

                    <p class="mt-1 text-sm text-gray-400">
                        Theo dõi trạng thái vé và thông tin suất chiếu của bạn.
                    </p>
                </div>

                <div class="inline-flex w-fit items-center gap-2 rounded-full bg-[#d99a32]/15 px-4 py-2 text-sm font-bold text-[#d99a32]">
                    <i class="fa-solid fa-ticket"></i>
                    {{ $veXemPhims->total() }} vé
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] text-left">
                    <thead class="bg-[#1f1f1f] text-xs uppercase tracking-wider text-gray-400">
                        <tr>
                            <th class="px-6 py-4">Mã vé</th>
                            <th class="px-6 py-4">Phim</th>
                            <th class="px-6 py-4">Ghế</th>
                            <th class="px-6 py-4">Suất chiếu</th>
                            <th class="px-6 py-4">Tổng tiền</th>
                            <th class="px-6 py-4">Trạng thái</th>
                            <th class="px-6 py-4 text-right">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-white/10">
                        @forelse ($veXemPhims as $veXemPhim)
                            <tr class="group transition duration-300 hover:bg-white/[0.04]">

                                {{-- MÃ VÉ --}}
                                <td class="px-6 py-5">
                                    <div class="font-black text-[#d99a32]">
                                        {{ $veXemPhim->ma_ve }}
                                    </div>

                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $veXemPhim->created_at?->format('d/m/Y H:i') }}
                                    </div>
                                </td>

                                {{-- PHIM --}}
                                <td class="px-6 py-5">
                                    <div class="max-w-[220px] font-bold text-white">
                                        {{ $veXemPhim->ten_phim }}
                                    </div>

                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $veXemPhim->ten_rap ?? 'CineHome Cinema' }}
                                    </div>
                                </td>

                                {{-- GHẾ --}}
                                <td class="px-6 py-5">
                                    <span class="inline-flex rounded-full border border-[#d99a32]/30 bg-[#d99a32]/10 px-3 py-1 text-sm font-black text-[#f4c56a]">
                                        {{ $veXemPhim->ma_ghe ?? '---' }}
                                    </span>
                                </td>

                                {{-- SUẤT CHIẾU --}}
                                <td class="px-6 py-5">
                                    <div class="font-bold text-white">
                                        {{ $veXemPhim->thoi_gian_chieu?->format('H:i') }}
                                    </div>

                                    <div class="mt-1 text-sm text-gray-400">
                                        {{ $veXemPhim->thoi_gian_chieu?->format('d/m/Y') }}
                                    </div>
                                </td>

                                {{-- TỔNG TIỀN --}}
                                <td class="px-6 py-5">
                                    <div class="font-black text-white">
                                        {{ number_format($veXemPhim->tong_tien, 0, ',', '.') }} VNĐ
                                    </div>

                                    @if($veXemPhim->tien_hoan > 0)
                                        <div class="mt-1 text-xs text-green-400">
                                            Hoàn: {{ number_format($veXemPhim->tien_hoan, 0, ',', '.') }} VNĐ
                                        </div>
                                    @endif
                                </td>

                                {{-- TRẠNG THÁI --}}
                                <td class="px-6 py-5">
                                    @if($veXemPhim->trang_thai === 'da_thanh_toan')
                                        <span class="inline-flex items-center gap-2 rounded-full bg-green-500/15 px-3 py-1 text-xs font-bold text-green-400">
                                            <span class="h-2 w-2 rounded-full bg-green-400"></span>
                                            Đã thanh toán
                                        </span>
                                    @elseif($veXemPhim->trang_thai === 'da_su_dung')
                                        <span class="inline-flex items-center gap-2 rounded-full bg-blue-500/15 px-3 py-1 text-xs font-bold text-blue-400">
                                            <span class="h-2 w-2 rounded-full bg-blue-400"></span>
                                            Đã sử dụng
                                        </span>
                                    @elseif($veXemPhim->trang_thai === 'da_huy')
                                        <span class="inline-flex items-center gap-2 rounded-full bg-red-500/15 px-3 py-1 text-xs font-bold text-red-400">
                                            <span class="h-2 w-2 rounded-full bg-red-400"></span>
                                            Đã hủy
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-2 rounded-full bg-gray-500/15 px-3 py-1 text-xs font-bold text-gray-400">
                                            {{ $veXemPhim->trang_thai }}
                                        </span>
                                    @endif
                                </td>

                                {{-- THAO TÁC --}}
                                <td class="px-6 py-5 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('user.ve_xem_phim.show', $veXemPhim) }}"
                                           class="inline-flex items-center justify-center rounded-xl bg-[#d99a32] px-4 py-2 text-xs font-black text-black no-underline transition duration-300 hover:-translate-y-0.5 hover:bg-[#f4c56a]">
                                            Chi tiết
                                        </a>

                                        @if($veXemPhim->trang_thai === 'da_thanh_toan' && $veXemPhim->canCancel())
                                            <form method="POST" action="{{ route('user.ve_xem_phim.cancel', $veXemPhim) }}"
                                                  onsubmit="return confirm('Bạn có chắc muốn hủy vé này không?');">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit"
                                                        class="inline-flex items-center justify-center rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-2 text-xs font-black text-red-400 transition duration-300 hover:-translate-y-0.5 hover:bg-red-500 hover:text-white">
                                                    Hủy vé
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-white/5 text-gray-500">
                                        <i class="fa-solid fa-ticket text-3xl"></i>
                                    </div>

                                    <h3 class="text-xl font-black text-white">
                                        Bạn chưa có vé nào
                                    </h3>

                                    <p class="mt-2 text-gray-400">
                                        Hãy chọn phim và đặt vé để bắt đầu trải nghiệm CineHome.
                                    </p>

                                    <a href="{{ route('dat_ve.chon_rap') }}"
                                       class="mt-5 inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-black text-white no-underline transition hover:-translate-y-1 hover:shadow-lg hover:shadow-[#d99a32]/30">
                                        <i class="fa-solid fa-plus"></i>
                                        Đặt vé ngay
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($veXemPhims->hasPages())
                <div class="border-t border-white/10 px-6 py-4">
                    {{ $veXemPhims->links() }}
                </div>
            @endif

        </div>
    </div>
</div>
@endsection