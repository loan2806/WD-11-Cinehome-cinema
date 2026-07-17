<x-guest-layout>
    @php
        $heThongSettings = \App\Models\CaiDatHeThong::first();
        $urlAnhNen = ($heThongSettings && $heThongSettings->anh_nen_login)
            ? asset('storage/' . $heThongSettings->anh_nen_login)
            : 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=1920&auto=format&fit=crop';
    @endphp

    <main class="auth-page" style="--auth-bg: url('{{ $urlAnhNen }}')" lang="vi" spellcheck="false">
        <section class="auth-page-shell">
            <aside class="auth-page-visual">
                <a href="{{ route('home') }}" class="auth-page-brand">
                    <span class="cinehome-logo-sparkle">
                        <img src="{{ asset('assets/images/LOGO copy.png') }}" alt="CineHome" class="cinehome-logo-img">
                    </span>
                    Cine<span>Home</span>
                </a>

                <div>
                    <span class="auth-side-kicker">Gia nhập CineHome</span>
                    <h1>Tạo tài khoản để xem phim tiện hơn.</h1>
                    <p>Nhận điểm chào mừng, lưu ưu đãi và đặt vé nhanh trong các lần xem phim tiếp theo.</p>
                </div>

                <div class="auth-page-benefits">
                    <span><i class="fa-solid fa-star"></i> Tặng điểm thành viên mới</span>
                    <span><i class="fa-solid fa-ticket"></i> Đặt vé chỉ trong vài bước</span>
                    <span><i class="fa-solid fa-bell"></i> Cập nhật ưu đãi mới</span>
                </div>
            </aside>

            <section class="auth-page-card">
                <div class="auth-brand-block">
                    <div class="auth-logo-mark cinehome-logo-sparkle">
                        <img src="{{ asset('assets/images/LOGO copy.png') }}" alt="CineHome" class="cinehome-logo-img">
                    </div>
                    <div>
                        <h2>Cine<span>Home</span></h2>
                        <p>Tạo tài khoản thành viên CineHome</p>
                    </div>
                </div>

                <div class="auth-tab-switch">
                    <a href="{{ route('login') }}" class="auth-tab-btn">Đăng nhập</a>
                    <a href="{{ route('register') }}" class="auth-tab-btn is-active">Đăng ký</a>
                </div>

                @if ($errors->any())
                    <div class="auth-alert">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="auth-form auth-form-animate">
                    @csrf

                    <label class="auth-field">
                        <span>Họ và tên</span>
                        <div class="auth-input-wrap">
                            <i class="fa-solid fa-user"></i>
                            <input
                                id="ho_ten"
                                type="text"
                                name="ho_ten"
                                value="{{ old('ho_ten') }}"
                                required
                                autofocus
                                placeholder="Nhập họ tên"
                            >
                        </div>
                    </label>

                    <label class="auth-field">
                        <span>Email</span>
                        <div class="auth-input-wrap">
                            <i class="fa-solid fa-envelope"></i>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                placeholder="Nhập email"
                            >
                        </div>
                    </label>

                    <label class="auth-field">
                        <span>Mật khẩu</span>
                        <div class="auth-input-wrap">
                            <i class="fa-solid fa-lock"></i>
                            <input
                                id="registerPagePassword"
                                type="password"
                                name="mat_khau"
                                required
                                placeholder="Tạo mật khẩu"
                            >
                            <button type="button" data-toggle-password="registerPagePassword" class="auth-password-toggle" aria-label="Hiện mật khẩu">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </label>

                    <label class="auth-field">
                        <span>Xác nhận mật khẩu</span>
                        <div class="auth-input-wrap">
                            <i class="fa-solid fa-shield-halved"></i>
                            <input
                                id="registerPagePasswordConfirm"
                                type="password"
                                name="mat_khau_confirmation"
                                required
                                placeholder="Nhập lại mật khẩu"
                            >
                            <button type="button" data-toggle-password="registerPagePasswordConfirm" class="auth-password-toggle" aria-label="Hiện mật khẩu">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </label>

                    <button type="submit" class="auth-submit-btn">
                        Tạo tài khoản
                        <i class="fa-solid fa-user-plus"></i>
                    </button>
                </form>
            </section>
        </section>
    </main>
</x-guest-layout>
