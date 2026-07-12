@extends('layouts.user')

@section('title', $tinTuc->tieu_de)

@section('content')
    @php
        $fallbackHero = 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=1600&auto=format&fit=crop';
        $fallbackCard = 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=900&auto=format&fit=crop';
        $heroImage = $tinTuc->hinh_anh && file_exists(public_path('storage/' . $tinTuc->hinh_anh))
            ? asset('storage/' . $tinTuc->hinh_anh)
            : $fallbackHero;

        $relatedImage = function ($tin) use ($fallbackCard) {
            return $tin->hinh_anh && file_exists(public_path('storage/' . $tin->hinh_anh))
                ? asset('storage/' . $tin->hinh_anh)
                : $fallbackCard;
        };
    @endphp

    <article class="news-article-page" lang="vi" spellcheck="false">
        <section class="news-article-hero" style="--article-bg: url('{{ $heroImage }}')">
            <div class="news-article-breadcrumb">
                <a href="{{ route('home') }}"><i class="fa-solid fa-house"></i> Trang chủ</a>
                <i class="fa-solid fa-chevron-right"></i>
                <a href="{{ route('user.tin-tuc.index') }}">Tin tức</a>
                @if ($tinTuc->danhMucTin)
                    <i class="fa-solid fa-chevron-right"></i>
                    <a href="{{ route('user.tin-tuc.index', ['danh_muc' => $tinTuc->danhMucTin->slug]) }}">
                        {{ $tinTuc->danhMucTin->ten_danh_muc }}
                    </a>
                @endif
            </div>

            <div class="news-article-hero-content">
                <div class="news-article-tags">
                    @if ($tinTuc->danhMucTin)
                        <a href="{{ route('user.tin-tuc.index', ['danh_muc' => $tinTuc->danhMucTin->slug]) }}">
                            <i class="{{ $tinTuc->danhMucTin->icon ?? 'fa-solid fa-tag' }}"></i>
                            {{ $tinTuc->danhMucTin->ten_danh_muc }}
                        </a>
                    @endif
                    @if ($tinTuc->noi_bat)
                        <span><i class="fa-solid fa-fire"></i> Nổi bật</span>
                    @endif
                </div>

                <h1>{{ $tinTuc->tieu_de }}</h1>

                @if ($tinTuc->mo_ta_ngan)
                    <p>{{ $tinTuc->mo_ta_ngan }}</p>
                @endif

                <div class="news-article-meta">
                    @if ($tinTuc->tac_gia)
                        <span><i class="fa-solid fa-user-pen"></i>{{ $tinTuc->tac_gia }}</span>
                    @endif
                    <span><i class="fa-solid fa-calendar"></i>{{ $tinTuc->ngay_dang ? $tinTuc->ngay_dang->format('d/m/Y') : now()->format('d/m/Y') }}</span>
                    <span><i class="fa-solid fa-eye"></i>{{ number_format($tinTuc->luot_xem) }} lượt xem</span>
                </div>
            </div>
        </section>

        <div class="news-article-layout">
            <div class="news-article-main">
                <figure class="news-article-cover">
                    <img src="{{ $heroImage }}" alt="{{ $tinTuc->tieu_de }}">
                </figure>

                <section class="news-article-body">
                    @if ($tinTuc->noi_dung)
                        {!! $tinTuc->noi_dung !!}
                    @else
                        <div class="news-article-empty">
                            <i class="fa-solid fa-file-pen"></i>
                            <p>Nội dung đang được cập nhật...</p>
                        </div>
                    @endif
                </section>

                @if ($tinTuc->tags->count() > 0)
                    <section class="news-article-tag-list">
                        <span><i class="fa-solid fa-tags"></i> Tags</span>
                        @foreach ($tinTuc->tags as $tag)
                            <a href="{{ route('user.tin-tuc.index', ['tag' => $tag->slug]) }}">#{{ $tag->ten_tag }}</a>
                        @endforeach
                    </section>
                @endif
            </div>

            <aside class="news-article-sidebar">
                <div class="news-share-panel">
                    <span>Chia sẻ bài viết</span>
                    <div>
                        <button type="button" data-share="facebook" aria-label="Chia sẻ Facebook">
                            <i class="fa-brands fa-facebook-f"></i>
                        </button>
                        <button type="button" data-share="twitter" aria-label="Chia sẻ Twitter">
                            <i class="fa-brands fa-twitter"></i>
                        </button>
                        <button type="button" data-share="zalo" aria-label="Chia sẻ Zalo">
                            <i class="fa-solid fa-comment"></i>
                        </button>
                        <button type="button" data-share="copy" aria-label="Sao chép liên kết">
                            <i class="fa-solid fa-link"></i>
                        </button>
                    </div>
                </div>

                @if ($vouchers->count() > 0)
                    <div class="news-article-voucher-panel">
                        <div class="news-sidebar-head">
                            <span>Ưu đãi</span>
                            <a href="{{ route('user.khuyen-mai.index') }}">Xem tất cả</a>
                        </div>

                        @foreach ($vouchers as $voucher)
                            <article class="news-mini-voucher">
                                <span>{{ $voucher->ma_voucher }}</span>
                                <h3>{{ $voucher->ten_voucher }}</h3>
                                <strong>{{ number_format($voucher->gia_tri_giam, 0, ',', '.') }}đ</strong>
                                @if ($voucher->ngay_het_han)
                                    <small>Hạn: {{ \Carbon\Carbon::parse($voucher->ngay_het_han)->format('d/m/Y') }}</small>
                                @endif
                                <button type="button" data-voucher-id="{{ $voucher->id }}">
                                    Sử dụng ngay
                                </button>
                            </article>
                        @endforeach
                    </div>
                @endif
            </aside>
        </div>

        @if ($tinLienQuan->count() > 0)
            <section class="news-related-section">
                <div class="news-section-head">
                    <div>
                        <span>Đọc tiếp</span>
                        <h2>Tin liên quan</h2>
                    </div>
                </div>

                <div class="news-related-grid">
                    @foreach ($tinLienQuan as $tin)
                        <a href="{{ route('user.tin-tuc.show', $tin->slug) }}" class="news-card news-related-card">
                            <figure>
                                <img src="{{ $relatedImage($tin) }}" alt="{{ $tin->tieu_de }}">
                                @if ($tin->danhMucTin)
                                    <span class="news-category-badge">{{ $tin->danhMucTin->ten_danh_muc }}</span>
                                @endif
                            </figure>
                            <div class="news-card-body">
                                <h3>{{ $tin->tieu_de }}</h3>
                                <div class="news-meta">
                                    <span><i class="fa-solid fa-calendar"></i>{{ $tin->ngay_dang ? $tin->ngay_dang->format('d/m/Y') : now()->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </article>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-share]').forEach(function(button) {
                button.addEventListener('click', function() {
                    handleShare(this.dataset.share);
                });
            });

            document.querySelectorAll('[data-voucher-id]').forEach(function(button) {
                button.addEventListener('click', function() {
                    suDungVoucher(this.dataset.voucherId);
                });
            });
        });

        function handleShare(platform) {
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent(document.title);
            let shareUrl = '';

            if (platform === 'facebook') {
                shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
            }

            if (platform === 'twitter') {
                shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
            }

            if (platform === 'zalo') {
                shareUrl = `https://zalo.me/share?url=${url}`;
            }

            if (platform === 'copy') {
                navigator.clipboard.writeText(window.location.href).then(function() {
                    alert('Đã sao chép liên kết!');
                });
                return;
            }

            if (shareUrl) {
                window.open(shareUrl, '_blank', 'width=640,height=460');
            }
        }

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
