@extends('layouts.admin')

@section('title', 'Chi tiết phim')

@section('content')

<div class="min-h-screen bg-black text-white px-6 xl:px-10 py-10">

    <div class="w-full max-w-[1600px] mx-auto">

        {{-- HEADER --}}
        <div class="mb-10">

            <h1 class="text-4xl font-bold tracking-wide">
                🎬 Chi tiết phim
            </h1>

            <p class="text-zinc-400 mt-2">
                {{ $phim->ten_phim }}
            </p>

        </div>

        {{-- FORM (CHỈ HIỂN THỊ, KHÔNG EDIT) --}}
        <div class="space-y-8">

            {{-- GRID --}}
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-10 items-start">

                {{-- LEFT SIDE --}}
                <div class="space-y-6">

                    {{-- TÊN PHIM --}}
                    <div>
                        <label class="text-sm text-zinc-400">Tên phim</label>

                        <div class="w-full mt-2 bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-3">
                            {{ $phim->ten_phim }}
                        </div>
                    </div>

                    {{-- THỂ LOẠI --}}
                    <div>
                        <label class="text-sm text-zinc-400">Thể loại</label>

                        <div class="w-full mt-2 bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-3">
                            @forelse($phim->genres as $genre)
                                <span class="bg-white/10 px-3 py-1 rounded-full text-sm">
                                    {{ $genre->ten_the_loai }}
                                </span>
                            @empty
                                <span class="text-zinc-500">Chưa cập nhật</span>
                            @endforelse
                        </div>
                    </div>

                    {{-- QUỐC GIA --}}
                    <div>
                        <label class="text-sm text-zinc-400">Quốc gia</label>

                        <div class="w-full mt-2 bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-3">
                            {{ $phim->country->ten_quoc_gia ?? 'Chưa cập nhật' }}
                        </div>
                    </div>

                    {{-- ĐẠO DIỄN --}}
                    <div>
                        <label class="text-sm text-zinc-400">Đạo diễn</label>

                        <div class="w-full mt-2 bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-3">
                            {{ $phim->dao_dien ?? 'Chưa cập nhật' }}
                        </div>
                    </div>

                    {{-- DIỄN VIÊN --}}
                    <div>
                        <label class="text-sm text-zinc-400">Diễn viên</label>

                        <div class="w-full mt-2 bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-3">
                            {{ $phim->dien_vien ?? 'Chưa cập nhật' }}
                        </div>
                    </div>

                    {{-- NGÔN NGỮ --}}
                    <div>
                        <label class="text-sm text-zinc-400">Ngôn ngữ</label>

                        <div class="w-full mt-2 bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-3">
                            {{ $phim->ngon_ngu ?? 'Chưa cập nhật' }}
                        </div>
                    </div>

                    {{-- GIỚI HẠN TUỔI --}}
                    <div>
                        <label class="text-sm text-zinc-400">Giới hạn tuổi</label>

                        <div class="w-full mt-2 bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-3">
                            {{ $phim->gioi_han_tuoi ?? 'P' }}
                        </div>
                    </div>

                    {{-- THỜI LƯỢNG --}}
                    <div>
                        <label class="text-sm text-zinc-400">Thời lượng</label>

                        <div class="w-full mt-2 bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-3">
                            {{ $phim->thoi_luong }} phút
                        </div>
                    </div>

                    {{-- TRAILER --}}
                    <div>
                        <label class="text-sm text-zinc-400">Trailer</label>

                        <div class="w-full mt-2 bg-zinc-900 border border-zinc-800 rounded-2xl px-4 py-3 break-all">
                            {{ $phim->trailer }}
                        </div>
                    </div>

                </div>

                {{-- RIGHT SIDE --}}
                <div class="space-y-6 sticky top-10">

                    {{-- POSTER --}}
                    <div>
                        <label class="text-sm text-zinc-400">Poster</label>

                        <div class="mt-2 border border-zinc-800 rounded-3xl overflow-hidden bg-zinc-950">
                            <img src="{{ asset('storage/' . $phim->poster) }}"
                                 class="w-full h-[520px] object-cover">
                        </div>
                    </div>

                    {{-- MÔ TẢ --}}
                    <div>
                        <label class="text-sm text-zinc-400">Mô tả</label>

                        <div class="w-full mt-2 bg-zinc-900 border border-zinc-800 rounded-3xl px-5 py-4 min-h-[220px]">
                            {{ $phim->mo_ta }}
                        </div>
                    </div>

                </div>

            </div>

            {{-- BUTTON --}}
            <div class="flex justify-end pt-4">

                <a href="{{ route('admin.phims.index') }}"
                   class="px-6 py-3 rounded-2xl bg-zinc-800 hover:bg-zinc-700 transition font-medium">

                    ← Quay lại

                </a>

            </div>

        </div>

    </div>

</div>

@endsection