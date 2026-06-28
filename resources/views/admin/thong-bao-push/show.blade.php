@extends('layouts.admin')

@section('page-title', 'Chi tiết thông báo đẩy')

@section('content')

<div class="admin-panel max-w-3xl">

    {{-- HEADER --}}
    <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h5 class="text-2xl font-black text-white">
                Chi tiết thông báo đẩy
            </h5>
            <small class="text-gray-400">
                Thông tin chi tiết thông báo #{{ $thongBaoPush->id }}
            </small>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.thong-bao-push.index') }}"
                class="rounded-2xl border border-white/10 bg-white/5 px-5 py-2.5 text-sm font-bold text-gray-300 transition hover:bg-white/10">
                <i class="fa-solid fa-arrow-left mr-1"></i>
                Quay lại
            </a>
        </div>
    </div>

    {{-- DETAIL CARD --}}
    <div class="mt-6 rounded-3xl border border-white/10 bg-white/5 p-6 space-y-5">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-gray-500">Tiêu đề</label>
                <p class="mt-1 text-white">{{ $thongBaoPush->tieu_de }}</p>
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-gray-500">Loại thông báo</label>
                <div class="mt-1">
                    @php
                        $badgeClass = match ($thongBaoPush->loai) {
                            'info' => 'bg-blue-500/20 text-blue-400',
                            'success' => 'bg-emerald-500/20 text-emerald-400',
                            'warning' => 'bg-amber-500/20 text-amber-400',
                            'error' => 'bg-red-500/20 text-red-400',
                            default => 'bg-gray-500/20 text-gray-400',
                        };
                        $label = match ($thongBaoPush->loai) {
                            'info' => 'Info',
                            'success' => 'Success',
                            'warning' => 'Warning',
                            'error' => 'Error',
                            default => $thongBaoPush->loai,
                        };
                    @endphp
                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $badgeClass }}">
                        {{ $label }}
                    </span>
                </div>
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-gray-500">Đối tượng nhận</label>
                <p class="mt-1 text-white">
                    @switch($thongBaoPush->doi_tuong_nhan)
                        @case('all') Tất cả người dùng @break
                        @case('khach_hang') Khách hàng @break
                        @case('nhan_vien') Nhân viên @break
                        @case('quan_tri_vien') Quản trị viên @break
                        @case('nguoi_dung_cu_the') Người dùng cụ thể @break
                        @default {{ $thongBaoPush->doi_tuong_nhan }}
                    @endswitch
                </p>
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-gray-500">Trạng thái</label>
                <div class="mt-1">
                    @if ($thongBaoPush->trang_thai === 'da_gui')
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
                </div>
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-gray-500">Người tạo</label>
                <p class="mt-1 text-white">{{ $thongBaoPush->nguoiTao->ho_ten ?? 'Hệ thống' }}</p>
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-gray-500">Ngày tạo</label>
                <p class="mt-1 text-white">{{ $thongBaoPush->created_at->format('d/m/Y H:i') }}</p>
            </div>
            @if ($thongBaoPush->thoi_gian_gui)
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-gray-500">Thời gian gửi</label>
                    <p class="mt-1 text-white">{{ $thongBaoPush->thoi_gian_gui->format('d/m/Y H:i') }}</p>
                </div>
            @endif
        </div>

        <div>
            <label class="text-xs font-bold uppercase tracking-wider text-gray-500">Nội dung</label>
            <div class="mt-2 rounded-2xl border border-white/10 bg-[#111] p-4 text-sm text-gray-300 whitespace-pre-line">
                {{ $thongBaoPush->noi_dung }}
            </div>
        </div>
    </div>

    {{-- DANH SÁCH NGƯỜI NHẬN --}}
    @if ($thongBaoPush->doi_tuong_nhan === 'nguoi_dung_cu_the' && $nguoiNhanList->count() > 0)
        <div class="mt-6 rounded-3xl border border-white/10 bg-white/5 p-6">
            <h6 class="mb-4 text-lg font-bold text-white">
                <i class="fa-solid fa-user mr-2 text-[#d99a32]"></i>
                Danh sách người nhận
            </h6>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-white/5 text-xs uppercase tracking-wider text-gray-400">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Họ tên</th>
                            <th class="px-4 py-3">Email</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-gray-300">
                        @foreach ($nguoiNhanList as $nguoiDung)
                            <tr class="hover:bg-white/5 transition">
                                <td class="px-4 py-3 font-mono text-xs">{{ $nguoiDung->id }}</td>
                                <td class="px-4 py-3">{{ $nguoiDung->ho_ten }}</td>
                                <td class="px-4 py-3">{{ $nguoiDung->email }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ACTION BAR --}}
    <div class="mt-6 flex items-center gap-3">
        <a href="{{ route('admin.thong-bao-push.index') }}"
            class="rounded-2xl border border-white/10 bg-white/5 px-5 py-2.5 text-sm font-bold text-gray-300 transition hover:bg-white/10">
            <i class="fa-solid fa-arrow-left mr-1"></i>
            Quay lại danh sách
        </a>
        <form action="{{ route('admin.thong-bao-push.destroy', $thongBaoPush) }}"
            method="POST"
            class="inline-block"
            onsubmit="return confirm('Bạn có chắc chắn muốn xóa thông báo này không? Thao tác này không thể hoàn tác.');">
            @csrf
            @method('DELETE')
            <button type="submit"
                class="rounded-2xl border border-red-500/30 bg-red-500/10 px-5 py-2.5 text-sm font-bold text-red-400 transition hover:bg-red-500/20">
                <i class="fa-solid fa-trash mr-1"></i>
                Xóa thông báo
            </button>
        </form>
    </div>

</div>

@endsection
