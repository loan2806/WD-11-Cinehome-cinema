@php
    /*
    |--------------------------------------------------------------------------
    | AUTH MODAL
    |--------------------------------------------------------------------------
    */

    /*
    | Chỉ mở modal khi:
    | - Có lỗi validation
    | - Có session error
    */
    $shouldOpenAuthModal =
        $errors->any() ||
        session('error');

    /*
    | Tab mặc định.
    */
    $activeAuthTab = old(
        'auth_modal',
        'login'
    );

    /*
    |--------------------------------------------------------------------------
    | EMAIL CHƯA XÁC THỰC
    |--------------------------------------------------------------------------
    |
    | CHỈ lấy từ session('unverified_email').
    |
    | KHÔNG dùng old('email').
    |
    | old('email') chỉ là dữ liệu form cũ, không chứng minh tài khoản
    | chưa xác thực.
    |
    */

    $unverifiedEmail = session(
        'unverified_email'
    );
@endphp


<div
    id="authModal"
    class="{{ $shouldOpenAuthModal ? 'flex' : 'hidden' }} auth-modal-overlay"
    aria-modal="true"
    role="dialog"
>

    {{-- BACKDROP --}}
    <div
        id="authModalOverlay"
        class="auth-modal-backdrop"
    ></div>


    {{-- MODAL --}}
    <div
        id="authModalBox"
        class="auth-modal-box"
    >

        {{-- CLOSE --}}
        <button
            type="button"
            id="closeAuthModal"
            class="auth-close-btn"
            aria-label="Đóng"
        >
            <i class="fa-solid fa-xmark"></i>
        </button>


        {{-- =========================================================
             LEFT
        ========================================================== --}}

        <aside class="auth-modal-side">

            <span class="auth-side-kicker">
                CineHome Member
            </span>

            <h2>
                Đặt vé nhanh hơn, quản lý vé gọn hơn.
            </h2>

            <p>
                Lưu voucher, theo dõi vé đã mua và nhận ưu đãi
                dành riêng cho thành viên CineHome.
            </p>


            <div class="auth-benefit-list">

                <span>
                    <i class="fa-solid fa-ticket"></i>
                    Chọn ghế và thanh toán nhanh
                </span>

                <span>
                    <i class="fa-solid fa-gift"></i>
                    Lưu voucher khuyến mãi
                </span>

                <span>
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Xem lại lịch sử đặt vé
                </span>

            </div>

        </aside>


        {{-- =========================================================
             RIGHT
        ========================================================== --}}

        <section class="auth-modal-main">


            {{-- BRAND --}}
            <div class="auth-brand-block">

                <div class="auth-logo-mark cinehome-logo-sparkle">

                    <img
                        src="{{ asset('assets/images/LOGO copy.png') }}"
                        alt="CineHome"
                        class="cinehome-logo-img"
                    >

                </div>


                <div>

                    <h2>
                        Cine<span>Home</span>
                    </h2>

                    <p id="authModalSubtitle">

                        {{
                            $activeAuthTab === 'register'
                                ? 'Tạo tài khoản để nhận ưu đãi thành viên'
                                : 'Đăng nhập để đặt vé và quản lý vé của bạn'
                        }}

                    </p>

                </div>

            </div>


            {{-- =====================================================
                 TAB
            ====================================================== --}}

            <div
                class="auth-tab-switch"
                aria-label="Chọn biểu mẫu"
            >

                <button
                    type="button"
                    data-auth-tab="login"
                    class="auth-tab-btn {{
                        $activeAuthTab === 'login'
                            ? 'is-active'
                            : ''
                    }}"
                >
                    Đăng nhập
                </button>


                <button
                    type="button"
                    data-auth-tab="register"
                    class="auth-tab-btn {{
                        $activeAuthTab === 'register'
                            ? 'is-active'
                            : ''
                    }}"
                >
                    Đăng ký
                </button>

            </div>


            {{-- =====================================================
                 VALIDATION ERROR
            ====================================================== --}}

            @if ($errors->any())

                <div class="auth-alert">

                    @foreach ($errors->all() as $error)

                        @php

                            $translatedError = match (
                                trim($error)
                            ) {

                                'These credentials do not match our records.'
                                    => 'Tài khoản hoặc mật khẩu không chính xác.',

                                'The email has already been taken.'
                                    => 'Email này đã được sử dụng bởi một tài khoản khác.',

                                'The password field is required.'
                                    => 'Vui lòng nhập mật khẩu.',

                                'The email field is required.'
                                    => 'Vui lòng nhập địa chỉ email.',

                                'The ho ten field is required.'
                                    => 'Vui lòng nhập họ tên của bạn.',

                                'The mat khau confirmation does not match.'
                                    => 'Mật khẩu xác nhận không trùng khớp.',

                                'The password must be at least 8 characters.'
                                    => 'Mật khẩu phải chứa ít nhất 8 ký tự.',

                                default
                                    => $error
                            };

                        @endphp


                        <div>
                            {{ $translatedError }}
                        </div>

                    @endforeach

                </div>

            @endif


            {{-- =====================================================
                 SESSION ERROR
            ====================================================== --}}

            @if (session('error'))

                <div class="auth-alert">

                    @php

                        $sessionError =
                            session('error');

                        $translatedSessionError =
                            match (
                                trim($sessionError)
                            ) {

                                'These credentials do not match our records.'
                                    => 'Tài khoản hoặc mật khẩu không chính xác.',

                                default
                                    => $sessionError
                            };

                    @endphp


                    {{ $translatedSessionError }}

                </div>

            @endif


            {{-- =====================================================
                 NÚT GỬI LẠI EMAIL XÁC THỰC
            ====================================================== --}}

            @if ($unverifiedEmail)

                <form
                    method="POST"
                    action="{{ route('verification.resend-guest') }}"
                    class="auth-resend-verify-form"
                >

                    @csrf


                    <input
                        type="hidden"
                        name="email"
                        value="{{ $unverifiedEmail }}"
                    >


                    <button
                        type="submit"
                        class="auth-resend-verify-btn"
                    >

                        <i class="fa-solid fa-paper-plane"></i>

                        Gửi lại email xác thực tới
                        {{ $unverifiedEmail }}

                    </button>

                </form>

            @endif


            {{-- =====================================================
                 LOGIN FORM
            ====================================================== --}}

            <form
                id="loginForm"
                method="POST"
                action="{{ route('login') }}"
                class="auth-form auth-form-animate {{
                    $activeAuthTab === 'login'
                        ? ''
                        : 'hidden'
                }}"
            >

                @csrf


                <input
                    type="hidden"
                    name="auth_modal"
                    value="login"
                >


                {{-- EMAIL --}}
                <label class="auth-field">

                    <span>
                        Email
                    </span>


                    <div class="auth-input-wrap">

                        <i class="fa-solid fa-envelope"></i>


                        <input
                            type="email"
                            name="email"
                            value="{{
                                old('auth_modal') === 'login'
                                    ? old('email')
                                    : ''
                            }}"
                            required
                            placeholder="Nhập email của bạn"
                        >

                    </div>

                </label>


                {{-- PASSWORD --}}
                <label class="auth-field">

                    <span>
                        Mật khẩu
                    </span>


                    <div class="auth-input-wrap">

                        <i class="fa-solid fa-lock"></i>


                        <input
                            type="password"
                            name="mat_khau"
                            id="loginPassword"
                            required
                            placeholder="Nhập mật khẩu"
                        >


                        <button
                            type="button"
                            data-toggle-password="loginPassword"
                            class="auth-password-toggle"
                            aria-label="Hiện mật khẩu"
                        >

                            <i class="fa-solid fa-eye"></i>

                        </button>

                    </div>

                </label>


                {{-- LOGIN OPTIONS --}}
                <div class="auth-row">

                    <label class="auth-check">

                        <input
                            type="checkbox"
                            name="remember"
                        >

                        <span>
                            Ghi nhớ đăng nhập
                        </span>

                    </label>


                    @if (Route::has('password.request'))

                        <a
                            href="{{ route('password.request') }}"
                        >
                            Quên mật khẩu?
                        </a>

                    @endif

                </div>


                {{-- SUBMIT --}}
                <button
                    type="submit"
                    class="auth-submit-btn"
                >

                    Đăng nhập

                    <i class="fa-solid fa-arrow-right-to-bracket"></i>

                </button>

            </form>


            {{-- =====================================================
                 REGISTER FORM
            ====================================================== --}}

            <form
                id="registerForm"
                method="POST"
                action="{{ route('register') }}"
                class="auth-form auth-form-animate {{
                    $activeAuthTab === 'register'
                        ? ''
                        : 'hidden'
                }}"
            >

                @csrf


                <input
                    type="hidden"
                    name="auth_modal"
                    value="register"
                >


                {{-- HỌ TÊN --}}
                <label class="auth-field">

                    <span>
                        Họ tên
                    </span>


                    <div class="auth-input-wrap">

                        <i class="fa-solid fa-user"></i>


                        <input
                            type="text"
                            name="ho_ten"
                            value="{{
                                old('auth_modal') === 'register'
                                    ? old('ho_ten')
                                    : ''
                            }}"
                            required
                            placeholder="Nhập họ tên"
                        >

                    </div>

                </label>


                {{-- EMAIL --}}
                <label class="auth-field">

                    <span>
                        Email
                    </span>


                    <div class="auth-input-wrap">

                        <i class="fa-solid fa-envelope"></i>


                        <input
                            type="email"
                            name="email"
                            value="{{
                                old('auth_modal') === 'register'
                                    ? old('email')
                                    : ''
                            }}"
                            required
                            placeholder="Nhập email"
                        >

                    </div>

                </label>


                {{-- PASSWORD --}}
                <label class="auth-field">

                    <span>
                        Mật khẩu
                    </span>


                    <div class="auth-input-wrap">

                        <i class="fa-solid fa-lock"></i>


                        <input
                            type="password"
                            name="mat_khau"
                            id="registerPassword"
                            required
                            placeholder="Tạo mật khẩu"
                        >


                        <button
                            type="button"
                            data-toggle-password="registerPassword"
                            class="auth-password-toggle"
                            aria-label="Hiện mật khẩu"
                        >

                            <i class="fa-solid fa-eye"></i>

                        </button>

                    </div>

                </label>


                {{-- CONFIRM PASSWORD --}}
                <label class="auth-field">

                    <span>
                        Xác nhận mật khẩu
                    </span>


                    <div class="auth-input-wrap">

                        <i class="fa-solid fa-shield-halved"></i>


                        <input
                            type="password"
                            name="mat_khau_confirmation"
                            id="registerPasswordConfirm"
                            required
                            placeholder="Nhập lại mật khẩu"
                        >


                        <button
                            type="button"
                            data-toggle-password="registerPasswordConfirm"
                            class="auth-password-toggle"
                            aria-label="Hiện mật khẩu"
                        >

                            <i class="fa-solid fa-eye"></i>

                        </button>

                    </div>

                </label>


                {{-- REGISTER SUBMIT --}}
                <button
                    type="submit"
                    class="auth-submit-btn"
                >

                    Tạo tài khoản

                    <i class="fa-solid fa-user-plus"></i>

                </button>

            </form>

        </section>

    </div>

</div>