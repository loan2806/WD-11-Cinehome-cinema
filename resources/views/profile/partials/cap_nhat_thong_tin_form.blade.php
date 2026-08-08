@php
    $thanhVien = $user->thanhVien;

    $daLienKetGioiThieu = $thanhVien
        && (
            $thanhVien->nguoi_gioi_thieu_id !== null
            || $thanhVien->da_nhan_thuong
        );

    $nguoiGioiThieu = $thanhVien?->nguoiGioiThieu;
    $taiKhoanNguoiGioiThieu = $nguoiGioiThieu?->nguoiDung;
@endphp

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

    @if(session('referral-success'))
        <div class="profile-toast is-success">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('referral-success') }}
        </div>
    @endif

    <form
        id="send-verification"
        method="POST"
        action="{{ route('verification.send') }}"
    >
        @csrf
    </form>

    <form
        method="POST"
        action="{{ route('profile.update') }}"
        class="profile-form"
    >
        @csrf
        @method('patch')

        {{-- Họ tên --}}
        <label class="profile-field" for="ho_ten">
            <span>Họ và tên thành viên</span>

            <div class="profile-input-wrap">
                <i class="fa-solid fa-user"></i>

                <input
                    id="ho_ten"
                    name="ho_ten"
                    type="text"
                    value="{{ old('ho_ten', $user->ho_ten) }}"
                    required
                    autofocus
                    autocomplete="name"
                >
            </div>

            @error('ho_ten')
                <small class="profile-error">{{ $message }}</small>
            @enderror
        </label>

        {{-- Ngày sinh --}}
        <label class="profile-field" for="ngay_sinh">
            <span>Ngày sinh nhận quà</span>

            <div class="profile-input-wrap">
                <i class="fa-solid fa-cake-candles"></i>

                <input
                    id="ngay_sinh"
                    name="ngay_sinh"
                    type="date"
                    value="{{ old(
                        'ngay_sinh',
                        $user->ngay_sinh
                            ? \Carbon\Carbon::parse($user->ngay_sinh)->format('Y-m-d')
                            : ''
                    ) }}"
                    max="{{ now()->format('Y-m-d') }}"
                    {{ $user->ngay_sinh ? 'disabled' : '' }}
                >
            </div>

            @if($user->ngay_sinh)
                <small class="profile-hint is-locked">
                    <i class="fa-solid fa-lock"></i>
                    Ngày sinh đã được khóa để bảo vệ quyền lợi voucher sinh nhật.
                </small>
            @else
                <small class="profile-hint">
                    Ngày sinh chỉ được thiết lập một lần.
                </small>
            @endif

            @error('ngay_sinh')
                <small class="profile-error">{{ $message }}</small>
            @enderror
        </label>

        {{-- Email --}}
        <label class="profile-field" for="email">
            <span>Địa chỉ email liên hệ</span>

            <div class="profile-input-wrap">
                <i class="fa-solid fa-envelope"></i>

                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email', $user->email) }}"
                    disabled
                    autocomplete="username"
                >
            </div>
        </label>

        {{-- Mã giới thiệu --}}
        <label class="profile-field" for="ma_gioi_thieu">
            <span>Mã giới thiệu</span>

            @if(!$thanhVien)
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

            @elseif($daLienKetGioiThieu)
                <div class="profile-input-wrap">
                    <i class="fa-solid fa-user-check"></i>

                    <input
                        id="ma_gioi_thieu"
                        type="text"
                        value="{{ $nguoiGioiThieu?->ma_gioi_thieu ?? 'Đã liên kết' }}"
                        disabled
                    >
                </div>

                <small class="profile-hint is-locked">
                    <i class="fa-solid fa-lock"></i>

                    Đã liên kết
                    @if($taiKhoanNguoiGioiThieu)
                        với {{ $taiKhoanNguoiGioiThieu->ho_ten }}.
                    @else
                        mã giới thiệu thành công.
                    @endif

                    Mã giới thiệu chỉ được nhập một lần.
                </small>

            @else
                <div class="profile-input-wrap">
                    <i class="fa-solid fa-user-plus"></i>

                    <input
                        id="ma_gioi_thieu"
                        name="ma_gioi_thieu"
                        type="text"
                        value="{{ old('ma_gioi_thieu') }}"
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
            @endif

            @error('ma_gioi_thieu')
                <small class="profile-error">{{ $message }}</small>
            @enderror
        </label>

        <div class="profile-actions">
            <button type="submit" class="profile-primary-btn">
                <i class="fa-solid fa-floppy-disk"></i>
                Lưu thay đổi
            </button>
        </div>
    </form>
</section>