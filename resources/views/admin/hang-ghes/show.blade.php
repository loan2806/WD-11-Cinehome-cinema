@extends('layouts.admin')

@section('page-title', 'Chi tiết Hàng Ghế')

@section('content')

<div class="admin-panel">

    <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

        <div>

            <h5 class="text-2xl font-black text-white">
                Hàng ghế: {{ $hangGhe->ten_hang }}
            </h5>

            <small class="text-gray-400">
                Thông tin chi tiết hàng ghế
            </small>

        </div>

        <div class="flex gap-3">

            <a href="{{ route('admin.hang-ghes.edit', $hangGhe) }}"
                class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">

                <i class="fa-solid fa-pen"></i>

                Sửa

            </a>

            <a href="{{ route('admin.hang-ghes.index') }}"
                class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">

                <i class="fa-solid fa-arrow-left"></i>

                Quay lại

            </a>

        </div>

    </div>

    <div class="mt-6 grid gap-5 lg:grid-cols-3">

        {{-- LEFT: INFO --}}
        <div class="space-y-5">

            <div class="rounded-2xl border border-white/10 bg-[#0f0f0f] p-5">

                <h6 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-400">
                    Thông tin hàng ghế
                </h6>

                <div class="space-y-3">

                    <div class="flex justify-between border-b border-white/5 pb-3">
                        <span class="text-gray-400">Phòng Chiếu</span>
                        <span class="text-white font-medium">{{ $hangGhe->phongChieu->ten_phong ?? 'N/A' }}</span>
                    </div>

                    <div class="flex justify-between border-b border-white/5 pb-3">
                        <span class="text-gray-400">Rạp</span>
                        <span class="text-white font-medium">{{ $hangGhe->phongChieu->rapChieuPhim->ten_rap ?? 'N/A' }}</span>
                    </div>

                    <div class="flex justify-between border-b border-white/5 pb-3">
                        <span class="text-gray-400">Tên Hàng</span>
                        <span class="text-xl font-bold text-white">{{ $hangGhe->ten_hang }}</span>
                    </div>

                    <div class="flex justify-between border-b border-white/5 pb-3">
                        <span class="text-gray-400">Số Ghế</span>
                        <span class="text-white">{{ $hangGhe->gheNgois->count() }} ghế</span>
                    </div>

                    <div class="flex justify-between pt-1">
                        <span class="text-gray-400">Ghế Hoạt Động</span>
                        <span class="text-white">{{ $hangGhe->gheNgois->where('trang_thai', 'hoat_dong')->count() }}</span>
                    </div>

                </div>

            </div>

        </div>

        {{-- RIGHT: ACTIONS + TABLE --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Update row type --}}
            <div class="rounded-2xl border border-white/10 bg-[#0f0f0f] p-5">

                <h6 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-400">
                    Đổi loại ghế hàng loạt
                </h6>

                <form action="{{ route('admin.hang-ghes.update-row-type', $hangGhe) }}" method="POST" class="flex flex-col gap-4 sm:flex-row sm:items-center">

                    @csrf

                    <select name="loai_ghe_id"
                        class="flex-1 rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none transition focus:border-[#d99a32]">

                        @foreach (\App\Models\LoaiGhe::all() as $loai)
                            <option value="{{ $loai->id }}">
                                {{ $loai->ten_loai }} (+{{ number_format($loai->phu_thu) }}đ)
                            </option>
                        @endforeach

                    </select>

                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-6 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02] whitespace-nowrap">

                        <i class="fa-solid fa-sync"></i>

                        Cập nhật {{ $hangGhe->gheNgois->count() }} ghế

                    </button>

                </form>

            </div>

            {{-- Seat list --}}
            <div class="rounded-2xl border border-white/10 bg-[#0f0f0f] p-5">

                <h6 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-400">
                    Danh sách ghế
                </h6>

                @if ($hangGhe->gheNgois->count() > 0)
                    <div class="overflow-hidden rounded-2xl border border-white/10">

                        <div class="overflow-x-auto">
                            <table class="w-full text-left">

                                <thead class="bg-white/5 text-xs uppercase tracking-wider text-gray-400">

                                    <tr>
                                        <th class="px-5 py-4">STT</th>
                                        <th class="px-5 py-4">Mã Ghế</th>
                                        <th class="px-5 py-4">Loại Ghế</th>
                                        <th class="px-5 py-4">Phụ Thu</th>
                                        <th class="px-5 py-4">Trạng Thái</th>
                                    </tr>

                                </thead>

                                <tbody class="divide-y divide-white/5">

                                    @foreach ($hangGhe->gheNgois->sortBy('cot') as $key => $ghe)
                                        <tr class="bg-[#0f0f0f] transition hover:bg-white/5">

                                            <td class="px-5 py-5 text-gray-400">{{ $key + 1 }}</td>

                                            <td class="px-5 py-5 text-white font-bold">{{ $ghe->ma_ghe }}</td>

                                            <td class="px-5 py-5 text-gray-300">{{ $ghe->loaiGhe->ten_loai ?? 'N/A' }}</td>

                                            <td class="px-5 py-5 text-gray-300">{{ number_format($ghe->loaiGhe->phu_thu ?? 0) }}đ</td>

                                            <td class="px-5 py-5 text-left">
                                                @php
                                                    $ttClass = $ghe->trang_thai === 'hoat_dong' ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400';
                                                    $ttLabel = $ghe->trang_thai === 'hoat_dong' ? 'Hoạt động' : 'Bảo trì';
                                                @endphp
                                                <span class="rounded-full px-3 py-1 text-xs font-medium {{ $ttClass }}">
                                                    {{ $ttLabel }}
                                                </span>
                                            </td>

                                        </tr>
                                    @endforeach

                                </tbody>

                            </table>
                        </div>

                    </div>
                @else
                    <p class="py-12 text-center text-gray-500">Chưa có ghế nào trong hàng này.</p>
                @endif

            </div>

        </div>

    </div>

</div>

@endsection
