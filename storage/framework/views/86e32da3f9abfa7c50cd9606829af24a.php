<?php $__env->startSection('title', 'Quản lý Loại Ghế - CineHome'); ?>
<?php $__env->startSection('page-title', 'Quản lý Loại Ghế'); ?>

<?php $__env->startSection('content'); ?>

    <?php
        // Tính toán chỉ số thống kê từ collection
        $totalSeatTypes = $loaiGhes->count();
        $totalSeats = $loaiGhes->sum('ghe_ngois_count');
        $maxSurcharge = $loaiGhes->max('phu_thu');
    ?>

    <div class="admin-panel space-y-6">

        
        <div class="panel-header flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-white/5 pb-6">
            <div>
                <h5 class="text-3xl font-black text-white tracking-wide">
                    Danh sách loại ghế
                </h5>
                <p class="text-sm text-gray-400 mt-1">
                    Quản lý định dạng các loại ghế (Thường, VIP, Couple), màu sắc hiển thị và mức phụ thu tương ứng
                </p>
            </div>

            <a href="<?php echo e(route('admin.loai-ghes.create')); ?>"
                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-[#e50914] to-[#ff3b46] px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-red-500/10 hover:shadow-red-500/20 hover:scale-[1.02] active:scale-[0.98] transition duration-200">
                <i class="fa-solid fa-plus text-base"></i>
                Thêm loại ghế mới
            </a>
        </div>

        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Thẻ 1: Tổng loại ghế -->
            <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-br from-[#1e1e1e] to-[#121212] p-5 shadow-xl transition-all duration-300 hover:scale-[1.02] hover:border-red-500/40 group">
                <div class="absolute -right-4 -bottom-4 text-white/5 text-8xl transition-transform duration-500 group-hover:scale-110 group-hover:rotate-6">
                    <i class="fa-solid fa-couch"></i>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Tổng số loại ghế</p>
                        <h3 class="text-4xl font-black text-white mt-2"><?php echo e($totalSeatTypes); ?></h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-500/10 text-red-500 border border-red-500/20">
                        <i class="fa-solid fa-couch text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-xs text-gray-400">
                    <i class="fa-solid fa-circle text-[6px] text-gray-500"></i>
                    <span>Các phân hạng ghế trong rạp</span>
                </div>
            </div>

            <!-- Thẻ 2: Tổng số ghế được dùng -->
            <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-br from-[#1e1e1e] to-[#121212] p-5 shadow-xl transition-all duration-300 hover:scale-[1.02] hover:border-red-500/40 group">
                <div class="absolute -right-4 -bottom-4 text-white/5 text-8xl transition-transform duration-500 group-hover:scale-110 group-hover:rotate-6">
                    <i class="fa-solid fa-chair"></i>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Ghế đang sử dụng</p>
                        <h3 class="text-4xl font-black text-white mt-2"><?php echo e(number_format($totalSeats)); ?></h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-500/10 text-red-500 border border-red-500/20">
                        <i class="fa-solid fa-circle-check text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-xs text-green-400">
                    <span class="inline-block h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
                    <span>Ghế được ánh xạ sơ đồ phòng</span>
                </div>
            </div>

            <!-- Thẻ 3: Phụ thu tối đa -->
            <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-br from-[#1e1e1e] to-[#121212] p-5 shadow-xl transition-all duration-300 hover:scale-[1.02] hover:border-red-500/40 group">
                <div class="absolute -right-4 -bottom-4 text-white/5 text-8xl transition-transform duration-500 group-hover:scale-110 group-hover:rotate-6">
                    <i class="fa-solid fa-tags"></i>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Phụ thu cao nhất</p>
                        <h3 class="text-4xl font-black text-red-500 mt-2">+<?php echo e(number_format($maxSurcharge)); ?>đ</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-500/10 text-red-500 border border-red-500/20">
                        <i class="fa-solid fa-tags text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-xs text-gray-400">
                    <i class="fa-solid fa-circle text-[6px] text-gray-500"></i>
                    <span>Áp dụng vào biểu giá vé cơ bản</span>
                </div>
            </div>
        </div>

        
        <div class="flex items-center justify-between bg-[#151515] border border-white/10 rounded-2xl p-4 shadow-md">
            <span class="text-sm font-bold text-gray-400 flex items-center gap-2">
                <i class="fa-solid fa-sliders text-red-500"></i>
                Phân loại hiển thị sơ đồ phòng vé
            </span>

            
            <div class="flex items-center gap-1 bg-black/35 p-1 rounded-xl border border-white/5">
                <button id="btnGridView"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-sm transition-all duration-200 text-gray-400 hover:text-white"
                    title="Chế độ ô lưới">
                    <i class="fa-solid fa-grip"></i>
                </button>
                <button id="btnTableView"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-sm transition-all duration-200 text-gray-400 hover:text-white"
                    title="Chế độ bảng biểu">
                    <i class="fa-solid fa-list"></i>
                </button>
            </div>
        </div>

        
        <div id="seatTypeGridView" class="hidden grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php $__empty_1 = true; $__currentLoopData = $loaiGhes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loai): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-b from-[#1c1c1c] to-[#121212] p-5 transition-all duration-300 hover:-translate-y-1 hover:border-red-500/30 hover:shadow-[0_10px_35px_rgba(229,9,20,0.15)]">
                    
                    
                    <div class="relative h-24 rounded-xl flex items-center justify-center overflow-hidden bg-black/30 border border-white/5 mb-5 transition-transform duration-500 group-hover:scale-[1.02]">
                        <!-- Hiệu ứng phát sáng neon mờ phía sau ghế -->
                        <div class="absolute h-12 w-12 rounded-full blur-2xl opacity-35" style="background-color: <?php echo e($loai->mau_sac ?? '#666666'); ?>"></div>
                        
                        <!-- Mô hình 3D vector ghế xem phim -->
                        <div class="relative flex flex-col items-center">
                            <!-- Tựa lưng -->
                            <div class="w-12 h-9 rounded-t-xl border-t border-x border-white/25 relative flex items-center justify-center transition-all duration-300 group-hover:scale-105" style="background-color: <?php echo e($loai->mau_sac ?? '#666666'); ?>">
                                <span class="text-[8px] font-black tracking-widest text-white/50 select-none drop-shadow-lg">CINE</span>
                            </div>
                            <!-- Đệm ngồi -->
                            <div class="w-13 h-4 rounded-b-lg border-b border-x border-white/30 mt-0.5" style="background-color: <?php echo e($loai->mau_sac ?? '#666666'); ?>"></div>
                            <!-- Tay vịn 2 bên -->
                            <div class="absolute -left-2 top-2.5 w-1.5 h-7 bg-white/10 rounded-sm border border-white/10 shadow-inner"></div>
                            <div class="absolute -right-2 top-2.5 w-1.5 h-7 bg-white/10 rounded-sm border border-white/10 shadow-inner"></div>
                        </div>
                    </div>

                    
                    <div class="flex-grow flex flex-col">
                        <div class="flex items-start justify-between gap-2">
                            <h4 class="text-xl font-extrabold text-white tracking-wide group-hover:text-red-500 transition duration-200">
                                <?php echo e($loai->ten_loai); ?>

                            </h4>
                            <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold border border-red-500/20 bg-red-500/10 text-red-400">
                                +<?php echo e(number_format($loai->phu_thu)); ?>đ
                            </span>
                        </div>

                        <p class="text-xs text-gray-400 mt-2.5 line-clamp-2 flex-grow">
                            <?php echo e($loai->mo_ta ?? 'Chưa có mô tả chi tiết cho loại ghế này.'); ?>

                        </p>

                        <div class="mt-4 pt-3.5 border-t border-white/5 flex items-center justify-between text-xs text-gray-400">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid fa-chair text-gray-500"></i>
                                Số ghế lắp đặt:
                            </span>
                            <span class="font-extrabold text-white text-sm bg-white/5 px-2.5 py-0.5 rounded-lg border border-white/5">
                                <?php echo e($loai->ghe_ngois_count); ?> ghế
                            </span>
                        </div>

                        
                        <div class="mt-6 pt-4 border-t border-white/5 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5 w-full">
                                <a href="<?php echo e(route('admin.loai-ghes.show', $loai)); ?>"
                                    class="flex-1 flex items-center justify-center gap-1.5 rounded-xl bg-white/5 border border-white/10 py-2.5 text-xs font-bold text-white hover:bg-white/10 active:scale-95 transition duration-200">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                    Chi tiết
                                </a>
                                <a href="<?php echo e(route('admin.loai-ghes.edit', $loai)); ?>"
                                    class="flex-1 flex items-center justify-center gap-1.5 rounded-xl bg-red-500/10 border border-red-500/20 py-2.5 text-xs font-bold text-red-400 hover:bg-red-500/20 active:scale-95 transition duration-200">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    Chỉnh sửa
                                </a>
                            </div>

                            <form action="<?php echo e(route('admin.loai-ghes.destroy', $loai)); ?>" method="POST" class="inline-block">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit"
                                    onclick="return confirm('Bạn có chắc muốn xóa loại ghế này?')"
                                    class="flex aspect-square h-10 w-10 items-center justify-center rounded-xl bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20 active:scale-95 transition duration-200 disabled:opacity-40 disabled:pointer-events-none"
                                    <?php echo e($loai->ghe_ngois_count > 0 ? 'disabled' : ''); ?>

                                    title="<?php echo e($loai->ghe_ngois_count > 0 ? 'Không thể xóa loại ghế đang có ghế hoạt động' : 'Xóa loại ghế'); ?>">
                                    <i class="fa-solid fa-trash text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-full py-16 text-center text-gray-500 bg-[#151515]/50 rounded-3xl border border-white/5">
                    <i class="fa-solid fa-couch text-4xl text-gray-600 mb-3 block"></i>
                    Chưa có loại ghế nào trong hệ thống
                </div>
            <?php endif; ?>
        </div>

        
        <div id="seatTypeTableView" class="hidden overflow-hidden rounded-3xl border border-white/10 bg-[#121212] shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] text-left border-collapse">
                    
                    
                    <thead class="bg-white/5 text-xs uppercase tracking-wider text-gray-400 border-b border-white/10">
                        <tr>
                            <th class="px-6 py-4.5 font-bold">STT</th>
                            <th class="px-6 py-4.5 font-bold">Màu sắc</th>
                            <th class="px-6 py-4.5 font-bold">Tên Loại Ghế</th>
                            <th class="px-6 py-4.5 font-bold">Mô tả</th>
                            <th class="px-6 py-4.5 font-bold">Mức Phụ Thu</th>
                            <th class="px-6 py-4.5 font-bold">Số Ghế Đang Dùng</th>
                            <th class="px-6 py-4.5 text-center font-bold">Hành động</th>
                        </tr>
                    </thead>

                    
                    <tbody class="divide-y divide-white/5">
                        <?php $__empty_1 = true; $__currentLoopData = $loaiGhes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $loai): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="transition duration-200 hover:bg-white/5">
                                
                                
                                <td class="px-6 py-4 text-gray-500 text-sm font-semibold">
                                    #<?php echo e($key + 1); ?>

                                </td>

                                
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <span class="inline-block h-8 w-8 rounded-lg border border-white/15 shadow-md transition group-hover:scale-105"
                                              style="background-color: <?php echo e($loai->mau_sac ?? '#666666'); ?>; box-shadow: 0 0 10px <?php echo e(($loai->mau_sac ?? '#666666') . '33'); ?>;"></span>
                                    </div>
                                </td>

                                
                                <td class="px-6 py-4">
                                    <span class="text-white font-extrabold text-base tracking-wide"><?php echo e($loai->ten_loai); ?></span>
                                </td>

                                
                                <td class="px-6 py-4 text-gray-300 text-sm max-w-[300px] truncate">
                                    <?php echo e($loai->mo_ta ?? '-'); ?>

                                </td>

                                
                                <td class="px-6 py-4 text-[#ff3b46] font-bold text-sm">
                                    +<?php echo e(number_format($loai->phu_thu)); ?>đ
                                </td>

                                
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-lg bg-red-500/10 border border-red-500/20 px-3 py-1 text-xs font-bold text-red-400">
                                        <?php echo e($loai->ghe_ngois_count); ?> ghế
                                    </span>
                                </td>

                                
                                <td class="px-6 py-4 align-middle">
                                    <div class="flex items-center justify-center gap-2.5">
                                        
                                        
                                        <a href="<?php echo e(route('admin.loai-ghes.show', $loai)); ?>"
                                            class="flex aspect-square h-9 w-9 items-center justify-center rounded-xl bg-white/5 border border-white/10 text-gray-300 hover:bg-white/10 hover:text-white active:scale-95 transition"
                                            title="Xem chi tiết">
                                            <i class="fa-solid fa-eye text-sm"></i>
                                        </a>

                                        
                                        <a href="<?php echo e(route('admin.loai-ghes.edit', $loai)); ?>"
                                            class="flex aspect-square h-9 w-9 items-center justify-center rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500/20 active:scale-95 transition"
                                            title="Sửa loại ghế">
                                            <i class="fa-solid fa-pen text-sm"></i>
                                        </a>

                                        
                                        <form action="<?php echo e(route('admin.loai-ghes.destroy', $loai)); ?>" method="POST" class="inline-block">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit"
                                                onclick="return confirm('Bạn có chắc muốn xóa loại ghế này?')"
                                                class="flex aspect-square h-9 w-9 items-center justify-center rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500/20 active:scale-95 transition disabled:opacity-40 disabled:pointer-events-none"
                                                <?php echo e($loai->ghe_ngois_count > 0 ? 'disabled' : ''); ?>>
                                                <i class="fa-solid fa-trash text-sm"></i>
                                            </button>
                                        </form>

                                    </div>
                                </td>

                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center text-gray-500">
                                    <i class="fa-solid fa-folder-open text-4xl text-gray-600 mb-3 block"></i>
                                    Chưa có loại ghế nào trong hệ thống
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>
        </div>

    </div>

    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnGrid = document.getElementById('btnGridView');
            const btnTable = document.getElementById('btnTableView');
            const gridView = document.getElementById('seatTypeGridView');
            const tableView = document.getElementById('seatTypeTableView');

            // Hàm chuyển đổi
            function toggleView(mode) {
                if (mode === 'grid') {
                    // Hiển thị Grid
                    gridView.classList.remove('hidden');
                    tableView.classList.add('hidden');
                    
                    // Style Active cho Grid Button
                    btnGrid.classList.add('bg-gradient-to-r', 'from-[#e50914]', 'to-[#ff3b46]', 'text-white', 'shadow-md');
                    btnGrid.classList.remove('text-gray-400');
                    
                    // Style Inactive cho Table Button
                    btnTable.classList.remove('bg-gradient-to-r', 'from-[#e50914]', 'to-[#ff3b46]', 'text-white', 'shadow-md');
                    btnTable.classList.add('text-gray-400');
                } else {
                    // Hiển thị Table
                    gridView.classList.add('hidden');
                    tableView.classList.remove('hidden');
                    
                    // Style Active cho Table Button
                    btnTable.classList.add('bg-gradient-to-r', 'from-[#e50914]', 'to-[#ff3b46]', 'text-white', 'shadow-md');
                    btnTable.classList.remove('text-gray-400');
                    
                    // Style Inactive cho Grid Button
                    btnGrid.classList.remove('bg-gradient-to-r', 'from-[#e50914]', 'to-[#ff3b46]', 'text-white', 'shadow-md');
                    btnGrid.classList.add('text-gray-400');
                }
                
                // Lưu lựa chọn người dùng vào LocalStorage
                localStorage.setItem('adminLoaiGheViewMode', mode);
            }

            // Đọc cấu hình từ LocalStorage, mặc định là 'grid'
            const savedMode = localStorage.getItem('adminLoaiGheViewMode') || 'grid';
            toggleView(savedMode);

            // Gắn sự kiện click
            btnGrid.addEventListener('click', () => toggleView('grid'));
            btnTable.addEventListener('click', () => toggleView('table'));
        });
    </script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/admin/loai-ghes/index.blade.php ENDPATH**/ ?>