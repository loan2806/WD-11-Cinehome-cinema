<x-profile-layout>
    @php
        $displayName = $user->ho_ten ?? 'Thành viên CineHome';
        $initial = mb_substr($displayName, 0, 1);
        $roleLabel = $user->vai_tro === 'admin'
            ? 'Quản trị viên'
            : ($user->vai_tro === 'nhan_vien' ? 'Nhân viên rạp' : 'Thành viên CineHome');
        $birthDate = $user->ngay_sinh ? \Carbon\Carbon::parse($user->ngay_sinh)->format('d/m/Y') : 'Chưa thiết lập';
        $joinedAt = $user->created_at ? $user->created_at->format('d/m/Y') : 'Đang cập nhật';
    @endphp

    <section class="profile-page">
        <div class="profile-hero">
            <div class="profile-hero-copy">
                <span class="profile-eyebrow">
                    <i class="fa-solid fa-id-card-clip"></i>
                    Tài khoản CineHome
                </span>
                <h1>Quản lý hồ sơ cá nhân của bạn.</h1>
                <p>Cập nhật thông tin liên hệ, đổi mật khẩu bảo mật và kiểm soát tài khoản tại một nơi gọn gàng.</p>
            </div>

            <div class="profile-hero-card">
                <div class="profile-avatar-lg">{{ $initial }}</div>
                <div>
                    <strong>{{ $displayName }}</strong>
                    <span>{{ $roleLabel }}</span>
                    <small>{{ $user->email }}</small>
                </div>
            </div>
        </div>

        <div class="profile-grid">
            <aside class="profile-summary-card">
                <div class="profile-summary-head">
                    <div class="profile-avatar-xl">{{ $initial }}</div>
                    <h2>{{ $displayName }}</h2>
                    <p>{{ $roleLabel }}</p>
                </div>

                <div class="profile-summary-list">
                    <div>
                        <i class="fa-solid fa-envelope"></i>
                        <span>Email</span>
                        <strong>{{ $user->email }}</strong>
                    </div>
                    <div>
                        <i class="fa-solid fa-cake-candles"></i>
                        <span>Ngày sinh</span>
                        <strong>{{ $birthDate }}</strong>
                    </div>
                    <div>
                        <i class="fa-solid fa-calendar-check"></i>
                        <span>Tham gia</span>
                        <strong>{{ $joinedAt }}</strong>
                    </div>
                </div>

                <a href="{{ route('user.ve_xem_phim.index') }}" class="profile-summary-link">
                    <i class="fa-solid fa-ticket"></i>
                    Vé đã đặt
                </a>
            </aside>

            <div class="profile-content">
                @if (session('status') === 'profile-updated')
                    <div class="profile-toast is-success">
                        <i class="fa-solid fa-circle-check"></i>
                        Hồ sơ đã được cập nhật thành công.
                    </div>
                @endif

                @if (session('status') === 'password-updated')
                    <div class="profile-toast is-success">
                        <i class="fa-solid fa-shield-halved"></i>
                        Mật khẩu đã được thay đổi an toàn.
                    </div>
                @endif

                <div class="profile-card">
                    @include('profile.partials.cap_nhat_thong_tin_form')
                </div>

                <div class="profile-card">
                    @include('profile.partials.doi_mat_khau_form')
                </div>

                <div class="profile-card profile-card-danger">
                    @include('profile.partials.xoa_tai_khoan_form')
                </div>
            </div>
        </div>
    </section>
</x-profile-layout>
