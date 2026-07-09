@extends('layouts.admin')

@section('title', 'Tính & Chốt lương nhân viên')
@section('page-title', 'Tính & Chốt lương nhân viên')
@section('page-subtitle', 'Tổng hợp dữ liệu chấm công và tính lương thực tế cho nhân viên')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.bang-luongs.index') }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-[#151515] text-white transition hover:bg-white/5">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-2xl font-black text-white">Tính & Chốt lương</h2>
            <p class="text-gray-400">Chọn nhân viên và thời gian để tổng hợp bảng lương</p>
        </div>
    </div>

    <!-- Form Chọn thông tin tính lương -->
    <div class="rounded-2xl border border-white/10 bg-[#151515] p-6">
        <form method="GET" action="{{ route('admin.bang-luongs.calculate') }}" class="grid grid-cols-1 gap-6 md:grid-cols-4">
            <div>
                <label for="nhan_vien_id" class="mb-2 block text-sm font-bold text-gray-300">Nhân viên <span class="text-red-500">*</span></label>
                <select name="nhan_vien_id" id="nhan_vien_id" required
                        class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white focus:border-[#d99a32] focus:outline-none">
                    <option value="">-- Chọn nhân viên --</option>
                    @foreach($nhanViens as $nv)
                        <option value="{{ $nv->id }}" {{ $nhanVienSelectedId == $nv->id ? 'selected' : '' }}>
                            {{ $nv->ho_ten }} ({{ $nv->email }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="thang" class="mb-2 block text-sm font-bold text-gray-300">Tháng <span class="text-red-500">*</span></label>
                <select name="thang" id="thang" required
                        class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white focus:border-[#d99a32] focus:outline-none">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $thang == $m ? 'selected' : '' }}>
                            Tháng {{ $m }}
                        </option>
                    @endfor
                </select>
            </div>
            <div>
                <label for="nam" class="mb-2 block text-sm font-bold text-gray-300">Năm <span class="text-red-500">*</span></label>
                <select name="nam" id="nam" required
                        class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white focus:border-[#d99a32] focus:outline-none">
                    @for($y = date('Y'); $y >= 2024; $y--)
                        <option value="{{ $y }}" {{ $nam == $y ? 'selected' : '' }}>
                            Năm {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-xl bg-[#d99a32] py-3 font-bold text-[#2b1208] transition hover:bg-[#d99a32]/85">
                    Tổng hợp số liệu
                </button>
            </div>
        </form>
    </div>

    <!-- Kết quả tính toán và Form chốt lương -->
    @if($dataCalculated)
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <!-- Cột trái: Tóm tắt chấm công -->
            <div class="md:col-span-2 space-y-6">
                <div class="rounded-2xl border border-white/10 bg-[#151515] p-6 space-y-4">
                    <h3 class="text-lg font-bold text-[#d99a32] border-b border-white/5 pb-2">
                        <i class="fa-solid fa-calendar-check mr-2"></i>Chi tiết công tháng {{ $thang }}/{{ $nam }}
                    </h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-xl bg-[#101010] p-4 text-center">
                            <div class="text-2xl font-black text-green-400">{{ $dataCalculated['tong_ngay_cong'] }}</div>
                            <div class="text-xs text-gray-400 mt-1">Ngày công thực tế</div>
                        </div>
                        <div class="rounded-xl bg-[#101010] p-4 text-center">
                            <div class="text-2xl font-black text-green-400">{{ $dataCalculated['tong_gio_lam'] }}h</div>
                            <div class="text-xs text-gray-400 mt-1">Tổng số giờ làm</div>
                        </div>
                        <div class="rounded-xl bg-[#101010] p-4 text-center">
                            <div class="text-2xl font-black text-yellow-400">{{ $dataCalculated['tong_gio_tang_ca'] }}h</div>
                            <div class="text-xs text-gray-400 mt-1">Tổng giờ tăng ca</div>
                        </div>
                        <div class="rounded-xl bg-[#101010] p-4 text-center">
                            <div class="text-2xl font-black text-blue-400">{{ $dataCalculated['so_ngay_nghi_phep'] }}</div>
                            <div class="text-xs text-gray-400 mt-1">Số ngày nghỉ phép</div>
                        </div>
                    </div>

                    <div class="divide-y divide-white/5">
                        <div class="flex justify-between py-2.5">
                            <span class="text-gray-400">Số lần đi muộn:</span>
                            <span class="font-bold text-orange-400">{{ $dataCalculated['so_lan_di_muon'] }} lần</span>
                        </div>
                        <div class="flex justify-between py-2.5">
                            <span class="text-gray-400">Số lần về sớm:</span>
                            <span class="font-bold text-yellow-500">{{ $dataCalculated['so_lan_ve_som'] }} lần</span>
                        </div>
                        <div class="flex justify-between py-2.5">
                            <span class="text-gray-400">Nghỉ không phép:</span>
                            <span class="font-bold text-red-500">{{ $dataCalculated['so_ngay_nghi_khong_phep'] }} ngày</span>
                        </div>
                        <div class="flex justify-between py-2.5">
                            <span class="text-gray-400">Lương cơ bản cấu hình:</span>
                            <span class="font-bold text-white">{{ number_format($dataCalculated['luong_co_ban'], 0, ',', '.') }} đ</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-[#151515] p-6 space-y-4">
                    <h3 class="text-lg font-bold text-[#d99a32] border-b border-white/5 pb-2">
                        <i class="fa-solid fa-scale-balanced mr-2"></i>Tính toán lương tạm tính
                    </h3>
                    
                    <div class="divide-y divide-white/5">
                        <div class="flex justify-between py-3">
                            <span class="text-gray-400">Lương theo ngày công thực tế (Lương cơ bản / 26 * số công):</span>
                            <span class="font-bold text-white">{{ number_format($dataCalculated['luong_thoi_gian'], 0, ',', '.') }} đ</span>
                        </div>
                        <div class="flex justify-between py-3">
                            <span class="text-gray-400">Lương tăng ca (Số giờ * Lương giờ * 1.5):</span>
                            <span class="font-bold text-white">{{ number_format($dataCalculated['luong_tang_ca'], 0, ',', '.') }} đ</span>
                        </div>
                        <div class="flex justify-between py-3 text-red-400">
                            <span>Phạt đi muộn, về sớm, không phép (Tự động):</span>
                            <span class="font-bold">-{{ number_format($dataCalculated['phat_tu_dong'], 0, ',', '.') }} đ</span>
                        </div>
                        <div class="flex justify-between py-3 font-black text-green-400 text-lg">
                            <span>Tổng tạm tính:</span>
                            <span>{{ number_format($dataCalculated['luong_thuc_nhan_tam_tinh'], 0, ',', '.') }} đ</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Form nhập điều chỉnh & Chốt -->
            <div class="md:col-span-1">
                <div class="rounded-2xl border border-white/10 bg-[#151515] p-6 space-y-6 sticky top-24">
                    <h3 class="text-lg font-bold text-white border-b border-white/5 pb-2">
                        <i class="fa-solid fa-stamp mr-2"></i>Chốt bảng lương
                    </h3>
                    
                    <form method="POST" action="{{ route('admin.bang-luongs.store') }}" class="space-y-4">
                        @csrf
                        
                        <!-- Hidden inputs -->
                        <input type="hidden" name="nguoi_dung_id" value="{{ $nhanVienSelectedId }}">
                        <input type="hidden" name="thang" value="{{ $thang }}">
                        <input type="hidden" name="nam" value="{{ $nam }}">
                        <input type="hidden" name="tong_ngay_cong" value="{{ $dataCalculated['tong_ngay_cong'] }}">
                        <input type="hidden" name="tong_gio_lam" value="{{ $dataCalculated['tong_gio_lam'] }}">
                        <input type="hidden" name="tong_gio_tang_ca" value="{{ $dataCalculated['tong_gio_tang_ca'] }}">
                        <input type="hidden" name="so_lan_di_muon" value="{{ $dataCalculated['so_lan_di_muon'] }}">
                        <input type="hidden" name="so_lan_ve_som" value="{{ $dataCalculated['so_lan_ve_som'] }}">
                        <input type="hidden" name="so_ngay_nghi_phep" value="{{ $dataCalculated['so_ngay_nghi_phep'] }}">
                        <input type="hidden" name="so_ngay_nghi_khong_phep" value="{{ $dataCalculated['so_ngay_nghi_khong_phep'] }}">
                        <input type="hidden" name="luong_co_ban" value="{{ $dataCalculated['luong_co_ban'] }}">

                        <!-- Phụ cấp -->
                        <div>
                            <label for="phu_cap" class="mb-2 block text-sm font-bold text-gray-300">Phụ cấp (đ)</label>
                            <input type="number" name="phu_cap" id="phu_cap" value="0" min="0" step="1000"
                                   class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white focus:border-[#d99a32] focus:outline-none">
                        </div>

                        <!-- Thưởng -->
                        <div>
                            <label for="thuong" class="mb-2 block text-sm font-bold text-gray-300">Thưởng thêm (đ)</label>
                            <input type="number" name="thuong" id="thuong" value="0" min="0" step="1000"
                                   class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white focus:border-[#d99a32] focus:outline-none">
                        </div>

                        <!-- Phạt -->
                        <div>
                            <label for="phat" class="mb-2 block text-sm font-bold text-gray-300">Phạt khấu trừ (đ)</label>
                            <input type="number" name="phat" id="phat" value="{{ (int)$dataCalculated['phat_tu_dong'] }}" min="0" step="1000"
                                   class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white focus:border-[#d99a32] focus:outline-none">
                        </div>

                        <!-- Lương thực nhận -->
                        <div class="border-t border-white/10 pt-4">
                            <label class="mb-2 block text-sm font-bold text-[#d99a32]">LƯƠNG THỰC NHẬN CHỐT (đ)</label>
                            <input type="number" name="luong_thuc_nhan" id="luong_thuc_nhan" value="{{ (int)$dataCalculated['luong_thuc_nhan_tam_tinh'] }}" readonly
                                   class="w-full rounded-xl border-0 bg-[#2b1208]/50 px-4 py-4 text-[#d99a32] font-black text-xl text-right cursor-not-allowed">
                        </div>

                        <button type="submit" class="w-full rounded-xl bg-[#d99a32] py-4 font-bold text-[#2b1208] text-base transition hover:bg-[#d99a32]/85 shadow-lg shadow-[#d99a32]/10">
                            <i class="fa-solid fa-check-double mr-2"></i>Chốt & Lưu bảng lương
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const luongThoiGian = {{ $dataCalculated['luong_thoi_gian'] }};
                const luongTangCa = {{ $dataCalculated['luong_tang_ca'] }};
                
                const phuCapInput = document.getElementById('phu_cap');
                const thuongInput = document.getElementById('thuong');
                const phatInput = document.getElementById('phat');
                const luongThucNhanInput = document.getElementById('luong_thuc_nhan');

                function updateLuongThucNhan() {
                    const phuCap = parseFloat(phuCapInput.value) || 0;
                    const thuong = parseFloat(thuongInput.value) || 0;
                    const phat = parseFloat(phatInput.value) || 0;

                    const thucNhan = Math.max(0, luongThoiGian + luongTangCa + phuCap + thuong - phat);
                    luongThucNhanInput.value = Math.round(thucNhan);
                }

                phuCapInput.addEventListener('input', updateLuongThucNhan);
                thuongInput.addEventListener('input', updateLuongThucNhan);
                phatInput.addEventListener('input', updateLuongThucNhan);
            });
        </script>
    @elseif($nhanVienSelectedId)
        <div class="rounded-xl border border-yellow-500/20 bg-yellow-500/10 p-5 text-yellow-500 text-center">
            <i class="fa-solid fa-triangle-exclamation mr-2 text-xl"></i> Không tìm thấy dữ liệu chấm công cho nhân viên này trong tháng {{ $thang }}/{{ $nam }}!
        </div>
    @endif
</div>
@endsection
