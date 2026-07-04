@extends('layouts.admin')

@section('page-title', 'Quản lý phim')

@section('content')

<div class="admin-panel">

    {{-- HEADER --}}
    <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

        <div>
            <h5 class="text-2xl font-black text-white">
                Danh sách phim
            </h5>
            <small class="text-gray-400">
                Quản lý toàn bộ phim trong hệ thống
            </small>
        </div>

        <a href="{{ route('admin.phims.create') }}"
            class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-bold text-white shadow-lg">

            <i class="fa-solid fa-plus"></i>
            Thêm phim

        </a>

    </div>

    {{-- FILTER --}}
    <form method="GET" action="{{ route('admin.phims.index') }}" class="mt-6 flex flex-wrap gap-3">

        <input type="text" name="tim_kiem" value="{{ request('tim_kiem') }}"
            placeholder="Tìm tên phim..."
            class="h-12 flex-1 rounded-2xl border border-white/10 bg-white/5 px-4 text-white">

        <select name="the_loai" class="h-12 rounded-2xl bg-[#151515] px-4 text-white">
            <option value="">Thể loại</option>
            @foreach ($genres as $genre)
                <option value="{{ $genre->ten_the_loai }}"
                    {{ request('the_loai') == $genre->ten_the_loai ? 'selected' : '' }}>
                    {{ $genre->ten_the_loai }}
                </option>
            @endforeach
        </select>

        <select name="quoc_gia" class="h-12 rounded-2xl bg-[#151515] px-4 text-white">
            <option value="">Quốc gia</option>
            @foreach ($countries as $country)
                <option value="{{ $country->ten_quoc_gia }}"
                    {{ request('quoc_gia') == $country->ten_quoc_gia ? 'selected' : '' }}>
                    {{ $country->ten_quoc_gia }}
                </option>
            @endforeach
        </select>

        <button class="h-12 rounded-2xl bg-[#d99a32] px-5 font-bold text-white">
            Lọc
        </button>

        <a href="{{ route('admin.phims.index') }}"
            class="flex h-12 items-center rounded-2xl bg-white/10 px-5 text-white">
            Reset
        </a>

    </form>

    {{-- TABLE --}}
    <div class="mt-6 overflow-hidden rounded-3xl border border-white/10">

        <div class="overflow-x-auto">

            <table class="w-full min-w-[1200px] text-left">

                <thead class="bg-white/5 text-xs uppercase text-gray-400">
                    <tr>
                        <th class="px-5 py-4">ID</th>
                        <th class="px-5 py-4">Phim</th>
                        <th class="px-5 py-4">Quốc gia</th>
                        <th class="px-5 py-4">Thể loại</th>
                        <th class="px-5 py-4">Ngôn ngữ</th>
                        <th class="px-5 py-4">Thời lượng</th>
                        <th class="px-5 py-4 text-right">Hành động</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-white/5">

                    @forelse ($movies as $movie)

                        <tr class="bg-[#0f0f0f] hover:bg-white/5">

                            <td class="px-5 py-5 text-gray-400">
                                {{ $movie->id }}
                            </td>

                            <td class="px-5 py-5">
                                <div class="flex items-center gap-4">

                                    <img src="{{ asset('storage/movies/' . $movie->poster) }}"
                                        class="h-20 w-14 rounded-xl object-cover">

                                    <div class="text-white font-bold">
                                        {{ $movie->ten_phim }}
                                    </div>

                                </div>
                            </td>

                            <td class="px-5 py-5 text-gray-300">
                                {{ $movie->country?->ten_quoc_gia ?? 'N/A' }}
                            </td>

                            <td class="px-5 py-5">
                                @foreach ($movie->genres as $genre)
                                    <span class="inline-block rounded-full bg-white/10 px-3 py-1 text-xs mr-1">
                                        {{ $genre->ten_the_loai }}
                                    </span>
                                @endforeach
                            </td>

                            <td class="px-5 py-5 text-gray-300">
                                {{ $movie->ngon_ngu }}
                            </td>

                            <td class="px-5 py-5 text-gray-300">
                                {{ $movie->thoi_luong }} phút
                            </td>

                            <td class="px-5 py-5">
                                <div class="flex justify-end gap-3">

                                    <a href="{{ route('admin.phims.show', $movie) }}"
                                        class="h-10 w-10 flex items-center justify-center rounded-xl bg-blue-500/15 text-blue-300">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>

                                    <a href="{{ route('admin.phims.edit', $movie) }}"
                                        class="h-10 w-10 flex items-center justify-center rounded-xl bg-yellow-500/15 text-yellow-300">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>

                                    <form method="POST" action="{{ route('admin.phims.destroy', $movie) }}">
                                        @csrf
                                        @method('DELETE')

                                        <button onclick="return confirm('Xóa phim này?')"
                                            class="h-10 w-10 rounded-xl bg-red-500/15 text-red-300">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="py-16 text-center text-gray-500">
                                Chưa có phim nào
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>
    </div>
@include('components.admin-pagination', ['paginator' => $movies])

</div>

@endsection