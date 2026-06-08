@extends('layouts.admin')

@section('page-title', 'Chi tiết Phòng Chiếu')

@section('content')

<div class="admin-panel">

    <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

        <div>

            <h5 class="text-2xl font-black text-white">
                Chi tiết phòng chiếu: {{ $phongChieu->ten_phong }}
            </h5>

            <small class="text-gray-400">
                Thông tin và sơ đồ ghế của phòng
            </small>

        </div>

        <div class="flex gap-3">

            <a href="{{ route('admin.phong-chieus.edit', $phongChieu) }}"
                class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">

                <i class="fa-solid fa-pen"></i>

                Sửa

            </a>

            <a href="{{ route('admin.phong-chieus.index') }}"
                class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">

                <i class="fa-solid fa-arrow-left"></i>

                Quay lại

            </a>

        </div>

    </div>

    <div class="mt-6 grid gap-5 lg:grid-cols-3">

        {{-- LEFT: THONG TIN + TAO GHE --}}
        <div class="space-y-5">

            {{-- Thong tin phong --}}
            <div class="rounded-2xl border border-white/10 bg-[#0f0f0f] p-5">

                <h6 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-400">
                    Thông tin phòng chiếu
                </h6>

                <div class="space-y-3">

                    <div class="flex justify-between border-b border-white/5 pb-3">
                        <span class="text-gray-400">Rạp Chiếu</span>
                        <span class="text-white font-medium">{{ $phongChieu->rapChieuPhim->ten_rap ?? 'N/A' }}</span>
                    </div>

                    <div class="flex justify-between border-b border-white/5 pb-3">
                        <span class="text-gray-400">Tên Phòng</span>
                        <span class="text-white font-bold">{{ $phongChieu->ten_phong }}</span>
                    </div>

                    <div class="flex justify-between border-b border-white/5 pb-3">
                        <span class="text-gray-400">Loại Phòng</span>
                        <span class="inline-block rounded-full bg-white/10 px-3 py-1 text-xs">
                            @php
                                $loaiLabels = ['2d' => '2D', '3d' => '3D', 'imax' => 'IMAX', '4dx' => '4DX'];
                            @endphp
                            {{ $loaiLabels[$phongChieu->loai_phong] ?? $phongChieu->loai_phong }}
                        </span>
                    </div>

                    <div class="flex justify-between border-b border-white/5 pb-3">
                        <span class="text-gray-400">Sức Chứa</span>
                        <span class="text-white">{{ $phongChieu->suc_chua }} ghế</span>
                    </div>

                    <div class="flex justify-between pt-1">
                        <span class="text-gray-400">Trạng Thái</span>
                        <span class="rounded-full px-3 py-1 text-xs font-medium
                            @if($phongChieu->trang_thai === 'hoat_dong') bg-green-500/20 text-green-400
                            @elseif($phongChieu->trang_thai === 'bao_tri') bg-yellow-500/20 text-yellow-400
                            @else bg-gray-500/20 text-gray-400
                            @endif">
                            @php
                                $trangThaiLabels = [
                                    'hoat_dong' => 'Hoạt động',
                                    'bao_tri' => 'Bảo trì',
                                    'ngung_hoat_dong' => 'Ngừng hoạt động'
                                ];
                            @endphp
                            {{ $trangThaiLabels[$phongChieu->trang_thai] ?? $phongChieu->trang_thai }}
                        </span>
                    </div>

                </div>

            </div>

            {{-- Tao ghe tu dong --}}
            <div class="rounded-2xl border border-white/10 bg-[#0f0f0f] p-5">

                <h6 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-400">
                    Tạo ghế tự động
                </h6>

                @if($phongChieu->gheNgois->count() > 0)
                    <div class="mb-4 rounded-xl border border-yellow-500/30 bg-yellow-500/10 p-3 text-sm text-yellow-400">
                        <i class="fa-solid fa-exclamation-triangle mr-1"></i>
                        Phòng đã có {{ $phongChieu->gheNgois->count() }} ghế.
                        Tạo mới sẽ xóa toàn bộ ghế cũ.
                    </div>
                @endif

                <form action="{{ route('admin.phong-chieus.generate-seats', $phongChieu) }}" method="POST">

                    @csrf

                    <div class="space-y-3">

                        <div>
                            <label class="mb-1 block text-xs text-gray-400">Số Hàng</label>
                            <input type="number" name="so_hang" value="8" min="1" max="20" required
                                class="w-full rounded-xl border border-white/10 bg-[#151515] px-3 py-2 text-sm text-white outline-none transition focus:border-[#d99a32]">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs text-gray-400">Số Cột</label>
                            <input type="number" name="so_cot" value="10" min="1" max="20" required
                                class="w-full rounded-xl border border-white/10 bg-[#151515] px-3 py-2 text-sm text-white outline-none transition focus:border-[#d99a32]">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs text-gray-400">Loại ghế Thường</label>
                            <select name="loai_ghe_thuong_id" required
                                class="w-full rounded-xl border border-white/10 bg-[#151515] px-3 py-2 text-sm text-white outline-none transition focus:border-[#d99a32]">
                                <option value="1">Thường</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs text-gray-400">Loại ghế VIP</label>
                            <select name="loai_ghe_vip_id"
                                class="w-full rounded-xl border border-white/10 bg-[#151515] px-3 py-2 text-sm text-white outline-none transition focus:border-[#d99a32]">
                                <option value="">-- Không có VIP --</option>
                                @foreach(\App\Models\LoaiGhe::all() as $loai)
                                    <option value="{{ $loai->id }}">{{ $loai->ten_loai }} (+{{ number_format($loai->phu_thu) }}đ)</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs text-gray-400">Loại ghế Couple</label>
                            <select name="loai_ghe_couple_id"
                                class="w-full rounded-xl border border-white/10 bg-[#151515] px-3 py-2 text-sm text-white outline-none transition focus:border-[#d99a32]">
                                <option value="">-- Không có Couple --</option>
                                @foreach(\App\Models\LoaiGhe::all() as $loai)
                                    <option value="{{ $loai->id }}">{{ $loai->ten_loai }} (+{{ number_format($loai->phu_thu) }}đ)</option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <button type="submit"
                        class="mt-4 w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-4 py-2.5 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">

                        <i class="fa-solid fa-couch"></i>

                        Tạo Ghế Tự Động

                    </button>

                </form>

            </div>

        </div>

        {{-- RIGHT: SO DO GHE + HANG GHE --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- So do ghe --}}
            <div class="rounded-2xl border border-white/10 bg-[#0f0f0f] p-5">

                <h6 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-400">
                    Sơ đồ Ghế ({{ $soHang }} hàng × {{ $soCot }} cột = {{ $phongChieu->gheNgois->count() }} ghế)
                </h6>

                @if(count($seatMap) > 0)
                    <div class="seat-legend">
                        <span class="seat-legend__item">
                            <span class="seat-chip seat-chip--regular">A</span>
                            Ghế thường
                        </span>
                        <span class="seat-legend__item">
                            <span class="seat-chip seat-chip--vip">V</span>
                            Ghế VIP
                        </span>
                        <span class="seat-legend__item">
                            <span class="seat-chip seat-chip--couple">C</span>
                            Ghế Couple
                        </span>
                        <span class="seat-legend__item">
                            <span class="seat-chip seat-chip--maintenance">M</span>
                            Bảo trì
                        </span>
                    </div>

                    <div class="seat-map-wrapper">
                        <div class="seat-map-inner">

                            <div class="screen-bar">
                                <span class="screen-bar__label">MÀN HÌNH</span>
                            </div>

                            @foreach($seatMap as $tenHang => $cotGhe)
                                <div class="seat-row">

                                    <span class="seat-row__label">{{ $tenHang }}</span>

                                    <div class="seat-row__seats">
                                        @php
                                            $j = 1;
                                        @endphp
                                        @while($j <= $soCot)
                                            @if(isset($cotGhe[$j]))
                                                @php
                                                    $ghe = $cotGhe[$j];
                                                    $seatClass = 'seat-chip seat-chip--regular';
                                                    if ($ghe['loai_ghe'] === 'VIP') $seatClass = 'seat-chip seat-chip--vip';
                                                    if ($ghe['loai_ghe'] === 'Couple') $seatClass = 'seat-chip seat-chip--couple seat-chip--wide';
                                                    if ($ghe['trang_thai'] === 'bao_tri') $seatClass = 'seat-chip seat-chip--maintenance';
                                                    $label = $j;
                                                    $skip = false;
                                                    if ($ghe['is_couple'] && !empty($ghe['cot_end'])) {
                                                        $label = $j . '-' . $ghe['cot_end'];
                                                        $j = (int) $ghe['cot_end'];
                                                    }
                                                @endphp
                                                <div class="{{ $seatClass }}"
                                                     title="{{ $ghe['ma_ghe'] }} - {{ $ghe['loai_ghe'] }}">
                                                    {{ $label }}
                                                </div>
                                                @php
                                                    $j++;
                                                @endphp
                                            @else
                                                <div class="seat-chip seat-chip--empty"></div>
                                                @php $j++; @endphp
                                            @endif
                                        @endwhile
                                    </div>

                                    <span class="seat-row__label">{{ $tenHang }}</span>

                                </div>
                            @endforeach

                        </div>
                    </div>
                @else
                    <div class="py-12 text-center text-gray-500">
                        <i class="fa-solid fa-chair fa-3x mb-3 opacity-30"></i>
                        <p>Phòng chưa có ghế. Vui lòng tạo ghế tự động.</p>
                    </div>
                @endif

            </div>

            {{-- Danh sach hang ghe --}}
            <div class="rounded-2xl border border-white/10 bg-[#0f0f0f] p-5">

                <h6 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-400">
                    Danh sách hàng ghế
                </h6>

                @if($phongChieu->hangGhes->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-white/5 text-left text-xs uppercase tracking-wider text-gray-400">
                                    <th class="pb-3 pr-4">Tên Hàng</th>
                                    <th class="pb-3 pr-4">Số Ghế</th>
                                    <th class="pb-3">Ghế Hoạt Động</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @foreach($phongChieu->hangGhes as $hang)
                                    <tr class="text-gray-300">
                                        <td class="py-3 pr-4 font-bold text-white">{{ $hang->ten_hang }}</td>
                                        <td class="py-3 pr-4">{{ $hang->gheNgois->count() }}</td>
                                        <td class="py-3">{{ $hang->gheNgois->where('trang_thai', 'hoat_dong')->count() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center text-gray-500">Chưa có hàng ghế nào.</p>
                @endif

            </div>

        </div>

    </div>

</div>

@endsection
