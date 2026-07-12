@extends('layouts.admin')

@section('page-title', 'Thêm phim')

@section('content')

    <div class="min-h-screen bg-black text-white px-6 xl:px-10 py-10">

        <div class="w-full max-w-[1600px] mx-auto">

            {{-- HEADER --}}
            <div class="mb-10">

                <h1 class="text-4xl font-bold tracking-wide">
                    🎬 Thêm phim mới
                </h1>

                <p class="text-zinc-400 mt-2">
                    Điền thông tin để thêm phim vào hệ thống
                </p>

            </div>

            <form action="{{ route('admin.phims.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">

                @csrf
                {{-- MAIN GRID --}}
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-10 items-start">

                    {{-- LEFT --}}
                    <div class="space-y-6">

                        {{-- TÊN PHIM --}}
                        <div>

                            <label class="text-sm text-zinc-400">
                                Tên phim
                            </label>

                            <input type="text" name="ten_phim" value="{{ old('ten_phim') }}"
                                placeholder="Nhập tên phim..."
                                class="w-full mt-2 bg-zinc-950 border rounded-2xl px-4 py-3
                               focus:outline-none transition
                               {{ $errors->has('ten_phim') ? 'border-red-500' : 'border-zinc-800' }}
                               focus:border-red-500">

                            @error('ten_phim')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        {{-- THỂ LOẠI --}}
                        <div>

                            <label class="text-sm text-zinc-400">
                                Thể loại
                            </label>

                            <div
                                class="space-y-2 max-h-64 overflow-y-auto rounded-2xl border border-zinc-800 bg-zinc-950 p-4 scrollbar scrollbar-w-2 scrollbar-track-zinc-950 scrollbar-thumb-zinc-700 hover:scrollbar-thumb-zinc-600">

                                @forelse($genres as $genre)
                                    <label
                                        class="flex items-center cursor-pointer hover:bg-zinc-900 p-2 rounded transition">

                                        <input type="checkbox" name="the_loai_id[]" value="{{ $genre->id }}"
                                            {{ in_array($genre->id, old('the_loai_id', [])) ? 'checked' : '' }}
                                            class="w-4 h-4 rounded">

                                        <span class="ml-3 flex-1">
                                            {{ $genre->ten_the_loai }}
                                        </span>

                                    </label>

                                @empty

                                    <p class="text-zinc-500 text-sm text-center py-6">
                                        Chưa có thể loại nào.

                                        <a href="{{ route('admin.the-loais.create') }}"
                                            class="text-amber-500 hover:underline">

                                            Tạo ngay

                                        </a>
                                    </p>
                                @endforelse

                            </div>

                            @error('the_loai_id')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        {{-- QUỐC GIA --}}
                        <div>

                            <label class="text-sm text-zinc-400">
                                Quốc gia
                            </label>

                            <select name="quoc_gia_id"
                                class="w-full mt-2 bg-zinc-950 border border-zinc-800 rounded-2xl px-4 py-3 text-white">

                                <option value="">
                                    -- Chọn quốc gia --
                                </option>

                                @foreach ($countries as $country)
                                    <option value="{{ $country->id }}"
                                        {{ old('quoc_gia_id') == $country->id ? 'selected' : '' }}>

                                        {{ $country->ten_quoc_gia }}

                                    </option>
                                @endforeach

                            </select>

                            @error('quoc_gia_id')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        {{-- ĐẠO DIỄN --}}
                        <div>

                            <label class="text-sm text-zinc-400">
                                Đạo diễn
                            </label>

                            <input type="text" name="dao_dien" value="{{ old('dao_dien') }}"
                                placeholder="Christopher Nolan..."
                                class="w-full mt-2 bg-zinc-950 border rounded-2xl px-4 py-3
                               border-zinc-800 focus:outline-none focus:border-red-500">

                            @error('dao_dien')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        {{-- DIỄN VIÊN --}}
                        <div>

                            <label class="text-sm text-zinc-400">
                                Diễn viên
                            </label>

                            <input type="text" name="dien_vien" value="{{ old('dien_vien') }}"
                                placeholder="Robert Downey Jr..."
                                class="w-full mt-2 bg-zinc-950 border rounded-2xl px-4 py-3
                               border-zinc-800 focus:outline-none focus:border-red-500">

                            @error('dien_vien')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        {{-- NGÔN NGỮ --}}
                        <div>

                            <label class="text-sm text-zinc-400">
                                Ngôn ngữ
                            </label>

                            <input type="text" name="ngon_ngu" value="{{ old('ngon_ngu') }}" placeholder="Tiếng Anh..."
                                class="w-full mt-2 bg-zinc-950 border rounded-2xl px-4 py-3
                               border-zinc-800 focus:outline-none focus:border-red-500">

                            @error('ngon_ngu')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        {{-- THỜI LƯỢNG --}}
                        <div>

                            <label class="text-sm text-zinc-400">
                                Thời lượng (phút)
                            </label>

                            <input type="number" name="thoi_luong" value="{{ old('thoi_luong') }}" placeholder="120"
                                class="w-full mt-2 bg-zinc-950 border rounded-2xl px-4 py-3
                               border-zinc-800 focus:outline-none focus:border-red-500">

                            @error('thoi_luong')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        {{-- GIỚI HẠN TUỔI --}}
                        <div>

                            <label class="text-sm text-zinc-400">
                                Giới hạn tuổi
                            </label>

                            <input type="text" name="gioi_han_tuoi" value="{{ old('gioi_han_tuoi') }}"
                                placeholder="P, C13, C16..."
                                class="w-full mt-2 bg-zinc-950 border rounded-2xl px-4 py-3
                               border-zinc-800 focus:outline-none focus:border-red-500">

                            @error('gioi_han_tuoi')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        {{-- TRAILER --}}
                        <div>

                            <label class="text-sm text-zinc-400">
                                Trailer URL
                            </label>

                            <input type="url" id="trailer" name="trailer" value="{{ old('trailer') }}"
                                placeholder="https://youtube.com/..."
                                class="w-full mt-2 bg-zinc-950 border rounded-2xl px-4 py-3
                               border-zinc-800 focus:outline-none focus:border-red-500">

                            @error('trailer')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                    </div>

                    {{-- RIGHT --}}
                    <div class="space-y-6 sticky top-10">

                        {{-- POSTER --}}
                        <div>

                            <label class="text-sm text-zinc-400">
                                Poster phim
                            </label>

                            <div class="mt-2">

                                <label for="poster"
                                    class="cursor-pointer flex flex-col items-center justify-center
                                   w-full h-[520px]
                                   border-2 border-dashed rounded-3xl
                                   bg-zinc-950 hover:border-red-500
                                   transition relative overflow-hidden
                                   border-zinc-700">

                                    <div id="upload-placeholder" class="text-zinc-500 flex flex-col items-center">

                                        <span class="text-7xl font-light">
                                            +
                                        </span>

                                        <span class="text-sm mt-3 tracking-wide">
                                            Upload poster
                                        </span>

                                    </div>

                                    <img id="preview" class="absolute inset-0 w-full h-full object-cover hidden" />

                                </label>

                                <input id="poster" type="file" name="poster" accept="image/*" class="hidden"
                                    onchange="previewImage(event)">

                                @error('poster')
                                    <p class="text-red-500 text-sm mt-2">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                        </div>

                        {{-- MÔ TẢ --}}
                        <div>

                            <label class="text-sm text-zinc-400">
                                Mô tả phim
                            </label>

                            <textarea name="mo_ta" rows="8" placeholder="Nhập mô tả phim..."
                                class="w-full mt-2 bg-zinc-900 border rounded-3xl px-5 py-4
                                  border-zinc-800 focus:outline-none focus:border-red-500 min-h-[220px]">{{ old('mo_ta') }}</textarea>

                            @error('mo_ta')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>
                        <div id="trailerBox" class="hidden mt-5">
                            <label class="text-sm text-zinc-400">
                                Xem trước trailer
                            </label>

                            <div class="mt-2 overflow-hidden rounded-2xl border border-zinc-800 aspect-video">
                                <iframe id="trailerPreview" class="w-full h-full" src="" frameborder="0"
                                    allowfullscreen>
                                </iframe>
                            </div>
                        </div>


                    </div>

                </div>

                {{-- BUTTON --}}
                <div class="flex items-center justify-end gap-4 pt-4">

                    <a href="{{ url()->previous() }}"
                        class="px-6 py-3 rounded-2xl bg-zinc-800 hover:bg-zinc-700 transition font-medium">

                        Quay lại

                    </a>

                    <button type="submit"
                        class="px-8 py-3 rounded-2xl
                        bg-gradient-to-r from-[#6b3a1e] via-[#a66a2b] to-[#d9a441]
                        text-white font-semibold shadow-lg shadow-amber-900/30">

                        💾 Thêm mới phim

                    </button>

                </div>

            </form>

        </div>

    </div>

    <script>
        function previewImage(event) {
            const input = event.target;

            const preview = document.getElementById('preview');

            const placeholder = document.getElementById('upload-placeholder');

            if (input.files && input.files[0]) {

                const reader = new FileReader();

                reader.onload = function(e) {

                    preview.src = e.target.result;

                    preview.classList.remove('hidden');

                    placeholder.classList.add('hidden');
                };

                reader.readAsDataURL(input.files[0]);
            }
        }

        const trailerInput = document.getElementById('trailer');
        const trailerBox = document.getElementById('trailerBox');
        const trailerPreview = document.getElementById('trailerPreview');

        function getYoutubeEmbed(url) {

            let id = '';

            if (url.includes('watch?v=')) {
                id = url.split('watch?v=')[1].split('&')[0];

            } else if (url.includes('youtu.be/')) {
                id = url.split('youtu.be/')[1].split('?')[0];

            } else if (url.includes('/shorts/')) {
                id = url.split('/shorts/')[1].split('?')[0];
            }

            return id ?
                `https://www.youtube.com/embed/${id}` :
                null;
        }

        trailerInput.addEventListener('input', async function() {

            const url = this.value.trim();
            const embed = getYoutubeEmbed(url);

            if (!embed) {
                trailerPreview.src = '';
                trailerBox.classList.add('hidden');
                return;
            }

            try {
                const res = await fetch(
                    `https://www.youtube.com/oembed?url=${encodeURIComponent(url)}&format=json`
                );

                if (!res.ok) {
                    trailerPreview.src = '';
                    trailerBox.classList.add('hidden');
                    return;
                }

                trailerPreview.src = embed;
                trailerBox.classList.remove('hidden');

            } catch (e) {
                trailerPreview.src = '';
                trailerBox.classList.add('hidden');
            }
        });
    </script>

@endsection
