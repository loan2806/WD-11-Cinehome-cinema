@extends('layouts.admin')

@section('page-title', 'Sửa quốc gia')

@section('content')
    @php
        $movieCount = $quocGia->phims_count ?? 0;
        $statusValue = (string) old('trang_thai', $quocGia->trang_thai);
        $previewName = old('ten_quoc_gia', $quocGia->ten_quoc_gia);
        $previewCode = old('ma_quoc_gia', $quocGia->ma_quoc_gia);
        $previewInitials = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($previewCode ?: $previewName, 0, 2));
    @endphp

    <form action="{{ route('admin.quoc-gias.update', $quocGia) }}" method="POST" class="country-form-page" novalidate>
        @csrf
        @method('PUT')

        @include('admin.partials.flash')

        <section class="country-form-hero">
            <div class="country-form-hero-copy">
                <span class="country-kicker">
                    <i class="fa-solid fa-earth-asia"></i>
                    Cập nhật quốc gia phim
                </span>
                <h1>Sửa quốc gia</h1>
                <p>
                    Cập nhật tên, mã quốc gia và trạng thái sử dụng cho
                    <strong>{{ $quocGia->ten_quoc_gia }}</strong>. Các phim đã liên kết vẫn được giữ nguyên.
                </p>

                <div class="country-form-meta">
                    <span><i class="fa-solid fa-hashtag"></i> ID {{ $quocGia->id }}</span>
                    <span><i class="fa-solid fa-film"></i> {{ number_format($movieCount) }} phim liên kết</span>
                    <span><i class="fa-solid fa-code"></i> {{ \Illuminate\Support\Str::upper($quocGia->ma_quoc_gia ?: '--') }}</span>
                </div>
            </div>

            <div class="country-form-hero-actions">
                <a href="{{ route('admin.quoc-gias.index') }}" class="movie-action-btn is-ghost">
                    <i class="fa-solid fa-arrow-left"></i>
                    Quay lại
                </a>
                <button type="submit" class="movie-action-btn is-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Lưu thay đổi
                </button>
            </div>
        </section>

        <div class="country-form-layout">
            <main class="country-form-main">
                <section class="country-form-panel">
                    <div class="country-form-panel-head">
                        <span><i class="fa-solid fa-globe"></i></span>
                        <div>
                            <h2>Thông tin quốc gia</h2>
                            <p>Đặt tên và mã ngắn gọn để quản trị viên lọc phim nhanh hơn.</p>
                        </div>
                    </div>

                    <div class="country-form-grid">
                        <label class="country-form-field">
                            <span>Tên quốc gia <b>*</b></span>
                            <input
                                type="text"
                                name="ten_quoc_gia"
                                id="ten_quoc_gia"
                                value="{{ old('ten_quoc_gia', $quocGia->ten_quoc_gia) }}"
                                placeholder="Ví dụ: Việt Nam, United States, Japan..."
                                maxlength="255"
                                required
                                class="{{ $errors->has('ten_quoc_gia') ? 'is-invalid' : '' }}"
                            >
                            @error('ten_quoc_gia')
                                <small class="country-form-error">{{ $message }}</small>
                            @else
                                <small>Tên quốc gia sẽ xuất hiện trong bộ lọc phim và trang quản trị.</small>
                            @enderror
                        </label>

                        <label class="country-form-field">
                            <span>Mã quốc gia <b>*</b></span>
                            <input
                                type="text"
                                name="ma_quoc_gia"
                                id="ma_quoc_gia"
                                value="{{ old('ma_quoc_gia', $quocGia->ma_quoc_gia) }}"
                                placeholder="Ví dụ: VN, US, JP..."
                                maxlength="10"
                                required
                                class="{{ $errors->has('ma_quoc_gia') ? 'is-invalid' : '' }}"
                            >
                            @error('ma_quoc_gia')
                                <small class="country-form-error">{{ $message }}</small>
                            @else
                                <small>Dùng mã ngắn, dễ nhận diện khi hiển thị trong bảng dữ liệu.</small>
                            @enderror
                        </label>
                    </div>
                </section>

                <section class="country-form-panel">
                    <div class="country-form-panel-head">
                        <span><i class="fa-solid fa-toggle-on"></i></span>
                        <div>
                            <h2>Trạng thái sử dụng</h2>
                            <p>Quốc gia đang dùng sẽ xuất hiện trong các luồng chọn và lọc phim.</p>
                        </div>
                    </div>

                    <div class="country-status-choice-grid {{ $errors->has('trang_thai') ? 'is-invalid' : '' }}">
                        <label class="country-status-choice">
                            <input type="radio" name="trang_thai" value="1" @checked($statusValue === '1')>
                            <span class="country-status-choice-card is-active">
                                <i class="fa-solid fa-circle-check"></i>
                                <strong>Đang sử dụng</strong>
                                <small>Cho phép chọn quốc gia này khi thêm hoặc sửa phim.</small>
                            </span>
                        </label>

                        <label class="country-status-choice">
                            <input type="radio" name="trang_thai" value="0" @checked($statusValue === '0')>
                            <span class="country-status-choice-card is-inactive">
                                <i class="fa-solid fa-circle-pause"></i>
                                <strong>Tạm ẩn</strong>
                                <small>Giữ dữ liệu cũ nhưng hạn chế dùng cho phim mới.</small>
                            </span>
                        </label>
                    </div>

                    @error('trang_thai')
                        <p class="country-form-error">{{ $message }}</p>
                    @enderror
                </section>
            </main>

            <aside class="country-form-side">
                <section class="country-preview-card">
                    <span class="country-preview-avatar">{{ $previewInitials ?: '--' }}</span>
                    <small>Xem nhanh</small>
                    <h2>{{ $previewName ?: 'Tên quốc gia' }}</h2>
                    <p>Mã quốc gia: <strong>{{ \Illuminate\Support\Str::upper($previewCode ?: '--') }}</strong></p>

                    <div class="country-preview-badges">
                        @if ($statusValue === '1')
                            <span class="country-status is-active">
                                <i class="fa-solid fa-circle-check"></i>
                                Đang dùng
                            </span>
                        @else
                            <span class="country-status is-inactive">
                                <i class="fa-solid fa-circle-pause"></i>
                                Tạm ẩn
                            </span>
                        @endif

                        <span class="country-movie-count {{ $movieCount > 0 ? 'has-movies' : '' }}">
                            <i class="fa-solid fa-film"></i>
                            {{ number_format($movieCount) }} phim
                        </span>
                    </div>
                </section>

                <section class="country-help-card">
                    <div class="country-help-head">
                        <i class="fa-solid fa-shield-halved"></i>
                        <div>
                            <strong>Lưu ý khi chỉnh sửa</strong>
                            <span>Thông tin quốc gia ảnh hưởng đến bộ lọc và dữ liệu phim liên quan.</span>
                        </div>
                    </div>

                    <ul class="country-help-list">
                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            Tên và mã quốc gia không được trùng với bản ghi khác.
                        </li>
                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            Quốc gia có phim liên kết vẫn có thể đổi trạng thái.
                        </li>
                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            Nên dùng mã ngắn như VN, US, JP để bảng dễ quét.
                        </li>
                    </ul>
                </section>
            </aside>
        </div>

        <div class="country-form-savebar">
            <div>
                <strong>{{ $quocGia->ten_quoc_gia }}</strong>
                <span>Kiểm tra tên, mã và trạng thái trước khi lưu.</span>
            </div>

            <div class="country-form-save-actions">
                <a href="{{ route('admin.quoc-gias.index') }}" class="movie-action-btn is-ghost">
                    Hủy
                </a>
                <button type="submit" class="movie-action-btn is-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Cập nhật quốc gia
                </button>
            </div>
        </div>
    </form>
@endsection
