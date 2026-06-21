@extends('layouts.user')

@section('title', 'Thông báo của tôi')

@section('content')
<div class="min-h-screen bg-[#080808] px-6 pt-28 pb-16 text-white">
    <div class="mx-auto max-w-5xl">

        <div class="mb-8">
            <p class="mb-2 text-sm font-bold uppercase tracking-[0.25em] text-[#d99a32]">
                CineHome Notifications
            </p>

            <h1 class="text-4xl font-black">
                Thông báo <span class="text-[#d99a32]">của tôi</span>
            </h1>

            <p class="mt-3 max-w-2xl text-gray-400">
                Theo dõi các thông báo liên quan đến vé, điểm thưởng, voucher và hạng thành viên của bạn.
            </p>
        </div>

        <div class="space-y-4">
            @forelse($thongBaos as $thongBao)
                <div class="group rounded-[26px] border border-white/10 bg-gradient-to-br from-[#171717] via-[#111111] to-[#070707] p-5 shadow-xl shadow-black/30 transition duration-300 hover:-translate-y-1 hover:border-[#d99a32]/50 hover:shadow-[#d99a32]/20">

                    <div class="flex gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl
                            @class([
                                'bg-blue-500/15 text-blue-400' => $thongBao->loai_thong_bao === 'he_thong',
                                'bg-green-500/15 text-green-400' => $thongBao->loai_thong_bao === 'diem',
                                'bg-yellow-500/15 text-yellow-400' => $thongBao->loai_thong_bao === 'voucher',
                                'bg-purple-500/15 text-purple-400' => $thongBao->loai_thong_bao === 'hang_thanh_vien',
                                'bg-red-500/15 text-red-400' => $thongBao->loai_thong_bao === 've',
                            ])">

                            @if($thongBao->loai_thong_bao === 'diem')
                                <i class="fa-solid fa-star"></i>
                            @elseif($thongBao->loai_thong_bao === 'voucher')
                                <i class="fa-solid fa-gift"></i>
                            @elseif($thongBao->loai_thong_bao === 'hang_thanh_vien')
                                <i class="fa-solid fa-crown"></i>
                            @elseif($thongBao->loai_thong_bao === 've')
                                <i class="fa-solid fa-ticket"></i>
                            @else
                                <i class="fa-solid fa-bell"></i>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                <h2 class="text-lg font-black text-white">
                                    {{ $thongBao->tieu_de }}
                                </h2>

                                <span class="text-xs font-semibold text-gray-500">
                                    {{ $thongBao->created_at?->format('d/m/Y H:i') }}
                                </span>
                            </div>

                            <p class="mt-2 leading-relaxed text-gray-400">
                                {{ $thongBao->noi_dung }}
                            </p>

                            @if($thongBao->duong_dan)
                                <a href="{{ $thongBao->duong_dan }}"
                                   class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#d99a32] px-4 py-2 text-sm font-black text-black no-underline transition hover:bg-[#f4c56a]">
                                    Xem chi tiết
                                    <i class="fa-solid fa-arrow-right text-xs"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-[28px] border border-white/10 bg-[#121212] px-6 py-16 text-center">
                    <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-white/5 text-gray-500">
                        <i class="fa-solid fa-bell text-3xl"></i>
                    </div>

                    <h3 class="text-xl font-black text-white">
                        Chưa có thông báo nào
                    </h3>

                    <p class="mt-2 text-gray-400">
                        Khi có thông báo mới về vé, điểm hoặc voucher, hệ thống sẽ hiển thị tại đây.
                    </p>
                </div>
            @endforelse
        </div>

        @if($thongBaos->hasPages())
            <div class="mt-8 rounded-2xl border border-white/10 bg-[#121212] px-6 py-4">
                {{ $thongBaos->links() }}
            </div>                                                                                                                                                                                          
        @endif
    </div>
</div>
@endsection