@extends('layouts.admin')

@section('title', 'Ma Trận Phân Quyền - CineHome')

@section('content')
<div class="space-y-6 text-white">

    {{-- HEADER & BUTTON LƯU --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-[#121212] p-5 rounded-2xl border border-white/10">
        <div>
            <h2 class="text-2xl font-black text-white m-0">Ma trận phân quyền hệ thống</h2>
            <p class="text-sm text-gray-400 m-0 mt-1">Thiết lập quyền truy cập chi tiết cho Quản Lý Rạp và Nhân Viên Quầy</p>
        </div>
        <button type="submit" form="formPhanQuyen" class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 text-white font-bold px-5 py-3 rounded-xl shadow-lg shadow-red-900/30 transition border-0 cursor-pointer">
            <i class="fa-solid fa-floppy-disk"></i>
            <span>Lưu cấu hình phân quyền</span>
        </button>
    </div>

    {{-- THỐNG KÊ 3 VAI TRÒ --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($danhSachVaiTro as $khoaVaiTro => $tenVaiTro)
            <div class="bg-[#121212] border border-white/10 rounded-2xl p-4 flex items-center justify-between shadow-lg">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-gray-400">{{ $tenVaiTro }}</div>
                    <div class="text-xl font-black text-[#d99a32] mt-1" id="dem-{{ $khoaVaiTro }}">0/0 quyền</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-white/5 flex items-center justify-center text-[#d99a32] text-xl border border-white/10">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
            </div>
        @endforeach
    </div>

    {{-- FORM MA TRẬN PHÂN QUYỀN --}}
    <form id="formPhanQuyen" action="{{ route('admin.phan-quyen.cap-nhat') }}" method="POST" class="space-y-4">
        @csrf

        @foreach(config('phan_quyen.nhom_quyen') as $khoaNhom => $nhom)
            <div class="bg-[#121212] border border-white/10 rounded-2xl overflow-hidden shadow-md">
                
                {{-- TIÊU ĐỀ NHÓM (CLICK ĐỂ ẨN/HIỆN) --}}
                <div class="flex items-center justify-between px-5 py-4 bg-white/5 border-b border-white/10 cursor-pointer select-none transition hover:bg-white/10" onclick="toggleNhom('{{ $khoaNhom }}')">
                    <div class="flex items-center gap-3">
                        <span class="text-red-500 text-xs">●</span>
                        <h3 class="text-base font-bold text-white m-0 uppercase tracking-wide">{{ $nhom['tieu_de'] }}</h3>
                        <span class="bg-white/10 text-gray-300 text-xs font-bold px-2.5 py-1 rounded-full border border-white/10">
                            {{ count($nhom['danh_sach_quyen']) }} quyền
                        </span>
                    </div>
                    <i id="icon-{{ $khoaNhom }}" class="fa-solid fa-chevron-down text-gray-400 transition-transform duration-200"></i>
                </div>

                {{-- BẢNG DANH SÁCH QUYỀN --}}
                <div id="nhom-{{ $khoaNhom }}" class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-white/10 bg-black/30 text-xs text-gray-400 uppercase tracking-wider">
                                <th class="py-3 px-5 font-semibold" style="width: 40%;">Chức năng / Mã quyền hệ thống</th>
                                @foreach($danhSachVaiTro as $khoaVaiTro => $tenVaiTro)
                                    <th class="py-3 px-4 text-center font-semibold" style="width: 20%;">
                                        <div class="text-gray-200 font-bold">{{ $tenVaiTro }}</div>
                                        @if($khoaVaiTro !== 'super_admin')
                                            <div class="mt-1 text-[11px] normal-case">
                                                <button type="button" onclick="chonCotVaiTro('{{ $khoaNhom }}', '{{ $khoaVaiTro }}', true)" class="text-[#d99a32] hover:underline bg-transparent border-0 p-0 cursor-pointer">Tất cả</button>
                                                <span class="text-gray-600 mx-1">|</span>
                                                <button type="button" onclick="chonCotVaiTro('{{ $khoaNhom }}', '{{ $khoaVaiTro }}', false)" class="text-gray-400 hover:underline bg-transparent border-0 p-0 cursor-pointer">Bỏ chọn</button>
                                            </div>
                                        @else
                                            <div class="mt-1 text-emerald-400 text-[11px] font-bold normal-case flex items-center justify-center gap-1">
                                                <i class="fa-solid fa-lock text-[10px]"></i> Toàn quyền
                                            </div>
                                        @endif
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-sm">
                            @foreach($nhom['danh_sach_quyen'] as $maQuyen => $tenQuyen)
                                <tr class="hover:bg-white/5 transition duration-150">
                                    <td class="py-3 px-5">
                                        {{-- 🌟 ĐÃ ĐẨY TÊN CHỨC NĂNG LÊN TRÊN & MÃ QUYỀN MÀU VÀNG XUỐNG DƯỚI --}}
                                        <div class="text-white font-bold text-sm">{{ $tenQuyen }}</div>
                                        <div class="font-mono text-[#d99a32]/80 text-xs mt-0.5 tracking-wide">{{ $maQuyen }}</div>
                                    </td>
                                    @foreach($danhSachVaiTro as $khoaVaiTro => $tenVaiTro)
                                        @php
                                            $laSuperAdmin = ($khoaVaiTro === 'super_admin');
                                            $daDuocChon = $laSuperAdmin || (isset($maTranQuyen[$khoaVaiTro]) && in_array($maQuyen, $maTranQuyen[$khoaVaiTro]));
                                        @endphp
                                        <td class="py-3 px-4 text-center">
                                            <input 
                                                type="checkbox" 
                                                class="w-5 h-5 accent-[#d99a32] rounded cursor-pointer cot-{{ $khoaNhom }}-{{ $khoaVaiTro }} dem-vai-tro-{{ $khoaVaiTro }}" 
                                                name="danh_sach_quyen[{{ $khoaVaiTro }}][]" 
                                                value="{{ $maQuyen }}"
                                                {{ $daDuocChon ? 'checked' : '' }}
                                                {{ $laSuperAdmin ? 'disabled' : '' }}
                                            >
                                            @if($laSuperAdmin)
                                                <input type="hidden" name="danh_sach_quyen[super_admin][]" value="{{ $maQuyen }}">
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        @endforeach

    </form>

</div>

<script>
    function toggleNhom(khoaNhom) {
        const nhomEl = document.getElementById(`nhom-${khoaNhom}`);
        const iconEl = document.getElementById(`icon-${khoaNhom}`);
        if (nhomEl) nhomEl.classList.toggle('hidden');
        if (iconEl) iconEl.classList.toggle('rotate-180');
    }

    function chonCotVaiTro(khoaNhom, khoaVaiTro, trangThai) {
        if (khoaVaiTro === 'super_admin') return;
        const danhSachOTich = document.querySelectorAll(`.cot-${khoaNhom}-${khoaVaiTro}`);
        danhSachOTich.forEach(cb => {
            if (!cb.disabled) cb.checked = trangThai;
        });
        capNhatSoLuong();
    }

    function capNhatSoLuong() {
        const danhSachVaiTro = @json(array_keys(config('phan_quyen.vai_tro')));
        danhSachVaiTro.forEach(khoaVaiTro => {
            const tong = document.querySelectorAll(`.dem-vai-tro-${khoaVaiTro}`).length;
            const daTich = document.querySelectorAll(`.dem-vai-tro-${khoaVaiTro}:checked`).length;
            const theHienThi = document.getElementById(`dem-${khoaVaiTro}`);
            if (theHienThi) theHienThi.innerText = `${daTich}/${tong} quyền`;
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        capNhatSoLuong();
        document.querySelectorAll('.dem-vai-tro-quan_ly_rap, .dem-vai-tro-nhan_vien').forEach(cb => {
            cb.addEventListener('change', capNhatSoLuong);
        });
    });
</script>
@endsection