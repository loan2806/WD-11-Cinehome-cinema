@extends('layouts.admin')

@section('page-title', 'Danh sách thể loại phim')

@section('content')
    @php
        $summary = $summary ?? [
            'total' => 0,
            'active' => 0,
            'inactive' => 0,
            'with_movies' => 0,
        ];
    @endphp

    <div class="genre-admin-page">
        @include('admin.partials.flash')

        <section class="genre-hero-panel">
            <div class="genre-hero-content">
                <span class="genre-kicker">
                    <i class="fa-solid fa-layer-group"></i>
                    Kho thể loại CineHome
                </span>
                <h1>Danh sách thể loại phim</h1>
                <p>
                    Quản lý nhóm nội dung, trạng thái hiển thị và số lượng phim đang gắn với từng thể loại.
                    Giao diện được tối ưu để lọc nhanh, kiểm tra nhanh và thao tác an toàn.
                </p>
            </div>

            <div class="genre-hero-actions">
                <a href="{{ route('admin.phims.index') }}" class="movie-action-btn is-ghost">
                    <i class="fa-solid fa-film"></i>
                    Danh sách phim
                </a>
                <a href="{{ route('admin.the-loais.create') }}" class="movie-action-btn is-primary">
                    <i class="fa-solid fa-plus"></i>
                    Thêm thể loại
                </a>
            </div>
        </section>

        <section class="genre-stat-grid" aria-label="Thống kê thể loại">
            <article class="genre-stat-card">
                <span class="genre-stat-icon is-total"><i class="fa-solid fa-clapperboard"></i></span>
                <div>
                    <span>Tổng thể loại</span>
                    <strong>{{ number_format($summary['total'] ?? 0) }}</strong>
                </div>
            </article>

            <article class="genre-stat-card">
                <span class="genre-stat-icon is-active"><i class="fa-solid fa-circle-check"></i></span>
                <div>
                    <span>Đang kích hoạt</span>
                    <strong>{{ number_format($summary['active'] ?? 0) }}</strong>
                </div>
            </article>

            <article class="genre-stat-card">
                <span class="genre-stat-icon is-muted"><i class="fa-solid fa-circle-pause"></i></span>
                <div>
                    <span>Tạm ẩn</span>
                    <strong>{{ number_format($summary['inactive'] ?? 0) }}</strong>
                </div>
            </article>

            <article class="genre-stat-card">
                <span class="genre-stat-icon is-linked"><i class="fa-solid fa-link"></i></span>
                <div>
                    <span>Có phim liên kết</span>
                    <strong>{{ number_format($summary['with_movies'] ?? 0) }}</strong>
                </div>
            </article>
        </section>

        <form action="{{ route('admin.the-loais.index') }}" method="GET" class="genre-filter-panel">
            <label class="genre-filter-field">
                <span>Tìm kiếm</span>
                <div class="genre-filter-control">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Nhập tên thể loại, ví dụ: Hành động..."
                    >
                </div>
            </label>

            @php
                $genreStatusOptions = [
                    '' => ['label' => 'Tất cả trạng thái', 'icon' => 'fa-sliders'],
                    '1' => ['label' => 'Đang kích hoạt', 'icon' => 'fa-circle-check'],
                    '0' => ['label' => 'Tạm ẩn', 'icon' => 'fa-circle-pause'],
                ];
                $currentGenreStatus = (string) request('status', '');
                if (!isset($genreStatusOptions[$currentGenreStatus])) {
                    $currentGenreStatus = '';
                }
            @endphp

            <label class="genre-filter-field is-status">
                <span>Trạng thái</span>
                <div class="genre-status-select" data-value="{{ $currentGenreStatus }}">
                    <input type="hidden" name="status" value="{{ $currentGenreStatus }}">

                    <button type="button" class="genre-status-trigger">
                        <i class="fa-solid {{ $genreStatusOptions[$currentGenreStatus]['icon'] }}"></i>
                        <span class="label">{{ $genreStatusOptions[$currentGenreStatus]['label'] }}</span>
                        <i class="fa-solid fa-chevron-down chevron"></i>
                    </button>

                    <div class="genre-status-menu hidden">
                        @foreach ($genreStatusOptions as $value => $meta)
                            <button
                                type="button"
                                class="genre-status-option {{ $value === $currentGenreStatus ? 'is-selected' : '' }}"
                                data-value="{{ $value }}"
                                data-label="{{ $meta['label'] }}"
                                data-icon="{{ $meta['icon'] }}"
                            >
                                <i class="fa-solid {{ $meta['icon'] }}"></i>
                                <span>{{ $meta['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </label>

            <div class="genre-filter-actions">
                <button type="submit" class="movie-action-btn is-primary">
                    <i class="fa-solid fa-filter"></i>
                    Lọc dữ liệu
                </button>
                <a href="{{ route('admin.the-loais.index') }}" class="movie-action-btn is-soft">
                    <i class="fa-solid fa-rotate-left"></i>
                    Đặt lại
                </a>
            </div>
        </form>

        <section class="genre-table-panel">
            <div class="genre-table-head">
                <div>
                    <span class="genre-kicker">
                        <i class="fa-solid fa-list-check"></i>
                        Bảng quản lý
                    </span>
                    <h2>Thể loại phim</h2>
                </div>
                <div class="genre-result-count">
                    {{ number_format($theLoais->total()) }} kết quả
                </div>
            </div>

            <div class="genre-table-wrap">
                <table class="genre-admin-table">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Thể loại</th>
                            <th>Mô tả</th>
                            <th>Slug</th>
                            <th>Số phim</th>
                            <th>Trạng thái</th>
                            <th class="is-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($theLoais as $key => $theLoai)
                            <tr>
                                <td class="genre-index-cell">
                                    #{{ ($theLoais->currentPage() - 1) * $theLoais->perPage() + $key + 1 }}
                                </td>
                                <td>
                                    <div class="genre-title-cell">
                                        <span class="genre-icon">
                                            <i class="fa-solid fa-ticket"></i>
                                        </span>
                                        <div class="genre-title-copy">
                                            <strong>{{ $theLoai->ten_the_loai }}</strong>
                                            <small>ID {{ $theLoai->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="genre-desc">
                                        {{ \Illuminate\Support\Str::limit($theLoai->mo_ta ?: 'Chưa có mô tả cho thể loại này.', 86) }}
                                    </p>
                                </td>
                                <td>
                                    <code class="genre-slug">{{ $theLoai->slug ?: 'chua-co-slug' }}</code>
                                </td>
                                <td>
                                    <span class="genre-movie-count {{ $theLoai->phims_count > 0 ? 'has-movies' : '' }}">
                                        <i class="fa-solid fa-film"></i>
                                        {{ number_format($theLoai->phims_count) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($theLoai->trang_thai)
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
                                </td>
                                <td>
                                    <div class="genre-row-actions">
                                        <a
                                            href="{{ route('admin.the-loais.edit', $theLoai) }}"
                                            class="movie-icon-btn is-edit"
                                            title="Chỉnh sửa thể loại"
                                            aria-label="Chỉnh sửa {{ $theLoai->ten_the_loai }}"
                                        >
                                            <i class="fa-solid fa-pen"></i>
                                        </a>

                                        @if ($theLoai->phims_count > 0)
                                            <button
                                                type="button"
                                                class="movie-icon-btn is-delete genre-delete-disabled"
                                                title="Không thể xóa vì đang có phim liên kết"
                                                aria-label="Không thể xóa {{ $theLoai->ten_the_loai }}"
                                                disabled
                                            >
                                                <i class="fa-solid fa-lock"></i>
                                            </button>
                                        @else
                                            <form action="{{ route('admin.the-loais.destroy', $theLoai) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    onclick="return confirm('Bạn có chắc muốn xóa thể loại này?')"
                                                    class="movie-icon-btn is-delete"
                                                    title="Xóa thể loại"
                                                    aria-label="Xóa {{ $theLoai->ten_the_loai }}"
                                                >
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="genre-empty-state">
                                        <span><i class="fa-solid fa-folder-open"></i></span>
                                        <h3>Chưa tìm thấy thể loại phù hợp</h3>
                                        <p>Thử đổi từ khóa, bỏ bộ lọc trạng thái hoặc tạo thể loại mới cho kho phim.</p>
                                        <a href="{{ route('admin.the-loais.create') }}" class="movie-action-btn is-primary">
                                            <i class="fa-solid fa-plus"></i>
                                            Thêm thể loại
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="genre-pagination">
                @include('components.admin-pagination', ['paginator' => $theLoais])
            </div>
        </section>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.genre-status-select').forEach(function (wrap) {
                const trigger = wrap.querySelector('.genre-status-trigger');
                const menu = wrap.querySelector('.genre-status-menu');
                const hiddenInput = wrap.querySelector('input[type="hidden"]');
                const labelEl = trigger.querySelector('.label');
                const iconEl = trigger.querySelector('i:first-child');
                const options = wrap.querySelectorAll('.genre-status-option');

                // Đưa menu ra làm con trực tiếp của <body> để thoát khỏi các
                // container cha có backdrop-filter/overflow:hidden (khiến
                // position:fixed bị "nhốt" lại bên trong thay vì bám viewport).
                document.body.appendChild(menu);

                function closeMenu() {
                    wrap.classList.remove('is-open');
                    menu.classList.add('hidden');
                }

                function positionMenu() {
                    const rect = trigger.getBoundingClientRect();
                    menu.style.left = rect.left + 'px';
                    menu.style.width = rect.width + 'px';

                    const menuHeight = menu.offsetHeight;
                    const spaceBelow = window.innerHeight - rect.bottom;
                    const spaceAbove = rect.top;

                    if (spaceBelow < menuHeight + 8 && spaceAbove > spaceBelow) {
                        menu.style.top = (rect.top - menuHeight - 8) + 'px';
                    } else {
                        menu.style.top = (rect.bottom + 8) + 'px';
                    }
                }

                trigger.addEventListener('click', function (event) {
                    event.stopPropagation();
                    const willOpen = menu.classList.contains('hidden');
                    closeMenu();
                    if (willOpen) {
                        wrap.classList.add('is-open');
                        menu.classList.remove('hidden');
                        positionMenu();
                    }
                });

                window.addEventListener('scroll', closeMenu, true);
                window.addEventListener('resize', closeMenu);

                options.forEach(function (opt) {
                    opt.addEventListener('click', function () {
                        hiddenInput.value = opt.dataset.value;
                        labelEl.textContent = opt.dataset.label;
                        iconEl.className = 'fa-solid ' + opt.dataset.icon;

                        options.forEach((o) => o.classList.toggle('is-selected', o === opt));
                        closeMenu();
                    });
                });

                document.addEventListener('click', function (event) {
                    if (!wrap.contains(event.target) && !menu.contains(event.target)) {
                        closeMenu();
                    }
                });
            });
        });
    </script>
    @endpush
@endsection
