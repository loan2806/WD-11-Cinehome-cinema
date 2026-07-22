<?php $__env->startSection('page-title', 'Thêm suất chiếu'); ?>
<?php $__env->startSection('page-subtitle', 'Lên lịch chiếu đơn lẻ hoặc tạo chuỗi suất chiếu hàng loạt'); ?>

<?php
    $selectedLoaiTao = old('loai_tao', 'don_le');
    $selectedPhimId = old('phim_id', request('phim_id'));
    $selectedRapId = old('rap_chieu_phim_id', request('rap_chieu_phim_id'));
    $selectedPhongId = old('phong_chieu_id', $phongChieuId ?? request('phong_chieu_id'));
    $selectedKhungGio = old('khung_gio', []);
    $selectedKhungGio = is_array($selectedKhungGio) ? $selectedKhungGio : [];
    $khungGioMacDinh = ['08:30', '11:00', '13:30', '16:00', '18:30', '21:00', '23:30'];
?>

<?php $__env->startSection('content'); ?>
<div class="showtime-create-page">
    <?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <section class="showtime-hero-panel">
        <div>
            <span class="showtime-kicker">
                <i class="fa-solid fa-calendar-plus"></i>
                Trung tâm lên lịch
            </span>
            <h2>Thêm suất chiếu mới</h2>
            <p>
                Cấu hình lịch chiếu đơn lẻ hoặc rải chuỗi suất chiếu hàng loạt. Giao diện được gom theo từng bước
                để giảm nhầm phòng, nhầm giờ và dễ kiểm tra trước khi lưu.
            </p>
        </div>

        <div class="showtime-hero-actions">
            <a href="<?php echo e(route('admin.suat-chieus.index')); ?>" class="movie-action-btn is-ghost">
                <i class="fa-solid fa-arrow-left"></i>
                Danh sách suất chiếu
            </a>
            <button type="submit" form="showtimeCreateForm" class="movie-action-btn is-primary">
                <i class="fa-solid fa-floppy-disk"></i>
                Xác nhận lên lịch
            </button>
        </div>
    </section>

    <?php if(session('suat_chieu_trung_danh_sach')): ?>
        <section class="showtime-alert is-danger">
            <div class="showtime-alert-head">
                <span><i class="fa-solid fa-triangle-exclamation"></i></span>
                <div>
                    <strong>Phát hiện trùng lịch phòng chiếu</strong>
                    <p>Hệ thống đã chặn thao tác vì khung giờ bạn chọn đè lên các suất chiếu bên dưới.</p>
                </div>
            </div>

            <div class="showtime-conflict-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID suất</th>
                            <th>Phim đang chiếm chỗ</th>
                            <th>Phòng</th>
                            <th>Thời gian chiếu</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = session('suat_chieu_trung_danh_sach'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $scTrung): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>#<?php echo e($scTrung->id); ?></td>
                                <td><?php echo e($scTrung->phim->ten_phim ?? 'Không rõ'); ?></td>
                                <td><?php echo e($scTrung->phongChieu->ten_phong ?? 'Không rõ'); ?></td>
                                <td>
                                    <?php echo e(\Carbon\Carbon::parse($scTrung->thoi_gian_chieu)->format('H:i d/m/Y')); ?>

                                    -
                                    <?php echo e(\Carbon\Carbon::parse($scTrung->thoi_gian_ket_thuc)->format('H:i d/m/Y')); ?>

                                </td>
                                <td>
                                    <a href="<?php echo e(route('admin.suat-chieus.edit', $scTrung->id)); ?>" target="_blank">
                                        Sửa suất này <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <section class="showtime-alert is-danger">
            <div class="showtime-alert-head">
                <span><i class="fa-solid fa-circle-exclamation"></i></span>
                <div>
                    <strong>Vui lòng kiểm tra lại thông tin</strong>
                    <p>Có <?php echo e($errors->count()); ?> lỗi cần xử lý trước khi tạo suất chiếu.</p>
                </div>
            </div>
            <ul class="showtime-error-list">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </section>
    <?php endif; ?>

    <form id="showtimeCreateForm" action="<?php echo e(route('admin.suat-chieus.store')); ?>" method="POST" class="showtime-form">
        <?php echo csrf_field(); ?>

        <div class="showtime-form-layout">
            <main class="showtime-main">
                <section class="showtime-panel">
                    <div class="showtime-panel-head">
                        <span><i class="fa-solid fa-clapperboard"></i></span>
                        <div>
                            <h3>1. Chọn phim và phòng chiếu</h3>
                            <p>Xác định phim, rạp và phòng trước khi chọn khung giờ.</p>
                        </div>
                    </div>

                    <div class="showtime-grid">
                        <label class="showtime-field">
                            <span>Phim trình chiếu <b>*</b></span>
                            <select name="phim_id" id="phim_id" required>
                                <option value="">Chọn phim</option>
                                <?php $__currentLoopData = $phims; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $phim): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        value="<?php echo e($phim->id); ?>"
                                        data-thoi-luong="<?php echo e($phim->thoi_luong); ?>"
                                        <?php if((string) $selectedPhimId === (string) $phim->id): echo 'selected'; endif; ?>
                                    >
                                        <?php echo e($phim->ten_phim); ?> (<?php echo e($phim->thoi_luong ?? 90); ?> phút)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </label>

                        <label class="showtime-field">
                            <span>Rạp chiếu <b>*</b></span>
                            <select name="rap_chieu_phim_id" id="rap_chieu_phim_id" required>
                                <option value="">Chọn rạp</option>
                                <?php $__currentLoopData = $rapChieuPhims; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rap): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($rap->id); ?>" <?php if((string) $selectedRapId === (string) $rap->id): echo 'selected'; endif; ?>>
                                        <?php echo e($rap->ten_rap); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </label>

                        <label class="showtime-field">
                            <span>Phòng chiếu <b>*</b></span>
                            <select name="phong_chieu_id" id="phong_chieu_id" required>
                                <option value="">Chọn phòng</option>
                                <?php $__currentLoopData = $phongChieus ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $phong): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        value="<?php echo e($phong->id); ?>"
                                        data-rap-id="<?php echo e($phong->rap_chieu_phim_id); ?>"
                                        data-room-type="<?php echo e(strtoupper($phong->loai_phong)); ?>"
                                        <?php if((string) $selectedPhongId === (string) $phong->id): echo 'selected'; endif; ?>
                                    >
                                        <?php echo e($phong->ten_phong); ?> (<?php echo e(strtoupper($phong->loai_phong)); ?>)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </label>
                    </div>
                </section>

                <section class="showtime-panel">
                    <div class="showtime-panel-head">
                        <span><i class="fa-solid fa-sliders"></i></span>
                        <div>
                            <h3>2. Phương thức lên lịch</h3>
                            <p>Tạo một suất cụ thể hoặc rải nhiều suất theo khoảng ngày.</p>
                        </div>
                    </div>

                    <div class="showtime-mode-row">
                        <label class="showtime-field">
                            <span>Chế độ tạo lịch <b>*</b></span>
                            <select name="loai_tao" id="loai_tao" required>
                                <option value="don_le" <?php if($selectedLoaiTao === 'don_le'): echo 'selected'; endif; ?>>Tạo 1 suất chiếu đơn lẻ</option>
                                <option value="hang_loat" <?php if($selectedLoaiTao === 'hang_loat'): echo 'selected'; endif; ?>>Tạo chuỗi suất chiếu hàng loạt</option>
                            </select>
                        </label>

                        <div class="showtime-mode-note">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span>Hệ thống sẽ kiểm tra trùng lịch theo thời lượng phim và thời gian dọn phòng.</span>
                        </div>
                    </div>

                    <div id="khu_don_le" class="showtime-mode-block">
                        <div class="showtime-grid two-cols">
                            <label class="showtime-field">
                                <span>Ngày chiếu <b>*</b></span>
                                <input
                                    type="date"
                                    name="ngay_chieu_don_le"
                                    id="ngay_chieu_don_le"
                                    min="<?php echo e(date('Y-m-d')); ?>"
                                    value="<?php echo e(old('ngay_chieu_don_le')); ?>"
                                >
                            </label>

                            <label class="showtime-field">
                                <span>Giờ khởi chiếu <b>*</b></span>
                                <input
                                    type="time"
                                    name="gio_chieu_don_le"
                                    id="gio_chieu_don_le"
                                    value="<?php echo e(old('gio_chieu_don_le')); ?>"
                                >
                            </label>
                        </div>
                    </div>

                    <div id="khu_hang_loat" class="showtime-mode-block">
                        <div class="showtime-grid two-cols">
                            <label class="showtime-field">
                                <span>Từ ngày <b>*</b></span>
                                <input
                                    type="date"
                                    name="ngay_bat_dau"
                                    id="ngay_bat_dau"
                                    min="<?php echo e(date('Y-m-d')); ?>"
                                    value="<?php echo e(old('ngay_bat_dau')); ?>"
                                >
                            </label>

                            <label class="showtime-field">
                                <span>Đến hết ngày <b>*</b></span>
                                <input
                                    type="date"
                                    name="ngay_ket_thuc"
                                    id="ngay_ket_thuc"
                                    min="<?php echo e(date('Y-m-d')); ?>"
                                    value="<?php echo e(old('ngay_ket_thuc')); ?>"
                                >
                            </label>
                        </div>

                        <div class="showtime-time-picker">
                            <div>
                                <strong>Khung giờ trong ngày <b>*</b></strong>
                                <span>Chọn một hoặc nhiều mốc giờ để tạo lịch hàng loạt.</span>
                            </div>

                            <div class="showtime-time-list" id="khung_gio_checkboxes">
                                <?php $__currentLoopData = $khungGioMacDinh; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gio): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label class="showtime-time-chip">
                                        <input
                                            type="checkbox"
                                            name="khung_gio[]"
                                            value="<?php echo e($gio); ?>"
                                            <?php if(in_array($gio, $selectedKhungGio)): echo 'checked'; endif; ?>
                                        >
                                        <span><?php echo e($gio); ?></span>
                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>

                            <div class="showtime-custom-time">
                                <input type="time" id="custom_time_input">
                                <button type="button" id="btn_add_custom_time">
                                    <i class="fa-solid fa-plus"></i>
                                    Chèn giờ khác
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="showtime-panel">
                    <div class="showtime-panel-head">
                        <span><i class="fa-solid fa-money-bill-wave"></i></span>
                        <div>
                            <h3>3. Biểu giá</h3>
                            <p>Để trống giá tùy chỉnh nếu muốn dùng ma trận giá tự động của hệ thống.</p>
                        </div>
                    </div>

                    <div id="khu_vuc_gia_ngay_le" class="showtime-holiday-price">
                        <div class="showtime-holiday-head">
                            <i class="fa-solid fa-gift"></i>
                            <div>
                                <strong>Phát hiện ngày lễ: <span id="ten_ngay_le_label"></span></strong>
                                <small>Nhập giá riêng nếu muốn áp dụng biểu giá ngày lễ.</small>
                            </div>
                        </div>
                        <label class="showtime-field">
                            <span>Giá vé ngày lễ</span>
                            <div class="showtime-money-input">
                                <input
                                    type="number"
                                    name="gia_ve_ngay_le"
                                    id="gia_ve_ngay_le"
                                    value="<?php echo e(old('gia_ve_ngay_le')); ?>"
                                    placeholder="Ví dụ: 120000"
                                >
                                <em>VND</em>
                            </div>
                        </label>
                    </div>

                    <div class="showtime-grid two-cols">
                        <label class="showtime-field">
                            <span>Giá vé tùy chỉnh</span>
                            <div class="showtime-money-input">
                                <input
                                    type="number"
                                    name="gia_ve_tuy_chinh"
                                    value="<?php echo e(old('gia_ve_tuy_chinh')); ?>"
                                    placeholder="Bỏ trống để dùng giá tự động"
                                >
                                <em>VND</em>
                            </div>
                        </label>

                        <div class="showtime-price-note">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>Giá tùy chỉnh sẽ ghi đè giá thường/cuối tuần. Giá ngày lễ chỉ áp dụng khi lịch rơi vào ngày lễ.</span>
                        </div>
                    </div>
                </section>
            </main>

            <aside class="showtime-side">
                <section class="showtime-panel showtime-monitor">
                    <div class="showtime-panel-head">
                        <span><i class="fa-solid fa-desktop"></i></span>
                        <div>
                            <h3>Monitor lịch</h3>
                            <p>Xem nhanh thời lượng chiếm phòng trước khi tạo.</p>
                        </div>
                    </div>

                    <textarea id="thoi_luong_preview" readonly placeholder="Chọn phim và thời gian để xem phân tích lịch..."></textarea>

                    <div class="showtime-monitor-metrics">
                        <div>
                            <small>Dọn phòng</small>
                            <strong><?php echo e($thoiGianDonPhong); ?> phút</strong>
                        </div>
                        <div>
                            <small>Chế độ</small>
                            <strong id="modePreview">Đơn lẻ</strong>
                        </div>
                    </div>
                </section>

                <section class="showtime-panel showtime-guide">
                    <div class="showtime-panel-head">
                        <span><i class="fa-solid fa-lightbulb"></i></span>
                        <div>
                            <h3>Gợi ý vận hành</h3>
                            <p>Các điểm nên kiểm tra trước khi lưu lịch.</p>
                        </div>
                    </div>

                    <ul>
                        <li><i class="fa-solid fa-check"></i> Chọn đúng rạp và phòng trước khi chọn giờ.</li>
                        <li><i class="fa-solid fa-check"></i> Với lịch hàng loạt, tránh chọn quá nhiều khung giờ sát nhau.</li>
                        <li><i class="fa-solid fa-check"></i> Dùng giá tùy chỉnh khi có chương trình đặc biệt.</li>
                    </ul>
                </section>
            </aside>
        </div>

        <div class="showtime-savebar">
            <div>
                <strong>Sẵn sàng tạo suất chiếu?</strong>
                <span>Hệ thống sẽ kiểm tra trùng lịch trước khi lưu.</span>
            </div>
            <div class="showtime-save-actions">
                <a href="<?php echo e(route('admin.suat-chieus.index')); ?>" class="movie-action-btn is-ghost">
                    <i class="fa-solid fa-xmark"></i>
                    Hủy
                </a>
                <button type="submit" class="movie-action-btn is-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Xác nhận lên lịch
                </button>
            </div>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const loaiParam = document.getElementById('loai_tao');
    const khuDonLe = document.getElementById('khu_don_le');
    const khuHangLoat = document.getElementById('khu_hang_loat');
    const phimSelect = document.getElementById('phim_id');
    const rapSelect = document.getElementById('rap_chieu_phim_id');
    const phongSelect = document.getElementById('phong_chieu_id');
    const thoiLuongPreview = document.getElementById('thoi_luong_preview');
    const modePreview = document.getElementById('modePreview');
    const ngayChieuDonLe = document.getElementById('ngay_chieu_don_le');
    const gioChieuDonLe = document.getElementById('gio_chieu_don_le');
    const khuVucGiaNgayLe = document.getElementById('khu_vuc_gia_ngay_le');
    const tenNgayLeLabel = document.getElementById('ten_ngay_le_label');
    const ngayBatDauInput = document.getElementById('ngay_bat_dau');
    const ngayKetThucInput = document.getElementById('ngay_ket_thuc');
    const btnAddCustomTime = document.getElementById('btn_add_custom_time');
    const customTimeInput = document.getElementById('custom_time_input');
    const checkboxesContainer = document.getElementById('khung_gio_checkboxes');
    const thoiGianDonPhong = <?php echo e((int) $thoiGianDonPhong); ?>;

    const cacNgayLeVN = {
        '01-01': 'Tết Dương Lịch',
        '04-30': 'Ngày Giải Phóng Miền Nam',
        '05-01': 'Ngày Quốc Tế Lao Động',
        '09-02': 'Ngày Quốc Khánh',
        '09-03': 'Ngày Quốc Khánh dự phòng'
    };

    function formatTime(date) {
        return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
    }

    function filterRoomsByCinema() {
        if (!rapSelect || !phongSelect) {
            return;
        }

        const rapId = rapSelect.value;
        let firstVisibleValue = '';
        let currentStillVisible = false;

        Array.from(phongSelect.options).forEach(function(option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            const isVisible = !rapId || option.dataset.rapId === rapId;
            option.hidden = !isVisible;

            if (isVisible && !firstVisibleValue) {
                firstVisibleValue = option.value;
            }

            if (isVisible && option.selected) {
                currentStillVisible = true;
            }
        });

        if (rapId && !currentStillVisible) {
            phongSelect.value = firstVisibleValue;
        }
    }

    function checkNgayLeRealtime() {
        const holidayFound = [];

        if (loaiParam.value === 'don_le' && ngayChieuDonLe.value) {
            const dateParts = ngayChieuDonLe.value.split('-');
            const key = dateParts[1] + '-' + dateParts[2];

            if (cacNgayLeVN[key]) {
                holidayFound.push(cacNgayLeVN[key]);
            }
        }

        if (loaiParam.value === 'hang_loat' && ngayBatDauInput.value && ngayKetThucInput.value) {
            const start = new Date(ngayBatDauInput.value);
            const end = new Date(ngayKetThucInput.value);

            for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
                const mm = String(d.getMonth() + 1).padStart(2, '0');
                const dd = String(d.getDate()).padStart(2, '0');
                const key = mm + '-' + dd;

                if (cacNgayLeVN[key] && !holidayFound.includes(cacNgayLeVN[key])) {
                    holidayFound.push(cacNgayLeVN[key]);
                }
            }
        }

        if (holidayFound.length > 0) {
            tenNgayLeLabel.textContent = holidayFound.join(', ');
            khuVucGiaNgayLe.classList.add('is-visible');
            khuVucGiaNgayLe.style.display = 'block';
            return;
        }

        tenNgayLeLabel.textContent = '';
        khuVucGiaNgayLe.classList.remove('is-visible');
        khuVucGiaNgayLe.style.display = 'none';
    }

    function switchFormMode() {
        const isSingle = loaiParam.value === 'don_le';
        khuDonLe.style.display = isSingle ? 'block' : 'none';
        khuHangLoat.style.display = isSingle ? 'none' : 'block';
        modePreview.textContent = isSingle ? 'Đơn lẻ' : 'Hàng loạt';
        checkNgayLeRealtime();
        updateTimePreview();
    }

    function updateTimePreview() {
        const selectedOption = phimSelect.options[phimSelect.selectedIndex];

        if (!selectedOption || !selectedOption.value) {
            thoiLuongPreview.value = 'Chọn phim để hệ thống phân tích thời lượng chiếm phòng.';
            return;
        }

        const thoiLuong = parseInt(selectedOption.dataset.thoiLuong, 10) || 90;
        const tongChiếmPhong = thoiLuong + thoiGianDonPhong;

        if (loaiParam.value === 'don_le') {
            if (ngayChieuDonLe.value && gioChieuDonLe.value) {
                const start = new Date(`${ngayChieuDonLe.value}T${gioChieuDonLe.value}`);
                const end = new Date(start.getTime() + tongChiếmPhong * 60000);
                thoiLuongPreview.value = `Suất đơn lẻ: ${formatTime(start)} - ${formatTime(end)}. Phòng bị chiếm ${tongChiếmPhong} phút, gồm ${thoiLuong} phút phim và ${thoiGianDonPhong} phút dọn phòng.`;
                return;
            }

            thoiLuongPreview.value = `Phim đã chọn có thời lượng ${thoiLuong} phút. Sau khi chọn ngày giờ, hệ thống sẽ tính thời gian kết thúc dự kiến.`;
            return;
        }

        const checkedTimes = checkboxesContainer.querySelectorAll('input[type="checkbox"]:checked').length;
        thoiLuongPreview.value = `Chế độ hàng loạt: mỗi suất chiếm ${tongChiếmPhong} phút. Hiện đang chọn ${checkedTimes} khung giờ trong ngày.`;
    }

    btnAddCustomTime.addEventListener('click', function() {
        const customTime = customTimeInput.value;

        if (!customTime) {
            alert('Vui lòng chọn mốc giờ hợp lệ.');
            return;
        }

        const existing = Array.from(checkboxesContainer.querySelectorAll('input[type="checkbox"]'))
            .some(function(input) {
                return input.value === customTime;
            });

        if (existing) {
            alert('Khung giờ này đã có trong danh sách.');
            return;
        }

        const newLabel = document.createElement('label');
        newLabel.className = 'showtime-time-chip is-custom';
        newLabel.innerHTML = `<input type="checkbox" name="khung_gio[]" value="${customTime}" checked><span>${customTime}</span>`;
        checkboxesContainer.appendChild(newLabel);
        customTimeInput.value = '';
        updateTimePreview();
    });

    loaiParam.addEventListener('change', switchFormMode);
    phimSelect.addEventListener('change', updateTimePreview);
    rapSelect.addEventListener('change', function() {
        filterRoomsByCinema();
        updateTimePreview();
    });
    ngayChieuDonLe.addEventListener('change', function() {
        checkNgayLeRealtime();
        updateTimePreview();
    });
    gioChieuDonLe.addEventListener('change', updateTimePreview);
    ngayBatDauInput.addEventListener('change', function() {
        checkNgayLeRealtime();
        updateTimePreview();
    });
    ngayKetThucInput.addEventListener('change', function() {
        checkNgayLeRealtime();
        updateTimePreview();
    });
    checkboxesContainer.addEventListener('change', updateTimePreview);

    filterRoomsByCinema();
    switchFormMode();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/admin/suat-chieus/create.blade.php ENDPATH**/ ?>