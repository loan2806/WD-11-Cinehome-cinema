@extends('layouts.admin')

@section('page-title', 'Thêm phim mới')
@section('page-subtitle', 'Tạo phim mới, cập nhật poster, trailer và thông tin phân loại')

@php
$countryList = $quocGias ?? $countries ?? [];
$genreList = $theLoais ?? $genres ?? [];
$selectedGenres = old('the_loai', old('the_loai_id', []));
$selectedGenres = is_array($selectedGenres) ? $selectedGenres : [];
@endphp

@push('styles')
<style>
    /* 🌟 BỎ OVERFLOW HIDDEN ĐỂ KHÔNG BỊ XÉN DROPDOWN POPUP */
    .movie-create-page,
    .movie-form-layout,
    .movie-panel,
    .movie-field {
        overflow: visible !important;
        position: relative !important;
    }

    .movie-panel {
        background: #151518;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        padding: 24px;
        margin-bottom: 24px;
        z-index: 10;
    }

    .movie-panel:has(.cine-select-wrapper.open) {
        z-index: 900 !important;
    }

    /* 🌟 CINE-SELECT DROPDOWN CARD POPUP */
    .cine-select-wrapper {
        position: relative !important;
        width: 100% !important;
        user-select: none !important;
        z-index: 20 !important;
    }

    .cine-select-wrapper.open {
        z-index: 99999 !important;
    }

    .text-danger {
        display: block;
        margin-top: 6px;
        color: #ef4444;
        font-size: 13px;
        font-weight: 500;
    }

    .is-invalid {
        border: 1px solid #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, .15);
    }

    .cine-select-trigger {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        background: #18181c !important;
        border: 1px solid rgba(255, 255, 255, 0.18) !important;
        border-radius: 12px !important;
        padding: 12px 16px !important;
        color: #f3f4f6 !important;
        font-size: 14px !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        outline: none !important;
    }

    .cine-select-trigger:hover,
    .cine-select-wrapper.open .cine-select-trigger {
        border-color: #facc15 !important;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.2) !important;
    }

    .cine-select-trigger i {
        color: #facc15 !important;
        font-size: 12px !important;
        transition: transform 0.2s ease !important;
    }

    .cine-select-wrapper.open .cine-select-trigger i {
        transform: rotate(180deg) !important;
    }

    .cine-select-menu {
        position: absolute !important;
        top: calc(100% + 6px) !important;
        left: 0 !important;
        right: 0 !important;
        min-width: 100% !important;
        background: #18181c !important;
        border: 1px solid rgba(250, 204, 21, 0.35) !important;
        border-radius: 16px !important;
        padding: 8px !important;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.95), 0 0 0 1px rgba(255, 255, 255, 0.05) !important;
        z-index: 999999 !important;
        max-height: 260px !important;
        overflow-y: auto !important;
        display: none !important;
    }

    .cine-select-wrapper.open .cine-select-menu {
        display: block !important;
    }

    .cine-select-option {
        padding: 10px 12px !important;
        border-radius: 10px !important;
        color: #d1d5db !important;
        font-size: 13.5px !important;
        font-weight: 500 !important;
        cursor: pointer !important;
        transition: all 0.15s ease !important;
        margin-bottom: 2px !important;
    }

    .cine-select-option:hover {
        background: rgba(250, 204, 21, 0.15) !important;
        color: #facc15 !important;
    }

    .cine-select-option.selected {
        background: rgba(250, 204, 21, 0.25) !important;
        color: #facc15 !important;
        font-weight: 700 !important;
    }

    /* INPUT FIELD BASE */
    .form-input-cine {
        width: 100%;
        background: #18181c;
        border: 1px solid rgba(255, 255, 255, 0.18);
        color: #fff;
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 14px;
        outline: none;
        transition: all 0.2s ease;
    }

    .form-input-cine:focus {
        border-color: #facc15;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.2);
    }
</style>
@endpush

@section('content')
<div class="movie-create-page" style="padding-top: 10px;">
    @include('admin.partials.flash')

    <!-- HERO HEADER -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <span class="showtime-kicker"><i class="fa-solid fa-film"></i> Quản lý kho phim</span>
            <h2 style="font-size: 28px; font-weight: 900; color: #fff; margin-top: 4px;">Thêm phim mới</h2>
            <p style="color: #9ca3af; font-size: 14px;">Tạo phim mới, tải poster và điền đầy đủ các thông tin chi tiết.
            </p>
        </div>

        <div style="display: flex; gap: 12px;">
            <a href="{{ route('admin.phims.index') }}" class="movie-action-btn is-ghost">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
            </a>
            <button type="submit" form="createMovieForm" class="movie-action-btn is-primary">
                <i class="fa-solid fa-floppy-disk"></i> Lưu thông tin phim
            </button>
        </div>
    </div>

    <form id="createMovieForm" action="{{ route('admin.phims.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
            <!-- CỘT BÊN TRÁI: THÔNG TIN PHIM -->
            <div>
                <!-- PANEL 1: THÔNG TIN CƠ BẢN -->
                <div class="movie-panel">
                    <h3
                        style="color: #fff; font-size: 18px; font-weight: 800; margin-bottom: 16px; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-circle-info" style="color: #facc15;"></i>
                        1. Thông tin cơ bản
                    </h3>

                    <div style="display:flex;flex-direction:column;gap:16px;">

                        {{-- Tên phim --}}
                        <div>
                            <label class="form-label">
                                Tên phim <span style="color:#ef4444">*</span>
                            </label>

                            <input
                                type="text"
                                name="ten_phim"
                                value="{{ old('ten_phim') }}"
                                placeholder="Ví dụ: Ánh Sáng Thành Phố"
                                class="form-input-cine @error('ten_phim') is-invalid @enderror">

                            @error('ten_phim')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

                            {{-- Đạo diễn --}}
                            <div>
                                <label class="form-label">Đạo diễn <span style="color:#ef4444">*</span></label>

                                <input
                                    type="text"
                                    name="dao_dien"
                                    value="{{ old('dao_dien') }}"
                                    placeholder="Tên đạo diễn"
                                    class="form-input-cine @error('dao_dien') is-invalid @enderror">

                                @error('dao_dien')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Diễn viên --}}
                            <div>
                                <label class="form-label">Diễn viên <span style="color:#ef4444">*</span></label>

                                <input
                                    type="text"
                                    name="dien_vien"
                                    value="{{ old('dien_vien') }}"
                                    placeholder="Các diễn viên chính..."
                                    class="form-input-cine @error('dien_vien') is-invalid @enderror">

                                @error('dien_vien')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">

                            {{-- Thời lượng --}}
                            <div>
                                <label class="form-label">
                                    Thời lượng (phút)
                                    <span style="color:#ef4444">*</span>
                                </label>

                                <input
                                    type="number"
                                    name="thoi_luong"
                                    value="{{ old('thoi_luong') }}"
                                    placeholder="120"
                                    class="form-input-cine @error('thoi_luong') is-invalid @enderror">

                                @error('thoi_luong')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Giới hạn tuổi --}}
                            <div>
                                <label class="form-label">
                                    Giới hạn tuổi
                                    <span style="color:#ef4444">*</span>
                                </label>

                                <div class="cine-select-wrapper @error('gioi_han_tuoi') is-invalid @enderror">

                                    <input
                                        type="hidden"
                                        name="gioi_han_tuoi"
                                        value="{{ old('gioi_han_tuoi','P') }}">

                                    <div class="cine-select-trigger" tabindex="0">
                                        <span class="cine-select-value">
                                            {{ old('gioi_han_tuoi','P') }}
                                        </span>
                                        <i class="fa-solid fa-chevron-down"></i>
                                    </div>

                                    <div class="cine-select-menu">

                                        <div class="cine-select-option"
                                            data-value="P">
                                            P - Mọi lứa tuổi
                                        </div>

                                        <div class="cine-select-option"
                                            data-value="K">
                                            K - Dưới 13 tuổi có phụ huynh
                                        </div>

                                        <div class="cine-select-option"
                                            data-value="T13">
                                            T13 - Từ 13 tuổi trở lên
                                        </div>

                                        <div class="cine-select-option"
                                            data-value="T16">
                                            T16 - Từ 16 tuổi trở lên
                                        </div>

                                        <div class="cine-select-option"
                                            data-value="T18">
                                            T18 - Từ 18 tuổi trở lên
                                        </div>

                                    </div>
                                </div>

                                @error('gioi_han_tuoi')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror

                            </div>

                            {{-- Ngôn ngữ --}}
                            <div>

                                <label class="form-label">
                                    Ngôn ngữ
                                    <span style="color:#ef4444">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="ngon_ngu"
                                    value="{{ old('ngon_ngu') }}"
                                    placeholder="Tiếng Việt..."
                                    class="form-input-cine @error('ngon_ngu') is-invalid @enderror">

                                @error('ngon_ngu')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror

                            </div>

                        </div>

                    </div>
                </div>

                <!-- PANEL 2: PHÂN LOẠI PHIM -->
                <!-- PANEL 2: PHÂN LOẠI PHIM -->
                <div class="movie-panel">
                    <h3
                        style="color:#fff;font-size:18px;font-weight:800;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
                        <i class="fa-solid fa-tags" style="color:#facc15;"></i>
                        2. Phân loại phim
                    </h3>

                    <div style="display:flex;flex-direction:column;gap:16px;">

                        {{-- Quốc gia --}}
                        <div>

                            <label class="form-label">
                                Quốc gia <span style="color:#ef4444">*</span>
                            </label>

                            <div class="cine-select-wrapper">

                                <input
                                    type="hidden"
                                    name="quoc_gia_id"
                                    value="{{ old('quoc_gia_id') }}">

                                <div class="cine-select-trigger @error('quoc_gia_id') is-invalid @enderror"
                                    tabindex="0">

                                    <span class="cine-select-value">

                                        @php
                                        $selectedCountry = collect($countryList)->first(function ($item) {
                                        return (is_object($item) ? $item->id : $item) == old('quoc_gia_id');
                                        });
                                        @endphp

                                        {{ old('quoc_gia_id')
                            ? (is_object($selectedCountry)
                                ? $selectedCountry->ten_quoc_gia
                                : old('quoc_gia_id'))
                            : 'Chọn quốc gia' }}

                                    </span>

                                    <i class="fa-solid fa-chevron-down"></i>

                                </div>

                                <div class="cine-select-menu">

                                    @foreach ($countryList as $qg)

                                    @php
                                    $id = is_object($qg) ? $qg->id : $qg;
                                    $name = is_object($qg) ? $qg->ten_quoc_gia : $qg;
                                    @endphp

                                    <div
                                        class="cine-select-option {{ old('quoc_gia_id') == $id ? 'selected' : '' }}"
                                        data-value="{{ $id }}">

                                        {{ $name }}

                                    </div>

                                    @endforeach

                                </div>

                            </div>

                            @error('quoc_gia_id')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror

                        </div>

                        {{-- Thể loại --}}
                        <div>

                            <label class="form-label">
                                Thể loại phim
                                <span style="color:#ef4444">*</span>
                            </label>

                            <div
                                class="@error('the_loai_id') is-invalid @enderror"
                                style="
                    display:flex;
                    flex-wrap:wrap;
                    gap:10px;
                    background:#18181c;
                    padding:14px;
                    border-radius:12px;
                    border:1px solid rgba(255,255,255,.15);
                ">

                                @foreach($genreList as $tl)

                                @php
                                $id = is_object($tl) ? $tl->id : $tl;
                                $name = is_object($tl) ? $tl->ten_the_loai : $tl;
                                @endphp

                                <label
                                    style="
                            display:inline-flex;
                            align-items:center;
                            gap:8px;
                            background:rgba(255,255,255,.06);
                            padding:6px 12px;
                            border-radius:8px;
                            color:#fff;
                            font-size:13px;
                            cursor:pointer;
                        ">

                                    <input
                                        type="checkbox"
                                        name="the_loai_id[]"
                                        value="{{ $id }}"
                                        {{ in_array($id, old('the_loai_id', [])) ? 'checked' : '' }}
                                        style="accent-color:#facc15;">

                                    <span>{{ $name }}</span>

                                </label>

                                @endforeach

                            </div>

                            @error('the_loai_id')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror

                        </div>

                    </div>
                </div>

                <!-- PANEL 3: NỘI DUNG MÔ TẢ -->
                <div class="movie-panel">
                    <h3
                        style="color: #fff; font-size: 18px; font-weight: 800; margin-bottom: 16px; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-align-left" style="color: #facc15;"></i> 3. Mô tả tóm tắt
                    </h3>
                    <textarea name="mo_ta" rows="4" placeholder="Nhập tóm tắt nội dung phim..." class="form-input-cine"
                        style="resize: vertical;">{{ old('mo_ta') }}</textarea>
                    @error('mo_ta')
                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <!-- CỘT BÊN PHẢI: POSTER & TRAILER -->
            <div>
                <!-- POSTER UPLOAD -->
                <div class="movie-panel">
                    <h3
                        style="color: #fff; font-size: 18px; font-weight: 800; margin-bottom: 16px; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-image" style="color: #facc15;"></i> Tải lên Poster
                    </h3>

                    <div
                        style="text-align: center; border: 2px dashed rgba(255,255,255,0.2); padding: 20px; border-radius: 16px; background: #18181c;">
                        <img id="posterPreview" src="{{ asset('assets/images/LOGO copy.png') }}"
                            style="max-height: 220px; border-radius: 12px; margin-bottom: 12px; object-fit: cover;">
                        <input type="file" name="poster" id="posterInput" accept="image/*" style="display: none;">
                        <button type="button" onclick="document.getElementById('posterInput').click()"
                            class="movie-action-btn is-ghost" style="width: 100%; justify-content: center;">
                            <i class="fa-solid fa-upload"></i> Chọn file ảnh
                        </button>
                        @error('poster')
                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <!-- TRAILER YOUTUBE VỚI PREVIEW DEMO VIDEO LIVE -->
                <div class="movie-panel">
                    <h3
                        style="color: #fff; font-size: 18px; font-weight: 800; margin-bottom: 16px; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-brands fa-youtube" style="color: #ef4444;"></i> Trailer YouTube
                    </h3>

                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div>
                            <label
                                style="display: block; color: #d1d5db; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                                Trailer URL <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="url" name="trailer" id="trailerInput"
                                value="{{ old('trailer', old('trailer_url')) }}"
                                placeholder="https://www.youtube.com/watch?v=..." class="form-input-cine"
                                autocomplete="off">
                            @error('trailer')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                            <small id="trailerHint"
                                style="display: block; margin-top: 6px; font-size: 12px; color: #9ca3af; transition: color 0.2s;">
                                Hỗ trợ youtube.com, youtu.be và YouTube Shorts.
                            </small>
                        </div>

                        <!-- KHUNG KHUNG PLAYER IFRAME DEMO VIDEO -->
                        <div id="trailerBox"
                            style="display: none; width: 100%; border-radius: 12px; overflow: hidden; border: 1px solid rgba(250, 204, 21, 0.35); background: #000; aspect-ratio: 16/9; position: relative; margin-top: 4px;">
                            <iframe id="trailerPreview" style="width: 100%; height: 100%; border: none;"
                                title="Xem trước trailer phim" allowfullscreen
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 🌟 KÍCH HOẠT TẤT CẢ DROPDOWN CINE-SELECT (QUỐC GIA, GIỚI HẠN TUỔI)
        document.querySelectorAll('.cine-select-wrapper').forEach(function(wrapper) {
            const hiddenInput = wrapper.querySelector('input[type="hidden"]');
            const trigger = wrapper.querySelector('.cine-select-trigger');
            const triggerText = wrapper.querySelector('.cine-select-value');
            const options = wrapper.querySelectorAll('.cine-select-option');

            options.forEach(function(opt) {
                if (opt.classList.contains('selected') && opt.dataset.value !== "") {
                    triggerText.textContent = opt.textContent.trim();
                }
            });

            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                e.preventDefault();

                document.querySelectorAll('.cine-select-wrapper').forEach(function(w) {
                    if (w !== wrapper) {
                        w.classList.remove('open');
                    }
                });

                wrapper.classList.toggle('open');
            });

            options.forEach(function(opt) {
                opt.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const val = opt.dataset.value;
                    hiddenInput.value = val;
                    triggerText.textContent = opt.textContent.trim();

                    options.forEach(o => o.classList.remove('selected'));
                    opt.classList.add('selected');

                    wrapper.classList.remove('open');
                });
            });
        });

        document.addEventListener('click', function() {
            document.querySelectorAll('.cine-select-wrapper').forEach(function(w) {
                w.classList.remove('open');
            });
        });

        // 🌟 PREVIEW ANH POSTER KHI CHỌN FILE
        const posterInput = document.getElementById('posterInput');
        const posterPreview = document.getElementById('posterPreview');

        if (posterInput && posterPreview) {
            posterInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        posterPreview.src = evt.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });
        }

        // 🌟 LOGIC NHẬN DẠNG & PHÁT DEMO VIDEO YOUTUBE (CÓ XỬ LÝ SHORTS & YOUTU.BE)
        const trailerInput = document.getElementById('trailerInput');
        const trailerBox = document.getElementById('trailerBox');
        const trailerPreview = document.getElementById('trailerPreview');
        const trailerHint = document.getElementById('trailerHint');

        function getYoutubeEmbed(url) {
            if (!url) return null;
            try {
                const parsed = new URL(url.trim());
                const host = parsed.hostname.replace(/^www\./, '').replace(/^m\./, '');

                // 1. Dạng youtube.com/watch?v=VIDEO_ID
                if (host === 'youtube.com' && parsed.searchParams.get('v')) {
                    return 'https://www.youtube.com/embed/' + parsed.searchParams.get('v');
                }

                // 2. Dạng link ngắn youtu.be/VIDEO_ID
                if (host === 'youtu.be') {
                    const id = parsed.pathname.split('/').filter(Boolean)[0];
                    return id ? 'https://www.youtube.com/embed/' + id : null;
                }

                // 3. Dạng YouTube Shorts youtube.com/shorts/VIDEO_ID
                if (host === 'youtube.com' && parsed.pathname.includes('/shorts/')) {
                    const id = parsed.pathname.split('/shorts/')[1]?.split('/')[0];
                    return id ? 'https://www.youtube.com/embed/' + id : null;
                }

                // 4. Dạng đã là embed youtube.com/embed/VIDEO_ID
                if (host === 'youtube.com' && parsed.pathname.includes('/embed/')) {
                    const id = parsed.pathname.split('/embed/')[1]?.split('/')[0];
                    return id ? 'https://www.youtube.com/embed/' + id : null;
                }
            } catch (error) {
                return null;
            }
            return null;
        }

        function updateTrailerPreview() {
            if (!trailerInput || !trailerBox || !trailerPreview || !trailerHint) return;

            const url = trailerInput.value.trim();

            if (!url) {
                trailerPreview.src = '';
                trailerBox.style.display = 'none';
                trailerHint.textContent = 'Hỗ trợ youtube.com, youtu.be và YouTube Shorts.';
                trailerHint.style.color = '#9ca3af';
                return;
            }

            const embedUrl = getYoutubeEmbed(url);

            if (embedUrl) {
                if (trailerPreview.src !== embedUrl) {
                    trailerPreview.src = embedUrl;
                }
                trailerBox.style.display = 'block';
                trailerHint.textContent = '✓ Link hợp lệ, có thể xem trực tiếp video bên dưới.';
                trailerHint.style.color = '#10b981';
            } else {
                trailerPreview.src = '';
                trailerBox.style.display = 'none';
                trailerHint.textContent = '✕ Link chưa đúng định dạng video YouTube.';
                trailerHint.style.color = '#ef4444';
            }
        }

        if (trailerInput) {
            trailerInput.addEventListener('input', updateTrailerPreview);
            trailerInput.addEventListener('paste', function() {
                setTimeout(updateTrailerPreview, 100);
            });
            updateTrailerPreview();
        }
    });
</script>
@endpush