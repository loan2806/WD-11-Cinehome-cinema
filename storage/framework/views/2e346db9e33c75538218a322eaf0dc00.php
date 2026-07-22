<?php $__env->startSection('page-title', 'Quản lý Thông báo đẩy'); ?>

<?php $__env->startSection('content'); ?>

<div class="admin-panel">

    
    <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-[#8a4a21] to-[#d99a32] shadow-lg shadow-[#d99a32]/20">
                <i class="fa-solid fa-bell text-2xl text-white"></i>
            </div>
            <div>
                <h5 class="text-xl font-bold text-white">
                    Danh sách thông báo đẩy
                </h5>
                <p class="text-sm text-gray-500">
                    Quản lý và theo dõi các thông báo đẩy trong hệ thống
                </p>
            </div>
        </div>
        <a href="<?php echo e(route('admin.thong-bao-push.create')); ?>"
            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-4 py-2.5 text-sm font-bold text-white shadow-lg transition-all hover:scale-[1.02] hover:shadow-[#d99a32]/30">
            <i class="fa-solid fa-plus"></i>
            Tạo thông báo mới
        </a>
    </div>

    
    <form method="GET" action="<?php echo e(route('admin.thong-bao-push.index')); ?>" class="mt-5 flex flex-wrap items-end gap-3">
        <div class="min-w-[200px] flex-1">
            <label class="mb-1.5 block text-xs font-semibold text-gray-400">Tìm kiếm</label>
            <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                placeholder="Tìm kiếm theo tiêu đề..."
                class="h-10 w-full rounded-xl border border-white/10 bg-[#151515] px-4 text-sm text-white outline-none focus:border-[#d99a32] transition-colors">
        </div>

        
        <div class="relative min-w-[160px]" id="filterLoaiDropdownWrapper">
            <input type="hidden" name="loai" id="filterLoai" value="<?php echo e(request('loai', '')); ?>">
            <label class="mb-1.5 block text-xs font-semibold text-gray-400">Loại thông báo</label>
            <button type="button" id="filterLoaiDropdownBtn"
                class="dropdown-trigger flex h-10 w-full items-center justify-between rounded-xl border border-white/10 bg-[#151515] px-3 text-left text-sm transition-all focus:border-[#d99a32] cursor-pointer">
                <div class="flex items-center gap-2">
                    <span id="filterLoaiIcon" class="flex h-6 w-6 items-center justify-center rounded-md">
                        <?php $filterLoaiValue = request('loai', ''); ?>
                        <?php switch($filterLoaiValue):
                            case ('info'): ?>
                                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-blue-500/20 text-blue-400">
                                    <i class="fa-solid fa-circle-info text-xs"></i>
                                </span>
                                <?php break; ?>
                            <?php case ('success'): ?>
                                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-green-500/20 text-green-400">
                                    <i class="fa-solid fa-check-circle text-xs"></i>
                                </span>
                                <?php break; ?>
                            <?php case ('warning'): ?>
                                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-yellow-500/20 text-yellow-400">
                                    <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                                </span>
                                <?php break; ?>
                            <?php case ('promo'): ?>
                                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-purple-500/20 text-purple-400">
                                    <i class="fa-solid fa-gift text-xs"></i>
                                </span>
                                <?php break; ?>
                            <?php case ('system'): ?>
                                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-gray-500/20 text-gray-400">
                                    <i class="fa-solid fa-gear text-xs"></i>
                                </span>
                                <?php break; ?>
                            <?php default: ?>
                                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-[#d99a32]/20 text-[#d99a32]">
                                    <i class="fa-solid fa-filter text-xs"></i>
                                </span>
                        <?php endswitch; ?>
                    </span>
                    <span id="filterLoaiLabel" class="text-white">
                        <?php switch($filterLoaiValue):
                            case ('info'): ?> Thông tin <?php break; ?>
                            <?php case ('success'): ?> Thành công <?php break; ?>
                            <?php case ('warning'): ?> Cảnh báo <?php break; ?>
                            <?php case ('promo'): ?> Khuyến mãi <?php break; ?>
                            <?php case ('system'): ?> Hệ thống <?php break; ?>
                            <?php default: ?> Tất cả
                        <?php endswitch; ?>
                    </span>
                </div>
                <i class="fa-solid fa-chevron-down text-gray-500 text-xs transition-transform duration-300 dropdown-arrow"></i>
            </button>
            <div id="filterLoaiDropdownMenu"
                class="dropdown-menu absolute left-0 right-0 top-[calc(100%+8px)] z-50 hidden max-h-52 overflow-y-auto rounded-xl border border-white/10 bg-[#1a1a1a] p-1.5 shadow-2xl shadow-black/50 scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent">
                <button type="button"
                    class="dropdown-option group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left transition-all hover:bg-white/5 <?php echo e($filterLoaiValue === '' ? 'selected' : ''); ?>"
                    data-value="" data-label="Tất cả">
                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-[#d99a32]/20 text-[#d99a32]">
                        <i class="fa-solid fa-layer-group text-xs"></i>
                    </span>
                    <span class="text-sm text-white">Tất cả</span>
                </button>
                <button type="button"
                    class="dropdown-option group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left transition-all hover:bg-white/5 <?php echo e($filterLoaiValue === 'info' ? 'selected' : ''); ?>"
                    data-value="info" data-label="Thông tin">
                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-blue-500/20 text-blue-400">
                        <i class="fa-solid fa-circle-info text-xs"></i>
                    </span>
                    <span class="text-sm text-white">Thông tin</span>
                </button>
                <button type="button"
                    class="dropdown-option group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left transition-all hover:bg-white/5 <?php echo e($filterLoaiValue === 'success' ? 'selected' : ''); ?>"
                    data-value="success" data-label="Thành công">
                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-green-500/20 text-green-400">
                        <i class="fa-solid fa-check-circle text-xs"></i>
                    </span>
                    <span class="text-sm text-white">Thành công</span>
                </button>
                <button type="button"
                    class="dropdown-option group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left transition-all hover:bg-white/5 <?php echo e($filterLoaiValue === 'warning' ? 'selected' : ''); ?>"
                    data-value="warning" data-label="Cảnh báo">
                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-yellow-500/20 text-yellow-400">
                        <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                    </span>
                    <span class="text-sm text-white">Cảnh báo</span>
                </button>
                <button type="button"
                    class="dropdown-option group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left transition-all hover:bg-white/5 <?php echo e($filterLoaiValue === 'promo' ? 'selected' : ''); ?>"
                    data-value="promo" data-label="Khuyến mãi">
                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-purple-500/20 text-purple-400">
                        <i class="fa-solid fa-gift text-xs"></i>
                    </span>
                    <span class="text-sm text-white">Khuyến mãi</span>
                </button>
                <button type="button"
                    class="dropdown-option group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left transition-all hover:bg-white/5 <?php echo e($filterLoaiValue === 'system' ? 'selected' : ''); ?>"
                    data-value="system" data-label="Hệ thống">
                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-gray-500/20 text-gray-400">
                        <i class="fa-solid fa-gear text-xs"></i>
                    </span>
                    <span class="text-sm text-white">Hệ thống</span>
                </button>
            </div>
        </div>

        
        <div class="relative min-w-[150px]" id="filterDoiTuongDropdownWrapper">
            <input type="hidden" name="doi_tuong_nhan" id="filterDoiTuong" value="<?php echo e(request('doi_tuong_nhan', '')); ?>">
            <label class="mb-1.5 block text-xs font-semibold text-gray-400">Đối tượng nhận</label>
            <button type="button" id="filterDoiTuongDropdownBtn"
                class="dropdown-trigger flex h-10 w-full items-center justify-between rounded-xl border border-white/10 bg-[#151515] px-3 text-left text-sm transition-all focus:border-[#d99a32] cursor-pointer">
                <div class="flex items-center gap-2">
                    <span id="filterDoiTuongIcon" class="flex h-6 w-6 items-center justify-center rounded-md">
                        <?php $filterDoiTuongValue = request('doi_tuong_nhan', ''); ?>
                        <?php switch($filterDoiTuongValue):
                            case ('all'): ?>
                                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-[#d99a32]/20 text-[#d99a32]">
                                    <i class="fa-solid fa-globe text-xs"></i>
                                </span>
                                <?php break; ?>
                            <?php case ('user'): ?>
                                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-blue-500/20 text-blue-400">
                                    <i class="fa-solid fa-user text-xs"></i>
                                </span>
                                <?php break; ?>
                            <?php case ('vip'): ?>
                                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-yellow-500/20 text-yellow-400">
                                    <i class="fa-solid fa-crown text-xs"></i>
                                </span>
                                <?php break; ?>
                            <?php case ('staff'): ?>
                                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-green-500/20 text-green-400">
                                    <i class="fa-solid fa-user-tie text-xs"></i>
                                </span>
                                <?php break; ?>
                            <?php case ('admin'): ?>
                                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-purple-500/20 text-purple-400">
                                    <i class="fa-solid fa-user-shield text-xs"></i>
                                </span>
                                <?php break; ?>
                            <?php case ('nguoi_dung_cu_the'): ?>
                                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-pink-500/20 text-pink-400">
                                    <i class="fa-solid fa-user-pen text-xs"></i>
                                </span>
                                <?php break; ?>
                            <?php default: ?>
                                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-[#d99a32]/20 text-[#d99a32]">
                                    <i class="fa-solid fa-users text-xs"></i>
                                </span>
                        <?php endswitch; ?>
                    </span>
                    <span id="filterDoiTuongLabel" class="text-white">
                        <?php switch($filterDoiTuongValue):
                            case ('all'): ?> Tất cả <?php break; ?>
                            <?php case ('user'): ?> Người dùng <?php break; ?>
                            <?php case ('vip'): ?> VIP <?php break; ?>
                            <?php case ('staff'): ?> Nhân viên <?php break; ?>
                            <?php case ('admin'): ?> Quản trị <?php break; ?>
                            <?php case ('nguoi_dung_cu_the'): ?> Cụ thể <?php break; ?>
                            <?php default: ?> Tất cả
                        <?php endswitch; ?>
                    </span>
                </div>
                <i class="fa-solid fa-chevron-down text-gray-500 text-xs transition-transform duration-300 dropdown-arrow"></i>
            </button>
            <div id="filterDoiTuongDropdownMenu"
                class="dropdown-menu absolute left-0 right-0 top-[calc(100%+8px)] z-50 hidden max-h-52 overflow-y-auto rounded-xl border border-white/10 bg-[#1a1a1a] p-1.5 shadow-2xl shadow-black/50 scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent">
                <button type="button"
                    class="dropdown-option group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left transition-all hover:bg-white/5 <?php echo e($filterDoiTuongValue === '' ? 'selected' : ''); ?>"
                    data-value="" data-label="Tất cả">
                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-[#d99a32]/20 text-[#d99a32]">
                        <i class="fa-solid fa-layer-group text-xs"></i>
                    </span>
                    <span class="text-sm text-white">Tất cả</span>
                </button>
                <button type="button"
                    class="dropdown-option group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left transition-all hover:bg-white/5 <?php echo e($filterDoiTuongValue === 'all' ? 'selected' : ''); ?>"
                    data-value="all" data-label="Tất cả">
                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-[#d99a32]/20 text-[#d99a32]">
                        <i class="fa-solid fa-globe text-xs"></i>
                    </span>
                    <span class="text-sm text-white">Tất cả</span>
                </button>
                <button type="button"
                    class="dropdown-option group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left transition-all hover:bg-white/5 <?php echo e($filterDoiTuongValue === 'user' ? 'selected' : ''); ?>"
                    data-value="user" data-label="Người dùng">
                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-blue-500/20 text-blue-400">
                        <i class="fa-solid fa-user text-xs"></i>
                    </span>
                    <span class="text-sm text-white">Người dùng</span>
                </button>
                <button type="button"
                    class="dropdown-option group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left transition-all hover:bg-white/5 <?php echo e($filterDoiTuongValue === 'vip' ? 'selected' : ''); ?>"
                    data-value="vip" data-label="VIP">
                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-yellow-500/20 text-yellow-400">
                        <i class="fa-solid fa-crown text-xs"></i>
                    </span>
                    <span class="text-sm text-white">VIP</span>
                </button>
                <button type="button"
                    class="dropdown-option group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left transition-all hover:bg-white/5 <?php echo e($filterDoiTuongValue === 'staff' ? 'selected' : ''); ?>"
                    data-value="staff" data-label="Nhân viên">
                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-green-500/20 text-green-400">
                        <i class="fa-solid fa-user-tie text-xs"></i>
                    </span>
                    <span class="text-sm text-white">Nhân viên</span>
                </button>
                <button type="button"
                    class="dropdown-option group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left transition-all hover:bg-white/5 <?php echo e($filterDoiTuongValue === 'admin' ? 'selected' : ''); ?>"
                    data-value="admin" data-label="Quản trị">
                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-purple-500/20 text-purple-400">
                        <i class="fa-solid fa-user-shield text-xs"></i>
                    </span>
                    <span class="text-sm text-white">Quản trị</span>
                </button>
                <button type="button"
                    class="dropdown-option group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left transition-all hover:bg-white/5 <?php echo e($filterDoiTuongValue === 'nguoi_dung_cu_the' ? 'selected' : ''); ?>"
                    data-value="nguoi_dung_cu_the" data-label="Cụ thể">
                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-pink-500/20 text-pink-400">
                        <i class="fa-solid fa-user-pen text-xs"></i>
                    </span>
                    <span class="text-sm text-white">Cụ thể</span>
                </button>
            </div>
        </div>

        
        <div class="relative min-w-[150px]" id="filterTrangThaiDropdownWrapper">
            <input type="hidden" name="trang_thai" id="filterTrangThai" value="<?php echo e(request('trang_thai', '')); ?>">
            <label class="mb-1.5 block text-xs font-semibold text-gray-400">Trạng thái</label>
            <button type="button" id="filterTrangThaiDropdownBtn"
                class="dropdown-trigger flex h-10 w-full items-center justify-between rounded-xl border border-white/10 bg-[#151515] px-3 text-left text-sm transition-all focus:border-[#d99a32] cursor-pointer">
                <div class="flex items-center gap-2">
                    <span id="filterTrangThaiIcon" class="flex h-6 w-6 items-center justify-center rounded-md">
                        <?php $filterTrangThaiValue = request('trang_thai', ''); ?>
                        <?php switch($filterTrangThaiValue):
                            case ('da_gui'): ?>
                                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-emerald-500/20 text-emerald-400">
                                    <i class="fa-solid fa-paper-plane text-xs"></i>
                                </span>
                                <?php break; ?>
                            <?php case ('chua_gui'): ?>
                                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-amber-500/20 text-amber-400">
                                    <i class="fa-solid fa-clock text-xs"></i>
                                </span>
                                <?php break; ?>
                            <?php default: ?>
                                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-[#d99a32]/20 text-[#d99a32]">
                                    <i class="fa-solid fa-circle-half-stroke text-xs"></i>
                                </span>
                        <?php endswitch; ?>
                    </span>
                    <span id="filterTrangThaiLabel" class="text-white">
                        <?php switch($filterTrangThaiValue):
                            case ('da_gui'): ?> Đã gửi <?php break; ?>
                            <?php case ('chua_gui'): ?> Chưa gửi <?php break; ?>
                            <?php default: ?> Tất cả
                        <?php endswitch; ?>
                    </span>
                </div>
                <i class="fa-solid fa-chevron-down text-gray-500 text-xs transition-transform duration-300 dropdown-arrow"></i>
            </button>
            <div id="filterTrangThaiDropdownMenu"
                class="dropdown-menu absolute left-0 right-0 top-[calc(100%+8px)] z-50 hidden max-h-52 overflow-y-auto rounded-xl border border-white/10 bg-[#1a1a1a] p-1.5 shadow-2xl shadow-black/50 scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent">
                <button type="button"
                    class="dropdown-option group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left transition-all hover:bg-white/5 <?php echo e($filterTrangThaiValue === '' ? 'selected' : ''); ?>"
                    data-value="" data-label="Tất cả">
                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-[#d99a32]/20 text-[#d99a32]">
                        <i class="fa-solid fa-layer-group text-xs"></i>
                    </span>
                    <span class="text-sm text-white">Tất cả</span>
                </button>
                <button type="button"
                    class="dropdown-option group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left transition-all hover:bg-white/5 <?php echo e($filterTrangThaiValue === 'da_gui' ? 'selected' : ''); ?>"
                    data-value="da_gui" data-label="Đã gửi">
                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-emerald-500/20 text-emerald-400">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                    </span>
                    <span class="text-sm text-white">Đã gửi</span>
                </button>
                <button type="button"
                    class="dropdown-option group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left transition-all hover:bg-white/5 <?php echo e($filterTrangThaiValue === 'chua_gui' ? 'selected' : ''); ?>"
                    data-value="chua_gui" data-label="Chưa gửi">
                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-amber-500/20 text-amber-400">
                        <i class="fa-solid fa-clock text-xs"></i>
                    </span>
                    <span class="text-sm text-white">Chưa gửi</span>
                </button>
            </div>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit"
                class="h-10 rounded-xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-4 text-sm font-bold text-white shadow-lg transition-all hover:opacity-90">
                <i class="fa-solid fa-filter mr-1"></i>
                Lọc
            </button>
            <?php if(request()->has('search') || request()->has('loai') || request()->has('trang_thai') || request()->has('doi_tuong_nhan')): ?>
                <a href="<?php echo e(route('admin.thong-bao-push.index')); ?>"
                    class="flex h-10 items-center rounded-xl border border-white/10 bg-white/5 px-4 text-sm font-semibold text-gray-400 transition-all hover:bg-white/10 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>

    
    <div class="mt-5 overflow-hidden rounded-2xl border border-white/10">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gradient-to-r from-[#1a1a1a] to-[#151515] text-xs uppercase tracking-wider text-gray-400">
                    <tr>
                        <th class="px-4 py-3 font-semibold">ID</th>
                        <th class="px-4 py-3 font-semibold">Tiêu đề</th>
                        <th class="px-4 py-3 font-semibold">Loại</th>
                        <th class="px-4 py-3 font-semibold">Người tạo</th>
                        <th class="px-4 py-3 font-semibold">Ngày tạo</th>
                        <th class="px-4 py-3 font-semibold">Trạng thái</th>
                        <th class="px-4 py-3 text-right font-semibold">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-sm text-gray-300">
                    <?php $__empty_1 = true; $__currentLoopData = $thongBaos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $thongBao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3.5">
                                <span class="font-mono text-xs text-gray-500">#<?php echo e($thongBao->id); ?></span>
                            </td>
                            <td class="px-4 py-3.5">
                                <a href="<?php echo e(route('admin.thong-bao-push.show', $thongBao)); ?>"
                                    class="font-medium text-[#d99a32] hover:underline">
                                    <?php echo e(\Illuminate\Support\Str::limit($thongBao->tieu_de, 40)); ?>

                                </a>
                            </td>
                            <td class="px-4 py-3.5">
                                <?php
                                    $loaiLabels = [
                                        'info' => 'Thông tin',
                                        'success' => 'Thành công',
                                        'warning' => 'Cảnh báo',
                                        'promo' => 'Khuyến mãi',
                                        'system' => 'Hệ thống',
                                    ];
                                    $badgeClass = match ($thongBao->loai) {
                                        'info' => 'bg-blue-500/20 text-blue-400 border border-blue-500/30',
                                        'success' => 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30',
                                        'warning' => 'bg-amber-500/20 text-amber-400 border border-amber-500/30',
                                        'promo' => 'bg-purple-500/20 text-purple-400 border border-purple-500/30',
                                        'system' => 'bg-gray-500/20 text-gray-400 border border-gray-500/30',
                                        default => 'bg-gray-500/20 text-gray-400 border border-gray-500/30',
                                    };
                                    $icon = match ($thongBao->loai) {
                                        'info' => 'fa-info-circle',
                                        'success' => 'fa-circle-check',
                                        'warning' => 'fa-triangle-exclamation',
                                        'promo' => 'fa-gift',
                                        'system' => 'fa-gear',
                                        default => 'fa-bell',
                                    };
                                ?>
                                <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-bold <?php echo e($badgeClass); ?>">
                                    <i class="fa-solid <?php echo e($icon); ?> text-[10px]"></i>
                                    <?php echo e($loaiLabels[$thongBao->loai] ?? $thongBao->loai); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-[#8a4a21]/50 to-[#d99a32]/50 text-[10px] font-bold text-white">
                                        <?php echo e(substr($thongBao->nguoiTao->ho_ten ?? 'H', 0, 1)); ?>

                                    </div>
                                    <span class="text-sm"><?php echo e($thongBao->nguoiTao->ho_ten ?? 'Hệ thống'); ?></span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="text-sm text-gray-400"><?php echo e($thongBao->created_at->format('d/m/Y')); ?></span>
                                <span class="ml-1 text-xs text-gray-600"><?php echo e($thongBao->created_at->format('H:i')); ?></span>
                            </td>
                            <td class="px-4 py-3.5">
                                <?php if($thongBao->trang_thai === 'da_gui'): ?>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 px-2.5 py-1 text-xs font-bold text-emerald-400 border border-emerald-500/20">
                                        <i class="fa-solid fa-paper-plane text-[10px]"></i>
                                        Đã gửi
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/15 px-2.5 py-1 text-xs font-bold text-amber-400 border border-amber-500/20">
                                        <i class="fa-solid fa-clock text-[10px]"></i>
                                        Chưa gửi
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="<?php echo e(route('admin.thong-bao-push.show', $thongBao)); ?>"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg border border-white/10 bg-white/5 text-gray-400 transition-all hover:border-[#d99a32] hover:text-[#d99a32]"
                                        title="Xem chi tiết">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>
                                    <form action="<?php echo e(route('admin.thong-bao-push.destroy', $thongBao)); ?>"
                                        method="POST"
                                        class="inline-block"
                                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa thông báo này không?');">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-white/10 bg-white/5 text-gray-400 transition-all hover:border-red-500/50 hover:text-red-400"
                                            title="Xóa">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/5 mb-4">
                                        <i class="fa-solid fa-bell-slash text-3xl text-gray-600"></i>
                                    </div>
                                    <p class="text-gray-400 font-medium">Chưa có thông báo đẩy nào</p>
                                    <a href="<?php echo e(route('admin.thong-bao-push.create')); ?>" class="mt-3 text-sm text-[#d99a32] hover:underline">
                                        Tạo thông báo đầu tiên
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <?php if($thongBaos->hasPages()): ?>
        <div class="mt-5 flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Hiển thị <span class="font-semibold text-gray-400"><?php echo e($thongBaos->firstItem() ?? 0); ?></span> -
                <span class="font-semibold text-gray-400"><?php echo e($thongBaos->lastItem() ?? 0); ?></span>
                trong <span class="font-semibold text-gray-400"><?php echo e($thongBaos->total()); ?></span> thông báo
            </div>
            <div class="flex items-center gap-1">
                <?php if($thongBaos->onFirstPage()): ?>
                    <span class="flex h-9 items-center rounded-lg border border-white/10 bg-white/5 px-3 text-sm font-medium text-gray-500">
                        <i class="fa-solid fa-chevron-left mr-1 text-xs"></i> Trước
                    </span>
                <?php else: ?>
                    <a href="<?php echo e($thongBaos->previousPageUrl()); ?>"
                    class="flex h-9 items-center rounded-lg border border-white/10 bg-white/5 px-3 text-sm font-medium text-gray-300 transition-all hover:bg-white/10">
                        <i class="fa-solid fa-chevron-left mr-1 text-xs"></i> Trước
                    </a>
                <?php endif; ?>

                <?php $__currentLoopData = $thongBaos->getUrlRange(max(1, $thongBaos->currentPage() - 1), min($thongBaos->lastPage(), $thongBaos->currentPage() + 1)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($page == $thongBaos->currentPage()): ?>
                        <span class="flex h-9 items-center rounded-lg bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-3 text-sm font-bold text-white">
                            <?php echo e($page); ?>

                        </span>
                    <?php else: ?>
                        <a href="<?php echo e($url); ?>"
                        class="flex h-9 items-center rounded-lg border border-white/10 bg-white/5 px-3 text-sm font-medium text-gray-300 transition-all hover:bg-white/10">
                            <?php echo e($page); ?>

                        </a>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php if($thongBaos->hasMorePages()): ?>
                    <a href="<?php echo e($thongBaos->nextPageUrl()); ?>"
                    class="flex h-9 items-center rounded-lg border border-white/10 bg-white/5 px-3 text-sm font-medium text-gray-300 transition-all hover:bg-white/10">
                        Sau <i class="fa-solid fa-chevron-right ml-1 text-xs"></i>
                    </a>
                <?php else: ?>
                    <span class="flex h-9 items-center rounded-lg border border-white/10 bg-white/5 px-3 text-sm font-medium text-gray-500">
                        Sau <i class="fa-solid fa-chevron-right ml-1 text-xs"></i>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<style>
    .dropdown-menu {
        animation: dropdownFadeIn 0.2s ease-out;
    }

    @keyframes dropdownFadeIn {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .dropdown-menu.active { display: block !important; }

    .dropdown-arrow.rotate-180 { transform: rotate(180deg); }

    .dropdown-option.selected {
        background: rgba(217, 154, 50, 0.15) !important;
        border: 1px solid rgba(217, 154, 50, 0.3);
    }
</style>

<script>
    function initFilterDropdown(wrapperId, hiddenInputId, iconId, labelId) {
        const wrapper = document.getElementById(wrapperId);
        if (!wrapper) return;

        const trigger = wrapper.querySelector('.dropdown-trigger');
        const menu = wrapper.querySelector('.dropdown-menu');
        const arrow = trigger.querySelector('.dropdown-arrow');
        const hiddenInput = document.getElementById(hiddenInputId);
        const iconEl = document.getElementById(iconId);
        const labelEl = document.getElementById(labelId);

        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            closeAllDropdowns();
            menu.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        });

        wrapper.querySelectorAll('.dropdown-option').forEach(option => {
            option.addEventListener('click', function(e) {
                e.stopPropagation();
                const value = this.dataset.value;
                const label = this.dataset.label;
                const iconHtml = this.querySelector('span').innerHTML;
                const iconClass = this.querySelector('span').className;

                hiddenInput.value = value;
                labelEl.textContent = label;
                iconEl.innerHTML = iconHtml;
                iconEl.querySelector('span').className = iconClass.replace('group-hover:', '').replace('transition-colors', '');

                wrapper.querySelectorAll('.dropdown-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');

                menu.classList.add('hidden');
                arrow.classList.remove('rotate-180');
            });
        });
    }

    function closeAllDropdowns() {
        document.querySelectorAll('.dropdown-menu').forEach(menu => menu.classList.add('hidden'));
        document.querySelectorAll('.dropdown-arrow').forEach(arrow => arrow.classList.remove('rotate-180'));
    }

    document.addEventListener('click', closeAllDropdowns);

    document.addEventListener('DOMContentLoaded', function() {
        initFilterDropdown('filterLoaiDropdownWrapper', 'filterLoai', 'filterLoaiIcon', 'filterLoaiLabel');
        initFilterDropdown('filterDoiTuongDropdownWrapper', 'filterDoiTuong', 'filterDoiTuongIcon', 'filterDoiTuongLabel');
        initFilterDropdown('filterTrangThaiDropdownWrapper', 'filterTrangThai', 'filterTrangThaiIcon', 'filterTrangThaiLabel');
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/admin/thong-bao-push/index.blade.php ENDPATH**/ ?>