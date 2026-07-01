@extends('layouts.admin')

@section('page-title', 'Quản lý Thông báo đẩy')

@section('content')

<div class="admin-panel">

    {{-- HEADER --}}
    <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-[#8a4a21] to-[#d99a32] shadow-lg shadow-[#d99a32]/20">
                <i class="fa-solid fa-bell text-2xl text-white"></i>
            </div>
            <div>
                <h5 class="text-xl font-bold text-white">
                    Danh sách thông báo đẩy
                </h5>
                <p class="text-sm text-gray-500">
                    Quản lý và theo dõi các thông báo đẩy trong hệ thống
                </p>
            </div>
        </div>
        <a href="{{ route('admin.thong-bao-push.create') }}"
            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-4 py-2.5 text-sm font-bold text-white shadow-lg transition-all hover:scale-[1.02] hover:shadow-[#d99a32]/30">
            <i class="fa-solid fa-plus"></i>
            Tạo thông báo mới
        </a>
    </div>

    {{-- FILTER --}}
    <form method="GET" action="{{ route('admin.thong-bao-push.index') }}" class="mt-5 flex flex-wrap items-end gap-3">
        <div class="min-w-[200px] flex-1">
            <label class="mb-1.5 block text-xs font-semibold text-gray-400">Tìm kiếm</label>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Tìm kiếm theo tiêu đề..."
                class="h-10 w-full rounded-xl border border-white/10 bg-[#151515] px-4 text-sm text-white outline-none focus:border-[#d99a32] transition-colors">
        </div>

        <div class="min-w-[150px]">
            <label class="mb-1.5 block text-xs font-semibold text-gray-400">Loại thông báo</label>
            <select name="loai"
                class="h-10 w-full rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition-colors">
                <option value="">Tất cả</option>
                <option value="info" {{ request('loai') == 'info' ? 'selected' : '' }}>Thông tin</option>
                <option value="success" {{ request('loai') == 'success' ? 'selected' : '' }}>Thành công</option>
                <option value="warning" {{ request('loai') == 'warning' ? 'selected' : '' }}>Cảnh báo</option>
                <option value="danger" {{ request('loai') == 'danger' ? 'selected' : '' }}>Lỗi</option>
            </select>
        </div>

        <div class="min-w-[140px]">
            <label class="mb-1.5 block text-xs font-semibold text-gray-400">Trạng thái</label>
            <select name="trang_thai"
                class="h-10 w-full rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition-colors">
                <option value="">Tất cả</option>
                <option value="da_gui" {{ request('trang_thai') == 'da_gui' ? 'selected' : '' }}>Đã gửi</option>
                <option value="chua_gui" {{ request('trang_thai') == 'chua_gui' ? 'selected' : '' }}>Chưa gửi</option>
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit"
                class="h-10 rounded-xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-4 text-sm font-bold text-white shadow-lg transition-all hover:opacity-90">
                <i class="fa-solid fa-filter mr-1"></i>
                Lọc
            </button>
            @if(request()->has('search') || request()->has('loai') || request()->has('trang_thai'))
                <a href="{{ route('admin.thong-bao-push.index') }}"
                    class="flex h-10 items-center rounded-xl border border-white/10 bg-white/5 px-4 text-sm font-semibold text-gray-400 transition-all hover:bg-white/10 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </div>
    </form>

    {{-- TABLE --}}
    <div class="mt-5 overflow-hidden rounded-2xl border border-white/10">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gradient-to-r from-[#1a1a1a] to-[#151515] text-xs uppercase tracking-wider text-gray-400">
                    <tr>
                        <th class="px-4 py-3 font-semibold">ID</th>
                        <th class="px-4 py-3 font-semibold">Tiêu đề</th>
                        <th class="px-4 py-3 font-semibold">Loại</th>
                        <th class="px-4 py-3 font-semibold">Người tạo</th>
                        <th class="px-4 py-3 font-semibold">Ngày tạo</th>
                        <th class="px-4 py-3 font-semibold">Trạng thái</th>
                        <th class="px-4 py-3 text-right font-semibold">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-sm text-gray-300">
                    @forelse ($thongBaos as $thongBao)
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3.5">
                                <span class="font-mono text-xs text-gray-500">#{{ $thongBao->id }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                <a href="{{ route('admin.thong-bao-push.show', $thongBao) }}"
                                    class="font-medium text-[#d99a32] hover:underline">
                                    {{ \Illuminate\Support\Str::limit($thongBao->tieu_de, 40) }}
                                </a>
                            </td>
                            <td class="px-4 py-3.5">
                                @php
                                    $loaiLabels = [
                                        'info' => 'Thông tin',
                                        'success' => 'Thành công',
                                        'warning' => 'Cảnh báo',
                                        'danger' => 'Lỗi',
                                    ];
                                    $badgeClass = match ($thongBao->loai) {
                                        'info' => 'bg-blue-500/20 text-blue-400 border border-blue-500/30',
                                        'success' => 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30',
                                        'warning' => 'bg-amber-500/20 text-amber-400 border border-amber-500/30',
                                        'danger' => 'bg-red-500/20 text-red-400 border border-red-500/30',
                                        default => 'bg-gray-500/20 text-gray-400 border border-gray-500/30',
                                    };
                                    $icon = match ($thongBao->loai) {
                                        'info' => 'fa-info-circle',
                                        'success' => 'fa-circle-check',
                                        'warning' => 'fa-triangle-exclamation',
                                        'danger' => 'fa-circle-xmark',
                                        default => 'fa-bell',
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-bold {{ $badgeClass }}">
                                    <i class="fa-solid {{ $icon }} text-[10px]"></i>
                                    {{ $loaiLabels[$thongBao->loai] ?? $thongBao->loai }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-[#8a4a21]/50 to-[#d99a32]/50 text-[10px] font-bold text-white">
                                        {{ substr($thongBao->nguoiTao->ho_ten ?? 'H', 0, 1) }}
                                    </div>
                                    <span class="text-sm">{{ $thongBao->nguoiTao->ho_ten ?? 'Hệ thống' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="text-sm text-gray-400">{{ $thongBao->created_at->format('d/m/Y') }}</span>
                                <span class="ml-1 text-xs text-gray-600">{{ $thongBao->created_at->format('H:i') }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                @if ($thongBao->trang_thai === 'da_gui')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 px-2.5 py-1 text-xs font-bold text-emerald-400 border border-emerald-500/20">
                                        <i class="fa-solid fa-paper-plane text-[10px]"></i>
                                        Đã gửi
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/15 px-2.5 py-1 text-xs font-bold text-amber-400 border border-amber-500/20">
                                        <i class="fa-solid fa-clock text-[10px]"></i>
                                        Chưa gửi
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.thong-bao-push.show', $thongBao) }}"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg border border-white/10 bg-white/5 text-gray-400 transition-all hover:border-[#d99a32] hover:text-[#d99a32]"
                                        title="Xem chi tiết">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.thong-bao-push.destroy', $thongBao) }}"
                                        method="POST"
                                        class="inline-block"
                                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa thông báo này không?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-white/10 bg-white/5 text-gray-400 transition-all hover:border-red-500/50 hover:text-red-400"
                                            title="Xóa">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/5 mb-4">
                                        <i class="fa-solid fa-bell-slash text-3xl text-gray-600"></i>
                                    </div>
                                    <p class="text-gray-400 font-medium">Chưa có thông báo đẩy nào</p>
                                    <a href="{{ route('admin.thong-bao-push.create') }}" class="mt-3 text-sm text-[#d99a32] hover:underline">
                                        Tạo thông báo đầu tiên
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PAGINATION --}}
    @if ($thongBaos->hasPages())
        <div class="mt-5 flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Hiển thị <span class="font-semibold text-gray-400">{{ $thongBaos->firstItem() ?? 0 }}</span> - 
                <span class="font-semibold text-gray-400">{{ $thongBaos->lastItem() ?? 0 }}</span> 
                trong <span class="font-semibold text-gray-400">{{ $thongBaos->total() }}</span> thông báo
            </div>
            <div class="flex items-center gap-1">
                @if ($thongBaos->onFirstPage())
                    <span class="flex h-9 items-center rounded-lg border border-white/10 bg-white/5 px-3 text-sm font-medium text-gray-500">
                        <i class="fa-solid fa-chevron-left mr-1 text-xs"></i> Trước
                    </span>
                @else
                    <a href="{{ $thongBaos->previousPageUrl() }}"
                    class="flex h-9 items-center rounded-lg border border-white/10 bg-white/5 px-3 text-sm font-medium text-gray-300 transition-all hover:bg-white/10">
                        <i class="fa-solid fa-chevron-left mr-1 text-xs"></i> Trước
                    </a>
                @endif
                
                @foreach ($thongBaos->getUrlRange(max(1, $thongBaos->currentPage() - 1), min($thongBaos->lastPage(), $thongBaos->currentPage() + 1)) as $page => $url)
                    @if ($page == $thongBaos->currentPage())
                        <span class="flex h-9 items-center rounded-lg bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-3 text-sm font-bold text-white">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                        class="flex h-9 items-center rounded-lg border border-white/10 bg-white/5 px-3 text-sm font-medium text-gray-300 transition-all hover:bg-white/10">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
                
                @if ($thongBaos->hasMorePages())
                    <a href="{{ $thongBaos->nextPageUrl() }}"
                    class="flex h-9 items-center rounded-lg border border-white/10 bg-white/5 px-3 text-sm font-medium text-gray-300 transition-all hover:bg-white/10">
                        Sau <i class="fa-solid fa-chevron-right ml-1 text-xs"></i>
                    </a>
                @else
                    <span class="flex h-9 items-center rounded-lg border border-white/10 bg-white/5 px-3 text-sm font-medium text-gray-500">
                        Sau <i class="fa-solid fa-chevron-right ml-1 text-xs"></i>
                    </span>
                @endif
            </div>
        </div>
    @endif

</div>

@endsection
