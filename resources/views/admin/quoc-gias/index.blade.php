@extends('layouts.admin')

@section('page-title', 'Danh sách quốc gia phim')

@section('content')
    @php
        $summary = $summary ?? [
            'total' => 0,
            'active' => 0,
            'inactive' => 0,
            'with_movies' => 0,
        ];
    @endphp

    <div class="country-admin-page">
        @include('admin.partials.flash')

        <section class="country-hero-panel">
            <div class="country-hero-content">
                <span class="country-kicker">
                    <i class="fa-solid fa-earth-asia"></i>
                    Kho quốc gia CineHome
                </span>
                <h1>Danh sách quốc gia</h1>
                <p>
                    Quản lý nguồn gốc phim, mã quốc gia và trạng thái sử dụng trong hệ thống.
                    Bảng được thiết kế để tìm kiếm nhanh, kiểm tra số phim liên kết và thao tác an toàn hơn.
                </p>
            </div>

            <div class="country-hero-actions">
                <a href="{{ route('admin.phims.index') }}" class="movie-action-btn is-ghost">
                    <i class="fa-solid fa-film"></i>
                    Danh sách phim
                </a>
                <a href="{{ route('admin.quoc-gias.create') }}" class="movie-action-btn is-primary">
                    <i class="fa-solid fa-plus"></i>
                    Thêm quốc gia
                </a>
            </div>
        </section>

        <section class="country-stat-grid" aria-label="Thống kê quốc gia">
            <article class="country-stat-card">
                <span class="country-stat-icon is-total"><i class="fa-solid fa-globe"></i></span>
                <div>
                    <span>Tổng quốc gia</span>
                    <strong>{{ number_format($summary['total'] ?? 0) }}</strong>
                </div>
            </article>

            <article class="country-stat-card">
                <span class="country-stat-icon is-active"><i class="fa-solid fa-circle-check"></i></span>
                <div>
                    <span>Đang sử dụng</span>
                    <strong>{{ number_format($summary['active'] ?? 0) }}</strong>
                </div>
            </article>

            <article class="country-stat-card">
                <span class="country-stat-icon is-muted"><i class="fa-solid fa-circle-pause"></i></span>
                <div>
                    <span>Tạm ẩn</span>
                    <strong>{{ number_format($summary['inactive'] ?? 0) }}</strong>
                </div>
            </article>

            <article class="country-stat-card">
                <span class="country-stat-icon is-linked"><i class="fa-solid fa-link"></i></span>
                <div>
                    <span>Có phim liên kết</span>
                    <strong>{{ number_format($summary['with_movies'] ?? 0) }}</strong>
                </div>
            </article>
        </section>

        <form action="{{ route('admin.quoc-gias.index') }}" method="GET" class="country-filter-panel">
            <label class="country-filter-field">
                <span>Tìm kiếm</span>
                <div class="country-filter-control">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Nhập tên hoặc mã quốc gia, ví dụ: Việt Nam, US..."
                    >
                </div>
            </label>

            <label class="country-filter-field is-status">
                <span>Trạng thái</span>
                <div class="country-filter-control">
                    <i class="fa-solid fa-sliders"></i>
                    <select name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1" @selected(request('status') === '1')>Đang sử dụng</option>
                        <option value="0" @selected(request('status') === '0')>Tạm ẩn</option>
                    </select>
                </div>
            </label>

            <div class="country-filter-actions">
                <button type="submit" class="movie-action-btn is-primary">
                    <i class="fa-solid fa-filter"></i>
                    Lọc dữ liệu
                </button>
                <a href="{{ route('admin.quoc-gias.index') }}" class="movie-action-btn is-soft">
                    <i class="fa-solid fa-rotate-left"></i>
                    Đặt lại
                </a>
            </div>
        </form>

        <section class="country-table-panel">
            <div class="country-table-head">
                <div>
                    <span class="country-kicker">
                        <i class="fa-solid fa-list-check"></i>
                        Bảng quản lý
                    </span>
                    <h2>Quốc gia phim</h2>
                </div>
                <div class="country-result-count">
                    {{ number_format($countries->total()) }} kết quả
                </div>
            </div>

            <div class="country-table-wrap">
                <table class="country-admin-table">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Quốc gia</th>
                            <th>Mã quốc gia</th>
                            <th>Số phim</th>
                            <th>Trạng thái</th>
                            <th>Cập nhật</th>
                            <th class="is-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($countries as $key => $country)
                            <tr>
                                <td class="country-index-cell">
                                    #{{ ($countries->currentPage() - 1) * $countries->perPage() + $key + 1 }}
                                </td>
                                <td>
                                    <div class="country-title-cell">
                                        <span class="country-flag-avatar">
                                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($country->ma_quoc_gia ?: $country->ten_quoc_gia, 0, 2)) }}
                                        </span>
                                        <div class="country-title-copy">
                                            <strong>{{ $country->ten_quoc_gia }}</strong>
                                            <small>ID {{ $country->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code class="country-code">
                                        {{ \Illuminate\Support\Str::upper($country->ma_quoc_gia ?: '--') }}
                                    </code>
                                </td>
                                <td>
                                    <span class="country-movie-count {{ $country->phims_count > 0 ? 'has-movies' : '' }}">
                                        <i class="fa-solid fa-film"></i>
                                        {{ number_format($country->phims_count) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($country->trang_thai)
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
                                </td>
                                <td>
                                    <span class="country-date">
                                        <i class="fa-regular fa-clock"></i>
                                        {{ optional($country->updated_at)->format('d/m/Y') ?? 'Chưa cập nhật' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="country-row-actions">
                                        <a
                                            href="{{ route('admin.quoc-gias.edit', $country) }}"
                                            class="movie-icon-btn is-edit"
                                            title="Chỉnh sửa quốc gia"
                                            aria-label="Chỉnh sửa {{ $country->ten_quoc_gia }}"
                                        >
                                            <i class="fa-solid fa-pen"></i>
                                        </a>

                                        @if ($country->phims_count > 0)
                                            <button
                                                type="button"
                                                class="movie-icon-btn is-delete country-delete-disabled"
                                                title="Không thể xóa vì đang có phim liên kết"
                                                aria-label="Không thể xóa {{ $country->ten_quoc_gia }}"
                                                disabled
                                            >
                                                <i class="fa-solid fa-lock"></i>
                                            </button>
                                        @else
                                            <form action="{{ route('admin.quoc-gias.destroy', $country) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    onclick="return confirm('Bạn có chắc muốn xóa quốc gia này?')"
                                                    class="movie-icon-btn is-delete"
                                                    title="Xóa quốc gia"
                                                    aria-label="Xóa {{ $country->ten_quoc_gia }}"
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
                                    <div class="country-empty-state">
                                        <span><i class="fa-solid fa-earth-asia"></i></span>
                                        <h3>Chưa tìm thấy quốc gia phù hợp</h3>
                                        <p>Thử đổi từ khóa, bỏ bộ lọc trạng thái hoặc thêm quốc gia mới cho kho phim.</p>
                                        <a href="{{ route('admin.quoc-gias.create') }}" class="movie-action-btn is-primary">
                                            <i class="fa-solid fa-plus"></i>
                                            Thêm quốc gia
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="country-pagination">
                @include('components.admin-pagination', ['paginator' => $countries])
            </div>
        </section>
    </div>
@endsection
