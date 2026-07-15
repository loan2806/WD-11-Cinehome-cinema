@extends('layouts.admin')

@section('page-title', 'Chỉnh sửa thể loại phim')

@section('content')
    @php
        $movieCount = $theLoai->phims_count ?? 0;
        $statusValue = (string) old('trang_thai', $theLoai->trang_thai);
        $previewName = old('ten_the_loai', $theLoai->ten_the_loai);
        $previewDescription = old('mo_ta', $theLoai->mo_ta);
    @endphp

    <form action="{{ route('admin.the-loais.update', $theLoai) }}" method="POST" class="genre-form-page" novalidate>
        @csrf
        @method('PUT')

        @include('admin.partials.flash')

        <section class="genre-form-hero">
            <div class="genre-form-hero-copy">
                <span class="genre-kicker">
                    <i class="fa-solid fa-pen-to-square"></i>
                    Cập nhật danh mục phim
                </span>
                <h1>Chỉnh sửa thể loại phim</h1>
                <p>
                    Điều chỉnh tên, mô tả và trạng thái hiển thị cho thể loại
                    <strong>{{ $theLoai->ten_the_loai }}</strong>. Các phim đang liên kết vẫn được giữ nguyên.
                </p>

                <div class="genre-form-meta">
                    <span><i class="fa-solid fa-hashtag"></i> ID {{ $theLoai->id }}</span>
                    <span><i class="fa-solid fa-film"></i> {{ number_format($movieCount) }} phim liên kết</span>
                    <span><i class="fa-solid fa-link"></i> {{ $theLoai->slug ?: 'chua-co-slug' }}</span>
                </div>
            </div>

            <div class="genre-form-hero-actions">
                <a href="{{ route('admin.the-loais.index') }}" class="movie-action-btn is-ghost">
                    <i class="fa-solid fa-arrow-left"></i>
                    Quay lại
                </a>
                <button type="submit" class="movie-action-btn is-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Lưu thay đổi
                </button>
            </div>
        </section>

        <div class="genre-form-layout">
            <main class="genre-form-main">
                <section class="genre-form-panel">
                    <div class="genre-form-panel-head">
                        <span><i class="fa-solid fa-layer-group"></i></span>
                        <div>
                            <h2>Thông tin thể loại</h2>
                            <p>Đặt tên ngắn gọn, dễ hiểu để người quản trị lọc phim nhanh hơn.</p>
                        </div>
                    </div>

                    <div class="genre-form-grid">
                        <label class="genre-form-field is-wide">
                            <span>Tên thể loại <b>*</b></span>
                            <input
                                type="text"
                                name="ten_the_loai"
                                id="ten_the_loai"
                                value="{{ old('ten_the_loai', $theLoai->ten_the_loai) }}"
                                placeholder="Ví dụ: Hành động, Kinh dị, Tình cảm..."
                                maxlength="255"
                                required
                                class="{{ $errors->has('ten_the_loai') ? 'is-invalid' : '' }}"
                            >
                            @error('ten_the_loai')
                                <small class="genre-form-error">{{ $message }}</small>
                            @else
                                <small>Tên thể loại sẽ được dùng trong bộ lọc phim và trang người dùng.</small>
                            @enderror
                        </label>

                        <label class="genre-form-field is-wide">
                            <span>Mô tả</span>
                            <textarea
                                name="mo_ta"
                                id="mo_ta"
                                rows="6"
                                maxlength="500"
                                placeholder="Nhập mô tả ngắn về nhóm phim này..."
                                class="{{ $errors->has('mo_ta') ? 'is-invalid' : '' }}"
                            >{{ old('mo_ta', $theLoai->mo_ta) }}</textarea>
                            @error('mo_ta')
                                <small class="genre-form-error">{{ $message }}</small>
                            @else
                                <small>Tối đa 500 ký tự. Nên viết 1-2 câu để dễ đọc khi quản trị nội dung.</small>
                            @enderror
                        </label>
                    </div>
                </section>

                <section class="genre-form-panel">
                    <div class="genre-form-panel-head">
                        <span><i class="fa-solid fa-toggle-on"></i></span>
                        <div>
                            <h2>Trạng thái hiển thị</h2>
                            <p>Chọn thể loại đang hoạt động hoặc tạm ẩn khỏi các luồng quản lý.</p>
                        </div>
                    </div>

                    <div class="genre-status-choice-grid {{ $errors->has('trang_thai') ? 'is-invalid' : '' }}">
                        <label class="genre-status-choice">
                            <input type="radio" name="trang_thai" value="1" @checked($statusValue === '1') required>
                            <span class="genre-status-choice-card is-active">
                                <i class="fa-solid fa-circle-check"></i>
                                <strong>Đang kích hoạt</strong>
                                <small>Cho phép dùng thể loại này khi quản lý và hiển thị phim.</small>
                            </span>
                        </label>

                        <label class="genre-status-choice">
                            <input type="radio" name="trang_thai" value="0" @checked($statusValue === '0') required>
                            <span class="genre-status-choice-card is-inactive">
                                <i class="fa-solid fa-circle-pause"></i>
                                <strong>Tạm ẩn</strong>
                                <small>Giữ dữ liệu nhưng hạn chế dùng cho các nội dung mới.</small>
                            </span>
                        </label>
                    </div>

                    @error('trang_thai')
                        <p class="genre-form-error">{{ $message }}</p>
                    @enderror
                </section>
            </main>

            <aside class="genre-form-side">
                <section class="genre-preview-card">
                    <span class="genre-preview-icon">
                        <i class="fa-solid fa-ticket"></i>
                    </span>
                    <small>Xem nhanh</small>
                    <h2>{{ $previewName ?: 'Tên thể loại' }}</h2>
                    <p>{{ $previewDescription ?: 'Chưa có mô tả cho thể loại này.' }}</p>

                    <div class="genre-preview-badges">
                        @if ($statusValue === '1')
                            <span class="genre-status is-active">
                                <i class="fa-solid fa-circle-check"></i>
                                Đang bật
                            </span>
                        @else
                            <span class="genre-status is-inactive">
                                <i class="fa-solid fa-circle-pause"></i>
                                Tạm ẩn
                            </span>
                        @endif

                        <span class="genre-movie-count {{ $movieCount > 0 ? 'has-movies' : '' }}">
                            <i class="fa-solid fa-film"></i>
                            {{ number_format($movieCount) }} phim
                        </span>
                    </div>
                </section>

                <section class="genre-help-card">
                    <div class="genre-help-head">
                        <i class="fa-solid fa-shield-halved"></i>
                        <div>
                            <strong>Lưu ý khi chỉnh sửa</strong>
                            <span>Các thay đổi sẽ áp dụng cho toàn bộ phim đang gắn với thể loại này.</span>
                        </div>
                    </div>

                    <ul class="genre-help-list">
                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            Tên thể loại không được trùng với thể loại khác.
                        </li>
                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            Thể loại có phim liên kết vẫn có thể đổi trạng thái.
                        </li>
                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            Nên giữ mô tả ngắn để nhân sự vận hành dễ quét thông tin.
                        </li>
                    </ul>
                </section>
            </aside>
        </div>

        <div class="genre-form-savebar">
            <div>
                <strong>{{ $theLoai->ten_the_loai }}</strong>
                <span>Kiểm tra thông tin trước khi lưu thay đổi.</span>
            </div>

            <div class="genre-form-save-actions">
                <a href="{{ route('admin.the-loais.index') }}" class="movie-action-btn is-ghost">
                    Hủy
                </a>
                <button type="submit" class="movie-action-btn is-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Cập nhật thể loại
                </button>
            </div>
        </div>
    </form>
@endsection
