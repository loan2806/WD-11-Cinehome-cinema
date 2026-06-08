@extends('layouts.staff')

@section('title', 'Dashboard Staff - CineHome')
@section('page-title', 'Dashboard Staff')

@section('content')

{{-- STATS --}}
<div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">

    {{-- TOTAL MOVIES --}}
    <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur-md">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400">Tổng phim</p>
                <h2 class="mt-2 text-4xl font-black text-white">
                    {{ $totalMovies ?? 0 }}
                </h2>
            </div>
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-red-500/20 text-3xl text-red-400">
                <i class="fa-solid fa-film"></i>
            </div>
        </div>
    </div>

    {{-- CINEMAS --}}
    <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur-md">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400">Rạp chiếu</p>
                <h2 class="mt-2 text-4xl font-black text-white">
                    {{ $totalCinemas ?? 0 }}
                </h2>
            </div>
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-500/20 text-3xl text-blue-400">
                <i class="fa-solid fa-building"></i>
            </div>
        </div>
    </div>

    {{-- SHOWTIMES --}}
    <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur-md">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400">Suất chiếu hôm nay</p>
                <h2 class="mt-2 text-4xl font-black text-white">
                    {{ $todayShowtimes ?? 0 }}
                </h2>
            </div>
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-green-500/20 text-3xl text-green-400">
                <i class="fa-solid fa-clock"></i>
            </div>
        </div>
    </div>

    {{-- STAFF STATUS --}}
    <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur-md">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400">Trạng thái</p>
                <h2 class="mt-2 text-2xl font-black text-[#d99a32]">Đang hoạt động</h2>
            </div>
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#d99a32]/20 text-3xl text-[#d99a32]">
                <i class="fa-solid fa-user-check"></i>
            </div>
        </div>
    </div>

</div>

{{-- UPCOMING SHOWTIMES --}}
<div class="mt-8 rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur-md">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-white">Lịch chiếu sắp tới</h2>
            <p class="mt-1 text-sm text-gray-400">Danh sách các suất chiếu gần nhất</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[850px]">
            <thead>
                <tr class="border-b border-white/10 text-left text-sm uppercase tracking-wider text-[#d99a32]">
                    <th class="pb-4">Phim</th>
                    <th class="pb-4">Rạp</th>
                    <th class="pb-4">Phòng</th>
                    <th class="pb-4">Ngày chiếu</th>
                    <th class="pb-4">Giờ chiếu</th>
                    <th class="pb-4">Giá vé</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-white/5">
                @forelse($upcomingShowtimes ?? [] as $showtime)
                    <tr class="transition hover:bg-white/5">
                        
                        <td class="py-4 font-bold text-white">
                            {{ $showtime->phim->ten_phim ?? 'Không có phim' }}
                        </td>

                        <td class="py-4 text-gray-300">
                            {{ $showtime->cinema->name ?? 'CineHome Thường Tín' }}
                        </td>

                        <td class="py-4 text-gray-300">
                            {{ $showtime->ten_phong ?? 'Phòng chiếu' }}
                        </td>

                        <td class="py-4 text-gray-300">
                            {{ $showtime->thoi_gian_chieu ? $showtime->thoi_gian_chieu->format('d/m/Y') : '---' }}
                        </td>

                        <td class="py-4 text-gray-300">
                            {{ $showtime->thoi_gian_chieu ? $showtime->thoi_gian_chieu->format('H:i') : '---' }}
                        </td>

                        <td class="py-4 font-bold text-[#d99a32]">
                            {{ number_format($showtime->gia_ve ?? 60000) }}đ
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-gray-500">
                            Chưa có suất chiếu sắp tới.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection