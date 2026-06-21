@extends('layouts.admin')

@section('page-title', 'Quản lý Ghế Ngồi')

@section('content')

    <div class="admin-panel">

        <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            <div>

                <h5 class="text-2xl font-black text-white">
                    Danh sách ghế ngồi
                </h5>

                <small class="text-gray-400">
                    Quản lý toàn bộ ghế ngồi trong hệ thống
                </small>

            </div>

            <a href="{{ route('admin.ghe-ngois.create') }}"
                class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">

                <i class="fa-solid fa-plus"></i>

                Thêm ghế

            </a>

        </div>

        <form method="GET" action="{{ route('admin.ghe-ngois.index') }}" class="mt-6 flex flex-wrap items-center gap-3">

            <select name="phong_chieu_id"
                class="h-12 rounded-2xl border border-white/10 bg-[#151515] px-4 text-white outline-none focus:border-[#d99a32]"
                onchange="this.form.submit()">

                <option value="">-- Tất cả Phòng --</option>

                @foreach ($phongChieus as $phong)
                    <option value="{{ $phong->id }}"
                        {{ request('phong_chieu_id') == $phong->id ? 'selected' : '' }}>
                        {{ $phong->ten_phong }}
                    </option>
                @endforeach

            </select>

            <select name="loai_ghe_id"
                class="h-12 rounded-2xl border border-white/10 bg-[#151515] px-4 text-white outline-none focus:border-[#d99a32]"
                onchange="this.form.submit()">

                <option value="">-- Tất cả Loại --</option>

                @foreach ($loaiGhes as $loai)
                    <option value="{{ $loai->id }}"
                        {{ request('loai_ghe_id') == $loai->id ? 'selected' : '' }}>
                        {{ $loai->ten_loai }}
                    </option>
                @endforeach

            </select>

            <select name="trang_thai"
                class="h-12 rounded-2xl border border-white/10 bg-[#151515] px-4 text-white outline-none focus:border-[#d99a32]"
                onchange="this.form.submit()">

                <option value="">-- Tất cả Trạng thái --</option>

                <option value="hoat_dong"
                    {{ request('trang_thai') == 'hoat_dong' ? 'selected' : '' }}>
                    Hoạt động
                </option>

                <option value="bao_tri"
                    {{ request('trang_thai') == 'bao_tri' ? 'selected' : '' }}>
                    Bảo trì
                </option>

            </select>

        </form>

        <div class="mt-6 overflow-hidden rounded-3xl border border-white/10">

            <div class="overflow-x-auto">

                <table class="w-full min-w-[1000px] text-left">

                    <thead class="bg-white/5 text-xs uppercase tracking-wider text-gray-400">

                        <tr>

                            <th class="px-5 py-4">STT</th>

                            <th class="px-5 py-4">Mã Ghế</th>

                            <th class="px-5 py-4">Phòng Chiếu</th>

                            <th class="px-5 py-4">Hàng</th>

                            <th class="px-5 py-4">Loại Ghế</th>

                            <th class="px-5 py-4">Phụ Thu</th>

                            <th class="px-5 py-4">Trạng Thái</th>

                            <th class="px-5 py-4 text-right">Hành động</th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-white/5">

                        @forelse ($gheNgois as $key => $ghe)
                            <tr class="bg-[#0f0f0f] transition hover:bg-white/5">

                                <td class="px-5 py-5 text-gray-400">

                                    #{{ $gheNgois->firstItem() + $key }}

                                </td>

                                <td class="px-5 py-5 text-white font-bold">

                                    {{ $ghe->ma_ghe }}

                                </td>

                                <td class="px-5 py-5 text-gray-300">

                                    {{ $ghe->phongChieu->ten_phong ?? 'N/A' }}

                                </td>

                                <td class="px-5 py-5 text-gray-300">

                                    {{ $ghe->hangGhe->ten_hang ?? 'N/A' }}

                                </td>

                                <td class="px-5 py-5 text-gray-300">

                                    {{ $ghe->loaiGhe->ten_loai ?? 'N/A' }}

                                </td>

                                <td class="px-5 py-5 text-gray-300">

                                    {{ number_format($ghe->loaiGhe->phu_thu ?? 0) }}đ

                                </td>

                                <td class="px-5 py-5">
                                    @php
                                        $ttClass = $ghe->trang_thai === 'hoat_dong' ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400';
                                        $ttLabel = $ghe->trang_thai === 'hoat_dong' ? 'Hoạt động' : 'Bảo trì';
                                    @endphp
                                    <span class="rounded-full px-2 py-1 text-xs font-medium {{ $ttClass }}">
                                        {{ $ttLabel }}
                                    </span>
                                </td>

                                <td class="px-5 py-5 align-middle">
                                    <div class="flex items-center justify-center gap-3 whitespace-nowrap">

                                        <a href="{{ route('admin.ghe-ngois.edit', $ghe) }}"
                                            class="flex aspect-square h-10 w-10 items-center justify-center rounded-xl bg-yellow-500/15 text-yellow-300 transition hover:bg-yellow-500/25">

                                            <i class="fa-solid fa-pen text-base leading-none"></i>

                                        </a>

                                        <form action="{{ route('admin.ghe-ngois.toggle-maintenance', $ghe) }}" method="POST">

                                            @csrf

                                            <button type="submit"
                                                class="flex aspect-square h-10 w-10 items-center justify-center rounded-xl transition hover:opacity-80
                                                    {{ $ghe->trang_thai === 'hoat_dong' ? 'bg-gray-500/15 text-gray-300 hover:bg-gray-500/25' : 'bg-green-500/15 text-green-300 hover:bg-green-500/25' }}"
                                                title="{{ $ghe->trang_thai === 'hoat_dong' ? 'Bảo trì' : 'Kích hoạt' }}">

                                                <i class="fa-solid {{ $ghe->trang_thai === 'hoat_dong' ? 'fa-wrench' : 'fa-check' }} text-base leading-none"></i>

                                            </button>

                                        </form>

                                        <a href="{{ route('admin.ghe-ngois.baoTri', ['ghe_ngoi_id' => $ghe->id]) }}"
                                            class="flex aspect-square h-10 w-10 items-center justify-center rounded-xl bg-blue-500/15 text-blue-300 transition hover:bg-blue-500/25"
                                            title="Lịch sử bảo trì">

                                            <i class="fa-solid fa-clock-rotate-left text-base leading-none"></i>

                                        </a>

                                        <button type="button"
                                            class="flex aspect-square h-10 w-10 items-center justify-center rounded-xl bg-red-500/15 text-red-300 transition hover:bg-red-500/25"
                                            title="Xóa ghế"
                                            @click="
                                                window.dispatchEvent(new CustomEvent('open-modal', {
                                                    detail: {
                                                        title: 'Xóa ghế?',
                                                        message: 'Hành động này không thể hoàn tác.',
                                                        icon: 'fa-trash text-red-400',
                                                        type: 'error',
                                                        actionUrl: '{{ route('admin.ghe-ngois.destroy', $ghe) }}',
                                                        actionMethod: 'DELETE',
                                                        actionBtn: 'Xóa',
                                                        actionBtnClass: 'bg-red-500 hover:bg-red-600'
                                                    }
                                                }))
                                            ">

                                            <i class="fa-solid fa-trash text-base leading-none"></i>

                                        </button>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="8" class="px-5 py-16 text-center text-gray-500">

                                    Chưa có ghế nào trong hệ thống

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        <div class="mt-4 flex justify-center">
            {{ $gheNgois->links() }}
        </div>

    </div>

@endsection

@php
    $pageParam = $gheNgois->currentPage();
@endphp

@prepend('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.addEventListener('modal-success', function(e) {
        var data = e.detail || {};
        var msg = data.message || 'Thao tác thành công!';

        var toast = document.createElement('div');
        toast.className = 'fixed top-5 right-5 z-[9999] flex w-80 items-center gap-3 rounded-xl border border-emerald-500/40 bg-emerald-500/20 px-4 py-3 text-emerald-200 shadow-xl backdrop-blur-md';
        toast.innerHTML = '<i class="fa-solid fa-circle-check text-lg leading-none"></i><p class="flex-1 text-sm font-medium leading-snug">' + msg + '</p><button onclick="this.parentElement.remove()" class="text-current opacity-60 hover:opacity-100 transition"><i class="fa-solid fa-xmark text-sm leading-none"></i></button>';
        document.body.appendChild(toast);
        setTimeout(function() { if (toast.parentElement) toast.remove(); }, 3000);

        var params = new URLSearchParams(window.location.search);
        params.set('page', {{ $pageParam }});
        window.location.search = params.toString();
    });

    window.addEventListener('modal-error', function(e) {
        var data = e.detail || {};
        var msg = data.message || 'Có lỗi xảy ra!';

        var toast = document.createElement('div');
        toast.className = 'fixed top-5 right-5 z-[9999] flex w-80 items-center gap-3 rounded-xl border border-rose-500/40 bg-rose-500/20 px-4 py-3 text-rose-200 shadow-xl backdrop-blur-md';
        toast.innerHTML = '<i class="fa-solid fa-circle-exclamation text-lg leading-none"></i><p class="flex-1 text-sm font-medium leading-snug">' + msg + '</p><button onclick="this.parentElement.remove()" class="text-current opacity-60 hover:opacity-100 transition"><i class="fa-solid fa-xmark text-sm leading-none"></i></button>';
        document.body.appendChild(toast);
        setTimeout(function() { if (toast.parentElement) toast.remove(); }, 3000);
    });
});
</script>
@endprepend