@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $start = max($current - 2, 1);
        $end = min($current + 2, $last);
    @endphp

    <nav class="admin-pagination" aria-label="Phân trang quản trị">
        <div class="admin-pagination__meta">
            <span>Trang {{ $current }}/{{ $last }}</span>
            <strong>{{ number_format($paginator->total()) }}</strong>
            <span>kết quả</span>
        </div>

        <div class="admin-pagination__controls">
            @if ($paginator->onFirstPage())
                <span class="admin-pagination__btn is-disabled" aria-disabled="true">
                    <i class="fa-solid fa-arrow-left"></i>
                    Trước
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="admin-pagination__btn" rel="prev">
                    <i class="fa-solid fa-arrow-left"></i>
                    Trước
                </a>
            @endif

            @if ($start > 1)
                <a href="{{ $paginator->url(1) }}" class="admin-pagination__page">1</a>

                @if ($start > 2)
                    <span class="admin-pagination__dots">...</span>
                @endif
            @endif

            @for ($page = $start; $page <= $end; $page++)
                @if ($page === $current)
                    <span class="admin-pagination__page is-active" aria-current="page">{{ $page }}</span>
                @else
                    <a href="{{ $paginator->url($page) }}" class="admin-pagination__page">{{ $page }}</a>
                @endif
            @endfor

            @if ($end < $last)
                @if ($end < $last - 1)
                    <span class="admin-pagination__dots">...</span>
                @endif

                <a href="{{ $paginator->url($last) }}" class="admin-pagination__page">{{ $last }}</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="admin-pagination__btn" rel="next">
                    Sau
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            @else
                <span class="admin-pagination__btn is-disabled" aria-disabled="true">
                    Sau
                    <i class="fa-solid fa-arrow-right"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
