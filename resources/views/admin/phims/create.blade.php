@extends('layouts.admin')

@section('page-title', 'Thêm phim')
@section('page-subtitle', 'Tạo phim mới, cập nhật poster, trailer và thông tin phân loại')

@php
    $selectedGenres = old('the_loai_id', []);
    $selectedGenres = is_array($selectedGenres) ? $selectedGenres : [];
@endphp

@section('content')
<form action="{{ route('admin.phims.store') }}" method="POST" enctype="multipart/form-data" class="movie-form-page">
    @csrf

    @include('admin.partials.flash')

    <section class="movie-form-hero">
        <div class="movie-form-copy">
            <span class="movie-kicker">
                <i class="fa-solid fa-clapperboard"></i>
                Tạo phim mới
            </span>
            <h2>Thêm phim vào kho CineHome</h2>
            <p>
                Nhập đầy đủ thông tin phim, poster và trailer để đội vận hành có thể gắn lịch chiếu,
                bán vé và hiển thị phim đẹp hơn ở trang người dùng.
            </p>
        </div>

        <div class="movie-form-hero-actions">
            <a href="{{ route('admin.phims.index') }}" class="movie-action-btn is-ghost">
                <i class="fa-solid fa-arrow-left"></i>
                Danh sách phim
            </a>
            <button type="submit" class="movie-action-btn is-primary">
                <i class="fa-solid fa-floppy-disk"></i>
                Lưu phim mới
            </button>
        </div>
    </section>

    <div class="movie-form-layout">
        <main class="movie-form-main">
            <section class="movie-form-panel">
                <div class="movie-form-panel-head">
                    <span><i class="fa-solid fa-circle-info"></i></span>
                    <div>
                        <h3>Thông tin chính</h3>
                        <p>Tên phim, đội ngũ sản xuất, thời lượng và ngôn ngữ hiển thị.</p>
                    </div>
                </div>

                <div class="movie-form-grid">
                    <label class="movie-form-field is-wide">
                        <span>Tên phim <b>*</b></span>
                        <input
                            type="text"
                            name="ten_phim"
                            value="{{ old('ten_phim') }}"
                            placeholder="Ví dụ: Ánh Sáng Thành Phố"
                            class="{{ $errors->has('ten_phim') ? 'is-invalid' : '' }}"
                            autocomplete="off"
                        >
                        @error('ten_phim')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="movie-form-field">
                        <span>Đạo diễn <b>*</b></span>
                        <input
                            type="text"
                            name="dao_dien"
                            value="{{ old('dao_dien') }}"
                            placeholder="Tên đạo diễn"
                            class="{{ $errors->has('dao_dien') ? 'is-invalid' : '' }}"
                        >
                        @error('dao_dien')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="movie-form-field">
                        <span>Ngôn ngữ <b>*</b></span>
                        <input
                            type="text"
                            name="ngon_ngu"
                            value="{{ old('ngon_ngu') }}"
                            placeholder="Tiếng Việt / Phụ đề Anh"
                            class="{{ $errors->has('ngon_ngu') ? 'is-invalid' : '' }}"
                        >
                        @error('ngon_ngu')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="movie-form-field is-wide">
                        <span>Diễn viên <b>*</b></span>
                        <input
                            type="text"
                            name="dien_vien"
                            value="{{ old('dien_vien') }}"
                            placeholder="Nhập các diễn viên chính, cách nhau bằng dấu phẩy"
                            class="{{ $errors->has('dien_vien') ? 'is-invalid' : '' }}"
                        >
                        @error('dien_vien')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="movie-form-field">
                        <span>Thời lượng <b>*</b></span>
                        <div class="movie-input-with-suffix">
                            <input
                                type="number"
                                name="thoi_luong"
                                value="{{ old('thoi_luong') }}"
                                placeholder="120"
                                min="1"
                                class="{{ $errors->has('thoi_luong') ? 'is-invalid' : '' }}"
                            >
                            <em>phút</em>
                        </div>
                        @error('thoi_luong')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="movie-form-field">
                        <span>Giới hạn tuổi <b>*</b></span>
                        <input
                            type="text"
                            name="gioi_han_tuoi"
                            value="{{ old('gioi_han_tuoi') }}"
                            placeholder="P, T13, T16, T18"
                            class="{{ $errors->has('gioi_han_tuoi') ? 'is-invalid' : '' }}"
                        >
                        @error('gioi_han_tuoi')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>
                </div>
            </section>

            <section class="movie-form-panel">
                <div class="movie-form-panel-head">
                    <span><i class="fa-solid fa-tags"></i></span>
                    <div>
                        <h3>Phân loại phim</h3>
                        <p>Chọn quốc gia và ít nhất một thể loại để khách lọc phim dễ hơn.</p>
                    </div>
                </div>

                <div class="movie-form-grid">
                    <label class="movie-form-field">
                        <span>Quốc gia <b>*</b></span>
                        <select name="quoc_gia_id" class="{{ $errors->has('quoc_gia_id') ? 'is-invalid' : '' }}">
                            <option value="">Chọn quốc gia</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country->id }}" @selected(old('quoc_gia_id') == $country->id)>
                                    {{ $country->ten_quoc_gia }}
                                </option>
                            @endforeach
                        </select>
                        @error('quoc_gia_id')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <div class="movie-form-field">
                        <span>Thể loại đã chọn</span>
                        <div class="movie-selected-count">
                            <i class="fa-solid fa-check"></i>
                            <strong id="genreCount">{{ count($selectedGenres) }}</strong>
                            <em>thể loại</em>
                        </div>
                    </div>
                </div>

                <div class="movie-genre-picker {{ $errors->has('the_loai_id') ? 'is-invalid' : '' }}">
                    @forelse($genres as $genre)
                        <label class="movie-genre-option">
                            <input
                                type="checkbox"
                                name="the_loai_id[]"
                                value="{{ $genre->id }}"
                                @checked(in_array($genre->id, $selectedGenres))
                            >
                            <span>
                                <i class="fa-solid fa-check"></i>
                                {{ $genre->ten_the_loai }}
                            </span>
                        </label>
                    @empty
                        <div class="movie-form-empty">
                            <i class="fa-solid fa-tags"></i>
                            <strong>Chưa có thể loại nào</strong>
                            <a href="{{ route('admin.the-loais.create') }}">Tạo thể loại ngay</a>
                        </div>
                    @endforelse
                </div>

                @error('the_loai_id')
                    <p class="movie-form-error">{{ $message }}</p>
                @enderror
            </section>

            <section class="movie-form-panel">
                <div class="movie-form-panel-head">
                    <span><i class="fa-solid fa-align-left"></i></span>
                    <div>
                        <h3>Nội dung phim</h3>
                        <p>Mô tả ngắn gọn, đủ hấp dẫn để dùng cho trang chi tiết phim.</p>
                    </div>
                </div>

                <label class="movie-form-field">
                    <span>Mô tả phim <b>*</b></span>
                    <textarea
                        name="mo_ta"
                        rows="8"
                        placeholder="Nhập mô tả phim, bối cảnh, điểm nhấn và lý do nên xem..."
                        class="{{ $errors->has('mo_ta') ? 'is-invalid' : '' }}"
                    >{{ old('mo_ta') }}</textarea>
                    @error('mo_ta')
                        <small>{{ $message }}</small>
                    @enderror
                </label>
            </section>
        </main>

        <aside class="movie-form-side">
            <section class="movie-form-panel movie-media-panel">
                <div class="movie-form-panel-head">
                    <span><i class="fa-solid fa-image"></i></span>
                    <div>
                        <h3>Poster phim</h3>
                        <p>JPG, PNG, WEBP. Tối đa 2MB.</p>
                    </div>
                </div>

                <label for="poster" class="movie-poster-uploader {{ $errors->has('poster') ? 'is-invalid' : '' }}">
                    <input id="poster" type="file" name="poster" accept="image/*">
                    <img id="posterPreview" alt="Xem trước poster" hidden>
                    <span id="posterPlaceholder">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <strong>Tải poster lên</strong>
                        <small>Kéo thả hoặc bấm để chọn ảnh dọc</small>
                    </span>
                </label>

                <div class="movie-file-note" id="posterFileName">
                    Chưa chọn poster
                </div>

                @error('poster')
                    <p class="movie-form-error">{{ $message }}</p>
                @enderror
            </section>

            <section class="movie-form-panel movie-media-panel">
                <div class="movie-form-panel-head">
                    <span><i class="fa-brands fa-youtube"></i></span>
                    <div>
                        <h3>Trailer</h3>
                        <p>Dán link YouTube để xem trước nhanh trước khi lưu.</p>
                    </div>
                </div>

                <label class="movie-form-field">
                    <span>Trailer URL <b>*</b></span>
                    <input
                        type="url"
                        id="trailer"
                        name="trailer"
                        value="{{ old('trailer') }}"
                        placeholder="https://www.youtube.com/watch?v=..."
                        class="{{ $errors->has('trailer') ? 'is-invalid' : '' }}"
                    >
                    @error('trailer')
                        <small>{{ $message }}</small>
                    @enderror
                    <small id="trailerHint" class="movie-live-hint">Hỗ trợ youtube.com, youtu.be và YouTube Shorts.</small>
                </label>

                <div id="trailerBox" class="movie-trailer-preview" hidden>
                    <iframe id="trailerPreview" title="Xem trước trailer" src="" allowfullscreen></iframe>
                </div>
            </section>

            <section class="movie-form-tip">
                <i class="fa-solid fa-lightbulb"></i>
                <div>
                    <strong>Gợi ý nhập liệu</strong>
                    <span>Poster đẹp, trailer hợp lệ và mô tả rõ sẽ giúp phim nổi bật hơn ở trang chủ và trang chi tiết.</span>
                </div>
            </section>
        </aside>
    </div>

    <div class="movie-form-savebar">
        <div>
            <strong>Sẵn sàng thêm phim?</strong>
            <span>Kiểm tra poster, trailer và thể loại trước khi lưu.</span>
        </div>
        <div class="movie-form-save-actions">
            <a href="{{ route('admin.phims.index') }}" class="movie-action-btn is-ghost">
                <i class="fa-solid fa-xmark"></i>
                Hủy
            </a>
            <button type="submit" class="movie-action-btn is-primary">
                <i class="fa-solid fa-floppy-disk"></i>
                Lưu phim mới
            </button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const posterInput = document.getElementById('poster');
        const posterPreview = document.getElementById('posterPreview');
        const posterPlaceholder = document.getElementById('posterPlaceholder');
        const posterFileName = document.getElementById('posterFileName');
        const genreCount = document.getElementById('genreCount');
        const trailerInput = document.getElementById('trailer');
        const trailerBox = document.getElementById('trailerBox');
        const trailerPreview = document.getElementById('trailerPreview');
        const trailerHint = document.getElementById('trailerHint');

        function updateGenreCount() {
            if (!genreCount) {
                return;
            }

            genreCount.textContent = document.querySelectorAll('input[name="the_loai_id[]"]:checked').length;
        }

        function getYoutubeEmbed(url) {
            if (!url) {
                return null;
            }

            try {
                const parsed = new URL(url);
                const host = parsed.hostname.replace(/^www\./, '');

                if (host === 'youtube.com' && parsed.searchParams.get('v')) {
                    return 'https://www.youtube.com/embed/' + parsed.searchParams.get('v');
                }

                if (host === 'youtu.be') {
                    const id = parsed.pathname.split('/').filter(Boolean)[0];
                    return id ? 'https://www.youtube.com/embed/' + id : null;
                }

                if (host === 'youtube.com' && parsed.pathname.includes('/shorts/')) {
                    const id = parsed.pathname.split('/shorts/')[1]?.split('/')[0];
                    return id ? 'https://www.youtube.com/embed/' + id : null;
                }
            } catch (error) {
                return null;
            }

            return null;
        }

        function updateTrailerPreview() {
            const embed = getYoutubeEmbed(trailerInput?.value.trim());

            if (!trailerInput || !trailerBox || !trailerPreview || !trailerHint) {
                return;
            }

            if (!trailerInput.value.trim()) {
                trailerPreview.src = '';
                trailerBox.hidden = true;
                trailerHint.textContent = 'Hỗ trợ youtube.com, youtu.be và YouTube Shorts.';
                trailerHint.classList.remove('is-error');
                return;
            }

            if (!embed) {
                trailerPreview.src = '';
                trailerBox.hidden = true;
                trailerHint.textContent = 'Link trailer chưa đúng định dạng YouTube.';
                trailerHint.classList.add('is-error');
                return;
            }

            trailerPreview.src = embed;
            trailerBox.hidden = false;
            trailerHint.textContent = 'Trailer hợp lệ, có thể xem trước bên dưới.';
            trailerHint.classList.remove('is-error');
        }

        document.querySelectorAll('input[name="the_loai_id[]"]').forEach(function(input) {
            input.addEventListener('change', updateGenreCount);
        });

        posterInput?.addEventListener('change', function(event) {
            const file = event.target.files?.[0];

            if (!file) {
                posterPreview.hidden = true;
                posterPreview.removeAttribute('src');
                posterPlaceholder.hidden = false;
                posterFileName.textContent = 'Chưa chọn poster';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(loadEvent) {
                posterPreview.src = loadEvent.target.result;
                posterPreview.hidden = false;
                posterPlaceholder.hidden = true;
                posterFileName.textContent = file.name;
            };
            reader.readAsDataURL(file);
        });

        trailerInput?.addEventListener('input', updateTrailerPreview);
        updateGenreCount();
        updateTrailerPreview();
    });
</script>
@endpush
