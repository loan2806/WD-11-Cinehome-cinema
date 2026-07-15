@extends('layouts.admin')

@section('page-title', 'Thêm thể loại phim mới')

@section('content')
    @php
        $statusValue = (string) old('trang_thai', '1');
        $previewName = old('ten_the_loai');
        $previewDescription = old('mo_ta');
    @endphp

    <form action="{{ route('admin.the-loais.store') }}" method="POST" class="genre-form-page" novalidate>
        @csrf

        @include('admin.partials.flash')

        <section class="genre-form-hero">
            <div class="genre-form-hero-copy">
                <span class="genre-kicker">
                    <i class="fa-solid fa-layer-group"></i>
                    Tạo danh mục phim
                </span>
                <h1>Thêm thể loại phim mới</h1>
                <p>
                    Tạo nhóm nội dung mới để quản trị viên gắn phim, lọc dữ liệu và giúp người dùng tìm đúng gu phim nhanh hơn.
                    Nên đặt tên ngắn, rõ nghĩa và dễ nhận diện.
                </p>

                <div class="genre-form-meta">
                    <span><i class="fa-solid fa-wand-magic-sparkles"></i> Tạo mới</span>
                    <span><i class="fa-solid fa-toggle-on"></i> Mặc định đang bật</span>
                    <span><i class="fa-solid fa-pen"></i> Mô tả tối đa 500 ký tự</span>
                </div>
            </div>

            <div class="genre-form-hero-actions">
                <a href="{{ route('admin.the-loais.index') }}" class="movie-action-btn is-ghost">
                    <i class="fa-solid fa-arrow-left"></i>
                    Quay lại
                </a>
                <button type="submit" class="movie-action-btn is-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Lưu thể loại
                </button>
            </div>
        </section>

        <div class="genre-form-layout">
            <main class="genre-form-main">
                <section class="genre-form-panel">
                    <div class="genre-form-panel-head">
                        <span><i class="fa-solid fa-ticket"></i></span>
                        <div>
                            <h2>Thông tin thể loại</h2>
                            <p>Điền tên và mô tả ngắn để nhóm phim hiển thị rõ ràng trong hệ thống.</p>
                        </div>
                    </div>

                    <div class="genre-form-grid">
                        <label class="genre-form-field is-wide">
                            <span>Tên thể loại <b>*</b></span>
                            <input
                                type="text"
                                name="ten_the_loai"
                                id="ten_the_loai"
                                value="{{ old('ten_the_loai') }}"
                                placeholder="Ví dụ: Hành động, Kinh dị, Tình cảm..."
                                maxlength="255"
                                required
                                class="{{ $errors->has('ten_the_loai') ? 'is-invalid' : '' }}"
                            >
                            @error('ten_the_loai')
                                <small class="genre-form-error">{{ $message }}</small>
                            @else
                                <small>Tên thể loại không nên quá dài để bảng quản trị và bộ lọc dễ đọc.</small>
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
                            >{{ old('mo_ta') }}</textarea>
                            @error('mo_ta')
                                <small class="genre-form-error">{{ $message }}</small>
                            @else
                                <small>Tối đa 500 ký tự. Phần này giúp đội vận hành hiểu nhanh phạm vi thể loại.</small>
                            @enderror
                        </label>
                    </div>
                </section>

                <section class="genre-form-panel">
                    <div class="genre-form-panel-head">
                        <span><i class="fa-solid fa-toggle-on"></i></span>
                        <div>
                            <h2>Trạng thái hiển thị</h2>
                            <p>Chọn trạng thái ban đầu cho thể loại khi thêm vào hệ thống.</p>
                        </div>
                    </div>

                    <div class="genre-status-choice-grid {{ $errors->has('trang_thai') ? 'is-invalid' : '' }}">
                        <label class="genre-status-choice">
                            <input type="radio" name="trang_thai" value="1" @checked($statusValue === '1')>
                            <span class="genre-status-choice-card is-active">
                                <i class="fa-solid fa-circle-check"></i>
                                <strong>Đang kích hoạt</strong>
                                <small>Có thể dùng ngay khi thêm hoặc chỉnh sửa phim.</small>
                            </span>
                        </label>

                        <label class="genre-status-choice">
                            <input type="radio" name="trang_thai" value="0" @checked($statusValue === '0')>
                            <span class="genre-status-choice-card is-inactive">
                                <i class="fa-solid fa-circle-pause"></i>
                                <strong>Tạm ẩn</strong>
                                <small>Lưu dữ liệu trước, bật lại khi thể loại đã sẵn sàng sử dụng.</small>
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
                    <p>{{ $previewDescription ?: 'Mô tả ngắn sẽ giúp đội quản trị nhận diện nhóm phim dễ hơn.' }}</p>

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

                        <span class="genre-movie-count">
                            <i class="fa-solid fa-film"></i>
                            0 phim
                        </span>
                    </div>
                </section>

                <section class="genre-help-card">
                    <div class="genre-help-head">
                        <i class="fa-solid fa-lightbulb"></i>
                        <div>
                            <strong>Gợi ý nhập liệu</strong>
                            <span>Một thể loại tốt giúp nhân sự quản trị và khách hàng tìm phim nhanh hơn.</span>
                        </div>
                    </div>

                    <ul class="genre-help-list">
                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            Ưu tiên tên ngắn như Hành động, Tâm lý, Gia đình.
                        </li>
                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            Không tạo nhiều thể loại có ý nghĩa quá giống nhau.
                        </li>
                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            Có thể tạo ở trạng thái tạm ẩn nếu cần chuẩn bị dữ liệu trước.
                        </li>
                    </ul>
                </section>
            </aside>
        </div>

        <div class="genre-form-savebar">
            <div>
                <strong>Thêm thể loại mới</strong>
                <span>Kiểm tra tên, mô tả và trạng thái trước khi lưu.</span>
            </div>

            <div class="genre-form-save-actions">
                <a href="{{ route('admin.the-loais.index') }}" class="movie-action-btn is-ghost">
                    Hủy
                </a>
                <button type="submit" class="movie-action-btn is-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Lưu thể loại
                </button>
            </div>
        </div>
    </form>
@endsection
