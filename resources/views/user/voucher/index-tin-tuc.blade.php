@extends('layouts.user')

@section('title', 'Khuyến mãi')

@push('styles')
<style>
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
            <span class="inline-block px-4 py-1.5 rounded-full border border-purple-500/30 bg-purple-500/10 text-purple-400 text-xs font-bold uppercase tracking-widest mb-4">
                <i class="fa-solid fa-ticket mr-2"></i>Ưu đãi đặc biệt
            </span>
            <h1 class="text-4xl md:text-5xl font-black mb-4">
                <span class="bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">Khuyến mãi</span>
                <span class="text-white"> hấp dẫn</span>
            </h1>
            <p class="text-gray-400 max-w-2xl mx-auto">
                Cập nhật những ưu đãi và voucher hot nhất từ CineHome Cinema
            </p>
        </div>

        {{-- STATS --}}
        <div class="grid grid-cols-3 gap-4 max-w-2xl mx-auto mb-12">
            <div class="rounded-xl border border-purple-500/20 bg-purple-500/10 p-4 text-center">
                <div class="text-2xl font-black text-purple-400">{{ $vouchers->total() }}</div>
                <div class="text-xs text-gray-500 mt-1">Voucher</div>
            </div>
            <div class="rounded-xl border border-pink-500/20 bg-pink-500/10 p-4 text-center">
                <div class="text-2xl font-black text-pink-400">{{ $vouchers->where('gia_tri_giam', '>=', 50000)->count() }}</div>
                <div class="text-xs text-gray-500 mt-1">Giảm 50k+</div>
            </div>
            <div class="rounded-xl border border-indigo-500/20 bg-indigo-500/10 p-4 text-center">
                <div class="text-2xl font-black text-indigo-400">{{ $vouchers->count() }}</div>
                <div class="text-xs text-gray-500 mt-1">Đang hoạt động</div>
            </div>
        </div>

        {{-- VOUCHERS GRID --}}
        @if($vouchers->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($vouchers as $voucher)
            <div class="voucher-card p-5">
                {{-- Header --}}
                <div class="flex items-start justify-between mb-3">
                    <span class="px-3 py-1 rounded-lg bg-purple-500/20 border border-purple-500/30 text-purple-400 text-xs font-bold">
                        <i class="fa-solid fa-ticket mr-1"></i>{{ $voucher->ma_voucher }}
                    </span>
                    @if($voucher->ngay_het_han)
                        @php
                            $daysLeft = now()->diffInDays($voucher->ngay_het_han, false);
                        @endphp
                        @if($daysLeft <= 7 && $daysLeft > 0)
                            <span class="px-2 py-1 rounded-lg bg-red-500/20 border border-red-500/30 text-red-400 text-xs font-bold">
                                <i class="fa-solid fa-clock mr-1"></i>{{ floor($daysLeft) }} ngày
                            </span>
                        @elseif($daysLeft <= 0)
                            <span class="px-2 py-1 rounded-lg bg-gray-500/20 border border-gray-500/30 text-gray-400 text-xs font-bold">
                                Hết hạn
                            </span>
                        @endif
                    @endif
                </div>

                {{-- Title --}}
                <h3 class="text-base font-bold text-white line-clamp-2 mb-3">
                    {{ $voucher->ten_voucher }}
                </h3>

                {{-- Discount Value --}}
                <div class="p-3 rounded-xl bg-black/30 border border-purple-500/20 text-center mb-3">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Giảm</p>
                    <p class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400">
                        {{ number_format($voucher->gia_tri_giam, 0, ',', '.') }}đ
                    </p>
                </div>

                {{-- Meta Info --}}
                <div class="space-y-2 text-xs text-gray-500 mb-4">
                    @if($voucher->dieu_kien)
                    <div class="flex items-start gap-2">
                        <i class="fa-solid fa-list-check text-purple-400 mt-0.5"></i>
                        <span>{{ $voucher->dieu_kien }}</span>
                    </div>
                    @endif
                    @if($voucher->ngay_het_han)
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-calendar-xmark text-purple-400"></i>
                        <span>HSD: {{ \Carbon\Carbon::parse($voucher->ngay_het_han)->format('d/m/Y') }}</span>
                    </div>
                    @endif
                    @if($voucher->diem_can_doi > 0)
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-coins text-yellow-400"></i>
                        <span>{{ number_format($voucher->diem_can_doi) }} điểm</span>
                    </div>
                    @endif
                </div>

                {{-- CTA Button --}}
                @if($voucher->ngay_het_han && now()->diffInDays($voucher->ngay_het_han, false) <= 0)
                    <button disabled
                        class="w-full py-2.5 rounded-xl border border-gray-500/30 bg-gray-500/10 text-gray-500 text-sm font-bold cursor-not-allowed">
                        <i class="fa-solid fa-ban mr-2"></i>Đã hết hạn
                    </button>
                @else
                    <button onclick="suDungVoucher({{ $voucher->id }})"
                        class="w-full py-2.5 rounded-xl border border-purple-500/50 bg-purple-500/20 text-purple-400 text-sm font-bold hover:bg-purple-500/30 hover:text-white transition-all">
                        <i class="fa-solid fa-bolt mr-2"></i>Sử dụng ngay
                    </button>
                @endif
            </div>
            @endforeach
        </div>

        {{-- PAGINATION --}}
        @if($vouchers->hasPages())
        <div class="mt-10 flex justify-center">
            <div class="flex items-center gap-2">
                @if($vouchers->onFirstPage())
                    <span class="pagination-btn border border-white/10 bg-white/5 text-gray-600">
                        <i class="fa-solid fa-chevron-left"></i>
                    </span>
                @else
                    <a href="{{ $vouchers->previousPageUrl() }}" class="pagination-btn border border-white/10 bg-white/5 text-gray-400 hover:bg-white/10">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                @endif

                @foreach($vouchers->getUrlRange(max(1, $vouchers->currentPage() - 1), min($vouchers->lastPage(), $vouchers->currentPage() + 1)) as $page => $url)
                    @if($page == $vouchers->currentPage())
                        <span class="pagination-btn bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" class="pagination-btn border border-white/10 bg-white/5 text-gray-400 hover:bg-white/10">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                @if($vouchers->hasMorePages())
                    <a href="{{ $vouchers->nextPageUrl() }}" class="pagination-btn border border-white/10 bg-white/5 text-gray-400 hover:bg-white/10">
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
        {{-- Empty State --}}
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <div class="w-20 h-20 rounded-2xl bg-white/5 flex items-center justify-center mb-4">
                <i class="fa-solid fa-ticket text-4xl text-gray-600"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-400">Chưa có voucher nào</h3>
            <p class="mt-2 text-sm text-gray-600">Hãy quay lại sau để cập nhật những ưu đãi mới!</p>
        </div>
        @endif

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
