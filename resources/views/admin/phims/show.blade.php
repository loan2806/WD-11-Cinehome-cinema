@extends('layouts.admin')

@section('page-title', 'Chi tiết phim')

@section('content')

    <div class="admin-panel">

        <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            <div>

                <h5 class="text-2xl font-black text-white">
                    Chi tiết phim: {{ $phim->ten_phim }}
                </h5>

                <small class="text-gray-400">
                    Thông tin chi tiết phim
                </small>

            </div>

            <div class="flex gap-3">

                <a href="{{ route('admin.phims.edit', $phim) }}"
                    class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">

                    <i class="fa-solid fa-pen"></i>

                    Sửa

                </a>

                <a href="{{ route('admin.phims.index') }}"
                    class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">

                    <i class="fa-solid fa-arrow-left"></i>

                    Quay lại

                </a>

            </div>

        </div>

        <div class="mt-6 grid gap-5 lg:grid-cols-3">

            {{-- LEFT: INFO --}}
            <div class="rounded-2xl border border-white/10 bg-[#0f0f0f] p-5">

                <h6 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-400">
                    Thông tin phim
                </h6>

                <div class="space-y-3">

                    <div class="flex justify-between border-b border-white/5 pb-3">
                        <span class="text-gray-400">Tên Phim</span>
                        <span class="text-white font-bold text-right max-w-[200px]">{{ $phim->ten_phim }}</span>
                    </div>

                    <div class="flex justify-between border-b border-white/5 pb-3">
                        <span class="text-gray-400">Quốc gia</span>
                        <span class="text-white">{{ $phim->country->ten_quoc_gia }}</span>
                    </div>

                    <div class="flex justify-between border-b border-white/5 pb-3">
                        <span class="text-gray-400">Thể loại</span>
                        <div class="flex flex-wrap gap-1 justify-end">
                            @forelse($phim->genres as $genre)
                                <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs">{{ $genre->ten_the_loai }}</span>
                            @empty
                                <span class="text-gray-500">Chưa cập nhật</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="flex justify-between border-b border-white/5 pb-3">
                        <span class="text-gray-400">Đạo diễn</span>
                        <span class="text-white">{{ $phim->dao_dien }}</span>
                    </div>

                    <div class="flex justify-between border-b border-white/5 pb-3">
                        <span class="text-gray-400">Diễn viên</span>
                        <span class="text-white text-right max-w-[200px]">{{ $phim->dien_vien }}</span>
                    </div>

                    <div class="flex justify-between border-b border-white/5 pb-3">
                        <span class="text-gray-400">Ngôn ngữ</span>
                        <span class="text-white">{{ $phim->ngon_ngu }}</span>
                    </div>

                    <div class="flex justify-between border-b border-white/5 pb-3">
                        <span class="text-gray-400">Giới hạn tuổi</span>
                        <span class="rounded-full bg-red-500/20 px-3 py-1 text-xs font-bold text-red-400">
                            {{ $phim->gioi_han_tuoi }}
                        </span>
                    </div>

                    <div class="flex justify-between border-b border-white/5 pb-3">
                        <span class="text-gray-400">Thời lượng</span>
                        <span class="text-white">{{ $phim->thoi_luong }} phút</span>
                    </div>

                    @php
                        $videoId = null;

                        if ($phim->trailer) {
                            if (str_contains($phim->trailer, 'watch?v=')) {
                                $videoId = explode('&', explode('watch?v=', $phim->trailer)[1])[0];
                            } elseif (str_contains($phim->trailer, 'youtu.be/')) {
                                $videoId = explode('?', explode('youtu.be/', $phim->trailer)[1])[0];
                            } elseif (str_contains($phim->trailer, '/shorts/')) {
                                $videoId = explode('?', explode('/shorts/', $phim->trailer)[1])[0];
                            }
                        }
                    @endphp

                    <div class="border-b border-white/5 pb-3">
                        <div class="mb-3 text-gray-400">
                            Trailer
                        </div>

                        @if ($videoId)
                            <div class="overflow-hidden rounded-xl aspect-video">
                                <iframe class="w-full h-full" src="https://www.youtube.com/embed/{{ $videoId }}"
                                    allowfullscreen>
                                </iframe>
                            </div>
                        @else
                            <span class="text-gray-500">
                                Chưa có trailer
                            </span>
                        @endif
                    </div>

                </div>

            </div>

            {{-- MIDDLE: POSTER --}}
            <div class="space-y-5">

                <div class="rounded-2xl border border-white/10 bg-[#0f0f0f] p-5 overflow-hidden">

                    <h6 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-400">
                        Poster
                    </h6>

                    <img src="{{ asset('storage/movies/' . $phim->poster) }}" alt="{{ $phim->ten_phim }}"
                        class="w-full rounded-2xl object-cover shadow-lg" style="height: 420px;">

                </div>

            </div>

            {{-- RIGHT: MO TA --}}
            <div class="rounded-2xl border border-white/10 bg-[#0f0f0f] p-5">

                <h6 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-400">
                    Mô tả
                </h6>

                <div class="text-gray-300 leading-relaxed">
                    {{ $phim->mo_ta }}
                </div>

            </div>

        </div>

    </div>

@endsection
