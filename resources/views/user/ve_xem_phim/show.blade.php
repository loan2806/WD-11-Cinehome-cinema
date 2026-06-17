@extends('layouts.user')

@section('title', 'Chi tiet ve')

@section('content')
<div class="min-h-screen bg-[#080808] px-6 pt-28 pb-12 text-white">
    <div class="mx-auto max-w-3xl rounded-2xl border border-white/10 bg-[#121212] p-8">
        @if(session('success'))
            <div class="mb-4 rounded-xl bg-green-500/15 px-4 py-3 text-green-300">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-xl bg-red-500/15 px-4 py-3 text-red-300">{{ session('error') }}</div>
        @endif

        <a href="{{ route('user.ve_xem_phim.index') }}" class="mb-6 inline-block text-sm font-bold text-[#d99a32]">
            Quay lai ve cua toi
        </a>

        <h1 class="text-3xl font-black text-[#d99a32]">{{ $veXemPhim->ma_ve }}</h1>

        <div class="mt-6 flex flex-col gap-5 rounded-2xl border border-[#d99a32]/30 bg-white/5 p-5 md:flex-row md:items-center">
            <div class="rounded-2xl bg-white p-3">
                <img
                    class="h-44 w-44"
                    src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($veXemPhim->ma_ve) }}"
                    alt="QR vé {{ $veXemPhim->ma_ve }}"
                >
            </div>
            <div>
                <p class="text-sm font-bold uppercase tracking-widest text-[#d99a32]">QR soát vé</p>
                <p class="mt-2 text-gray-300">Đưa mã này cho nhân viên/admin quét tại cửa phòng chiếu.</p>
                <p class="mt-3 rounded-xl bg-black/30 px-4 py-3 font-mono text-lg font-black text-white">{{ $veXemPhim->ma_ve }}</p>
            </div>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-2">
            <div class="rounded-xl bg-white/5 p-4">
                <p class="text-sm text-gray-400">Phim</p>
                <p class="font-bold">{{ $veXemPhim->ten_phim }}</p>
            </div>
            <div class="rounded-xl bg-white/5 p-4">
                <p class="text-sm text-gray-400">Rap</p>
                <p class="font-bold">{{ $veXemPhim->ten_rap }}</p>
            </div>
            <div class="rounded-xl bg-white/5 p-4">
                <p class="text-sm text-gray-400">Phong</p>
                <p class="font-bold">{{ $veXemPhim->ten_phong }}</p>
            </div>
            <div class="rounded-xl bg-white/5 p-4">
                <p class="text-sm text-gray-400">Ghe</p>
                <p class="font-bold">{{ $veXemPhim->ma_ghe }}</p>
            </div>
            <div class="rounded-xl bg-white/5 p-4">
                <p class="text-sm text-gray-400">Suat chieu</p>
                <p class="font-bold">{{ $veXemPhim->thoi_gian_chieu?->format('H:i d/m/Y') }}</p>
            </div>
            <div class="rounded-xl bg-white/5 p-4">
                <p class="text-sm text-gray-400">Tong tien</p>
                <p class="font-bold">{{ number_format($veXemPhim->tong_tien, 0, ',', '.') }} VND</p>
            </div>
            <div class="rounded-xl bg-white/5 p-4">
                <p class="text-sm text-gray-400">Trang thai</p>
                <p class="font-bold">{{ $veXemPhim->trang_thai }}</p>
            </div>
            @if($veXemPhim->trang_thai === 'da_huy')
                <div class="rounded-xl bg-white/5 p-4">
                    <p class="text-sm text-gray-400">Tien hoan</p>
                    <p class="font-bold text-green-300">{{ number_format($veXemPhim->tien_hoan, 0, ',', '.') }} VND</p>
                </div>
            @endif
        </div>

        <div class="mt-8">
            @if($veXemPhim->trang_thai === 'da_thanh_toan' && $veXemPhim->canCancel())
                <form method="POST" action="{{ route('user.ve_xem_phim.cancel', $veXemPhim) }}" onsubmit="return confirm('Huy ve nay va hoan 50%?')">
                    @csrf
                    @method('PATCH')
                    <button class="rounded-xl bg-red-500 px-5 py-3 font-bold text-white hover:bg-red-600">
                        Huy ve va hoan 50%
                    </button>
                </form>
            @else
                <p class="text-sm text-gray-400">Ve chi duoc huy trong vong 5 phut sau khi dat va khi chua su dung.</p>
            @endif
        </div>
    </div>
</div>
@endsection
