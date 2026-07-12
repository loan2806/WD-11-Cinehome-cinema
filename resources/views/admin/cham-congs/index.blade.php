@extends('layouts.admin')

@section('title', 'Lịch sử chấm công')
@section('page-title', 'Lịch sử chấm công')
@section('page-subtitle', 'Quản lý lịch sử chấm công nhân viên')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-white">Lịch sử chấm công</h2>
            <p class="text-gray-400">Xem và quản lý thông tin chấm công hàng ngày của nhân viên</p>
        </div>
        <a href="{{ route('admin.cham-congs.create') }}"
           class="rounded-xl bg-[#d99a32] px-5 py-3 font-bold text-[#2b1208] transition hover:scale-105">
            <i class="fa-solid fa-plus mr-2"></i> Thực hiện chấm công
        </a>
    </div>

    <!-- Bộ lọc -->
    <div class="rounded-2xl border border-white/10 bg-[#151515] p-5">
        <form method="GET" action="{{ route('admin.cham-congs.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-5">
            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">Nhân viên</label>
                <select name="nhan_vien_id"
                        class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white focus:border-[#d99a32] focus:outline-none">
                    <option value="">-- Tất cả nhân viên --</option>
                    @foreach($nhanViens as $nv)
                        <option value="{{ $nv->id }}" {{ request('nhan_vien_id') == $nv->id ? 'selected' : '' }}>
                            {{ $nv->ho_ten }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">Lọc theo</label>
                <select name="loai_loc" id="loai_loc" onchange="toggleFilterFields()"
                        class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white focus:border-[#d99a32] focus:outline-none">
                    <option value="ngay" {{ (!isset($loaiLoc) || $loaiLoc == 'ngay') ? 'selected' : '' }}>Ngày</option>
                    <option value="thang" {{ (isset($loaiLoc) && $loaiLoc == 'thang') ? 'selected' : '' }}>Tháng</option>
                    <option value="quy" {{ (isset($loaiLoc) && $loaiLoc == 'quy') ? 'selected' : '' }}>Quý</option>
                    <option value="nam" {{ (isset($loaiLoc) && $loaiLoc == 'nam') ? 'selected' : '' }}>Năm</option>
                </select>
            </div>
            
            <div id="filter_ngay_container" style="{{ (!isset($loaiLoc) || $loaiLoc == 'ngay') ? 'block' : 'none' }}">
                <label class="mb-2 block text-sm font-bold text-gray-300">Theo ngày</label>
                <input type="{{ request('ngay') ? 'date' : 'text' }}" name="ngay" value="{{ request('ngay') }}" placeholder="Chọn ngày..."
                       onfocus="(this.type='date'); this.showPicker()" onblur="if(!this.value) this.type='text'"
                       class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white focus:border-[#d99a32] focus:outline-none cursor-pointer" style="color-scheme: dark;" onclick="if(this.type==='date') this.showPicker()">
            </div>
            
            <div id="filter_thang_container" style="display: {{ (isset($loaiLoc) && $loaiLoc == 'thang') ? 'block' : 'none' }};">
                <label class="mb-2 block text-sm font-bold text-gray-300">Tháng</label>
                <select name="thang" id="thang"
                        class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white focus:border-[#d99a32] focus:outline-none">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('thang', date('m')) == $m ? 'selected' : '' }}>
                            Tháng {{ $m }}
                        </option>
                    @endfor
                </select>
            </div>
            <div id="filter_quy_container" style="display: {{ (isset($loaiLoc) && $loaiLoc == 'quy') ? 'block' : 'none' }};">
                <label class="mb-2 block text-sm font-bold text-gray-300">Quý</label>
                <select name="quy" id="quy"
                        class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white focus:border-[#d99a32] focus:outline-none">
                    @for($q = 1; $q <= 4; $q++)
                        <option value="{{ $q }}" {{ request('quy', ceil(date('m')/3)) == $q ? 'selected' : '' }}>
                            Quý {{ $q }}
                        </option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">Năm</label>
                <select name="nam"
                        class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white focus:border-[#d99a32] focus:outline-none">
                    @for($y = date('Y'); $y >= 2024; $y--)
                        <option value="{{ $y }}" {{ request('nam', date('Y')) == $y ? 'selected' : '' }}>
                            Năm {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full rounded-xl bg-[#d99a32] py-3 font-bold text-[#2b1208] transition hover:bg-[#d99a32]/85">
                    Lọc dữ liệu
                </button>
                <a href="{{ route('admin.cham-congs.index') }}" class="flex items-center justify-center rounded-xl border border-white/10 bg-[#1f1f1f] px-4 py-3 text-white transition hover:bg-white/5">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>

        <script>
            function toggleFilterFields() {
                var loaiLoc = document.getElementById('loai_loc').value;
                document.getElementById('filter_ngay_container').style.display = (loaiLoc === 'ngay') ? 'block' : 'none';
                document.getElementById('filter_thang_container').style.display = (loaiLoc === 'thang') ? 'block' : 'none';
                document.getElementById('filter_quy_container').style.display = (loaiLoc === 'quy') ? 'block' : 'none';
            }
            // Gọi 1 lần lúc load
            document.addEventListener('DOMContentLoaded', function() {
                toggleFilterFields();
            });
        </script>
    </div>

    <!-- Thông báo nếu có -->
    @if(session('success'))
        <div class="rounded-xl border border-green-500/20 bg-green-500/10 p-4 text-green-400">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-xl border border-red-500/20 bg-red-500/10 p-4 text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <!-- Bảng danh sách -->
    <div class="overflow-hidden rounded-2xl border border-white/10 bg-[#151515]">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#1f1f1f]">
                    <tr>
                        <th class="px-6 py-4 text-left">Ngày</th>
                        <th class="px-6 py-4 text-left">Nhân viên</th>
                        <th class="px-6 py-4 text-left">Giờ vào - ra</th>
                        <th class="px-6 py-4 text-center">Giờ làm việc</th>
                        <th class="px-6 py-4 text-center">Tăng ca</th>
                        <th class="px-6 py-4 text-center">Trạng thái</th>
                        <th class="px-6 py-4 text-left">Ghi chú</th>
                        <th class="px-6 py-4 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($chamCongs as $cc)
                        <tr class="hover:bg-white/5">
                            <td class="px-6 py-4 font-bold text-white">
                                {{ $cc->ngay->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white">
                                        <i class="fa-solid fa-user-tie"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-white">{{ $cc->nguoiDung->ho_ten }}</div>
                                        <div class="text-xs text-gray-400">{{ $cc->nguoiDung->email }}</div>
                                        @if($cc->nguoiDung->rapChieuPhim)
                                            <div class="text-xs text-[#d99a32] mt-0.5"><i class="fa-solid fa-film mr-1"></i>{{ $cc->nguoiDung->rapChieuPhim->ten_rap }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($cc->gio_vao && $cc->gio_ra)
                                    <div class="text-sm font-medium text-white">
                                        {{ \Carbon\Carbon::parse($cc->gio_vao)->format('H:i') }} - {{ \Carbon\Carbon::parse($cc->gio_ra)->format('H:i') }}
                                    </div>
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-green-400">
                                {{ $cc->so_gio_lam }}h
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-yellow-400">
                                {{ $cc->so_gio_tang_ca > 0 ? $cc->so_gio_tang_ca . 'h' : '0' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="space-y-1">
                                    @if($cc->nghi_phep)
                                        <span class="inline-block rounded-full bg-blue-500/20 px-3 py-1 text-xs font-bold text-blue-400">
                                            Nghỉ phép
                                        </span>
                                    @elseif($cc->nghi_khong_phep)
                                        <span class="inline-block rounded-full bg-red-500/20 px-3 py-1 text-xs font-bold text-red-400">
                                            Nghỉ không phép
                                        </span>
                                    @else
                                        <span class="inline-block rounded-full bg-green-500/20 px-3 py-1 text-xs font-bold text-green-400">
                                            Đi làm
                                        </span>
                                        @if($cc->di_muon)
                                            <br>
                                            <span class="inline-block rounded-full bg-yellow-500/20 px-3 py-1 text-xs font-bold text-yellow-400">
                                                Đi muộn
                                            </span>
                                        @endif
                                        @if($cc->ve_som)
                                            <br>
                                            <span class="inline-block rounded-full bg-orange-500/20 px-3 py-1 text-xs font-bold text-orange-400">
                                                Về sớm
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-300">
                                {{ $cc->ghi_chu ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('admin.cham-congs.edit', $cc) }}"
                                       class="rounded-lg bg-blue-500 px-3 py-2 text-white hover:bg-blue-600 transition">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.cham-congs.destroy', $cc) }}"
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa bản ghi chấm công này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg bg-red-500 px-3 py-2 text-white hover:bg-red-600 transition">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-gray-400">
                                Không tìm thấy dữ liệu chấm công nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="mt-4">
        {{ $chamCongs->appends(request()->query())->links() }}
    </div>
</div>
@endsection
