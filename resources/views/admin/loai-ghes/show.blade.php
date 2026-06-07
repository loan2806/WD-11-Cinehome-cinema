@extends('layouts.admin')

@section('page-title', 'Chi tiết Loại Ghế')

@section('content')

<div class="admin-panel">

    <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

        <div>

            <h5 class="text-2xl font-black text-white">
                Loại ghế: {{ $loaiGhe->ten_loai }}
            </h5>

            <small class="text-gray-400">
                Thông tin chi tiết loại ghế
            </small>

        </div>

        <div class="flex gap-3">

            <a href="{{ route('admin.loai-ghes.edit', $loaiGhe) }}"
                class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">

                <i class="fa-solid fa-pen"></i>

                Sửa

            </a>

            <a href="{{ route('admin.loai-ghes.index') }}"
                class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">

                <i class="fa-solid fa-arrow-left"></i>

                Quay lại

            </a>

        </div>

    </div>

    <div class="mt-6 grid gap-5 lg:grid-cols-3">

        {{-- LEFT: INFO --}}
        <div class="rounded-2xl border border-white/10 bg-[#0f0f0f] p-5">

            <h6 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-400">
                Thông tin loại ghế
            </h6>

            <div class="space-y-3">

                <div class="flex justify-between border-b border-white/5 pb-3">
                    <span class="text-gray-400">Tên Loại</span>
                    <span class="text-xl font-bold text-white">{{ $loaiGhe->ten_loai }}</span>
                </div>

                <div class="flex justify-between border-b border-white/5 pb-3">
                    <span class="text-gray-400">Mô tả</span>
                    <span class="text-white text-right max-w-[180px]">{{ $loaiGhe->mo_ta ?? '-' }}</span>
                </div>

                <div class="flex justify-between border-b border-white/5 pb-3">
                    <span class="text-gray-400">Phụ Thu</span>
                    <span class="text-lg font-bold text-red-400">{{ number_format($loaiGhe->phu_thu) }}đ</span>
                </div>

                <div class="flex justify-between pt-1">
                    <span class="text-gray-400">Tổng Ghế</span>
                    <span class="rounded-full bg-blue-500/15 px-3 py-1 text-xs font-medium text-blue-300">
                        {{ $loaiGhe->gheNgois->count() }} ghế
                    </span>
                </div>

            </div>

        </div>

        {{-- RIGHT: SEAT LIST --}}
        <div class="lg:col-span-2 rounded-2xl border border-white/10 bg-[#0f0f0f] p-5">

            <h6 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-400">
                Danh sách ghế
            </h6>

            @if ($loaiGhe->gheNgois->count() > 0)
                <div class="overflow-hidden rounded-2xl border border-white/10">

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">

                            <thead class="bg-white/5 text-xs uppercase tracking-wider text-gray-400">

                                <tr>
                                    <th class="px-5 py-4">STT</th>
                                    <th class="px-5 py-4">Mã Ghế</th>
                                    <th class="px-5 py-4">Phòng Chiếu</th>
                                    <th class="px-5 py-4">Rạp</th>
                                    <th class="px-5 py-4">Trạng Thái</th>
                                </tr>

                            </thead>

                            <tbody class="divide-y divide-white/5">

                                @foreach ($loaiGhe->gheNgois as $key => $ghe)
                                    <tr class="bg-[#0f0f0f] transition hover:bg-white/5">

                                        <td class="px-5 py-5 text-gray-400">{{ $key + 1 }}</td>

                                        <td class="px-5 py-5 text-white font-bold">{{ $ghe->ma_ghe }}</td>

                                        <td class="px-5 py-5 text-gray-300">{{ $ghe->phongChieu->ten_phong ?? 'N/A' }}</td>

                                        <td class="px-5 py-5 text-gray-300">{{ $ghe->phongChieu->rapChieuPhim->ten_rap ?? 'N/A' }}</td>

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
                <p class="py-12 text-center text-gray-500">Không có ghế nào sử dụng loại ghế này.</p>
            @endif

        </div>

    </div>

</div>

@endsection
