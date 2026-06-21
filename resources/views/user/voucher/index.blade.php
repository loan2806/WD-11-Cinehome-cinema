@extends('layouts.user')

@section('title','Đổi điểm lấy voucher')

@section('content')
<div class="min-h-screen bg-[#080808] px-6 pt-28 pb-16 text-white">
    <div class="mx-auto max-w-6xl">

        <div class="mb-8 flex flex-col justify-between gap-5 md:flex-row md:items-end">
            <div>
                <p class="mb-2 text-sm font-bold uppercase tracking-[0.25em] text-[#d99a32]">
                    CineHome Rewards
                </p>

                <h1 class="text-4xl font-black">
                    Đổi điểm lấy <span class="text-[#d99a32]">voucher</span>
                </h1>

                <p class="mt-3 max-w-2xl text-gray-400">
                    Sử dụng điểm thành viên để đổi voucher giảm giá cho những lần đặt vé tiếp theo.
                </p>
            </div>

            <div class="rounded-2xl border border-[#d99a32]/30 bg-[#d99a32]/10 px-6 py-4">
                <p class="text-sm text-gray-400">Điểm hiện tại</p>
                <p class="text-3xl font-black text-[#f4c56a]">
                    {{ number_format($thanhVien->diem_hien_tai ?? 0) }}
                </p>
            </div>
        </div>

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

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            @foreach($vouchers as $voucher)
                @php
                    $duDiem = ($thanhVien?->diem_hien_tai ?? 0) >= $voucher->diem_can_doi;
                @endphp

                <div class="group relative overflow-hidden rounded-[28px] border border-white/10 bg-gradient-to-br from-[#171717] via-[#111111] to-[#070707] p-6 shadow-xl shadow-black/30 transition duration-500 hover:-translate-y-2 hover:border-[#d99a32]/50 hover:shadow-[#d99a32]/20">

                    <div class="absolute -right-16 -top-16 h-44 w-44 rounded-full bg-[#d99a32]/20 blur-3xl transition duration-500 group-hover:bg-[#d99a32]/30"></div>

                    <div class="relative z-10">
                        <div class="mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white shadow-lg shadow-[#d99a32]/20 transition duration-300 group-hover:scale-110">
                            <i class="fa-solid fa-gift text-xl"></i>
                        </div>

                        <p class="text-sm font-bold uppercase tracking-widest text-gray-400">
                            {{ $voucher->ma_voucher }}
                        </p>

                        <h2 class="mt-2 text-2xl font-black text-white">
                            {{ $voucher->ten_voucher }}
                        </h2>

                        <div class="my-6 rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                            <p class="text-sm text-gray-400">Giá trị giảm</p>
                            <p class="mt-1 text-3xl font-black text-[#f4c56a]">
                                {{ number_format($voucher->gia_tri_giam, 0, ',', '.') }}đ
                            </p>
                        </div>

                        <div class="mb-5 flex items-center justify-between rounded-2xl bg-black/30 px-4 py-3">
                            <span class="text-sm text-gray-400">Điểm cần đổi</span>
                            <span class="font-black text-white">
                                {{ number_format($voucher->diem_can_doi) }} điểm
                            </span>
                        </div>

                        <form method="POST" action="{{ route('user.voucher.exchange', $voucher) }}">
                            @csrf

                            <button type="submit"
                                    class="w-full rounded-2xl px-5 py-3 text-sm font-black transition duration-300
                                    {{ $duDiem
                                        ? 'bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white shadow-lg shadow-[#d99a32]/20 hover:-translate-y-1 hover:shadow-[#d99a32]/35'
                                        : 'cursor-not-allowed bg-white/10 text-gray-500' }}"
                                    {{ $duDiem ? '' : 'disabled' }}>
                                @if($duDiem)
                                    Đổi voucher ngay
                                @else
                                    Chưa đủ điểm
                                @endif
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8 rounded-[24px] border border-white/10 bg-[#121212] p-6">
            <h3 class="font-black text-[#d99a32]">
                Quy định đổi điểm
            </h3>

            <p class="mt-3 text-sm leading-7 text-gray-400">
                Điểm sau khi đổi voucher sẽ bị trừ khỏi điểm hiện tại. Voucher đã đổi chỉ sử dụng một lần và không thể hoàn lại điểm sau khi đã sử dụng.
            </p>
        </div>
    </div>
</div>
@endsection