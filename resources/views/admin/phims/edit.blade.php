@extends('layouts.admin')

@section('page-title', 'Sửa phim')

@section('content')

    <div class="min-h-screen bg-black text-white px-6 xl:px-10 py-10">

        <div class="w-full max-w-[1600px] mx-auto">

            {{-- HEADER --}}
            <div class="mb-10">

                <h1 class="text-4xl font-bold tracking-wide">
                    🎬 Sửa phim
                </h1>

                <p class="text-zinc-400 mt-2">
                    {{ $phim->ten_phim }}
                </p>

            </div>

            <form action="{{ route('admin.phims.update', $phim) }}" method="POST" enctype="multipart/form-data"
                class="space-y-8">

                @csrf
                @method('PUT')

                {{-- MAIN GRID --}}
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-10 items-start">

                    {{-- LEFT SIDE --}}
                    <div class="space-y-6">

                        {{-- TITLE --}}
                        <div>

                            <label class="text-sm text-zinc-400">
                                Tên phim <span class="text-red-500">*</span>
                            </label>

                            <input type="text" name="ten_phim" value="{{ old('ten_phim', $phim->ten_phim) }}"
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

                        {{-- GENRES --}}
                        <div>

                            <label class="text-sm text-zinc-400 block mb-3">
                                Thể loại <span class="text-red-500">*</span>
                            </label>

                            <div
                                class="space-y-2 max-h-64 overflow-y-auto rounded-2xl border border-zinc-800 bg-zinc-950 p-4 scrollbar scrollbar-w-2 scrollbar-track-zinc-950 scrollbar-thumb-zinc-700 hover:scrollbar-thumb-zinc-600">


                                @forelse($genres as $genre)
                                    <label
                                        class="flex items-center cursor-pointer hover:bg-zinc-900 p-2 rounded transition">

                                        <input type="checkbox" name="the_loai_id[]" value="{{ $genre->id }}"
                                            {{ in_array($genre->id, old('the_loai_id', $selectedGenreIds)) ? 'checked' : '' }}
                                            class="w-4 h-4 rounded">

                                        <span class="ml-3 flex-1">
                                            {{ $genre->ten_the_loai }}
                                        </span>

                                    </label>

                                @empty

                                    <p class="text-zinc-500 text-sm text-center py-6">
                                        Chưa có thể loại nào. <a href="{{ route('admin.the-loais.create') }}"
                                            class="text-red-500 hover:underline">Tạo ngay</a>
                                    </p>
                                @endforelse

                            </div>

                            @error('the_loai_id')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        {{-- COUNTRY --}}
                        <div>

                            <label class="text-sm text-zinc-400">
                                Quốc gia <span class="text-red-500">*</span>
                            </label>

                            <select name="quoc_gia_id"
                                class="w-full mt-2 bg-zinc-950 border rounded-2xl px-4 py-3
                                focus:outline-none transition
                                {{ $errors->has('quoc_gia_id') ? 'border-red-500' : 'border-zinc-800' }}
                                focus:border-red-500">

                                <option value="">-- Chọn quốc gia --</option>

                                @foreach ($countries as $country)
                                    <option value="{{ $country->id }}"
                                        {{ old('quoc_gia_id', $phim->quoc_gia_id) == $country->id ? 'selected' : '' }}>
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
                                Đạo diễn <span class="text-red-500">*</span>
                            </label>

                            <input type="text" name="dao_dien" value="{{ old('dao_dien', $phim->dao_dien) }}"
                                placeholder="Christopher Nolan..."
                                class="w-full mt-2 bg-zinc-950 border rounded-2xl px-4 py-3
           focus:outline-none transition
           {{ $errors->has('dao_dien') ? 'border-red-500' : 'border-zinc-800' }}
           focus:border-red-500">

                            @error('dao_dien')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        {{-- DIỄN VIÊN --}}
                        <div>

                            <label class="text-sm text-zinc-400">
                                Diễn viên <span class="text-red-500">*</span>
                            </label>

                            <input type="text" name="dien_vien" value="{{ old('dien_vien', $phim->dien_vien) }}"
                                placeholder="Robert Downey Jr..."
                                class="w-full mt-2 bg-zinc-950 border rounded-2xl px-4 py-3
           focus:outline-none transition
           {{ $errors->has('dien_vien') ? 'border-red-500' : 'border-zinc-800' }}
           focus:border-red-500">

                            @error('dien_vien')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        {{-- NGÔN NGỮ --}}
                        <div>

                            <label class="text-sm text-zinc-400">
                                Ngôn ngữ <span class="text-red-500">*</span>
                            </label>

                            <input type="text" name="ngon_ngu" value="{{ old('ngon_ngu', $phim->ngon_ngu) }}"
                                placeholder="Tiếng Anh..."
                                class="w-full mt-2 bg-zinc-950 border rounded-2xl px-4 py-3
           focus:outline-none transition
           {{ $errors->has('ngon_ngu') ? 'border-red-500' : 'border-zinc-800' }}
           focus:border-red-500">

                            @error('ngon_ngu')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        {{-- GIỚI HẠN TUỔI --}}
                        <div>

                            <label class="text-sm text-zinc-400">
                                Giới hạn tuổi <span class="text-red-500">*</span>
                            </label>

                            <input type="text" name="gioi_han_tuoi"
                                value="{{ old('gioi_han_tuoi', $phim->gioi_han_tuoi) }}" placeholder="P, C13, C16..."
                                class="w-full mt-2 bg-zinc-950 border rounded-2xl px-4 py-3
           focus:outline-none transition
           {{ $errors->has('gioi_han_tuoi') ? 'border-red-500' : 'border-zinc-800' }}
           focus:border-red-500">

                            @error('gioi_han_tuoi')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>
                        {{-- DURATION --}}
                        <div>

                            <label class="text-sm text-zinc-400">
                                Thời lượng (phút) <span class="text-red-500">*</span>
                            </label>

                            <input type="number" name="thoi_luong" value="{{ old('thoi_luong', $phim->thoi_luong) }}"
                                placeholder="120"
                                class="w-full mt-2 bg-zinc-950 border rounded-2xl px-4 py-3
                               focus:outline-none transition
                               {{ $errors->has('thoi_luong') ? 'border-red-500' : 'border-zinc-800' }}
                               focus:border-red-500">

                            @error('thoi_luong')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        {{-- TRAILER --}}
                        <div>

                            <label class="text-sm text-zinc-400">
                                Trailer URL <span class="text-red-500">*</span>
                            </label>

                            <input type="url" id="trailer" name="trailer"
                                value="{{ old('trailer', $phim->trailer) }}" placeholder="https://youtube.com/..."
                                class="w-full mt-2 bg-zinc-950 border rounded-2xl px-4 py-3
                               focus:outline-none transition
                               {{ $errors->has('trailer') ? 'border-red-500' : 'border-zinc-800' }}
                               focus:border-red-500">

                            {{-- BACKEND ERROR --}}
                            @error('trailer')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror

                            {{-- REALTIME ERROR --}}
                            <p id="trailer-error" class="text-red-500 text-sm mt-2 hidden">
                                Trailer không hợp lệ
                            </p>

                        </div>

                    </div>

                    {{-- RIGHT SIDE --}}
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
                                   {{ $errors->has('poster') ? 'border-red-500' : 'border-zinc-700' }}">

                                    <div id="upload-placeholder" class="text-zinc-500 flex flex-col items-center">

                                        <span class="text-7xl font-light">
                                            +
                                        </span>

                                        <span class="text-sm mt-3 tracking-wide">
                                            Upload poster
                                        </span>

                                    </div>

                                    <img id="preview" src="{{ asset('storage/' . $phim->poster) }}"
                                        class="absolute inset-0 w-full h-full object-cover {{ $phim->poster ? '' : 'hidden' }}" />

                                </label>

                                <input id="poster" type="file" name="poster" accept="image/*" class="hidden"
                                    onchange="previewImage(event)">

                                @error('poster')
                                    <p class="text-red-500 text-sm mt-3">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        </div>

                        {{-- DESCRIPTION --}}
                        <div>

                            <label class="text-sm text-zinc-400">
                                Mô tả phim <span class="text-red-500">*</span>
                            </label>

                            <textarea name="mo_ta" rows="8" placeholder="Nhập mô tả phim..."
                                class="w-full mt-2 bg-zinc-900 border rounded-3xl px-5 py-4
                                  focus:outline-none transition
                                  {{ $errors->has('mo_ta') ? 'border-red-500' : 'border-zinc-800' }}
                                  focus:border-red-500 min-h-[220px]">{{ old('mo_ta', $phim->mo_ta) }}</textarea>

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

                {{-- BUTTONS --}}
                <div class="flex items-center justify-end gap-4 pt-4">

                    <a href="{{ route('admin.phims.index') }}"
                        class="px-6 py-3 rounded-2xl bg-zinc-800 hover:bg-zinc-700 transition font-medium">

                        Quay lại

                    </a>

                    <button type="submit"
                        class="px-8 py-3 rounded-2xl
                        bg-gradient-to-r from-[#6b3a1e] via-[#a66a2b] to-[#d9a441]
                        hover:from-[#7a4423] hover:to-[#e0b04a]
                        text-white font-semibold
                        shadow-lg shadow-amber-900/30
                        transition duration-200 hover:scale-[1.02]">

                        💾 Cập nhật phim

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

        function getYoutubeId(url) {
            const regExp =
                /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;

            const match = url.match(regExp);

            return (match && match[2].length === 11) ?
                match[2] :
                null;
        }

        const trailerInput = document.getElementById('trailer');

        const trailerBox = document.getElementById('trailerBox');
        
        const trailerPreview = document.getElementById('trailerPreview');


        trailerInput.addEventListener('input', function() {

            const url = this.value;

            const videoId = getYoutubeId(url);

            const error = document.getElementById('trailer-error');

            if (videoId && url.length > 0) {

                error.classList.add('hidden');

                trailerBox.classList.remove('hidden');

                trailerPreview.src = `https://www.youtube.com/embed/${videoId}`;


            } else {
                trailerPreview.src = "";

                trailerBox.classList.add('hidden');


                if (url.length > 0) {

                    error.classList.remove('hidden');

                } else {

                    error.classList.add('hidden');
                }
            }
        });
    </script>

@endsection
