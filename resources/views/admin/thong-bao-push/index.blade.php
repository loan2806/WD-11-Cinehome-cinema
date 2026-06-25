@extends('layouts.admin')

@section('page-title', 'Quản lý Thông báo đẩy')

@section('content')

<div class="admin-panel">

    {{-- HEADER --}}
    <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h5 class="text-2xl font-black text-white">
                Danh sách thông báo đẩy
            </h5>
            <small class="text-gray-400">
                Quản lý và theo dõi các thông báo đẩy trong hệ thống
            </small>
        </div>
        <a href="{{ route('admin.thong-bao-push.create') }}"
            class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">
            <i class="fa-solid fa-plus"></i>
            Tạo thông báo mới
        </a>
    </div>

    {{-- FILTER --}}
    <form method="GET" action="{{ route('admin.thong-bao-push.index') }}" class="mt-6 flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Tìm kiếm theo tiêu đề..."
                class="h-12 w-full rounded-2xl border border-white/10 bg-[#151515] px-4 text-white outline-none focus:border-[#d99a32]">
        </div>

        <select name="loai"
            class="h-12 rounded-2xl border border-white/10 bg-[#151515] px-4 text-white outline-none focus:border-[#d99a32]">
            <option value="">-- Loại thông báo --</option>
            <option value="info" {{ request('loai') == 'info' ? 'selected' : '' }}>
                Thông tin (Info)
            </option>
            <option value="success" {{ request('loai') == 'success' ? 'selected' : '' }}>
                Thành công (Success)
            </option>
            <option value="warning" {{ request('loai') == 'warning' ? 'selected' : '' }}>
                Cảnh báo (Warning)
            </option>
            <option value="error" {{ request('loai') == 'error' ? 'selected' : '' }}>
                Lỗi (Error)
            </option>
        </select>

        <select name="trang_thai"
            class="h-12 rounded-2xl border border-white/10 bg-[#151515] px-4 text-white outline-none focus:border-[#d99a32]">
            <option value="">-- Trạng thái --</option>
            <option value="da_gui" {{ request('trang_thai') == 'da_gui' ? 'selected' : '' }}>
                Đã gửi
            </option>
            <option value="chua_gui" {{ request('trang_thai') == 'chua_gui' ? 'selected' : '' }}>
                Chưa gửi
            </option>
        </select>

        <button type="submit"
            class="h-12 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 text-sm font-bold text-white shadow-lg transition hover:opacity-90">
            <i class="fa-solid fa-filter mr-1"></i>
            Lọc
        </button>

        <a href="{{ route('admin.thong-bao-push.index') }}"
            class="flex h-12 items-center rounded-2xl border border-white/10 bg-white/5 px-5 text-sm font-bold text-white transition hover:bg-white/10">
            Reset
        </a>
    </form>

    {{-- TABLE --}}
    <div class="mt-6 overflow-hidden rounded-3xl border border-white/10">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left">
                <thead class="bg-white/5 text-xs uppercase tracking-wider text-gray-400">
                    <tr>
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Tiêu đề</th>
                        <th class="px-6 py-4">Loại</th>
                        <th class="px-6 py-4">Người tạo</th>
                        <th class="px-6 py-4">Ngày tạo</th>
                        <th class="px-6 py-4">Trạng thái</th>
                        <th class="px-6 py-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-sm text-gray-300">
                    @forelse ($thongBaos as $thongBao)
                        <tr class="hover:bg-white/5 transition">
                            <td class="px-6 py-4 font-mono text-xs">{{ $thongBao->id }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.thong-bao-push.show', $thongBao) }}"
                                    class="font-semibold text-[#d99a32] hover:underline">
                                    {{ \Illuminate\Support\Str::limit($thongBao->tieu_de, 50) }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $badgeClass = match ($thongBao->loai) {
                                        'info' => 'bg-blue-500/20 text-blue-400',
                                        'success' => 'bg-emerald-500/20 text-emerald-400',
                                        'warning' => 'bg-amber-500/20 text-amber-400',
                                        'error' => 'bg-red-500/20 text-red-400',
                                        default => 'bg-gray-500/20 text-gray-400',
                                    };
                                    $label = match ($thongBao->loai) {
                                        'info' => 'Info',
                                        'success' => 'Success',
                                        'warning' => 'Warning',
                                        'error' => 'Error',
                                        default => $thongBao->loai,
                                    };
                                @endphp
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $badgeClass }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                {{ $thongBao->nguoiTao->ho_ten ?? 'Hệ thống' }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $thongBao->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($thongBao->trang_thai === 'da_gui')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-bold text-emerald-400">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i>
                                        Đã gửi
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/20 px-3 py-1 text-xs font-bold text-amber-400">
                                        <i class="fa-solid fa-clock text-[10px]"></i>
                                        Chưa gửi
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.thong-bao-push.show', $thongBao) }}"
                                        class="rounded-xl border border-white/10 bg-white/5 p-2 text-gray-400 transition hover:border-[#d99a32] hover:text-[#d99a32]"
                                        title="Xem chi tiết">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.thong-bao-push.destroy', $thongBao) }}"
                                        method="POST"
                                        class="inline-block"
                                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa thông báo này không? Thao tác này không thể hoàn tác.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="rounded-xl border border-white/10 bg-white/5 p-2 text-gray-400 transition hover:border-red-500/50 hover:text-red-400"
                                            title="Xóa">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <i class="fa-solid fa-inbox text-4xl mb-3 block opacity-30"></i>
                                Chưa có thông báo đẩy nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PAGINATION --}}
    @if ($thongBaos->hasPages())
        <div class="mt-6 flex flex-col items-center justify-between gap-4">
            <div class="text-sm text-gray-500">
                Hiển thị {{ $thongBaos->firstItem() }} - {{ $thongBaos->lastItem() }}
                trong tổng {{ $thongBaos->total() }} thông báo
            </div>
            <div class="flex gap-2">
                @if ($thongBaos->onFirstPage())
                    <span class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-bold text-gray-500">
                        Trước
                    </span>
                @else
                    <a href="{{ $thongBaos->previousPageUrl() }}"
                        class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-bold text-gray-300 transition hover:bg-white/10">
                        Trước
                    </a>
                @endif
                @if ($thongBaos->hasMorePages())
                    <a href="{{ $thongBaos->nextPageUrl() }}"
                        class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-bold text-gray-300 transition hover:bg-white/10">
                        Sau
                    </a>
                @else
                    <span class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-bold text-gray-500">
                        Sau
                    </span>
                @endif
            </div>
        </div>
    @endif

</div>

@endsection
