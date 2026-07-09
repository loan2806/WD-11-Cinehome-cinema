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

    <!-- Bộ lọc -->
    <div class="rounded-2xl border border-white/10 bg-[#151515] p-5">
        <form method="GET" action="{{ route('admin.bang-luongs.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-4">
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
                <label class="mb-2 block text-sm font-bold text-gray-300">Tháng</label>
                <select name="thang"
                        class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white focus:border-[#d99a32] focus:outline-none">
                    <option value="">-- Tất cả tháng --</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('thang') == $m ? 'selected' : '' }}>
                            Tháng {{ $m }}
                        </option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">Năm</label>
                <select name="nam"
                        class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white focus:border-[#d99a32] focus:outline-none">
                    <option value="">-- Tất cả năm --</option>
                    @for($y = date('Y'); $y >= 2024; $y--)
                        <option value="{{ $y }}" {{ request('nam') == $y ? 'selected' : '' }}>
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
                        <th class="whitespace-nowrap px-4 py-4 text-center align-middle">Tháng/Năm</th>
                        <th class="whitespace-nowrap px-4 py-4 text-left align-middle">Nhân viên</th>
                        <th class="whitespace-nowrap px-4 py-4 text-center align-middle">Ngày công</th>
                        <th class="whitespace-nowrap px-4 py-4 text-center align-middle">Tăng ca</th>
                        <th class="whitespace-nowrap px-4 py-4 text-right align-middle">Lương cơ bản</th>
                        <th class="whitespace-nowrap px-4 py-4 text-right align-middle">Thưởng / Phụ cấp</th>
                        <th class="whitespace-nowrap px-4 py-4 text-right align-middle text-red-400">Phạt</th>
                        <th class="whitespace-nowrap px-4 py-4 text-right align-middle text-[#d99a32] font-black">Lương thực nhận</th>
                        <th class="whitespace-nowrap px-4 py-4 text-center align-middle">Trạng thái</th>
                        <th class="whitespace-nowrap px-4 py-4 text-center align-middle">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 text-white">
                    @forelse($bangLuongs as $bl)
                        <tr class="hover:bg-white/5">
                            <td class="px-4 py-4 font-bold text-center">
                                {{ sprintf("%02d", $bl->thang) }}/{{ $bl->nam }}
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-bold">{{ $bl->nguoiDung->ho_ten }}</div>
                                <div class="text-xs text-gray-400">{{ $bl->nguoiDung->email }}</div>
                            </td>
                            <td class="px-4 py-4 text-center font-bold text-green-400">
                                {{ $bl->tong_ngay_cong }}
                            </td>
                            <td class="px-4 py-4 text-center font-bold text-yellow-400">
                                {{ $bl->tong_gio_tang_ca }}h
                            </td>
                            <td class="px-4 py-4 text-right font-medium">
                                {{ number_format($bl->luong_co_ban, 0, ',', '.') }}đ
                            </td>
                            <td class="px-4 py-4 text-right text-xs">
                                <span>Phụ cấp: {{ number_format($bl->phu_cap, 0, ',', '.') }}đ</span><br>
                                <span class="text-green-400">Thưởng: {{ number_format($bl->thuong, 0, ',', '.') }}đ</span>
                            </td>
                            <td class="px-4 py-4 text-right font-medium text-red-400">
                                -{{ number_format($bl->phat, 0, ',', '.') }}đ
                            </td>
                            <td class="px-4 py-4 text-right font-black text-[#d99a32] text-base">
                                {{ number_format($bl->luong_thuc_nhan, 0, ',', '.') }}đ
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if(isset($bl->is_tam_tinh) && $bl->is_tam_tinh)
                                    <span class="rounded-full bg-gray-500/20 px-3 py-1 text-xs font-bold text-gray-400">
                                        Tạm tính
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('admin.bang-luongs.toggle-payment', $bl->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        @if($bl->trang_thai === 'da_thanh_toan')
                                            <button type="submit" class="rounded-full bg-green-500/20 px-3 py-1 text-xs font-bold text-green-400 hover:bg-green-500/30 transition">
                                                Đã chi trả
                                            </button>
                                        @else
                                            <button type="submit" class="rounded-full bg-red-500/20 px-3 py-1 text-xs font-bold text-red-400 hover:bg-red-500/30 transition">
                                                Chưa chi trả
                                            </button>
                                        @endif
                                    </form>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex justify-center gap-1">
                                    <a href="{{ route('admin.bang-luongs.calculate', ['nhan_vien_id' => $bl->nguoi_dung_id, 'thang' => $bl->thang, 'nam' => $bl->nam]) }}"
                                       class="rounded-lg bg-blue-500 px-3 py-2 text-white hover:bg-blue-600 transition" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @if(!isset($bl->is_tam_tinh) || !$bl->is_tam_tinh)
                                        <form method="POST" action="{{ route('admin.bang-luongs.destroy', $bl->id) }}"
                                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa bảng lương này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg bg-red-500 px-3 py-2 text-white hover:bg-red-600 transition">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="py-10 text-center text-gray-400">
                                Không có dữ liệu
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="mt-4">
        {{ $bangLuongs->appends(request()->query())->links() }}
    </div>
</div>
@endsection
