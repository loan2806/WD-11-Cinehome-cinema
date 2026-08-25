<section class="profile-section profile-danger-section">
    <header class="profile-section-head">
        <span>
            <i class="fa-solid fa-triangle-exclamation"></i>
        </span>
        <div>
            <h2>Vùng nguy hiểm</h2>
            <p>Tài khoản sẽ chuyển vào trạng thái chờ xóa trong 14 ngày. Nếu bạn đăng nhập lại trong thời gian này, tài khoản sẽ tự động khôi phục. Sau 14 ngày không đăng nhập, tài khoản và dữ liệu tích lũy sẽ bị xóa vĩnh viễn.</p>
        </div>
    </header>

    <details class="profile-danger-details" {{ $errors->userDeletion->isNotEmpty() ? 'open' : '' }}>
        <summary>
            <i class="fa-solid fa-user-xmark"></i>
            Mở xác nhận xóa tài khoản
        </summary>

        <form method="POST" action="{{ route('profile.destroy') }}" class="profile-form profile-danger-form">
            @csrf
            @method('delete')

            <p class="profile-warning-copy">
                Sau khi xác nhận xóa, bạn có <strong>14 ngày</strong> để đổi ý bằng cách đăng nhập lại vào hệ thống để khôi phục tài khoản. Hết 14 ngày, tài khoản sẽ bị xóa vĩnh viễn và không thể phục hồi. Vui lòng nhập mật khẩu hiện tại để xác nhận.
            </p>

            <label class="profile-field" for="mat_khau_xoa">
                <span>Mật khẩu xác nhận</span>
                <div class="profile-input-wrap is-danger">
                    <i class="fa-solid fa-lock"></i>
                    <input id="mat_khau_xoa" name="mat_khau" type="password" placeholder="Nhập mật khẩu hiện tại" required>
                    <button type="button" data-toggle-password="mat_khau_xoa" class="profile-password-toggle" aria-label="Hiện mật khẩu">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                @if($errors->userDeletion->has('mat_khau'))
                    <small class="profile-error">{{ $errors->userDeletion->first('mat_khau') }}</small>
                @endif
            </label>

            <div class="profile-actions">
                <button type="submit" class="profile-danger-btn">
                    <i class="fa-solid fa-trash-can"></i>
                    Xác nhận yêu cầu xóa tài khoản (Chờ 14 ngày)
                </button>
            </div>
        </form>
    </details>
</section>