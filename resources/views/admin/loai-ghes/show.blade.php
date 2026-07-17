@extends('layouts.admin')

@section('title', 'Chi tiết Loại Ghế - CineHome')
@section('page-title', 'Chi tiết Loại Ghế')

@section('content')

    <div class="admin-panel space-y-6">

        {{-- HEADER --}}
        <div class="panel-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-white/5 pb-6">
            <div>
                <h5 class="text-3xl font-black text-white tracking-wide">
                    Loại ghế: {{ $loaiGhe->ten_loai }}
                </h5>
                <p class="text-sm text-gray-400 mt-1">
                    Xem thông tin cấu hình chi tiết và danh sách ghế ngồi đang áp dụng định dạng này
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.loai-ghes.edit', $loaiGhe) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-[#e50914] to-[#ff3b46] px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-red-500/10 hover:shadow-red-500/20 hover:scale-[1.02] active:scale-[0.98] transition duration-200">
                    <i class="fa-solid fa-pen-to-square"></i>
                    Sửa thông tin
                </a>
                
                <a href="{{ route('admin.loai-ghes.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-5 py-3.5 text-sm font-bold text-white hover:bg-white/10 active:scale-95 duration-200">
                    <i class="fa-solid fa-arrow-left"></i>
                    Quay lại
                </a>
            </div>
        </div>

        {{-- MAIN CONTENT GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- CỘT TRÁI: THÔNG TIN CHI TIẾT & HÌNH ẢNH MÔ PHỎNG --}}
            <div class="rounded-2xl border border-white/10 bg-gradient-to-b from-[#1c1c1c] to-[#121212] p-5 shadow-xl space-y-6">
                
                <div>
                    <h6 class="text-xs font-bold uppercase tracking-wider text-gray-500 border-b border-white/5 pb-3.5">
                        Nhận diện loại ghế
                    </h6>

                    {{-- Vector Ghế Mô Phỏng Phát Sáng --}}
                    <div class="relative h-28 rounded-xl flex items-center justify-center overflow-hidden bg-black/40 border border-white/5 mt-5">
                        <!-- Quầng sáng neon mờ phía sau -->
                        <div class="absolute h-14 w-14 rounded-full blur-2xl opacity-40" style="background-color: {{ $loaiGhe->mau_sac ?? '#666666' }}"></div>
                        
                        <!-- Mô hình 3D vector ghế xem phim -->
                        <div class="relative flex flex-col items-center">
                            <div class="flex gap-1 items-end relative">
                                <!-- Ghế 1 -->
                                <div class="relative flex flex-col items-center">
                                    <div class="w-14 h-10 rounded-t-xl border-t border-x border-white/25 relative flex items-center justify-center" style="background-color: {{ $loaiGhe->mau_sac ?? '#666666' }}">
                                        <span class="text-[8px] font-black tracking-widest text-white/50 select-none drop-shadow">CINE</span>
                                    </div>
                                    <div class="w-15 h-4.5 rounded-b-lg border-b border-x border-white/30 mt-0.5" style="background-color: {{ $loaiGhe->mau_sac ?? '#666666' }}"></div>
                                    <div class="absolute -left-2.5 top-3 w-1.5 h-7 bg-white/10 rounded border border-white/10"></div>
                                    @if(!$loaiGhe->la_couple)
                                        <div class="absolute -right-2.5 top-3 w-1.5 h-7 bg-white/10 rounded border border-white/10"></div>
                                    @endif
                                </div>

                                <!-- Ghế 2 (Chỉ hiện khi là Couple) -->
                                @if($loaiGhe->la_couple)
                                    <div class="relative flex flex-col items-center">
                                        <div class="w-14 h-10 rounded-t-xl border-t border-x border-white/25 relative flex items-center justify-center" style="background-color: {{ $loaiGhe->mau_sac ?? '#666666' }}">
                                            <span class="text-[8px] font-black tracking-widest text-white/50 select-none drop-shadow">LOVE</span>
                                        </div>
                                        <div class="w-15 h-4.5 rounded-b-lg border-b border-x border-white/30 mt-0.5" style="background-color: {{ $loaiGhe->mau_sac ?? '#666666' }}"></div>
                                        <div class="absolute -right-2.5 top-3 w-1.5 h-7 bg-white/10 rounded border border-white/10"></div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Thông số kỹ thuật --}}
                <div class="space-y-4">
                    <h6 class="text-xs font-bold uppercase tracking-wider text-gray-500 border-b border-white/5 pb-3">
                        Thông số kỹ thuật
                    </h6>

                    <div class="space-y-3.5 text-sm">
                        <div class="flex justify-between items-center border-b border-white/5 pb-3">
                            <span class="text-gray-400">Tên phân hạng</span>
                            <span class="text-base font-extrabold text-white">{{ $loaiGhe->ten_loai }}</span>
                        </div>

                        <div class="flex justify-between items-center border-b border-white/5 pb-3">
                            <span class="text-gray-400">Định dạng ghế</span>
                            <span class="text-sm font-bold text-white">
                                {{ $loaiGhe->la_couple ? 'Ghế đôi (Couple)' : 'Ghế đơn (Single)' }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center border-b border-white/5 pb-3">
                            <span class="text-gray-400">Phụ thu biểu giá</span>
                            <span class="text-lg font-black text-[#ff3b46]">+{{ number_format($loaiGhe->phu_thu) }}đ</span>
                        </div>

                        <div class="flex justify-between items-center border-b border-white/5 pb-3">
                            <span class="text-gray-400">Mã màu nhận diện</span>
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full border border-white/10" style="background-color: {{ $loaiGhe->mau_sac ?? '#666' }}"></span>
                                <span class="text-xs font-mono text-gray-300 uppercase">{{ $loaiGhe->mau_sac ?? '#666666' }}</span>
                            </div>
                        </div>

                        <div class="flex justify-between items-center pb-1">
                            <span class="text-gray-400">Tổng số ghế lắp</span>
                            <span class="inline-flex items-center rounded-lg bg-red-500/10 border border-red-500/20 px-2.5 py-0.5 text-xs font-bold text-red-400">
                                {{ $loaiGhe->gheNgois->count() }} ghế
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Mô tả --}}
                <div class="space-y-3.5">
                    <h6 class="text-xs font-bold uppercase tracking-wider text-gray-500 border-b border-white/5 pb-3">
                        Mô tả chi tiết
                    </h6>
                    <p class="text-xs text-gray-400 leading-relaxed bg-black/20 rounded-xl p-3 border border-white/5">
                        {{ $loaiGhe->mo_ta ?? 'Chưa có thông tin mô tả chi tiết cho định dạng ghế này.' }}
                    </p>
                </div>

            </div>

            {{-- CỘT PHẢI: DANH SÁCH CÁC GHẾ ĐANG SỬ DỤNG --}}
            <div class="lg:col-span-2 rounded-2xl border border-white/10 bg-[#121212] p-5 shadow-xl flex flex-col">
                
                <div class="flex items-center justify-between border-b border-white/5 pb-4.5 mb-5">
                    <h6 class="text-xs font-bold uppercase tracking-wider text-gray-500">
                        Danh sách vị trí ghế áp dụng
                    </h6>
                    <span class="text-xs text-gray-400">
                        Có {{ $loaiGhe->gheNgois->count() }} vị trí ghế hoạt động
                    </span>
                </div>

                @if ($loaiGhe->gheNgois->count() > 0)
                    <div class="overflow-hidden rounded-2xl border border-white/10 bg-[#0f0f0f]">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                
                                <thead class="bg-white/5 text-xs uppercase tracking-wider text-gray-400 border-b border-white/10">
                                    <tr>
                                        <th class="px-6 py-4">STT</th>
                                        <th class="px-6 py-4">Mã Ghế</th>
                                        <th class="px-6 py-4">Phòng Chiếu</th>
                                        <th class="px-6 py-4">Rạp Chiếu</th>
                                        <th class="px-6 py-4">Trạng Thái</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-white/5">
                                    @foreach ($loaiGhe->gheNgois as $key => $ghe)
                                        <tr class="transition duration-150 hover:bg-white/5">
                                            
                                            {{-- STT --}}
                                            <td class="px-6 py-4 text-gray-500 text-sm font-semibold">
                                                {{ $key + 1 }}
                                            </td>

                                            {{-- MÃ GHẾ --}}
                                            <td class="px-6 py-4 text-white font-extrabold text-sm tracking-wider">
                                                {{ $ghe->ma_ghe }}
                                            </td>

                                            {{-- PHÒNG --}}
                                            <td class="px-6 py-4 text-gray-300 text-sm">
                                                <div class="flex items-center gap-1.5">
                                                    <i class="fa-solid fa-door-open text-gray-500 text-xs"></i>
                                                    {{ $ghe->phongChieu->ten_phong ?? 'N/A' }}
                                                </div>
                                            </td>

                                            {{-- RẠP --}}
                                            <td class="px-6 py-4 text-gray-300 text-sm">
                                                <div class="flex items-center gap-1.5">
                                                    <i class="fa-solid fa-film text-gray-500 text-xs"></i>
                                                    {{ $ghe->phongChieu->rapChieuPhim->ten_rap ?? 'N/A' }}
                                                </div>
                                            </td>

                                            {{-- TRẠNG THÁI --}}
                                            <td class="px-6 py-4">
                                                @php
                                                    $isAct = $ghe->trang_thai === 'hoat_dong';
                                                    $ttClass = $isAct ? 'bg-green-500/10 text-green-400 border-green-500/20' : 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20';
                                                    $ttLabel = $isAct ? 'Hoạt động' : 'Bảo trì';
                                                    $pulseDot = $isAct 
                                                        ? '<span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-green-400 animate-pulse"></span>' 
                                                        : '<span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-yellow-400 animate-ping"></span>';
                                                @endphp
                                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-bold {{ $ttClass }}">
                                                    {!! $pulseDot !!}
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
                    <div class="py-16 text-center text-gray-500 bg-[#0f0f0f] rounded-2xl border border-white/5">
                        <i class="fa-solid fa-chair text-4xl text-gray-600 mb-3 block"></i>
                        <p>Không có ghế nào sử dụng loại ghế này.</p>
                    </div>
                @endif

            </div>

        </div>

    </div>

@endsection
