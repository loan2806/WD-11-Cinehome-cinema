@extends('layouts.admin')

@section('title', 'Giá theo phòng chiếu - CineHome')
@section('page-title', 'Giá theo phòng chiếu')

@section('content')

@php
    $loaiPhongLabels = \App\Models\PhongChieu::LOAI_PHONG;
@endphp

<div class="admin-panel space-y-6">

    <div class="panel-header flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-white/5 pb-6">
        <div>
            <h5 class="text-3xl font-black text-white tracking-wide">
                Giá vé theo từng phòng chiếu
            </h5>
            <p class="text-sm text-gray-400 mt-1">
                Danh sách này luôn khớp với "Quản lý phòng chiếu" — thêm phòng mới ở đó sẽ tự động có dòng để chỉnh giá ở đây.
            </p>
        </div>

        <a href="{{ route('admin.phong-chieus.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-bold text-white hover:bg-white/10 transition duration-200">
            <i class="fa-solid fa-door-open"></i>
            Quản lý phòng chiếu
        </a>
    </div>

    <div class="rounded-2xl border border-amber-500/20 bg-amber-500/5 p-4 text-xs text-amber-200 flex items-start gap-2.5">
        <i class="fa-solid fa-circle-info mt-0.5"></i>
        <span>Giá vé cuối cùng = Giá vé ngày thường/cuối tuần (cấu hình ở "Cài đặt hệ thống") + Phụ thu riêng của phòng bên dưới. Áp dụng khi Quản trị viên tạo suất chiếu và để trống ô "Giá vé tùy chỉnh".</span>
    </div>

    <form method="POST" action="{{ route('admin.loai-phong-gia.update') }}" class="overflow-hidden rounded-3xl border border-white/10 bg-[#121212] shadow-2xl">
        @csrf
        @method('PUT')

        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-left border-collapse">
                <thead class="bg-white/5 text-xs uppercase tracking-wider text-gray-400 border-b border-white/10">
                    <tr>
                        <th class="px-6 py-4.5 font-bold">Phòng chiếu</th>
                        <th class="px-6 py-4.5 font-bold">Rạp</th>
                        <th class="px-6 py-4.5 font-bold">Loại phòng</th>
                        <th class="px-6 py-4.5 font-bold">Phụ thu vé (VNĐ)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($danhSach as $phong)
                        <tr class="transition duration-200 hover:bg-white/5">
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-2 text-white font-extrabold text-base tracking-wide">
                                    <i class="fa-solid fa-door-open text-red-500"></i>
                                    {{ $phong->ten_phong }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-300 text-sm">
                                {{ $phong->rapChieuPhim?->ten_rap ?? 'Không rõ' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-lg bg-white/5 border border-white/10 px-3 py-1 text-xs font-bold text-gray-300">
                                    {{ $loaiPhongLabels[$phong->loai_phong] ?? strtoupper($phong->loai_phong) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 max-w-[220px]">
                                    <span class="text-gray-400 font-bold">+</span>
                                    <input
                                        type="number"
                                        name="phu_thu[{{ $phong->id }}]"
                                        value="{{ old('phu_thu.' . $phong->id, (int) $phong->phu_thu) }}"
                                        min="0"
                                        step="1000"
                                        required
                                        class="h-11 w-full rounded-xl border border-white/10 bg-[#151515] px-4 text-sm text-[#f4c56a] font-bold outline-none focus:border-[#d99a32] transition"
                                    >
                                    <span class="text-gray-400 font-bold">đ</span>
                                </div>
                                @error('phu_thu.' . $phong->id)
                                    <small class="text-red-500">{{ $message }}</small>
                                @enderror
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center text-gray-500">
                                <i class="fa-solid fa-door-open text-4xl text-gray-600 mb-3 block"></i>
                                Chưa có phòng chiếu nào. Vào "Quản lý phòng chiếu" để thêm phòng trước.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($danhSach->isNotEmpty())
            <div class="p-6 border-t border-white/5">
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-[#e50914] to-[#ff3b46] px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-red-500/10 hover:shadow-red-500/20 hover:scale-[1.02] active:scale-[0.98] transition duration-200">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Lưu giá theo phòng chiếu
                </button>
            </div>
        @endif
    </form>

</div>

@endsection
