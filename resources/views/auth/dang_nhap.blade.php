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
                    <span class="auth-side-kicker">Thành viên CineHome</span>
                    <h1>Chào mừng bạn quay lại.</h1>
                    <p>Đăng nhập để tiếp tục đặt vé, quản lý vé đã mua và lưu các voucher đang có.</p>
                </div>

                <div class="auth-page-benefits">
                    <span><i class="fa-solid fa-couch"></i> Chọn ghế yêu thích</span>
                    <span><i class="fa-solid fa-qrcode"></i> Nhận vé điện tử nhanh</span>
                    <span><i class="fa-solid fa-gift"></i> Dùng voucher thành viên</span>
                </div>
            </aside>

            <section class="auth-page-card">
                <div class="auth-brand-block">
                    <div class="auth-logo-mark cinehome-logo-sparkle">
                        <img src="{{ asset('assets/images/LOGO copy.png') }}" alt="CineHome" class="cinehome-logo-img">
                    </div>
                    <div>
                        <h2>Cine<span>Home</span></h2>
                        <p>Đăng nhập để đặt vé và quản lý tài khoản</p>
                    </div>
                </div>

                <div class="auth-tab-switch">
                    <a href="{{ route('login') }}" class="auth-tab-btn is-active">Đăng nhập</a>
                    <a href="{{ route('register') }}" class="auth-tab-btn">Đăng ký</a>
                </div>

                <x-auth-session-status class="auth-alert is-success" :status="session('status')" />

                @if (session('error'))
                    <div class="auth-alert">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="auth-form auth-form-animate">
                    @csrf

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
                                autofocus
                                placeholder="Nhập email của bạn"
                            >
                        </div>
                        @if ($errors->has('email'))
                            <small class="auth-error">{{ $errors->first('email') }}</small>
                        @endif
                    </label>

                    <label class="auth-field">
                        <span>Mật khẩu</span>
                        <div class="auth-input-wrap">
                            <i class="fa-solid fa-lock"></i>
                            <input
                                id="loginPagePassword"
                                type="password"
                                name="mat_khau"
                                required
                                placeholder="Nhập mật khẩu"
                            >
                            <button type="button" data-toggle-password="loginPagePassword" class="auth-password-toggle" aria-label="Hiện mật khẩu">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        @if ($errors->has('mat_khau'))
                            <small class="auth-error">{{ $errors->first('mat_khau') }}</small>
                        @endif
                    </label>

                    <div class="auth-row">
                        <label class="auth-check">
                            <input id="remember_me" type="checkbox" name="remember">
                            <span>Ghi nhớ đăng nhập</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}">Quên mật khẩu?</a>
                        @endif
                    </div>

                    <button type="submit" class="auth-submit-btn">
                        Đăng nhập
                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    </button>
                </form>
            </section>
        </section>
    </main>
</x-guest-layout>
