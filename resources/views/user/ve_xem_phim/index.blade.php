@extends('layouts.user')

@section('title', 'Ve cua toi')

@section('content')
<div class="min-h-screen bg-[#080808] px-6 pt-28 pb-12 text-white">
    <div class="mx-auto max-w-7xl">
        <h1 class="mb-8 text-3xl font-black">Ve <span class="text-[#d99a32]">cua toi</span></h1>

        @if(session('success'))
            <div class="mb-4 rounded-xl bg-green-500/15 px-4 py-3 text-green-300">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-xl bg-red-500/15 px-4 py-3 text-red-300">{{ session('error') }}</div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-white/10 bg-[#121212]">
            <table class="w-full text-left">
                <thead class="bg-[#1f1f1f] text-sm uppercase text-gray-400">
                    <tr>
                        <th class="px-5 py-4">Ma ve</th>
                        <th class="px-5 py-4">Phim</th>
                        <th class="px-5 py-4">Ghe</th>
                        <th class="px-5 py-4">Suat chieu</th>
                        <th class="px-5 py-4">Tong tien</th>
                        <th class="px-5 py-4">Trang thai</th>
                        <th class="px-5 py-4 text-right">Thao tac</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($veXemPhims as $ve)
                        <tr class="hover:bg-white/[0.03]">
                            <td class="px-5 py-4 font-bold text-[#d99a32]">{{ $ve->ma_ve }}</td>
                            <td class="px-5 py-4">{{ $ve->ten_phim }}</td>
                            <td class="px-5 py-4">{{ $ve->ma_ghe }}</td>
                            <td class="px-5 py-4">{{ $ve->thoi_gian_chieu?->format('H:i d/m/Y') }}</td>
                            <td class="px-5 py-4">{{ number_format($ve->tong_tien, 0, ',', '.') }} VND</td>
                            <td class="px-5 py-4">{{ $ve->trang_thai }}</td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('user.ve_xem_phim.show', $ve) }}" class="rounded-lg bg-[#d99a32] px-4 py-2 text-sm font-bold text-[#2b1208]">
                                    Chi tiet
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-gray-400">Chua co ve nao.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $veXemPhims->links() }}</div>
    </div>
</div>
@endsection
