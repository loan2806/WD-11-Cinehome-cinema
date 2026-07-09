@extends('layouts.user')

@section('title', 'Tin tức & Khuyến mãi')

@push('styles')
<style>
    .news-card {
        background: #151515;
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .news-card:hover {
        border-color: rgba(217, 154, 50, 0.3);
        box-shadow: 0 8px 30px rgba(0,0,0,0.3);
        transform: translateY(-4px);
    }
    .news-card .image-wrapper {
        position: relative;
        aspect-ratio: 16/10;
        overflow: hidden;
    }
    .news-card .image-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .news-card:hover .image-wrapper img {
        transform: scale(1.05);
    }
    .category-badge {
        background: rgba(0,0,0,0.7);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(217, 154, 50, 0.5);
    }
    .featured-overlay {
        background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.5) 50%, transparent 100%);
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
        box-shadow: 0 10px 30px rgba(139, 92, 246, 0.15);
    }
    .pagination-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-[#0a0a0a] text-white pt-24 pb-16">

    <div class="container mx-auto px-4 md:px-6">

        {{-- HEADER --}}
        <div class="text-center mb-12">
            <span class="inline-block px-4 py-1.5 rounded-full border border-[#d99a32]/30 bg-[#d99a32]/10 text-[#d99a32] text-xs font-bold uppercase tracking-widest mb-4">
                CineHome News
            </span>
            <h1 class="text-4xl md:text-5xl font-black mb-4">
                Tin tức & <span class="text-[#d99a32]">Khuyến mãi</span>
            </h1>
            <p class="text-gray-400 max-w-2xl mx-auto">
                Cập nhật những tin tức mới nhất về phim, sự kiện và ưu đãi hấp dẫn từ CineHome
            </p>
        </div>

        {{-- CATEGORIES --}}
        <div class="flex flex-wrap justify-center gap-2 mb-10">
            <a href="{{ route('user.tin-tuc.index') }}"
                class="pagination-btn px-5 py-2.5 text-sm font-semibold border transition-all
                {{ !request('danh_muc') ? 'border-[#d99a32] bg-[#d99a32]/20 text-[#d99a32]' : 'border-white/10 bg-white/5 text-gray-400 hover:border-white/30 hover:bg-white/10' }}">
                <i class="fa-solid fa-border-all mr-2"></i>Tất cả
            </a>
            @foreach($danhMucs as $danhMuc)
            <a href="{{ route('user.tin-tuc.index', ['danh_muc' => $danhMuc->slug]) }}"
                class="pagination-btn px-5 py-2.5 text-sm font-semibold border transition-all
                {{ request('danh_muc') === $danhMuc->slug ? 'border-[#d99a32] bg-[#d99a32]/20 text-[#d99a32]' : 'border-white/10 bg-white/5 text-gray-400 hover:border-white/30 hover:bg-white/10' }}">
                <i class="{{ $danhMuc->icon ?? 'fa-solid fa-tag' }} mr-2"></i>{{ $danhMuc->ten_danh_muc }}
            </a>
            @endforeach
            <a href="{{ route('user.khuyen-mai.index') }}"
                class="pagination-btn px-5 py-2.5 text-sm font-semibold border border-purple-500/30 bg-purple-500/10 text-purple-400 hover:bg-purple-500/20 transition-all">
                <i class="fa-solid fa-ticket-percent mr-2"></i>Voucher
            </a>
        </div>

        {{-- FEATURED NEWS --}}
        @if($tinNoiBat->count() > 0)
        <div class="mb-12">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-1 h-8 bg-gradient-to-b from-[#d99a32] to-[#8a4a21] rounded-full"></div>
                <h2 class="text-2xl font-bold">Tin nổi bật</h2>
                <span class="px-3 py-1 rounded-full border border-red-500/50 bg-red-500/20 text-red-400 text-xs font-bold">
                    <i class="fa-solid fa-fire mr-1"></i>HOT
                </span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($tinNoiBat as $key => $tin)
                @if($key === 0)
                {{-- HERO CARD --}}
                <a href="{{ route('user.tin-tuc.show', $tin->slug) }}"
                    class="relative rounded-2xl overflow-hidden group col-span-1 lg:row-span-1" style="min-height: 400px;">
                    {{-- Image --}}
                    <div class="absolute inset-0 z-0">
                        @if($tin->hinh_anh && file_exists(public_path('storage/' . $tin->hinh_anh)))
                            <img src="{{ asset('storage/' . $tin->hinh_anh) }}"
                                alt="{{ $tin->tieu_de }}"
                                class="w-full h-full object-cover">
                        @else
                            <img src="https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=800&q=80"
                                alt="{{ $tin->tieu_de }}"
                                class="w-full h-full object-cover">
                        @endif
                    </div>
                    {{-- Gradient Overlay --}}
                    <div class="absolute inset-0 z-10 featured-overlay"></div>
                    {{-- Content --}}
                    <div class="absolute bottom-0 left-0 right-0 z-20 p-6">
                        @if($tin->danhMucTin)
                        <span class="inline-block px-3 py-1 rounded-full bg-[#d99a32]/20 border border-[#d99a32]/50 text-[#d99a32] text-xs font-bold">
                            <i class="{{ $tin->danhMucTin->icon ?? 'fa-solid fa-tag' }} mr-1"></i>
                            {{ $tin->danhMucTin->ten_danh_muc }}
                        </span>
                        @endif
                        <h3 class="mt-3 text-xl lg:text-2xl font-bold text-white group-hover:text-[#d99a32] transition-colors line-clamp-2">
                            {{ $tin->tieu_de }}
                        </h3>
                        <p class="mt-2 text-sm text-gray-300 line-clamp-2">
                            {{ $tin->mo_ta_ngan }}
                        </p>
                        <div class="mt-4 flex items-center gap-4 text-xs text-gray-400">
                            <span><i class="fa-solid fa-eye mr-1"></i>{{ number_format($tin->luot_xem) }}</span>
                            <span><i class="fa-solid fa-calendar mr-1"></i>{{ $tin->ngay_dang ? $tin->ngay_dang->format('d/m/Y') : now()->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </a>
                @else
                {{-- SMALL CARD --}}
                <a href="{{ route('user.tin-tuc.show', $tin->slug) }}"
                    class="news-card group flex gap-4 p-4">
                    <div class="w-32 h-24 shrink-0 rounded-xl overflow-hidden">
                        @if($tin->hinh_anh && file_exists(public_path('storage/' . $tin->hinh_anh)))
                            <img src="{{ asset('storage/' . $tin->hinh_anh) }}"
                                alt="{{ $tin->tieu_de }}"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        @else
                            <img src="https://images.unsplash.com/photo-1536440136628-849c177e76a1?w=300&q=80"
                                alt="{{ $tin->tieu_de }}"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        @endif
                    </div>
                    <div class="flex flex-col justify-center flex-1 min-w-0">
                        @if($tin->danhMucTin)
                        <span class="text-xs font-bold text-[#d99a32]">
                            {{ $tin->danhMucTin->ten_danh_muc }}
                        </span>
                        @endif
                        <h3 class="mt-1 text-sm font-bold text-white line-clamp-2 group-hover:text-[#d99a32] transition-colors">
                            {{ $tin->tieu_de }}
                        </h3>
                        <p class="mt-1 text-xs text-gray-500">
                            {{ $tin->ngay_dang ? $tin->ngay_dang->format('d/m/Y') : now()->format('d/m/Y') }}
                        </p>
                    </div>
                </a>
                @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- VOUCHERS SECTION --}}
        @if($vouchers->count() > 0)
        <div class="mb-12">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-8 bg-gradient-to-b from-purple-500 to-purple-700 rounded-full"></div>
                    <h2 class="text-2xl font-bold">Voucher Hot</h2>
                </div>
                <a href="{{ route('user.khuyen-mai.index') }}"
                    class="text-sm font-semibold text-purple-400 hover:text-purple-300 transition-colors">
                    Xem tất cả <i class="fa-solid fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($vouchers as $voucher)
                <div class="voucher-card p-5">
                    <div class="flex items-start justify-between mb-3">
                        <span class="px-3 py-1 rounded-lg bg-purple-500/20 border border-purple-500/30 text-purple-400 text-xs font-bold">
                            <i class="fa-solid fa-ticket mr-1"></i>{{ $voucher->ma_voucher }}
                        </span>
                        @if($voucher->ngay_het_han)
                            @php $daysLeft = now()->diffInDays($voucher->ngay_het_han, false); @endphp
                            @if($daysLeft <= 7 && $daysLeft > 0)
                                <span class="px-2 py-1 rounded-lg bg-red-500/20 border border-red-500/30 text-red-400 text-xs font-bold">
                                    <i class="fa-solid fa-clock mr-1"></i>{{ floor($daysLeft) }} ngày
                                </span>
                            @endif
                        @endif
                    </div>

                    <h3 class="text-base font-bold text-white line-clamp-2">{{ $voucher->ten_voucher }}</h3>

                    <div class="mt-3 p-3 rounded-xl bg-black/30 border border-white/10 text-center">
                        <p class="text-xs text-gray-500">Giảm</p>
                        <p class="text-2xl font-black text-purple-400">{{ number_format($voucher->gia_tri_giam, 0, ',', '.') }}đ</p>
                    </div>

                    @if($voucher->ngay_het_han)
                    <p class="mt-3 text-xs text-gray-500">
                        <i class="fa-solid fa-calendar-xmark mr-1"></i>
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
        @endif

        {{-- ALL NEWS --}}
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-8 bg-gradient-to-b from-[#d99a32] to-[#8a4a21] rounded-full"></div>
                    <h2 class="text-2xl font-bold">Tin mới nhất</h2>
                </div>

                <form method="GET" action="{{ route('user.tin-tuc.index') }}" class="flex items-center gap-2">
                    @if(request('danh_muc'))
                        <input type="hidden" name="danh_muc" value="{{ request('danh_muc') }}">
                    @endif
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Tìm kiếm..."
                            class="h-11 w-48 md:w-64 pl-10 pr-4 rounded-xl border border-white/10 bg-[#151515] text-white text-sm placeholder-gray-500 outline-none focus:border-[#d99a32] transition-colors">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    </div>
                    <button type="submit"
                        class="h-11 px-5 rounded-xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white text-sm font-bold hover:opacity-90 transition-opacity">
                        Tìm
                    </button>
                </form>
            </div>

            @if($tinTucs->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($tinTucs as $tin)
                <a href="{{ route('user.tin-tuc.show', $tin->slug) }}" class="news-card group">
                    {{-- Image --}}
                    <div class="image-wrapper">
                        @if($tin->hinh_anh && file_exists(public_path('storage/' . $tin->hinh_anh)))
                            <img src="{{ asset('storage/' . $tin->hinh_anh) }}"
                                alt="{{ $tin->tieu_de }}">
                        @else
                            <img src="https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=600&q=80"
                                alt="{{ $tin->tieu_de }}">
                        @endif
                        @if($tin->danhMucTin)
                        <span class="category-badge absolute top-3 left-3 px-2 py-1 rounded-lg text-[10px] font-bold text-[#d99a32]">
                            {{ $tin->danhMucTin->ten_danh_muc }}
                        </span>
                        @endif
                    </div>
                    {{-- Content --}}
                    <div class="p-5">
                        <h3 class="text-base font-bold text-white line-clamp-2 group-hover:text-[#d99a32] transition-colors">
                            {{ $tin->tieu_de }}
                        </h3>
                        <p class="mt-2 text-sm text-gray-500 line-clamp-2">
                            {{ $tin->mo_ta_ngan }}
                        </p>
                        <div class="mt-4 flex items-center justify-between text-xs text-gray-500">
                            <span><i class="fa-solid fa-calendar mr-1"></i>{{ $tin->ngay_dang ? $tin->ngay_dang->format('d/m/Y') : now()->format('d/m/Y') }}</span>
                            <span><i class="fa-solid fa-eye mr-1"></i>{{ number_format($tin->luot_xem) }}</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>

            {{-- PAGINATION --}}
            @if($tinTucs->hasPages())
            <div class="mt-10 flex justify-center">
                <div class="flex items-center gap-2">
                    @if($tinTucs->onFirstPage())
                        <span class="pagination-btn border border-white/10 bg-white/5 text-gray-600">
                            <i class="fa-solid fa-chevron-left"></i>
                        </span>
                    @else
                        <a href="{{ $tinTucs->previousPageUrl() }}" class="pagination-btn border border-white/10 bg-white/5 text-gray-400 hover:bg-white/10">
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>
                    @endif

                    @foreach($tinTucs->getUrlRange(max(1, $tinTucs->currentPage() - 1), min($tinTucs->lastPage(), $tinTucs->currentPage() + 1)) as $page => $url)
                        @if($page == $tinTucs->currentPage())
                            <span class="pagination-btn bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white font-bold">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="pagination-btn border border-white/10 bg-white/5 text-gray-400 hover:bg-white/10">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    @if($tinTucs->hasMorePages())
                        <a href="{{ $tinTucs->nextPageUrl() }}" class="pagination-btn border border-white/10 bg-white/5 text-gray-400 hover:bg-white/10">
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    @else
                        <span class="pagination-btn border border-white/10 bg-white/5 text-gray-600">
                            <i class="fa-solid fa-chevron-right"></i>
                        </span>
                    @endif
                </div>
            </div>
            @endif

            @else
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-20 h-20 rounded-2xl bg-white/5 flex items-center justify-center mb-4">
                    <i class="fa-solid fa-newspaper text-4xl text-gray-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-400">Chưa có tin tức nào</h3>
                <p class="mt-2 text-sm text-gray-600">Hãy quay lại sau để cập nhật những tin tức mới nhất!</p>
            </div>
            @endif
        </div>

    </div>
</div>

@push('scripts')
<script>
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
@endsection
