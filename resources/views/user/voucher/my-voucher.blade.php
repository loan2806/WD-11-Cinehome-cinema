@extends('layouts.user')

@section('title','Voucher của tôi')

@section('content')
<div class="min-h-screen bg-[#080808] px-6 pt-28 pb-16 text-white">
    <div class="mx-auto max-w-6xl">

        <div class="mb-8 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">

            <div>
                <p class="mb-2 text-sm font-bold uppercase tracking-[0.25em] text-[#d99a32]">
                    My CineHome Vouchers
                </p>

                <h1 class="text-4xl font-black">
                    Voucher <span class="text-[#d99a32]">của tôi</span>
                </h1>

                <p class="mt-3 max-w-2xl text-gray-400">
                    Quản lý các voucher đã đổi, kiểm tra trạng thái và sử dụng khi đặt vé.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('user.voucher.index') }}"
                    class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-black text-white shadow-lg shadow-[#d99a32]/20 transition-all duration-300 hover:-translate-y-1 hover:scale-[1.02] hover:shadow-[#d99a32]/40">

                    <i class="fa-solid fa-gift"></i>

                    Đổi thêm voucher
                </a>

            </div>

        </div>
        @if(session('success'))
        <div
            class="mb-6 rounded-2xl border border-green-500/30 bg-green-500/10 px-5 py-4 text-sm font-bold text-green-400">
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

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
            @forelse($vouchers as $item)
            <div
                class="group relative overflow-hidden rounded-[28px] border border-white/10 bg-gradient-to-br from-[#171717] via-[#111111] to-[#070707] p-6 shadow-xl shadow-black/30 transition duration-500 hover:-translate-y-2 hover:border-[#d99a32]/50 hover:shadow-[#d99a32]/20">

                <div
                    class="absolute -right-16 -top-16 h-44 w-44 rounded-full bg-[#d99a32]/20 blur-3xl transition duration-500 group-hover:bg-[#d99a32]/30">
                </div>

                <div class="relative z-10">
                    <div class="mb-5 flex items-center justify-between">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white shadow-lg shadow-[#d99a32]/20 transition duration-300 group-hover:scale-110">
                            <i class="fa-solid fa-ticket text-xl"></i>
                        </div>

                        @if($item->da_su_dung)
                        <span class="rounded-full bg-gray-500/15 px-3 py-1 text-xs font-bold text-gray-400">
                            Đã sử dụng
                        </span>
                        @else
                        <span class="rounded-full bg-green-500/15 px-3 py-1 text-xs font-bold text-green-400">
                            Chưa sử dụng
                        </span>
                        @endif
                    </div>

                    <p class="text-sm font-bold uppercase tracking-widest text-gray-400">
                        Mã voucher
                    </p>

                    <h2 class="mt-2 break-all text-2xl font-black text-[#f4c56a]">
                        {{ $item->ma_voucher_ca_nhan }}
                    </h2>

                    <div class="my-6 rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                        <p class="text-sm text-gray-400">Giảm giá</p>
                        <p class="mt-1 text-3xl font-black text-white">
                            {{ number_format($item->voucher->gia_tri_giam, 0, ',', '.') }}đ
                        </p>
                    </div>

                    <div class="space-y-3 text-sm text-gray-400">
                        <div class="flex items-center justify-between">
                            <span>Ngày nhận</span>
                            <span class="font-bold text-white">
                                {{ $item->ngay_nhan?->format('d/m/Y') ?? '---' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span>Ngày sử dụng</span>
                            <span class="font-bold text-white">
                                {{ $item->ngay_su_dung?->format('d/m/Y') ?? 'Chưa dùng' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full rounded-[28px] border border-white/10 bg-[#121212] px-6 py-16 text-center">
                <div
                    class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-white/5 text-gray-500">
                    <i class="fa-solid fa-ticket text-3xl"></i>
                </div>

                <h3 class="text-xl font-black text-white">
                    Bạn chưa có voucher nào
                </h3>

                <p class="mt-2 text-gray-400">
                    Hãy dùng điểm thành viên để đổi voucher giảm giá.
                </p>

                <a href="{{ route('user.voucher.index') }}"
                    class="mt-5 inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-black text-white no-underline transition hover:-translate-y-1 hover:shadow-lg hover:shadow-[#d99a32]/30">
                    <i class="fa-solid fa-gift"></i>
                    Đổi điểm ngay
                </a>
            </div>
            @endforelse
        </div>

        @if($vouchers->hasPages())
        <div class="mt-8 rounded-2xl border border-white/10 bg-[#121212] px-6 py-4">
            {{ $vouchers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection