@extends('layouts.admin')

@section('title', 'Quản lý phim')

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
                class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">

                <i class="fa-solid fa-plus"></i>

                Thêm phim

            </a>

        </div>

        {{-- FILTER --}}
        <form method="GET" action="{{ route('admin.phims.index') }}" class="mt-6 flex flex-wrap items-center gap-3">

            {{-- SEARCH --}}
            <input type="text" name="tim_kiem" value="{{ request('tim_kiem') }}" placeholder="Tìm tên phim..."
                class="h-12 min-w-[220px] flex-1 rounded-2xl border border-white/10 bg-white/5 px-4 text-white outline-none transition focus:border-[#d99a32]">

            {{-- GENRE --}}
            <select name="the_loai"
                class="h-12 rounded-2xl border border-white/10 bg-[#151515] px-4 text-white outline-none focus:border-[#d99a32]">

                <option value="">Thể loại</option>

                @foreach ($genres as $genre)
                    <option value="{{ $genre->ten_the_loai }}"
                        {{ request('the_loai') == $genre->ten_the_loai ? 'selected' : '' }}>

                        {{ $genre->ten_the_loai }}

                    </option>
                @endforeach
            </select>

            {{-- COUNTRY --}}
            <select name="quoc_gia"
                class="h-12 rounded-2xl border border-white/10 bg-[#151515] px-4 text-white outline-none focus:border-[#d99a32]">

                <option value="">Quốc gia</option>

                @foreach ($countries as $country)
                    <option value="{{ $country->ten_quoc_gia }}"
                        {{ request('quoc_gia') == $country->ten_quoc_gia ? 'selected' : '' }}>

                        {{ $country->ten_quoc_gia }}

                    </option>
                @endforeach

            </select>

            {{-- FILTER BTN --}}
            <button type="submit"
                class="h-12 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 text-sm font-bold text-white shadow-lg transition hover:opacity-90">

                <i class="fa-solid fa-filter mr-1"></i>

                Lọc

            </button>

            {{-- RESET BTN --}}
            <a href="{{ route('admin.phims.index') }}"
                class="flex h-12 items-center rounded-2xl border border-white/10 bg-white/5 px-5 text-sm font-bold text-white transition hover:bg-white/10">

                Reset

            </a>

        </form>

        @if (session('success'))
            <div id="toast-success"
                class="fixed top-5 right-5 z-50 rounded-xl border border-green-500/30 bg-green-500 px-5 py-3 text-white shadow-lg transition-all duration-500">

                {{ session('success') }}

            </div>
        @endif

        @if (session('error'))
            <div id="toast-error"
                class="fixed top-5 right-5 z-50 rounded-xl border border-red-500/30 bg-red-500 px-5 py-3 text-white shadow-lg transition-all duration-500">

                {{ session('error') }}

            </div>
        @endif

        {{-- TABLE --}}
        <div class="mt-6 overflow-hidden rounded-3xl border border-white/10">

            <div class="overflow-x-auto">

                <table class="w-full min-w-[1200px] text-left">

                    {{-- HEAD --}}
                    <thead class="bg-white/5 text-xs uppercase tracking-wider text-gray-400">

                        <tr>

                            <th class="px-5 py-4">
                                ID
                            </th>

                            <th class="px-5 py-4">
                                Phim
                            </th>

                            <th class="px-5 py-4">
                                Quốc gia
                            </th>

                            <th class="px-5 py-4">
                                Thể loại
                            </th>

                            <th class="px-5 py-4">
                                Đạo diễn
                            </th>

                            <th class="px-5 py-4">
                                Diễn viên
                            </th>

                            <th class="px-5 py-4">
                                Ngôn ngữ
                            </th>

                            <th class="px-5 py-4">
                                Thời lượng
                            </th>

                            <th class="px-5 py-4">
                                Giới hạn tuổi
                            </th>
                            <th class="px-5 py-4">
                                Mô tả
                            </th>


                            <th class="px-5 py-4 text-right">
                                Hành động
                            </th>

                        </tr>

                    </thead>

                    {{-- BODY --}}
                    <tbody class="divide-y divide-white/5">

                        @forelse ($movies as $movie)

                            <tr class="bg-[#0f0f0f] transition hover:bg-white/5">

                                {{-- ID --}}
                                <td class="px-5 py-5 text-gray-400">

                                    {{ $movie->id }}

                                </td>

                                {{-- MOVIE --}}
                                <td class="px-5 py-5">

                                    <div class="flex items-center gap-4">

                                        <img src="{{ asset('storage/' . $movie->poster) }}" alt="{{ $movie->ten_phim }}"
                                            class="h-20 w-14 rounded-xl object-cover shadow-lg">

                                        <div>

                                            <div class="font-bold text-white">

                                                {{ $movie->ten_phim }}

                                            </div>

                                        </div>

                                    </div>

                                </td>

                                {{-- COUNTRY --}}
                                <td class="px-5 py-5 text-gray-300">

                                    {{ $movie->country->ten_quoc_gia  }}

                                </td>

                                {{-- GENRE --}}
                                <td class="px-5 py-5 text-gray-300">

                                    @foreach  ($movie->genres as $genre)
                                        <span class="inline-block rounded-full bg-white/10 px-3 py-1 text-xs mr-1 mb-1">
                                            {{ $genre->ten_the_loai }}
                                        </span>
                                    @endforeach

                                </td>

                                {{-- DIRECTOR --}}
                                <td class="px-5 py-5 text-gray-300">

                                    {{ $movie->dao_dien  }}

                                </td>

                                {{-- ACTORS --}}
                                <td class="px-5 py-5 text-gray-300">

                                    {{ \Illuminate\Support\Str::limit($movie->dien_vien , 40) }}

                                </td>

                                {{-- LANGUAGE --}}
                                <td class="px-5 py-5 text-gray-300">

                                    {{ $movie->ngon_ngu  }}

                                </td>

                                {{-- DURATION --}}
                                <td class="px-5 py-5 text-gray-300">

                                    {{ $movie->thoi_luong }} phút

                                </td>

                                {{-- AGE --}}
                                <td class="px-5 py-5 text-gray-300">

                                    {{ $movie->gioi_han_tuoi  }}

                                </td>
                                <td class="px-5 py-5 text-gray-300 max-w-[300px]">
                                    <div class="truncate">
                                        {{ $movie->mo_ta  }}
                                    </div>
                                </td>

                                {{-- STATUS --}}

                                {{-- ACTION --}}
                                <td class="px-5 py-5">

                                    <div class="flex justify-end gap-2">

                                        {{-- XEM CHI TIẾT --}}
                                        <a href="{{ route('admin.phims.show', $movie) }}"
                                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/15 text-blue-300 transition hover:bg-blue-500/25">

                                            <i class="fa-solid fa-eye"></i>

                                        </a>

                                        {{-- EDIT --}}
                                        <a href="{{ route('admin.phims.edit', $movie) }}"
                                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-yellow-500/15 text-yellow-300 transition hover:bg-yellow-500/25">

                                            <i class="fa-solid fa-pen"></i>

                                        </a>


                                        {{-- DELETE --}}
                                        <form action="{{ route('admin.phims.destroy', $movie) }}" method="POST">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                onclick="return confirm('Bạn có chắc muốn xóa phim này?')"
                                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-500/15 text-red-300 transition hover:bg-red-500/25">

                                                <i class="fa-solid fa-trash"></i>

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="10" class="px-5 py-16 text-center text-gray-500">

                                    Chưa có phim nào trong hệ thống

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

@endsection

<script>
    setTimeout(() => {
        const success = document.getElementById('toast-success');
        const error = document.getElementById('toast-error');

        if (success) {
            success.classList.add('opacity-0', 'translate-x-10');

            setTimeout(() => success.remove(), 500);
        }

        if (error) {
            error.classList.add('opacity-0', 'translate-x-10');

            setTimeout(() => error.remove(), 500);
        }
    }, 4000);
</script>
