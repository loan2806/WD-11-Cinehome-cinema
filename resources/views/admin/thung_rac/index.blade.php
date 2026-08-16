@extends('layouts.admin')

@section('title', 'Trung tâm Thùng Rác - CineHome')
@section('page-title', 'Quản lý thùng rác')
@section('page-subtitle', 'Theo dõi, phục hồi dữ liệu hoặc xóa vĩnh viễn các bản ghi rác trong hệ thống CineHome.')

@section('content')
<div class="space-y-6">

    <!-- 1. HERO HEADER BANNER -->
    <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-[#121214] p-6 md:p-8 shadow-2xl">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            
            <!-- Left Info -->
            <div class="space-y-3">
                <span class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-[#d99a32]">
                    <i class="fa-solid fa-shield-cat text-sm"></i> QUẢN TRỊ HỆ THỐNG CINEHOME
                </span>
                
                <h1 class="text-3xl md:text-4xl font-black tracking-tight text-white">
                    Thùng rác hệ thống <span class="text-red-500">CineHome</span>
                </h1>
                
                <p class="text-sm text-gray-300 max-w-2xl leading-relaxed">
                    Quản lý toàn bộ dữ liệu đã xóa mềm. Bạn có thể khôi phục từng phần, khôi phục hàng loạt hoặc dọn dẹp sạch sẽ để giải phóng bộ nhớ cơ sở dữ liệu.
                </p>

                <!-- Badges -->
                <div class="flex flex-wrap items-center gap-3 pt-1">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/5 border border-white/10 px-3.5 py-1.5 text-xs font-bold text-gray-200">
                        <i class="fa-solid fa-database text-amber-400"></i> {{ number_format($totalTrash) }} bản ghi trong rác
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/5 border border-white/10 px-3.5 py-1.5 text-xs font-bold text-gray-200">
                        <i class="fa-solid fa-layer-group text-red-400"></i> 5 danh mục phân loại
                    </span>
                </div>
            </div>

            <!-- Right Actions -->
            @if(($stats[$tab] ?? 0) > 0)
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <!-- Nút Khôi phục tất cả -->
                    <form action="{{ route('admin.thung-rac.restore-all', $tab) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn KHÔI PHỤC TẤT CẢ {{ $stats[$tab] }} bản ghi trong danh mục này?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#222328] border border-white/15 px-5 py-3 text-xs font-bold text-white hover:bg-white/10 transition shadow-xl">
                            <i class="fa-solid fa-rotate-left text-emerald-400 text-sm"></i> Khôi phục tất cả
                        </button>
                    </form>

                    <!-- Nút Dọn sạch mục này -->
                    <form action="{{ route('admin.thung-rac.empty', $tab) }}" method="POST" onsubmit="return confirm('CẢNH BÁO NGHÊM TRỌNG: Thao tác này sẽ XÓA VĨNH VIỄN toàn bộ {{ $stats[$tab] }} bản ghi trong mục này!')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-400 px-5 py-3 text-xs font-bold text-white transition shadow-xl shadow-red-600/30">
                            <i class="fa-solid fa-trash-can text-sm"></i> Dọn sạch mục này
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <!-- 2. STAT CARDS TAB -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
        
        <!-- Tab 1: Phim -->
        <a href="{{ route('admin.thung-rac.index', array_merge(request()->query(), ['tab' => 'phim'])) }}" 
           class="group relative flex items-center gap-3.5 rounded-2xl border p-4 transition-all duration-200 no-underline block overflow-hidden {{ $tab === 'phim' ? 'border-red-500 bg-red-500/10 shadow-[0_0_20px_rgba(239,68,68,0.2)]' : 'border-white/10 bg-[#121214] hover:border-white/20 hover:bg-white/[0.03]' }}">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-500 text-white font-bold text-lg shadow-lg shadow-red-500/30">
                <i class="fa-solid fa-film"></i>
            </div>
            <div class="min-w-0 flex-1">
                <span class="text-[11px] font-black uppercase tracking-wider text-gray-400 block truncate">PHIM ĐÃ XÓA</span>
                <span class="text-2xl font-black text-white block mt-0.5 leading-none">{{ number_format($stats['phim']) }}</span>
            </div>
        </a>

        <!-- Tab 2: Suất chiếu -->
        <a href="{{ route('admin.thung-rac.index', array_merge(request()->query(), ['tab' => 'suat_chieu'])) }}" 
           class="group relative flex items-center gap-3.5 rounded-2xl border p-4 transition-all duration-200 no-underline block overflow-hidden {{ $tab === 'suat_chieu' ? 'border-emerald-500 bg-emerald-500/10 shadow-[0_0_20px_rgba(16,185,129,0.2)]' : 'border-white/10 bg-[#121214] hover:border-white/20 hover:bg-white/[0.03]' }}">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-500 text-white font-bold text-lg shadow-lg shadow-emerald-500/30">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
            <div class="min-w-0 flex-1">
                <span class="text-[11px] font-black uppercase tracking-wider text-gray-400 block truncate">SUẤT CHIẾU</span>
                <span class="text-2xl font-black text-white block mt-0.5 leading-none">{{ number_format($stats['suat_chieu']) }}</span>
            </div>
        </a>

        <!-- Tab 3: Khách hàng -->
        <a href="{{ route('admin.thung-rac.index', array_merge(request()->query(), ['tab' => 'khach_hang'])) }}" 
           class="group relative flex items-center gap-3.5 rounded-2xl border p-4 transition-all duration-200 no-underline block overflow-hidden {{ $tab === 'khach_hang' ? 'border-blue-500 bg-blue-500/10 shadow-[0_0_20px_rgba(59,130,246,0.2)]' : 'border-white/10 bg-[#121214] hover:border-white/20 hover:bg-white/[0.03]' }}">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-500 text-white font-bold text-lg shadow-lg shadow-blue-500/30">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="min-w-0 flex-1">
                <span class="text-[11px] font-black uppercase tracking-wider text-gray-400 block truncate">KHÁCH HÀNG</span>
                <span class="text-2xl font-black text-white block mt-0.5 leading-none">{{ number_format($stats['khach_hang']) }}</span>
            </div>
        </a>

        <!-- Tab 4: Nhân viên -->
        <a href="{{ route('admin.thung-rac.index', array_merge(request()->query(), ['tab' => 'nhan_vien'])) }}" 
           class="group relative flex items-center gap-3.5 rounded-2xl border p-4 transition-all duration-200 no-underline block overflow-hidden {{ $tab === 'nhan_vien' ? 'border-amber-500 bg-amber-500/10 shadow-[0_0_20px_rgba(245,158,11,0.2)]' : 'border-white/10 bg-[#121214] hover:border-white/20 hover:bg-white/[0.03]' }}">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white font-bold text-lg shadow-lg shadow-amber-500/30">
                <i class="fa-solid fa-user-tie"></i>
            </div>
            <div class="min-w-0 flex-1">
                <span class="text-[11px] font-black uppercase tracking-wider text-gray-400 block truncate">NHÂN VIÊN</span>
                <span class="text-2xl font-black text-white block mt-0.5 leading-none">{{ number_format($stats['nhan_vien']) }}</span>
            </div>
        </a>

        <!-- Tab 5: Thông báo -->
        <a href="{{ route('admin.thung-rac.index', array_merge(request()->query(), ['tab' => 'thong_bao'])) }}" 
           class="group relative flex items-center gap-3.5 rounded-2xl border p-4 transition-all duration-200 no-underline block overflow-hidden {{ $tab === 'thong_bao' ? 'border-purple-500 bg-purple-500/10 shadow-[0_0_20px_rgba(168,85,247,0.2)]' : 'border-white/10 bg-[#121214] hover:border-white/20 hover:bg-white/[0.03]' }}">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-purple-500 text-white font-bold text-lg shadow-lg shadow-purple-500/30">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div class="min-w-0 flex-1">
                <span class="text-[11px] font-black uppercase tracking-wider text-gray-400 block truncate">THÔNG BÁO PUSH</span>
                <span class="text-2xl font-black text-white block mt-0.5 leading-none">{{ number_format($stats['thong_bao']) }}</span>
            </div>
        </a>

    </div>

    <!-- 3. MAIN CONTENT CONTAINER -->
    <div class="rounded-2xl border border-white/10 bg-[#121214] p-6 space-y-6 shadow-2xl">
        
        <!-- Section Title -->
        <div>
            <span class="text-xs font-black uppercase tracking-widest text-[#d99a32]">DANH SÁCH BẢN GHI RÁC</span>
            <h2 class="text-2xl font-black text-white mt-1">
                {{ match($tab) {
                    'phim' => 'Kho Phim Đã Xóa',
                    'suat_chieu' => 'Lịch Suất Chiếu Đã Xóa',
                    'khach_hang' => 'Tài Khoản Khách Hàng Đã Xóa',
                    'nhan_vien' => 'Tài Khoản Nhân Viên Đã Xóa',
                    'thong_bao' => 'Thông Báo Push Đã Xóa',
                    default => 'Thùng Rác Hệ Thống'
                } }}
            </h2>
            <p class="text-xs text-gray-400 mt-1 font-medium">Đang hiển thị {{ $items->count() }} bản ghi phù hợp theo bộ lọc hiện tại.</p>
        </div>

        <!-- FILTER BAR (Có JS ràng buộc min/max ngày) -->
        <form action="{{ route('admin.thung-rac.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3 bg-[#18181c] p-3.5 rounded-2xl border border-white/10">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <!-- Ô tìm kiếm -->
            <div class="relative md:col-span-5">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
                <input type="text" name="tim_kiem" value="{{ request('tim_kiem') }}"
                       placeholder="Tìm theo tên, email, từ khóa..."
                       class="h-11 w-full rounded-xl border border-white/10 bg-[#121214] pl-10 pr-3 text-sm text-white placeholder-gray-500 focus:border-red-500 focus:outline-none transition font-medium">
            </div>

            <!-- Từ ngày -->
            <div class="relative md:col-span-3 flex items-center gap-2 bg-[#121214] border border-white/10 rounded-xl px-3">
                <i class="fa-regular fa-calendar-days text-red-500 text-xs shrink-0"></i>
                <span class="text-xs font-bold text-gray-400 whitespace-nowrap">Từ:</span>
                <input type="date" id="filter_tu_ngay" name="tu_ngay" value="{{ request('tu_ngay') }}"
                       class="h-11 w-full bg-transparent text-xs font-semibold text-white focus:outline-none [color-scheme:dark]">
            </div>

            <!-- Đến ngày -->
            <div class="relative md:col-span-3 flex items-center gap-2 bg-[#121214] border border-white/10 rounded-xl px-3">
                <i class="fa-regular fa-calendar-days text-red-500 text-xs shrink-0"></i>
                <span class="text-xs font-bold text-gray-400 whitespace-nowrap">Đến:</span>
                <input type="date" id="filter_den_ngay" name="den_ngay" value="{{ request('den_ngay') }}"
                       class="h-11 w-full bg-transparent text-xs font-semibold text-white focus:outline-none [color-scheme:dark]">
            </div>

            <!-- Nút Lọc Dữ Liệu -->
            <div class="md:col-span-1 flex items-center gap-1.5">
                <button type="submit" class="h-11 w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-400 px-3 text-xs font-black text-white transition shadow-lg shadow-red-600/30 whitespace-nowrap" title="Lọc dữ liệu">
                    <i class="fa-solid fa-filter text-xs"></i> Lọc
                </button>

                @if(request()->hasAny(['tim_kiem', 'tu_ngay', 'den_ngay']))
                    <a href="{{ route('admin.thung-rac.index', ['tab' => $tab]) }}" 
                       class="h-11 w-11 inline-flex items-center justify-center rounded-xl border border-white/10 bg-white/5 text-gray-300 hover:bg-white/10 hover:text-white transition shrink-0"
                       title="Đặt lại bộ lọc">
                        <i class="fa-solid fa-rotate-left text-xs"></i>
                    </a>
                @endif
            </div>
        </form>

        <!-- DATA TABLE -->
        <div class="overflow-hidden rounded-2xl border border-white/10 bg-[#121214]">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-300">
                    <thead class="bg-[#18181c] text-xs font-black uppercase tracking-wider text-gray-400 border-b border-white/10">
                        <tr>
                            <th class="px-6 py-4">MÃ</th>
                            <th class="px-6 py-4">TÊN / NHẬN DIỆN</th>
                            <th class="px-6 py-4">THÔNG TIN CHI TIẾT</th>
                            <th class="px-6 py-4">THỜI GIAN XÓA</th>
                            <th class="px-6 py-4 text-right">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($items as $item)
                            @php
                                // Xử lý đường dẫn Ảnh Poster Phim đa dạng cột
                                $rawPoster = null;
                                if ($tab === 'phim') {
                                    $rawPoster = $item->poster ?? $item->anh_poster ?? $item->duong_dan_anh ?? $item->hinh_anh ?? $item->anh_bia ?? null;
                                } elseif ($tab === 'suat_chieu' && isset($item->phim)) {
                                    $rawPoster = $item->phim->poster ?? $item->phim->anh_poster ?? $item->phim->duong_dan_anh ?? $item->phim->hinh_anh ?? $item->phim->anh_bia ?? null;
                                }

                                $imageUrl = null;
                                if ($rawPoster) {
                                    if (\Illuminate\Support\Str::startsWith($rawPoster, ['http://', 'https://'])) {
                                        $imageUrl = $rawPoster;
                                    } else {
                                        $cleanPath = ltrim(preg_replace('#^storage/#', '', $rawPoster), '/');
                                        $imageUrl = asset('storage/' . $cleanPath);
                                    }
                                }
                            @endphp

                            <tr class="hover:bg-white/[0.03] transition duration-150">
                                <!-- Col 1: ID -->
                                <td class="px-6 py-4 font-mono text-xs text-gray-400">
                                    <span class="inline-block rounded-lg bg-white/5 px-2.5 py-1 font-bold border border-white/10">#{{ $item->id }}</span>
                                </td>

                                <!-- Col 2: Name + Movie Poster (fallback tự gỡ ảnh lỗi) -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3.5">
                                        <div class="relative h-12 w-9 shrink-0 overflow-hidden rounded-lg border border-white/15 bg-[#1a1a1e] shadow-md flex items-center justify-center">
                                            @if($imageUrl)
                                                <img src="{{ $imageUrl }}" alt="Poster" 
                                                     class="h-full w-full object-cover"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="hidden h-full w-full items-center justify-center bg-gradient-to-br from-red-500/20 to-amber-500/20 text-red-500 font-bold text-sm">
                                                    <i class="fa-solid fa-film"></i>
                                                </div>
                                            @else
                                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-red-500/20 to-amber-500/20 text-red-500 font-bold text-sm">
                                                    <i class="fa-solid {{ match($tab) {
                                                        'phim' => 'fa-film',
                                                        'suat_chieu' => 'fa-calendar-days',
                                                        'khach_hang' => 'fa-user',
                                                        'nhan_vien' => 'fa-user-tie',
                                                        'thong_bao' => 'fa-bell',
                                                        default => 'fa-trash'
                                                    } }}"></i>
                                                </div>
                                            @endif
                                        </div>

                                        <div>
                                            <strong class="block font-bold text-white text-base">
                                                @if($tab === 'suat_chieu')
                                                    {{ $item->phim->ten_phim ?? 'Suất chiếu #' . $item->id }}
                                                @else
                                                    {{ $item->ten_phim ?? $item->ho_ten ?? $item->name ?? $item->tieu_de ?? 'N/A' }}
                                                @endif
                                            </strong>
                                            <small class="text-xs text-gray-400 font-mono block mt-0.5">
                                                {{ $item->email ?? $item->so_dien_thoai ?? 'Bản ghi hệ thống' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <!-- Col 3: Details -->
                                <td class="px-6 py-4">
                                    @if($tab === 'phim')
                                        <span class="text-[#d99a32] font-bold text-xs"><i class="fa-regular fa-clock mr-1"></i> {{ $item->thoi_luong ?? 0 }} phút</span>
                                    @elseif($tab === 'suat_chieu')
                                        <span class="text-gray-300 text-xs">
                                            Phòng: <strong class="text-white font-bold">{{ $item->phongChieu->ten_phong ?? 'N/A' }}</strong> | Chiếu lúc: <strong class="text-[#d99a32] font-bold">{{ $item->thoi_gian_chieu ? $item->thoi_gian_chieu->format('H:i d/m/Y') : 'N/A' }}</strong>
                                        </span>
                                    @elseif($tab === 'khach_hang' || $tab === 'nhan_vien')
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-400 border border-emerald-500/20">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                            {{ $item->vai_tro ?? 'Thành viên' }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 font-medium">{{ Str::limit($item->noi_dung ?? '', 50) }}</span>
                                    @endif
                                </td>

                                <!-- Col 4: Deleted At -->
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 text-xs text-red-400 font-semibold">
                                        <i class="fa-regular fa-clock text-[10px]"></i>
                                        {{ $item->deleted_at ? $item->deleted_at->format('H:i - d/m/Y') : '—' }}
                                    </span>
                                </td>

                                <!-- Col 5: Actions -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @php
                                            $restoreRoute = match($tab) {
                                                'phim' => route('admin.phims.restore', $item->id),
                                                'suat_chieu' => route('admin.suat-chieus.restore', $item->id),
                                                'khach_hang' => route('admin.khach-hang.restore', $item->id),
                                                'nhan_vien' => route('admin.nhanviens.restore', $item->id),
                                                'thong_bao' => route('admin.thong-bao-push.restore', $item->id),
                                                default => '#'
                                            };

                                            $forceRoute = match($tab) {
                                                'phim' => route('admin.phims.force-delete', $item->id),
                                                'suat_chieu' => route('admin.suat-chieus.force-delete', $item->id),
                                                'khach_hang' => route('admin.khach-hang.force-delete', $item->id),
                                                'nhan_vien' => route('admin.nhanviens.forceDelete', $item->id),
                                                'thong_bao' => route('admin.thong-bao-push.force-delete', $item->id),
                                                default => '#'
                                            };
                                        @endphp

                                        <!-- Nút Khôi Phục -->
                                        <form action="{{ $restoreRoute }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" onclick="return confirm('Khôi phục bản ghi này về trạng thái hoạt động?')"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-blue-500/15 text-blue-400 hover:bg-blue-500 hover:text-white transition shadow-md"
                                                    title="Khôi phục dữ liệu">
                                                <i class="fa-solid fa-rotate-left text-xs"></i>
                                            </button>
                                        </form>

                                        <!-- Nút Xóa Cứng -->
                                        <form action="{{ $forceRoute }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('CẢNH BÁO: Thao tác này sẽ xóa vĩnh viễn khỏi Cơ sở dữ liệu!')"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-red-500/15 text-red-400 hover:bg-red-600 hover:text-white transition shadow-md"
                                                    title="Xóa vĩnh viễn">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-16 text-center text-gray-500">
                                    <i class="fa-solid fa-trash-can text-5xl mb-3 block opacity-20 text-gray-400"></i>
                                    <span class="text-sm font-bold text-gray-400">Không tìm thấy dữ liệu rác nào trong danh mục này!</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($items instanceof \Illuminate\Pagination\LengthAwarePaginator && $items->hasPages())
                <div class="border-t border-white/10 p-4">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- JS RÀNG BUỘC TỰ ĐỘNG KHÔNG CHO CHỌN "ĐẾN NGÀY" TRƯỚC "TỪ NGÀY" -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tuNgayInput = document.getElementById('filter_tu_ngay');
        const denNgayInput = document.getElementById('filter_den_ngay');

        function updateDateConstraints() {
            if (tuNgayInput.value) {
                denNgayInput.min = tuNgayInput.value;
                if (denNgayInput.value && denNgayInput.value < tuNgayInput.value) {
                    denNgayInput.value = tuNgayInput.value;
                }
            } else {
                denNgayInput.removeAttribute('min');
            }

            if (denNgayInput.value) {
                tuNgayInput.max = denNgayInput.value;
                if (tuNgayInput.value && tuNgayInput.value > denNgayInput.value) {
                    tuNgayInput.value = denNgayInput.value;
                }
            } else {
                tuNgayInput.removeAttribute('max');
            }
        }

        tuNgayInput.addEventListener('change', updateDateConstraints);
        denNgayInput.addEventListener('change', updateDateConstraints);

        updateDateConstraints();
    });
</script>
@endsection