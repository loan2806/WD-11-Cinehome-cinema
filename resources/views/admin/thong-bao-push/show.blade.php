@extends('layouts.admin')

@section('page-title', 'Chi tiết thông báo đẩy')

@section('content')

<div class="admin-panel max-w-3xl mx-auto">

    {{-- HEADER --}}
    <div class="panel-header flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.thong-bao-push.index') }}"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-gray-400 transition-all hover:bg-white/10 hover:text-white">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h5 class="text-xl font-bold text-white flex items-center gap-3">
                    Chi tiết thông báo đẩy
                    <span class="rounded-lg bg-white/10 px-2.5 py-1 text-sm font-mono text-gray-400">#{{ $thongBaoPush->id }}</span>
                </h5>
                <p class="text-sm text-gray-500">
                    Thông tin chi tiết thông báo
                </p>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT CARD --}}
    <div class="mt-5 rounded-2xl border border-white/10 bg-gradient-to-br from-[#1a1a1a] to-[#151515] overflow-hidden">
        
        {{-- Header Badge --}}
        <div class="flex items-center justify-between border-b border-white/10 bg-white/[0.02] px-6 py-4">
            <div class="flex items-center gap-3">
                @php
                    $badgeClass = match ($thongBaoPush->loai) {
                        'info' => 'bg-blue-500/20 text-blue-400 border border-blue-500/30',
                        'success' => 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30',
                        'warning' => 'bg-amber-500/20 text-amber-400 border border-amber-500/30',
                        'error' => 'bg-red-500/20 text-red-400 border border-red-500/30',
                        default => 'bg-gray-500/20 text-gray-400 border border-gray-500/30',
                    };
                    $icon = match ($thongBaoPush->loai) {
                        'info' => 'fa-info-circle',
                        'success' => 'fa-circle-check',
                        'warning' => 'fa-triangle-exclamation',
                        'error' => 'fa-circle-xmark',
                        default => 'fa-bell',
                    };
                @endphp
                <span class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-bold {{ $badgeClass }}">
                    <i class="fa-solid {{ $icon }}"></i>
                    {{ ucfirst($thongBaoPush->loai) }}
                </span>
                
                @if ($thongBaoPush->trang_thai === 'da_gui')
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-bold text-emerald-400 border border-emerald-500/20">
                        <i class="fa-solid fa-paper-plane text-[10px]"></i>
                        Đã gửi
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/15 px-3 py-1 text-xs font-bold text-amber-400 border border-amber-500/20">
                        <i class="fa-solid fa-clock text-[10px]"></i>
                        Chưa gửi
                    </span>
                @endif
            </div>
            <div class="text-xs text-gray-500">
                <i class="fa-regular fa-calendar mr-1"></i>
                {{ $thongBaoPush->created_at->format('d/m/Y H:i') }}
            </div>
        </div>

        {{-- Content --}}
        <div class="p-6">
            {{-- Title --}}
            <div class="mb-6">
                <h3 class="text-xl font-bold text-white leading-tight">
                    {{ $thongBaoPush->tieu_de }}
                </h3>
            </div>

            {{-- Info Grid --}}
            <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-3">
                <div class="rounded-xl bg-white/5 p-4">
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <i class="fa-solid fa-user mr-1"></i> Người tạo
                    </label>
                    <p class="mt-1 font-medium text-white">
                        {{ $thongBaoPush->nguoiTao->ho_ten ?? 'Hệ thống' }}
                    </p>
                </div>
                <div class="rounded-xl bg-white/5 p-4">
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <i class="fa-solid fa-users mr-1"></i> Đối tượng
                    </label>
                    <p class="mt-1 font-medium text-white">
                        @switch($thongBaoPush->doi_tuong_nhan)
                            @case('all') Tất cả @break
                            @case('khach_hang') Khách hàng @break
                            @case('nhan_vien') Nhân viên @break
                            @case('quan_tri_vien') Quản trị @break
                            @case('nguoi_dung_cu_the') Cụ thể @break
                            @default {{ $thongBaoPush->doi_tuong_nhan }}
                        @endswitch
                    </p>
                </div>
                @if ($thongBaoPush->thoi_gian_gui)
                <div class="rounded-xl bg-white/5 p-4">
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <i class="fa-solid fa-clock mr-1"></i> Thời gian gửi
                    </label>
                    <p class="mt-1 font-medium text-white">
                        {{ $thongBaoPush->thoi_gian_gui->format('d/m/Y H:i') }}
                    </p>
                </div>
                @endif
            </div>

            {{-- Nội dung --}}
            <div class="rounded-xl border border-white/10 bg-[#111] p-5">
                <label class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500">
                    <i class="fa-solid fa-align-left text-[#d99a32]"></i>
                    Nội dung thông báo
                </label>
                <div class="text-sm leading-relaxed text-gray-300 whitespace-pre-line">
                    {{ $thongBaoPush->noi_dung }}
                </div>
            </div>
        </div>
    </div>

    {{-- NGƯỜI NHẬN (nếu có) --}}
    @if ($thongBaoPush->doi_tuong_nhan === 'nguoi_dung_cu_the' && $nguoiNhanList->count() > 0)
        <div class="mt-5 rounded-2xl border border-white/10 bg-gradient-to-br from-[#1a1a1a] to-[#151515] overflow-hidden">
            <div class="flex items-center gap-3 border-b border-white/10 bg-white/[0.02] px-6 py-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#d99a32]/20">
                    <i class="fa-solid fa-users text-[#d99a32]"></i>
                </div>
                <div>
                    <h6 class="font-bold text-white">Danh sách người nhận</h6>
                    <p class="text-xs text-gray-500">{{ $nguoiNhanList->count() }} người dùng</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-6 py-3 font-semibold">ID</th>
                            <th class="px-6 py-3 font-semibold">Họ tên</th>
                            <th class="px-6 py-3 font-semibold">Email</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-gray-300">
                        @foreach ($nguoiNhanList as $nguoiDung)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-3">
                                    <span class="font-mono text-xs text-gray-500">#{{ $nguoiDung->id }}</span>
                                </td>
                                <td class="px-6 py-3 font-medium">{{ $nguoiDung->ho_ten }}</td>
                                <td class="px-6 py-3 text-gray-400">{{ $nguoiDung->email }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ACTION BAR --}}
    <div class="mt-5 flex items-center justify-between gap-4">
        <a href="{{ route('admin.thong-bao-push.index') }}"
            class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-gray-400 transition-all hover:bg-white/10 hover:text-white">
            <i class="fa-solid fa-list"></i>
            Danh sách thông báo
        </a>
        <form action="{{ route('admin.thong-bao-push.destroy', $thongBaoPush) }}"
            method="POST"
            class="inline-block"
            onsubmit="return confirm('Bạn có chắc chắn muốn xóa thông báo này không?');">
            @csrf
            @method('DELETE')
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-2.5 text-sm font-semibold text-red-400 transition-all hover:bg-red-500/20">
                <i class="fa-solid fa-trash"></i>
                Xóa thông báo
            </button>
        </form>
    </div>

</div>

@endsection
