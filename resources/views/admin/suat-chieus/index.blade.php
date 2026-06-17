@extends('layouts.admin')

@section('page-title', 'Bảng Điều Phối Lịch Chiếu')

@section('content')

    <div class="space-y-5 overflow-x-hidden">

        {{-- MAIN HEADER CONTROL (CÂN BẰNG TỶ LỆ) --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-[#110c06]/30 p-5 rounded-2xl border border-[#d99a32]/10 backdrop-blur-md">
            <div>
                <h5 class="text-2xl font-black tracking-wide text-white flex items-center gap-2.5 m-0">
                    <i class="fa-solid fa-layer-group text-[#d99a32]"></i>
                    Quản Lý Suất Chiếu Theo Phim
                </h5>
                <p class="text-xs text-gray-400 mt-1 m-0">
                    Giao diện thu gọn thông minh. Bấm vào từng bộ phim để kiểm tra chi tiết các khung giờ chiếu, phòng ban và giá vé tương ứng.
                </p>
            </div>

            <a href="{{ route('admin.suat-chieus.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-black text-white shadow-lg shadow-[#d99a32]/5 transition duration-300 hover:scale-[1.02] hover:opacity-95 no-underline border-0 cursor-pointer">
                <i class="fa-solid fa-calendar-plus text-sm"></i>
                Lên Lịch Chiếu Hàng Loạt
            </a>
        </div>

        {{-- SMART FILTER PANEL --}}
        <form method="GET" action="{{ route('admin.suat-chieus.index') }}" 
            class="p-4 rounded-xl border border-white/5 bg-[#121212]/60 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end m-0">
            
            <div class="space-y-1.5">
                <label class="text-xs uppercase tracking-wider text-gray-400 font-black pl-1 block">Bộ Phim Điện Ảnh</label>
                <select name="phim_id"
                    class="h-10 w-full rounded-lg border border-white/10 bg-[#181818] px-3 text-sm text-gray-200 outline-none focus:border-[#d99a32] transition">
                    <option value="">-- Tất cả tác phẩm --</option>
                    @foreach ($phims as $itemPhim)
                        <option value="{{ $itemPhim->id }}" {{ request('phim_id') == $itemPhim->id ? 'selected' : '' }}>
                            {{ $itemPhim->ten_phim }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs uppercase tracking-wider text-gray-400 font-black pl-1 block">Khu Vực Phòng</label>
                <select name="phong_chieu_id"
                    class="h-10 w-full rounded-lg border border-white/10 bg-[#181818] px-3 text-sm text-gray-200 outline-none focus:border-[#d99a32] transition">
                    <option value="">-- Tất cả phòng chiếu --</option>
                    @foreach ($phongChieus as $phong)
                        <option value="{{ $phong->id }}" {{ request('phong_chieu_id') == $phong->id ? 'selected' : '' }}>
                            {{ $phong->ten_phong }} ({{ strtoupper($phong->loai_phong) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs uppercase tracking-wider text-gray-400 font-black pl-1 block">Trạng Thái Vận Hành</label>
                <select name="trang_thai"
                    class="h-10 w-full rounded-lg border border-white/10 bg-[#181818] px-3 text-sm text-gray-200 outline-none focus:border-[#d99a32] transition">
                    <option value="">-- Tất cả trạng thái --</option>
                    @foreach (\App\Models\SuatChieu::TRANG_THAI_LIST as $value => $label)
                        <option value="{{ $value }}" {{ request('trang_thai') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs uppercase tracking-wider text-gray-400 font-black pl-1 block">Ngày Chiếu Mục Tiêu</label>
                <input type="date" name="ngay_chieu" value="{{ request('ngay_chieu') }}"
                    class="h-10 w-full rounded-lg border border-white/10 bg-[#181818] px-3 text-sm text-gray-200 outline-none focus:border-[#d99a32] transition">
            </div>

            <div class="flex gap-2">
                <button type="submit"
                    class="h-10 flex-1 rounded-lg bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-sm font-bold text-white shadow-md border-0 cursor-pointer flex items-center justify-center gap-1.5 hover:opacity-90 transition">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Lọc Tìm
                </button>
                <a href="{{ route('admin.suat-chieus.index') }}"
                    class="h-10 px-3 rounded-lg border border-white/10 bg-white/5 text-sm font-bold text-gray-400 hover:text-white hover:bg-white/10 transition flex items-center justify-center no-underline">
                    <i class="fa-solid fa-rotate"></i>
                </a>
            </div>
        </form>

        {{-- DROPDOWN GROUPED LIST MATRIX --}}
        <div class="space-y-3">

            @forelse ($phimsPhanTrang as $keyMovie => $phim)
                {{-- TỪNG KHỐI DROPDOWN PHIM (CHỈNH CO GIÃN ĐỒNG BỘ SIDEBAR) --}}
                <div class="movie-dropdown-card rounded-xl border border-white/5 bg-[#121212]/40 backdrop-blur-md overflow-hidden transition-all duration-300">
                    
                    {{-- ACCORDION HEADER --}}
                    <div class="movie-dropdown-trigger flex items-center justify-between p-4 bg-white/[0.02] hover:bg-white/[0.04] cursor-pointer select-none transition-all duration-200">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="h-10 w-8 rounded-lg bg-gradient-to-br from-[#8a4a21]/20 to-[#d99a32]/20 border border-[#d99a32]/30 flex items-center justify-center text-[#d99a32] shrink-0 shadow-inner">
                                <i class="fa-solid fa-clapperboard text-base"></i>
                            </div>
                            
                            <div class="min-w-0">
                                <h4 class="text-[17px] font-black text-white m-0 tracking-wide truncate pr-4">
                                    {{ $phim->ten_phim }}
                                </h4>
                                <div class="flex items-center gap-3 mt-1 text-xs text-gray-400 font-bold">
                                    <span><i class="fa-regular fa-clock mr-1 text-gray-500"></i>{{ $phim->thoi_luong ?? 90 }} phút</span>
                                    <span class="text-gray-600">•</span>
                                    <span class="text-[#d99a32] font-black bg-[#d99a32]/10 px-2 py-0.5 rounded border border-[#d99a32]/20 text-[11px]">
                                        Tổng số {{ $phim->showtimes->count() }} suất chiếu
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="h-8 w-8 rounded-full bg-white/5 flex items-center justify-center text-gray-400 border border-white/5 dropdown-arrow transition-transform duration-300">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>

                    {{-- ACCORDION CONTENT PANEL --}}
                    <div class="movie-dropdown-content border-t border-white/5 max-h-0 overflow-hidden transition-all duration-300 ease-in-out bg-[#0c0c0c]/60">
                        <div class="overflow-x-auto p-2">
                            <table class="w-full min-w-[950px] text-left border-collapse">
                                <thead>
                                    <tr class="text-[11px] uppercase tracking-widest text-gray-500 font-black border-b border-white/5">
                                        <th class="px-4 py-3 w-16 text-center">Mã Số</th>
                                        <th class="px-4 py-3">Cơ Sở Rạp</th>
                                        <th class="px-4 py-3 text-center">Phòng Chiếu</th>
                                        <th class="px-4 py-3 text-center">Công Nghệ</th>
                                        <th class="px-4 py-3">Ngày Trình Chiếu</th>
                                        <th class="px-4 py-3 text-center">Khung Giờ Chiếm Dụng</th>
                                        <th class="px-4 py-3 text-right">Giá Vé Gốc</th>
                                        <th class="px-4 py-3 text-center">Trạng Thái</th>
                                        <th class="px-4 py-3 text-center">Hành Động</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5 font-semibold text-gray-300">
                                    @forelse ($phim->showtimes as $suat)
                                        <tr class="hover:bg-white/[0.01] transition duration-150 text-[14px]">
                                            <td class="px-4 py-3 text-center text-xs font-mono text-gray-500">
                                                #{{ sprintf('%04d', $suat->id) }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-400 font-medium">
                                                {{ $suat->rapChieuPhim->ten_rap ?? 'N/A' }}
                                            </td>
                                            <td class="px-4 py-3 text-center text-white font-bold">
                                                {{ $suat->phongChieu->ten_phong ?? 'N/A' }}
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="text-[11px] font-black uppercase px-2 py-0.5 rounded bg-[#d99a32]/10 text-[#f4c56a] border border-[#d99a32]/20">
                                                    {{ $suat->phongChieu->loai_phong ?? '2D' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-1.5 text-xs text-gray-300 font-bold">
                                                    <i class="fa-regular fa-calendar text-[#d99a32]"></i>
                                                    <span>{{ $suat->thoi_gian_chieu ? $suat->thoi_gian_chieu->format('d/m/Y') : 'N/A' }}</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-white/5 border border-white/5 font-mono text-xs">
                                                    <span class="text-[#f4c56a] font-black">{{ $suat->thoi_gian_chieu ? $suat->thoi_gian_chieu->format('H:i') : '00:00' }}</span>
                                                    <span class="text-gray-600 text-[10px]">→</span>
                                                    <span class="text-gray-400 font-bold">{{ $suat->thoi_gian_ket_thuc ? $suat->thoi_gian_ket_thuc->format('H:i') : '--:--' }}</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-right font-black text-[#f4c56a] tracking-wide text-sm">
                                                {{ number_format($suat->gia_ve) }}<span class="text-[11px] ml-0.5 text-gray-500 font-bold">đ</span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-flex items-center justify-center rounded-lg px-2.5 py-0.5 text-[11px] font-black uppercase border tracking-wider
                                                    {{ $suat->trang_thai === 'dang_chieu' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : '' }}
                                                    {{ $suat->trang_thai === 'sap_chieu' ? 'bg-blue-500/10 text-blue-400 border-blue-500/20' : '' }}
                                                    {{ $suat->trang_thai === 'da_chieu' ? 'bg-white/5 text-gray-400 border-white/5' : '' }}
                                                    {{ $suat->trang_thai === 'huy' ? 'bg-red-500/10 text-red-400 border-red-500/20' : '' }}">
                                                    {{ \App\Models\SuatChieu::TRANG_THAI_LIST[$suat->trang_thai] ?? $suat->trang_thai }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <a href="{{ route('admin.suat-chieus.show', $suat) }}"
                                                        class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-500/10 text-blue-400 border border-blue-500/10 transition hover:bg-blue-500 hover:text-white no-underline">
                                                        <i class="fa-solid fa-eye text-xs"></i>
                                                    </a>
                                                    <a href="{{ route('admin.suat-chieus.edit', $suat) }}"
                                                        class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500/10 text-[#f4c56a] border border-amber-500/10 transition hover:bg-[#d99a32] hover:text-[#2b1208] no-underline">
                                                        <i class="fa-solid fa-pen text-xs"></i>
                                                    </a>
                                                    <form action="{{ route('admin.suat-chieus.destroy', $suat) }}" method="POST" class="m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            onclick="return confirm('⚠️ Bạn chắc chắn muốn xóa suất chiếu này?')"
                                                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-500/10 text-red-400 border border-red-500/10 transition hover:bg-red-500 hover:text-white border-0 cursor-pointer">
                                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="px-4 py-8 text-center text-xs text-gray-500 font-bold">
                                                Phim này chưa có lịch trình suất chiếu nào thỏa mãn điều kiện lọc.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            @empty
                <div class="p-16 text-center text-sm text-gray-500 font-bold bg-[#121212]/40 rounded-2xl border border-white/5">
                    <div class="flex flex-col items-center justify-center gap-2">
                        <i class="fa-solid fa-box-open text-3xl text-gray-600"></i>
                        <span>Không tìm thấy dữ liệu bộ phim nào khớp với điều kiện lọc tìm.</span>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- PAGINATION INTERFACE --}}
        <div class="mt-4 py-2 flex justify-center">
            {{ $phimsPhanTrang->links() }}
        </div>

    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropdownCards = document.querySelectorAll('.movie-dropdown-card');

    dropdownCards.forEach((card) => {
        const trigger = card.querySelector('.movie-dropdown-trigger');
        const content = card.querySelector('.movie-dropdown-content');
        const arrow = card.querySelector('.dropdown-arrow');

        trigger.addEventListener('click', function () {
            const isOpen = card.classList.contains('open');

            if (isOpen) {
                card.classList.remove('open');
                content.style.maxHeight = "0px";
                arrow.style.transform = "rotate(0deg)";
            } else {
                card.classList.add('open');
                content.style.maxHeight = content.scrollHeight + "px";
                arrow.style.transform = "rotate(180deg)";
            }
        });
    });
});
</script>
@endpush