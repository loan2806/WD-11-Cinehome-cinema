@extends('layouts.admin')

@section('title', 'Thống kê lương nhân viên')
@section('page-title', 'Thống kê lương nhân viên')
@section('page-subtitle', 'Quản lý và thống kê bảng lương của nhân viên theo chi nhánh')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-white">Thống kê lương</h2>
            <p class="text-gray-400">Xem báo cáo và tính toán lương thực nhận dựa trên chấm công</p>
        </div>
        <a href="{{ route('admin.bang-luongs.calculate') }}"
           class="rounded-xl bg-[#d99a32] px-5 py-3 font-bold text-[#2b1208] transition hover:scale-105">
            <i class="fa-solid fa-calculator mr-2"></i> Tính & Chốt lương tháng
        </a>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="rounded-2xl border border-white/10 bg-[#151515] p-5 flex items-center justify-between transition hover:border-[#d99a32]/50">
            <div>
                <p class="text-sm text-gray-400">Đã chốt lương</p>
                <h3 class="text-2xl font-black text-white mt-1">{{ $thongKe['so_nhan_vien_da_chot'] }} <span class="text-sm font-normal text-gray-500">nhân viên</span></h3>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-500/20 text-blue-400 text-xl">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>
        <div class="rounded-2xl border border-white/10 bg-[#151515] p-5 flex items-center justify-between transition hover:border-[#d99a32]/50">
            <div>
                <p class="text-sm text-gray-400">Tổng quỹ lương tháng</p>
                <h3 class="text-2xl font-black text-[#d99a32] mt-1">{{ number_format($thongKe['tong_chi_tra'], 0, ',', '.') }}đ</h3>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#d99a32]/20 text-[#d99a32] text-xl">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
        </div>
        <div class="rounded-2xl border border-white/10 bg-[#151515] p-5 flex items-center justify-between transition hover:border-[#d99a32]/50">
            <div>
                <p class="text-sm text-gray-400">Đã thanh toán</p>
                <h3 class="text-2xl font-black text-green-400 mt-1">{{ number_format($thongKe['da_thanh_toan'], 0, ',', '.') }}đ</h3>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-500/20 text-green-400 text-xl">
                <i class="fa-solid fa-check-double"></i>
            </div>
        </div>
    </div>

    <!-- Bộ lọc -->
    <div class="rounded-2xl border border-white/10 bg-[#151515] p-5">
        <form method="GET" action="{{ route('admin.bang-luongs.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-5">
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
                    <option value="thang" {{ (!isset($loaiLoc) || $loaiLoc == 'thang') ? 'selected' : '' }}>Tháng</option>
                    <option value="quy" {{ (isset($loaiLoc) && $loaiLoc == 'quy') ? 'selected' : '' }}>Quý</option>
                    <option value="nam" {{ (isset($loaiLoc) && $loaiLoc == 'nam') ? 'selected' : '' }}>Năm</option>
                </select>
            </div>
            <div id="filter_thang_container">
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
            <div id="filter_quy_container" style="display: none;">
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
                    Lọc thống kê
                </button>
                <a href="{{ route('admin.bang-luongs.index') }}" class="flex items-center justify-center rounded-xl border border-white/10 bg-[#1f1f1f] px-4 py-3 text-white transition hover:bg-white/5">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>

        <script>
            function toggleFilterFields() {
                var loaiLoc = document.getElementById('loai_loc').value;
                document.getElementById('filter_thang_container').style.display = (loaiLoc === 'thang') ? 'block' : 'none';
                document.getElementById('filter_quy_container').style.display = (loaiLoc === 'quy') ? 'block' : 'none';
            }
            // Gọi 1 lần lúc load
            document.addEventListener('DOMContentLoaded', function() {
                toggleFilterFields();
            });
        </script>
    </div>

    <!-- Thông báo -->
    @if(session('success'))
        <div class="rounded-xl border border-green-500/20 bg-green-500/10 p-4 text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <!-- Bảng danh sách -->
    <div class="overflow-hidden rounded-2xl border border-white/10 bg-[#151515]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-[#1f1f1f] text-gray-300">
                    <tr>
                        <th class="whitespace-nowrap px-4 py-4 text-left align-middle w-10"></th>
                        <th class="whitespace-nowrap px-4 py-4 text-left align-middle">Nhân viên</th>
                        <th class="whitespace-nowrap px-4 py-4 text-center align-middle">Tổng ngày công</th>
                        <th class="whitespace-nowrap px-4 py-4 text-center align-middle">Tổng tăng ca</th>
                        <th class="whitespace-nowrap px-4 py-4 text-right align-middle text-red-400">Tổng phạt</th>
                        <th class="whitespace-nowrap px-4 py-4 text-right align-middle text-[#d99a32] font-black">Tổng thực nhận</th>
                        <th class="whitespace-nowrap px-4 py-4 text-center align-middle">Đã chốt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 text-white">
                    @forelse($employeesPaginator as $emp)
                        <!-- Main Row -->
                        <tr class="hover:bg-white/5 cursor-pointer transition" onclick="toggleRow('details-{{ $emp->id }}', 'icon-{{ $emp->id }}')">
                            <td class="px-4 py-4 text-center text-gray-400">
                                <i id="icon-{{ $emp->id }}" class="fa-solid fa-chevron-right transition-transform duration-200"></i>
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-bold">{{ $emp->ho_ten }}</div>
                                <div class="text-xs text-gray-400">{{ $emp->email }}</div>
                            </td>
                            <td class="px-4 py-4 text-center font-bold text-green-400">
                                {{ $emp->summary->tong_ngay_cong }}
                            </td>
                            <td class="px-4 py-4 text-center font-bold text-yellow-400">
                                {{ $emp->summary->tong_gio_tang_ca }}h
                            </td>
                            <td class="px-4 py-4 text-right font-medium text-red-400">
                                -{{ number_format($emp->summary->tong_phat, 0, ',', '.') }}đ
                            </td>
                            <td class="px-4 py-4 text-right font-black text-[#d99a32] text-base">
                                {{ number_format($emp->summary->tong_thuc_nhan, 0, ',', '.') }}đ
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="rounded-full bg-blue-500/20 px-3 py-1 text-xs font-bold {{ $emp->summary->so_thang_da_chot == $emp->summary->tong_thang && $emp->summary->tong_thang > 0 ? 'text-blue-400' : 'text-gray-400' }}">
                                    {{ $emp->summary->so_thang_da_chot }} / {{ $emp->summary->tong_thang }} tháng
                                </span>
                            </td>
                        </tr>

                        <!-- Expanded Row (Details) -->
                        <tr id="details-{{ $emp->id }}" class="hidden bg-[#101010]/50 border-t-0">
                            <td colspan="7" class="p-0">
                                <div class="px-10 py-4">
                                    @if($emp->monthly_data->count() > 0)
                                        <table class="w-full text-xs text-left mb-2">
                                            <thead class="text-gray-400 border-b border-white/5">
                                                <tr>
                                                    <th class="py-2 px-3">Tháng</th>
                                                    <th class="py-2 px-3 text-center">Ngày công</th>
                                                    <th class="py-2 px-3 text-center">Tăng ca</th>
                                                    <th class="py-2 px-3 text-right">Lương CB</th>
                                                    <th class="py-2 px-3 text-right">Thưởng/Phụ cấp</th>
                                                    <th class="py-2 px-3 text-right">Phạt</th>
                                                    <th class="py-2 px-3 text-right font-bold text-[#d99a32]">Thực nhận</th>
                                                    <th class="py-2 px-3 text-center">Trạng thái</th>
                                                    <th class="py-2 px-3 text-center">Thao tác</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-white/5">
                                                @foreach($emp->monthly_data as $bl)
                                                    <tr class="hover:bg-white/5">
                                                        <td class="py-2 px-3 font-bold">{{ sprintf("%02d", $bl->thang) }}/{{ $bl->nam }}</td>
                                                        <td class="py-2 px-3 text-center text-green-400">{{ $bl->tong_ngay_cong }}</td>
                                                        <td class="py-2 px-3 text-center text-yellow-400">{{ $bl->tong_gio_tang_ca }}h</td>
                                                        <td class="py-2 px-3 text-right">{{ number_format($bl->luong_co_ban, 0, ',', '.') }}đ</td>
                                                        <td class="py-2 px-3 text-right">
                                                            <div class="text-[10px]">PC: {{ number_format($bl->phu_cap, 0, ',', '.') }}đ</div>
                                                            <div class="text-[10px] text-green-400">Th: {{ number_format($bl->thuong, 0, ',', '.') }}đ</div>
                                                        </td>
                                                        <td class="py-2 px-3 text-right text-red-400">-{{ number_format($bl->phat, 0, ',', '.') }}đ</td>
                                                        <td class="py-2 px-3 text-right font-bold text-[#d99a32]">{{ number_format($bl->luong_thuc_nhan, 0, ',', '.') }}đ</td>
                                                        <td class="py-2 px-3 text-center">
                                                            @if(isset($bl->is_tam_tinh) && $bl->is_tam_tinh)
                                                                <span class="rounded bg-gray-500/20 px-2 py-0.5 text-[10px] font-bold text-gray-400">Tạm tính</span>
                                                            @else
                                                                <form method="POST" action="{{ route('admin.bang-luongs.toggle-payment', $bl->id) }}" class="inline">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit" class="rounded px-2 py-0.5 text-[10px] font-bold transition {{ $bl->trang_thai === 'da_thanh_toan' ? 'bg-green-500/20 text-green-400 hover:bg-green-500/30' : 'bg-red-500/20 text-red-400 hover:bg-red-500/30' }}">
                                                                        {{ $bl->trang_thai === 'da_thanh_toan' ? 'Đã chi trả' : 'Chưa chi trả' }}
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </td>
                                                        <td class="py-2 px-3 text-center">
                                                            <div class="flex justify-center gap-1">
                                                                <a href="{{ route('admin.bang-luongs.calculate', ['nhan_vien_id' => $emp->id, 'thang' => $bl->thang, 'nam' => $bl->nam]) }}"
                                                                   class="rounded bg-blue-500/20 px-2 py-1 text-blue-400 hover:bg-blue-500/40 transition" title="Xem chi tiết">
                                                                    <i class="fa-solid fa-eye"></i>
                                                                </a>
                                                                @if(!isset($bl->is_tam_tinh) || !$bl->is_tam_tinh)
                                                                    <form method="POST" action="{{ route('admin.bang-luongs.destroy', $bl->id) }}" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bảng lương này?')">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="rounded bg-red-500/20 px-2 py-1 text-red-400 hover:bg-red-500/40 transition">
                                                                            <i class="fa-solid fa-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="text-center text-gray-400 py-4 text-xs italic">Không có dữ liệu trong khoảng thời gian này</div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-gray-400">
                                Không có dữ liệu
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="mt-4">
        {{ $employeesPaginator->appends(request()->query())->links() }}
    </div>

    <script>
        function toggleRow(rowId, iconId) {
            const row = document.getElementById(rowId);
            const icon = document.getElementById(iconId);
            
            if (row.classList.contains('hidden')) {
                row.classList.remove('hidden');
                icon.classList.add('rotate-90');
            } else {
                row.classList.add('hidden');
                icon.classList.remove('rotate-90');
            }
        }
    </script>
</div>
@endsection
