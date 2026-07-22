<?php
    $thanhVien = $user->thanhVien;

    $daLienKetGioiThieu = $thanhVien
        && (
            $thanhVien->nguoi_gioi_thieu_id !== null
            || $thanhVien->da_nhan_thuong
        );

    $nguoiGioiThieu = $thanhVien?->nguoiGioiThieu;
    $taiKhoanNguoiGioiThieu = $nguoiGioiThieu?->nguoiDung;
?>

<section class="profile-section">
    <header class="profile-section-head">
        <span>
            <i class="fa-solid fa-id-card"></i>
        </span>

        <div>
            <h2>Thông tin cá nhân</h2>
            <p>
                Chỉnh sửa tên hiển thị, email liên hệ, ngày sinh
                và liên kết mã giới thiệu.
            </p>
        </div>
    </header>

    <?php if(session('referral-success')): ?>
        <div class="profile-toast is-success">
            <i class="fa-solid fa-circle-check"></i>
            <?php echo e(session('referral-success')); ?>

        </div>
    <?php endif; ?>

    <form
        id="send-verification"
        method="POST"
        action="<?php echo e(route('verification.send')); ?>"
    >
        <?php echo csrf_field(); ?>
    </form>

    <form
        method="POST"
        action="<?php echo e(route('profile.update')); ?>"
        class="profile-form"
    >
        <?php echo csrf_field(); ?>
        <?php echo method_field('patch'); ?>

        
        <label class="profile-field" for="ho_ten">
            <span>Họ và tên thành viên</span>

            <div class="profile-input-wrap">
                <i class="fa-solid fa-user"></i>

                <input
                    id="ho_ten"
                    name="ho_ten"
                    type="text"
                    value="<?php echo e(old('ho_ten', $user->ho_ten)); ?>"
                    required
                    autofocus
                    autocomplete="name"
                >
            </div>

            <?php $__errorArgs = ['ho_ten'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <small class="profile-error"><?php echo e($message); ?></small>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </label>

        
        <label class="profile-field" for="ngay_sinh">
            <span>Ngày sinh nhận quà</span>

            <div class="profile-input-wrap">
                <i class="fa-solid fa-cake-candles"></i>

                <input
                    id="ngay_sinh"
                    name="ngay_sinh"
                    type="date"
                    value="<?php echo e(old(
                        'ngay_sinh',
                        $user->ngay_sinh
                            ? \Carbon\Carbon::parse($user->ngay_sinh)->format('Y-m-d')
                            : ''
                    )); ?>"
                    max="<?php echo e(now()->format('Y-m-d')); ?>"
                    <?php echo e($user->ngay_sinh ? 'disabled' : ''); ?>

                >
            </div>

            <?php if($user->ngay_sinh): ?>
                <small class="profile-hint is-locked">
                    <i class="fa-solid fa-lock"></i>
                    Ngày sinh đã được khóa để bảo vệ quyền lợi voucher sinh nhật.
                </small>
            <?php else: ?>
                <small class="profile-hint">
                    Ngày sinh chỉ được thiết lập một lần.
                </small>
            <?php endif; ?>

            <?php $__errorArgs = ['ngay_sinh'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <small class="profile-error"><?php echo e($message); ?></small>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </label>

        
        <label class="profile-field" for="email">
            <span>Địa chỉ email liên hệ</span>

            <div class="profile-input-wrap">
                <i class="fa-solid fa-envelope"></i>

                <input
                    id="email"
                    name="email"
                    type="email"
                    value="<?php echo e(old('email', $user->email)); ?>"
                    required
                    autocomplete="username"
                >
            </div>

            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <small class="profile-error"><?php echo e($message); ?></small>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </label>

        
        <label class="profile-field" for="ma_gioi_thieu">
            <span>Mã giới thiệu</span>

            <?php if(!$thanhVien): ?>
                <div class="profile-input-wrap">
                    <i class="fa-solid fa-user-group"></i>

                    <input
                        id="ma_gioi_thieu"
                        type="text"
                        value=""
                        placeholder="Tài khoản chưa có thẻ thành viên"
                        disabled
                    >
                </div>

                <small class="profile-hint is-locked">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    Tài khoản của bạn chưa được cấp thẻ thành viên.
                </small>

            <?php elseif($daLienKetGioiThieu): ?>
                <div class="profile-input-wrap">
                    <i class="fa-solid fa-user-check"></i>

                    <input
                        id="ma_gioi_thieu"
                        type="text"
                        value="<?php echo e($nguoiGioiThieu?->ma_gioi_thieu ?? 'Đã liên kết'); ?>"
                        disabled
                    >
                </div>

                <small class="profile-hint is-locked">
                    <i class="fa-solid fa-lock"></i>

                    Đã liên kết
                    <?php if($taiKhoanNguoiGioiThieu): ?>
                        với <?php echo e($taiKhoanNguoiGioiThieu->ho_ten); ?>.
                    <?php else: ?>
                        mã giới thiệu thành công.
                    <?php endif; ?>

                    Mã giới thiệu chỉ được nhập một lần.
                </small>

            <?php else: ?>
                <div class="profile-input-wrap">
                    <i class="fa-solid fa-user-plus"></i>

                    <input
                        id="ma_gioi_thieu"
                        name="ma_gioi_thieu"
                        type="text"
                        value="<?php echo e(old('ma_gioi_thieu')); ?>"
                        placeholder="Ví dụ: GT-TV000008"
                        maxlength="50"
                        autocomplete="off"
                        oninput="this.value = this.value.toUpperCase().replace(/\s/g, '')"
                    >
                </div>

                <small class="profile-hint">
                    Nhập mã của người đã giới thiệu bạn đến CineHome.
                    Mỗi tài khoản chỉ được nhập một lần.
                </small>

                <small class="profile-hint">
                    Bạn nhận <strong>20 điểm</strong> và người giới thiệu
                    nhận <strong>50 điểm</strong>.
                </small>
            <?php endif; ?>

            <?php $__errorArgs = ['ma_gioi_thieu'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <small class="profile-error"><?php echo e($message); ?></small>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </label>

        <div class="profile-actions">
            <button type="submit" class="profile-primary-btn">
                <i class="fa-solid fa-floppy-disk"></i>
                Lưu thay đổi
            </button>
        </div>
    </form>
</section><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/profile/partials/cap_nhat_thong_tin_form.blade.php ENDPATH**/ ?>