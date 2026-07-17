@extends('layouts.user')

@section('title', 'Tin tức & Khuyến mãi')

@section('content')
    @php
        $featuredHero = $tinNoiBat->first();
        $featuredSide = $tinNoiBat->skip(1);
        $fallbackHero = 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=1400&auto=format&fit=crop';
        $fallbackCard = 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=900&auto=format&fit=crop';

        $imageUrl = function ($tin, $fallback) {
            return $tin->hinh_anh && file_exists(public_path('storage/' . $tin->hinh_anh))
                ? asset('storage/' . $tin->hinh_anh)
                : $fallback;
        };
    @endphp

    <div class="news-page" lang="vi" spellcheck="false">
        <section class="news-hero">
            <div class="news-hero-copy">
                <span class="news-eyebrow">
                    <i class="fa-solid fa-newspaper"></i>
                    CineHome Newsroom
                </span>
                <h1>Tin điện ảnh, ưu đãi và câu chuyện sau màn ảnh.</h1>
                <p>
                    Cập nhật phim mới, sự kiện rạp chiếu và voucher đang mở. Mọi thứ được sắp xếp để bạn đọc nhanh,
                    chọn phim nhanh và đặt vé cũng nhanh.
                </p>
            </div>

            <form method="GET" action="{{ route('user.tin-tuc.index') }}" class="news-hero-search">
                @if (request('danh_muc'))
                    <input type="hidden" name="danh_muc" value="{{ request('danh_muc') }}">
                @endif
                @if (request('tag'))
                    <input type="hidden" name="tag" value="{{ request('tag') }}">
                @endif

                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm tin phim, sự kiện, voucher...">
                <button type="submit">Tìm</button>
            </form>
        </section>

        <nav class="news-category-rail" aria-label="Danh mục tin tức">
            <a href="{{ route('user.tin-tuc.index') }}" class="{{ !request('danh_muc') && !request('tag') ? 'is-active' : '' }}">
                <i class="fa-solid fa-border-all"></i>
                Tất cả
            </a>
            @foreach ($danhMucs as $danhMuc)
                <a href="{{ route('user.tin-tuc.index', ['danh_muc' => $danhMuc->slug]) }}"
                    class="{{ request('danh_muc') === $danhMuc->slug ? 'is-active' : '' }}">
                    <i class="{{ $danhMuc->icon ?? 'fa-solid fa-tag' }}"></i>
                    {{ $danhMuc->ten_danh_muc }}
                </a>
            @endforeach
            <a href="{{ route('user.khuyen-mai.index') }}" class="voucher-link">
                <i class="fa-solid fa-ticket"></i>
                Voucher
            </a>
        </nav>

        @if ($tinNoiBat->count() > 0)
            <section class="news-featured-section">
                <div class="news-section-head">
                    <div>
                        <span>Tin nổi bật</span>
                        <h2>Đang được quan tâm</h2>
                    </div>
                    <span class="news-hot-badge">
                        <i class="fa-solid fa-fire"></i>
                        HOT
                    </span>
                </div>

                <div class="news-featured-grid {{ $featuredSide->count() === 0 ? 'is-single' : '' }}">
                    @if ($featuredHero)
                        <a href="{{ route('user.tin-tuc.show', $featuredHero->slug) }}" class="news-featured-main">
                            <img src="{{ $imageUrl($featuredHero, $fallbackHero) }}" alt="{{ $featuredHero->tieu_de }}">
                            <div class="news-featured-overlay">
                                @if ($featuredHero->danhMucTin)
                                    <span class="news-category-badge">
                                        <i class="{{ $featuredHero->danhMucTin->icon ?? 'fa-solid fa-tag' }}"></i>
                                        {{ $featuredHero->danhMucTin->ten_danh_muc }}
                                    </span>
                                @endif
                                <h3>{{ $featuredHero->tieu_de }}</h3>
                                <p>{{ $featuredHero->mo_ta_ngan }}</p>
                                <div class="news-meta">
                                    <span><i class="fa-solid fa-calendar"></i>{{ $featuredHero->ngay_dang ? $featuredHero->ngay_dang->format('d/m/Y') : now()->format('d/m/Y') }}</span>
                                    <span><i class="fa-solid fa-eye"></i>{{ number_format($featuredHero->luot_xem) }}</span>
                                </div>
                            </div>
                        </a>
                    @endif

                    @if ($featuredSide->count() > 0)
                        <div class="news-featured-side">
                            @foreach ($featuredSide as $tin)
                                <a href="{{ route('user.tin-tuc.show', $tin->slug) }}" class="news-side-card">
                                    <img src="{{ $imageUrl($tin, $fallbackCard) }}" alt="{{ $tin->tieu_de }}">
                                    <div>
                                        @if ($tin->danhMucTin)
                                            <span>{{ $tin->danhMucTin->ten_danh_muc }}</span>
                                        @endif
                                        <h3>{{ $tin->tieu_de }}</h3>
                                        <small>{{ $tin->ngay_dang ? $tin->ngay_dang->format('d/m/Y') : now()->format('d/m/Y') }}</small>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        @endif

        @if ($vouchers->count() > 0)
            <section class="news-voucher-section">
                <div class="news-section-head">
                    <div>
                        <span>Ưu đãi hôm nay</span>
                        <h2>Voucher đang mở</h2>
                    </div>
                    <a href="{{ route('user.khuyen-mai.index') }}">
                        Xem tất cả
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <div class="news-voucher-grid">
                    @foreach ($vouchers as $voucher)
                        @php
                            $daysLeft = $voucher->ngay_het_han ? now()->diffInDays($voucher->ngay_het_han, false) : null;
                        @endphp
                        <article class="news-voucher-card">
                            <div class="news-voucher-top">
                                <span>{{ $voucher->ma_voucher }}</span>
                                @if (!is_null($daysLeft) && $daysLeft > 0 && $daysLeft <= 7)
                                    <small>Còn {{ floor($daysLeft) }} ngày</small>
                                @endif
                            </div>
                            <h3>{{ $voucher->ten_voucher }}</h3>
                            <div class="news-voucher-value">
                                <span>Giảm</span>
                                <strong>{{ number_format($voucher->gia_tri_giam, 0, ',', '.') }}đ</strong>
                            </div>
                            @if ($voucher->ngay_het_han)
                                <p>Hạn dùng: {{ \Carbon\Carbon::parse($voucher->ngay_het_han)->format('d/m/Y') }}</p>
                            @endif
                            <button type="button" data-voucher-id="{{ $voucher->id }}">
                                <i class="fa-solid fa-bolt"></i>
                                Sử dụng ngay
                            </button>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="news-latest-section">
            <div class="news-section-head">
                <div>
                    <span>Bài mới</span>
                    <h2>Tin mới nhất</h2>
                </div>
                @if (request('search') || request('danh_muc') || request('tag'))
                    <a href="{{ route('user.tin-tuc.index') }}">
                        Xóa bộ lọc
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </div>

            @if ($tinTucs->count() > 0)
                <div class="news-card-grid">
                    @foreach ($tinTucs as $tin)
                        <a href="{{ route('user.tin-tuc.show', $tin->slug) }}" class="news-card">
                            <figure>
                                <img src="{{ $imageUrl($tin, $fallbackCard) }}" alt="{{ $tin->tieu_de }}">
                                @if ($tin->danhMucTin)
                                    <span class="news-category-badge">{{ $tin->danhMucTin->ten_danh_muc }}</span>
                                @endif
                            </figure>
                            <div class="news-card-body">
                                <h3>{{ $tin->tieu_de }}</h3>
                                <p>{{ $tin->mo_ta_ngan }}</p>
                                <div class="news-meta">
                                    <span><i class="fa-solid fa-calendar"></i>{{ $tin->ngay_dang ? $tin->ngay_dang->format('d/m/Y') : now()->format('d/m/Y') }}</span>
                                    <span><i class="fa-solid fa-eye"></i>{{ number_format($tin->luot_xem) }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                @if ($tinTucs->hasPages())
                    <div class="news-pagination">
                        @if ($tinTucs->onFirstPage())
                            <span><i class="fa-solid fa-chevron-left"></i></span>
                        @else
                            <a href="{{ $tinTucs->previousPageUrl() }}"><i class="fa-solid fa-chevron-left"></i></a>
                        @endif

                        @foreach ($tinTucs->getUrlRange(max(1, $tinTucs->currentPage() - 1), min($tinTucs->lastPage(), $tinTucs->currentPage() + 1)) as $page => $url)
                            @if ($page === $tinTucs->currentPage())
                                <span class="is-current">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if ($tinTucs->hasMorePages())
                            <a href="{{ $tinTucs->nextPageUrl() }}"><i class="fa-solid fa-chevron-right"></i></a>
                        @else
                            <span><i class="fa-solid fa-chevron-right"></i></span>
                        @endif
                    </div>
                @endif
            @else
                <div class="news-empty-state">
                    <i class="fa-solid fa-newspaper"></i>
                    <h3>Chưa có tin tức phù hợp</h3>
                    <p>Thử đổi danh mục hoặc tìm kiếm từ khóa khác để xem thêm bài viết.</p>
                </div>
            @endif
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-voucher-id]').forEach(function(button) {
                button.addEventListener('click', function() {
                    suDungVoucher(this.dataset.voucherId);
                });
            });
        });

        function suDungVoucher(voucherId) {
            @guest
                const loginButton = document.querySelector('[data-auth-open="login"]');
                if (loginButton) {
                    loginButton.click();
                    return;
                }
                window.location.href = '{{ route('login') }}';
                return;
            @endguest

            fetch('{{ route('user.voucher.save-tam') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    voucher_id: voucherId
                })
            })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        window.location.href = '{{ route('dat_ve.chon_phim') }}';
                        return;
                    }

                    alert(data.message || 'Có lỗi xảy ra khi lưu voucher.');
                })
                .catch(function() {
                    window.location.href = '{{ route('dat_ve.chon_phim') }}';
                });
        }
    </script>
@endsection
