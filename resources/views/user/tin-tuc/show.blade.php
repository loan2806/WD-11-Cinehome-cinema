@extends('layouts.user')

@section('title', $tinTuc->tieu_de)

@push('styles')
<style>
    .article-content p {
        margin-bottom: 1.25rem;
        color: #d1d5db;
        line-height: 1.8;
    }
    .article-content img {
        border-radius: 12px;
        margin: 1.5rem auto;
        max-width: 100%;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }
    .article-content h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: white;
        margin-top: 2rem;
        margin-bottom: 1rem;
    }
    .article-content h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #d99a32;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
    }
    .article-content blockquote {
        border-left: 4px solid #d99a32;
        padding-left: 1.5rem;
        margin: 1.5rem 0;
        font-style: italic;
        color: #9ca3af;
    }
    .article-content ul, .article-content ol {
        padding-left: 1.5rem;
        margin-bottom: 1.25rem;
    }
    .article-content li {
        margin-bottom: 0.5rem;
        color: #d1d5db;
    }
    .share-btn {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255,255,255,0.1);
        background: rgba(255,255,255,0.05);
        color: #9ca3af;
        transition: all 0.3s ease;
    }
    .share-btn:hover {
        transform: translateY(-2px);
    }
    .related-card {
        background: #151515;
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .related-card:hover {
        border-color: rgba(217, 154, 50, 0.3);
        box-shadow: 0 8px 30px rgba(0,0,0,0.3);
    }
    .related-card .image-wrapper {
        aspect-ratio: 16/9;
        overflow: hidden;
    }
    .related-card .image-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .related-card:hover .image-wrapper img {
        transform: scale(1.05);
    }
    .voucher-card {
        background: linear-gradient(145deg, #1a1a2e 0%, #16213e 100%);
        border: 1px solid rgba(139, 92, 246, 0.3);
        border-radius: 16px;
        transition: all 0.3s ease;
    }
    .voucher-card:hover {
        border-color: rgba(139, 92, 246, 0.6);
        transform: translateY(-4px);
    }
    .tag-btn {
        padding: 8px 16px;
        border-radius: 20px;
        border: 1px solid rgba(255,255,255,0.2);
        background: rgba(255,255,255,0.05);
        color: #9ca3af;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    .tag-btn:hover {
        border-color: rgba(217, 154, 50, 0.5);
        background: rgba(217, 154, 50, 0.1);
        color: #d99a32;
    }
</style>
@endpush

@push('scripts')
<script>
    function shareSocial(platform) {
        const url = encodeURIComponent(window.location.href);
        const title = encodeURIComponent(document.title);
        let shareUrl = '';

        switch(platform) {
            case 'facebook':
                shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                break;
            case 'twitter':
                shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
                break;
            case 'zalo':
                shareUrl = `https://zalo.me/share?url=${url}`;
                break;
        }

        if (shareUrl) {
            window.open(shareUrl, '_blank', 'width=600,height=400');
        }
    }

    function copyLink() {
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Đã sao chép liên kết!');
        });
    }

    function suDungVoucher(voucherId) {
        fetch('{{ route("user.voucher.save-tam") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ voucher_id: voucherId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route("dat_ve.chon_phim") }}';
            } else {
                alert(data.message || 'Có lỗi xảy ra!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.location.href = '{{ route("dat_ve.chon_phim") }}';
        });
    }
</script>
@endpush

@section('content')
<div class="min-h-screen bg-[#0a0a0a] text-white pt-16 pb-16">

    {{-- BREADCRUMB --}}
    <div class="bg-[#0f0f0f] border-b border-white/5">
        <div class="container mx-auto px-4 md:px-6 py-4">
            <nav class="flex items-center gap-2 text-sm text-gray-500">
                <a href="{{ route('home') }}" class="hover:text-[#d99a32] transition-colors">
                    <i class="fa-solid fa-house mr-1"></i> Trang chủ
                </a>
                <i class="fa-solid fa-chevron-right text-xs"></i>
                <a href="{{ route('user.tin-tuc.index') }}" class="hover:text-[#d99a32] transition-colors">
                    Tin tức
                </a>
                @if($tinTuc->danhMucTin)
                <i class="fa-solid fa-chevron-right text-xs"></i>
                <a href="{{ route('user.tin-tuc.index', ['danh_muc' => $tinTuc->danhMucTin->slug]) }}"
                    class="hover:text-[#d99a32] transition-colors">
                    {{ $tinTuc->danhMucTin->ten_danh_muc }}
                </a>
                @endif
            </nav>
        </div>
    </div>

    {{-- ARTICLE --}}
    <article class="container mx-auto px-4 md:px-6 py-10">
        <div class="max-w-4xl mx-auto">

            {{-- HEADER --}}
            <header class="mb-8">
                <div class="flex flex-wrap items-center gap-3 mb-5">
                    @if($tinTuc->danhMucTin)
                    <a href="{{ route('user.tin-tuc.index', ['danh_muc' => $tinTuc->danhMucTin->slug]) }}"
                        class="px-4 py-1.5 rounded-full border border-[#d99a32]/50 bg-[#d99a32]/10 text-[#d99a32] text-sm font-bold hover:bg-[#d99a32]/20 transition-colors">
                        <i class="{{ $tinTuc->danhMucTin->icon ?? 'fa-solid fa-tag' }} mr-1"></i>
                        {{ $tinTuc->danhMucTin->ten_danh_muc }}
                    </a>
                    @endif
                    @if($tinTuc->noi_bat)
                    <span class="px-4 py-1.5 rounded-full border border-red-500/50 bg-red-500/10 text-red-400 text-sm font-bold">
                        <i class="fa-solid fa-fire mr-1"></i> Nổi bật
                    </span>
                    @endif
                </div>

                <h1 class="text-3xl md:text-4xl lg:text-5xl font-black leading-tight mb-6">
                    {{ $tinTuc->tieu_de }}
                </h1>

                @if($tinTuc->mo_ta_ngan)
                <p class="text-lg text-gray-400 leading-relaxed mb-6">
                    {{ $tinTuc->mo_ta_ngan }}
                </p>
                @endif

                <div class="flex flex-wrap items-center gap-6 text-sm text-gray-500">
                    @if($tinTuc->tac_gia)
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-user-pen text-[#d99a32]"></i>
                        {{ $tinTuc->tac_gia }}
                    </span>
                    @endif
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-calendar text-[#d99a32]"></i>
                        {{ $tinTuc->ngay_dang ? $tinTuc->ngay_dang->format('d/m/Y') : now()->format('d/m/Y') }}
                    </span>
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-eye text-[#d99a32]"></i>
                        {{ number_format($tinTuc->luot_xem) }} lượt xem
                    </span>
                </div>
            </header>

            {{-- FEATURED IMAGE --}}
            @if($tinTuc->hinh_anh)
            <figure class="mb-10 rounded-2xl overflow-hidden">
                @if(file_exists(public_path('storage/' . $tinTuc->hinh_anh)))
                    <img src="{{ asset('storage/' . $tinTuc->hinh_anh) }}"
                        alt="{{ $tinTuc->tieu_de }}"
                        class="w-full">
                @else
                    <img src="https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=1200&q=80"
                        alt="{{ $tinTuc->tieu_de }}"
                        class="w-full">
                @endif
            </figure>
            @endif

            {{-- CONTENT --}}
            <div class="article-content">
                @if($tinTuc->noi_dung)
                <div class="bg-[#151515] rounded-2xl p-6 md:p-10 border border-white/5">
                    {!! $tinTuc->noi_dung !!}
                </div>
                @else
                <div class="bg-[#151515] rounded-2xl p-12 text-center border border-white/5">
                    <i class="fa-solid fa-file-pen text-5xl text-gray-700 mb-4"></i>
                    <p class="text-gray-500 italic">Nội dung đang được cập nhật...</p>
                </div>
                @endif
            </div>

            {{-- TAGS --}}
            @if($tinTuc->tags->count() > 0)
            <div class="mt-10 flex flex-wrap items-center gap-3">
                <span class="text-sm font-bold text-gray-500">
                    <i class="fa-solid fa-tags mr-2 text-[#d99a32]"></i>Tags:
                </span>
                @foreach($tinTuc->tags as $tag)
                <a href="{{ route('user.tin-tuc.index', ['tag' => $tag->slug]) }}" class="tag-btn">
                    #{{ $tag->ten_tag }}
                </a>
                @endforeach
            </div>
            @endif

            {{-- SHARE --}}
            <div class="mt-10 py-6 border-t border-b border-white/10">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <span class="text-sm font-bold text-gray-500">Chia sẻ bài viết:</span>
                    <div class="flex items-center gap-3">
                        <button onclick="shareSocial('facebook')" class="share-btn hover:bg-blue-500/20 hover:border-blue-500/50 hover:text-blue-400">
                            <i class="fa-brands fa-facebook-f"></i>
                        </button>
                        <button onclick="shareSocial('twitter')" class="share-btn hover:bg-sky-500/20 hover:border-sky-500/50 hover:text-sky-400">
                            <i class="fa-brands fa-twitter"></i>
                        </button>
                        <button onclick="shareSocial('zalo')" class="share-btn hover:bg-blue-500/20 hover:border-blue-500/50 hover:text-blue-400">
                            <i class="fa-solid fa-comment"></i>
                        </button>
                        <button onclick="copyLink()" class="share-btn hover:bg-[#d99a32]/20 hover:border-[#d99a32]/50 hover:text-[#d99a32]">
                            <i class="fa-solid fa-link"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </article>

    {{-- VOUCHERS --}}
    @if($vouchers->count() > 0)
    <section class="bg-[#0f0f0f] border-t border-white/5 py-12">
        <div class="container mx-auto px-4 md:px-6">
            <div class="mb-8 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-8 bg-gradient-to-b from-purple-500 to-purple-700 rounded-full"></div>
                    <h2 class="text-2xl font-bold">Voucher đang áp dụng</h2>
                </div>
                <a href="{{ route('user.khuyen-mai.index') }}"
                    class="text-sm font-semibold text-purple-400 hover:text-purple-300 transition-colors">
                    Xem tất cả <i class="fa-solid fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($vouchers as $voucher)
                <div class="voucher-card p-5">
                    <span class="px-3 py-1 rounded-lg bg-purple-500/20 border border-purple-500/30 text-purple-400 text-xs font-bold">
                        <i class="fa-solid fa-ticket mr-1"></i>{{ $voucher->ma_voucher }}
                    </span>

                    <h4 class="mt-3 text-sm font-bold text-white line-clamp-2">
                        {{ $voucher->ten_voucher }}
                    </h4>

                    <div class="mt-3 p-3 rounded-xl bg-black/30 border border-white/10 text-center">
                        <p class="text-xl font-black text-purple-400">
                            {{ number_format($voucher->gia_tri_giam, 0, ',', '.') }}đ
                        </p>
                    </div>

                    @if($voucher->ngay_het_han)
                    <p class="mt-2 text-xs text-gray-500">
                        Hạn: {{ \Carbon\Carbon::parse($voucher->ngay_het_han)->format('d/m/Y') }}
                    </p>
                    @endif

                    <button onclick="suDungVoucher({{ $voucher->id }})"
                        class="mt-4 w-full py-2.5 rounded-xl border border-purple-500/50 bg-purple-500/20 text-purple-400 text-sm font-bold hover:bg-purple-500/30 hover:text-white transition-all">
                        <i class="fa-solid fa-bolt mr-2"></i>Sử dụng ngay
                    </button>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- RELATED NEWS --}}
    @if($tinLienQuan->count() > 0)
    <section class="container mx-auto px-4 md:px-6 py-12">
        <div class="mb-8 flex items-center gap-3">
            <div class="w-1 h-8 bg-gradient-to-b from-[#d99a32] to-[#8a4a21] rounded-full"></div>
            <h2 class="text-2xl font-bold">Tin liên quan</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($tinLienQuan as $tin)
            <a href="{{ route('user.tin-tuc.show', $tin->slug) }}" class="related-card group">
                <div class="image-wrapper">
                    @if($tin->hinh_anh && file_exists(public_path('storage/' . $tin->hinh_anh)))
                        <img src="{{ asset('storage/' . $tin->hinh_anh) }}" alt="{{ $tin->tieu_de }}">
                    @else
                        <img src="https://images.unsplash.com/photo-1536440136628-849c177e76a1?w=400&q=80" alt="{{ $tin->tieu_de }}">
                    @endif
                </div>
                <div class="p-4">
                    <h4 class="text-sm font-bold text-white line-clamp-2 group-hover:text-[#d99a32] transition-colors">
                        {{ $tin->tieu_de }}
                    </h4>
                    <p class="mt-2 text-xs text-gray-500">
                        {{ $tin->ngay_dang ? $tin->ngay_dang->format('d/m/Y') : now()->format('d/m/Y') }}
                    </p>
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif

</div>
@endsection
