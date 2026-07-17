<section class="profile-section">
    <header class="profile-section-head">
        <span>
            <i class="fa-solid fa-id-card"></i>
        </span>
        <div>
            <h2>Thông tin cá nhân</h2>
            <p>Chỉnh sửa tên hiển thị, email liên hệ và ngày sinh nhận ưu đãi.</p>
        </div>
    </header>

    <form id="send-verification" method="POST" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="POST" action="{{ route('profile.update') }}" class="profile-form">
        @csrf
        @method('patch')

        <label class="profile-field" for="ho_ten">
            <span>Họ và tên thành viên</span>
            <div class="profile-input-wrap">
                <i class="fa-solid fa-user"></i>
                <input id="ho_ten" name="ho_ten" type="text" value="{{ old('ho_ten', $user->ho_ten) }}" required autofocus autocomplete="name">
            </div>
            @if($errors->has('ho_ten'))
                <small class="profile-error">{{ $errors->first('ho_ten') }}</small>
            @endif
        </label>

        <label class="profile-field" for="ngay_sinh">
            <span>Ngày sinh nhận quà</span>
            <div class="profile-input-wrap">
                <i class="fa-solid fa-cake-candles"></i>
                <input
                    id="ngay_sinh"
                    name="ngay_sinh"
                    type="date"
                    value="{{ old('ngay_sinh', $user->ngay_sinh ? \Carbon\Carbon::parse($user->ngay_sinh)->format('Y-m-d') : '') }}"
                    {{ $user->ngay_sinh ? 'disabled' : '' }}
                >
            </div>

            @if($user->ngay_sinh)
                <small class="profile-hint is-locked">
                    <i class="fa-solid fa-lock"></i>
                    Ngày sinh đã được khóa để bảo vệ quyền lợi voucher sinh nhật.
                </small>
            @else
                <small class="profile-hint">Ngày sinh chỉ được thiết lập một lần.</small>
            @endif

            @if($errors->has('ngay_sinh'))
                <small class="profile-error">{{ $errors->first('ngay_sinh') }}</small>
            @endif
        </label>

        <label class="profile-field" for="email">
            <span>Địa chỉ email liên hệ</span>
            <div class="profile-input-wrap">
                <i class="fa-solid fa-envelope"></i>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
            </div>
            @if($errors->has('email'))
                <small class="profile-error">{{ $errors->first('email') }}</small>
            @endif
        </label>

        <div class="profile-actions">
            <button type="submit" class="profile-primary-btn">
                <i class="fa-solid fa-floppy-disk"></i>
                Lưu thay đổi
            </button>
        </div>
    </form>
</section>
