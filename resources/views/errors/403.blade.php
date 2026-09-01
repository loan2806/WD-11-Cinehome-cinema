<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Liên kết không hợp lệ - CineHome</title>

    <meta name="robots" content="noindex, nofollow">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <style>
        :root {
            --bg-main: #080a0f;
            --bg-card: #111318;
            --bg-card-2: #15171d;

            --text-main: #ffffff;
            --text-muted: #9ca3af;
            --text-soft: #cbd5e1;

            --red: #ef233c;
            --red-dark: #c91832;

            --gold: #e2a12b;
            --gold-light: #f5bd4f;

            --border: rgba(255, 255, 255, 0.09);
            --border-gold: rgba(226, 161, 43, 0.30);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            min-height: 100%;
        }

        body {
            font-family: "Be Vietnam Pro", Arial, sans-serif;
            background:
                radial-gradient(
                    circle at 50% 20%,
                    rgba(239, 35, 60, 0.10),
                    transparent 35%
                ),
                radial-gradient(
                    circle at 85% 80%,
                    rgba(226, 161, 43, 0.06),
                    transparent 30%
                ),
                var(--bg-main);

            color: var(--text-main);
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 30px 20px;

            overflow-x: hidden;
        }

        /* ==============================
           BACKGROUND
        ============================== */

        .background-glow {
            position: fixed;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
            z-index: 0;
        }

        .glow {
            position: absolute;
            border-radius: 999px;
            filter: blur(100px);
            opacity: 0.16;
        }

        .glow-red {
            width: 350px;
            height: 350px;
            background: var(--red);
            top: -180px;
            left: -120px;
        }

        .glow-gold {
            width: 300px;
            height: 300px;
            background: var(--gold);
            right: -120px;
            bottom: -120px;
            opacity: 0.08;
        }

        /* ==============================
           MAIN
        ============================== */

        .error-wrapper {
            width: 100%;
            max-width: 760px;
            position: relative;
            z-index: 2;
        }

        /* ==============================
           BRAND
        ============================== */

        .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;

            margin-bottom: 25px;

            color: #fff;
            text-decoration: none;
        }

        .brand-icon {
            width: 44px;
            height: 44px;

            border-radius: 13px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: linear-gradient(
                145deg,
                var(--red),
                var(--red-dark)
            );

            box-shadow:
                0 10px 30px rgba(239, 35, 60, 0.25);

            font-size: 20px;
        }

        .brand-name {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .brand-name span {
            color: var(--gold-light);
        }

        /* ==============================
           CARD
        ============================== */

        .error-card {
            position: relative;

            background:
                linear-gradient(
                    145deg,
                    rgba(20, 22, 28, 0.98),
                    rgba(10, 12, 17, 0.98)
                );

            border: 1px solid var(--border);

            border-radius: 24px;

            padding: 55px 50px 45px;

            text-align: center;

            box-shadow:
                0 25px 80px rgba(0, 0, 0, 0.45),
                inset 0 1px 0 rgba(255, 255, 255, 0.025);

            overflow: hidden;
        }

        .error-card::before {
            content: "";
            position: absolute;

            top: 0;
            left: 0;
            right: 0;

            height: 3px;

            background: linear-gradient(
                90deg,
                transparent,
                var(--red),
                var(--gold),
                var(--red),
                transparent
            );
        }

        /* ==============================
           ICON
        ============================== */

        .icon-wrapper {
            width: 100px;
            height: 100px;

            margin: 0 auto 25px;

            border-radius: 50%;

            display: flex;
            align-items: center;
            justify-content: center;

            background:
                radial-gradient(
                    circle,
                    rgba(239, 35, 60, 0.18),
                    rgba(239, 35, 60, 0.05) 65%,
                    transparent 70%
                );

            border: 1px solid rgba(239, 35, 60, 0.25);

            box-shadow:
                0 0 0 10px rgba(239, 35, 60, 0.025),
                0 0 50px rgba(239, 35, 60, 0.08);

            position: relative;
        }

        .icon-wrapper::after {
            content: "";

            position: absolute;
            inset: -9px;

            border-radius: 50%;

            border: 1px dashed rgba(226, 161, 43, 0.22);

            animation: rotate 15s linear infinite;
        }

        .icon-wrapper i {
            color: var(--red);
            font-size: 38px;

            filter:
                drop-shadow(
                    0 0 12px rgba(239, 35, 60, 0.35)
                );
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* ==============================
           ERROR CODE
        ============================== */

        .error-code {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            margin-bottom: 15px;

            padding: 7px 14px;

            border-radius: 999px;

            background: rgba(239, 35, 60, 0.08);

            border: 1px solid rgba(239, 35, 60, 0.18);

            color: #ff6b7a;

            font-size: 13px;
            font-weight: 700;

            letter-spacing: 0.4px;
        }

        .error-code i {
            font-size: 12px;
        }

        /* ==============================
           TITLE
        ============================== */

        .title {
            font-size: clamp(28px, 5vw, 40px);

            line-height: 1.25;

            font-weight: 800;

            letter-spacing: -1px;

            margin-bottom: 15px;

            color: #ffffff;
        }

        .title span {
            color: var(--gold-light);
        }

        /* ==============================
           DESCRIPTION
        ============================== */

        .description {
            max-width: 570px;

            margin: 0 auto;

            color: var(--text-muted);

            font-size: 15px;

            line-height: 1.8;
        }

        .description strong {
            color: #e5e7eb;
            font-weight: 600;
        }

        /* ==============================
           NOTICE
        ============================== */

        .notice {
            max-width: 570px;

            margin: 28px auto 0;

            padding: 17px 20px;

            display: flex;
            align-items: flex-start;
            gap: 13px;

            text-align: left;

            background: rgba(226, 161, 43, 0.055);

            border: 1px solid var(--border-gold);

            border-radius: 14px;
        }

        .notice-icon {
            width: 34px;
            height: 34px;

            flex: 0 0 34px;

            border-radius: 10px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: rgba(226, 161, 43, 0.12);

            color: var(--gold-light);

            font-size: 15px;
        }

        .notice-content {
            flex: 1;
        }

        .notice-title {
            color: #f3c55f;

            font-size: 13px;

            font-weight: 700;

            margin-bottom: 3px;
        }

        .notice-text {
            color: #aeb4bf;

            font-size: 12px;

            line-height: 1.6;
        }

        /* ==============================
           BUTTONS
        ============================== */

        .actions {
            margin-top: 32px;

            display: flex;

            align-items: center;
            justify-content: center;

            gap: 12px;

            flex-wrap: wrap;
        }

        .btn {
            min-height: 48px;

            padding: 0 21px;

            border-radius: 12px;

            display: inline-flex;

            align-items: center;
            justify-content: center;

            gap: 9px;

            text-decoration: none;

            border: none;

            font-family: inherit;

            font-size: 14px;

            font-weight: 700;

            cursor: pointer;

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease,
                border-color 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            color: #111318;

            background: linear-gradient(
                135deg,
                var(--gold-light),
                var(--gold)
            );

            box-shadow:
                0 10px 25px rgba(226, 161, 43, 0.16);
        }

        .btn-primary:hover {
            box-shadow:
                0 14px 30px rgba(226, 161, 43, 0.25);
        }

        .btn-secondary {
            color: #e5e7eb;

            background: rgba(255, 255, 255, 0.045);

            border: 1px solid rgba(255, 255, 255, 0.10);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.075);

            border-color: rgba(255, 255, 255, 0.16);
        }

        /* ==============================
           DIVIDER
        ============================== */

        .divider {
            display: flex;
            align-items: center;

            gap: 13px;

            max-width: 450px;

            margin: 35px auto 0;

            color: #555b66;

            font-size: 11px;

            text-transform: uppercase;

            letter-spacing: 1px;
        }

        .divider::before,
        .divider::after {
            content: "";

            height: 1px;

            flex: 1;

            background: rgba(255, 255, 255, 0.07);
        }

        /* ==============================
           FOOTER
        ============================== */

        .footer {
            text-align: center;

            margin-top: 22px;

            color: #5f6570;

            font-size: 12px;
        }

        .footer strong {
            color: #858b96;
        }

        /* ==============================
           GENERIC ERROR
        ============================== */

        .generic-message {
            margin-top: 20px;

            color: #9ca3af;

            font-size: 13px;

            line-height: 1.7;
        }

        /* ==============================
           RESPONSIVE
        ============================== */

        @media (max-width: 640px) {

            body {
                padding: 20px 14px;
            }

            .error-card {
                padding: 40px 22px 32px;

                border-radius: 20px;
            }

            .brand {
                margin-bottom: 20px;
            }

            .brand-name {
                font-size: 21px;
            }

            .brand-icon {
                width: 40px;
                height: 40px;

                border-radius: 11px;
            }

            .icon-wrapper {
                width: 85px;
                height: 85px;
            }

            .icon-wrapper i {
                font-size: 32px;
            }

            .title {
                font-size: 28px;

                letter-spacing: -0.5px;
            }

            .description {
                font-size: 14px;
            }

            .notice {
                padding: 14px;
            }

            .actions {
                flex-direction: column;

                width: 100%;
            }

            .btn {
                width: 100%;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation: none !important;

                transition: none !important;
            }
        }
    </style>
</head>

<body>

    <div class="background-glow">
        <div class="glow glow-red"></div>
        <div class="glow glow-gold"></div>
    </div>


    <main class="error-wrapper">

        {{-- Logo CineHome --}}
        <a href="{{ url('/') }}" class="brand">

            <div class="brand-icon">
                <i class="fa-solid fa-film"></i>
            </div>

            <div class="brand-name">
                Cine<span>Home</span>
            </div>

        </a>


        <section class="error-card">

            {{-- Icon --}}
            <div class="icon-wrapper">
                <i class="fa-solid fa-shield-exclamation"></i>
            </div>


            {{-- Mã lỗi --}}
            <div class="error-code">
                <i class="fa-solid fa-circle-exclamation"></i>
                LỖI 403
            </div>


            {{-- Tiêu đề --}}
            <h1 class="title">
                Liên kết <span>đã hết hạn</span>
            </h1>


            {{-- Nội dung --}}
            <p class="description">
                Liên kết xác thực email của bạn đã
                <strong>hết hạn hoặc không còn hợp lệ</strong>.
                Vì lý do bảo mật, liên kết này không thể được sử dụng để xác thực tài khoản.
            </p>


            {{-- Thông báo --}}
            <div class="notice">

                <div class="notice-icon">
                    <i class="fa-solid fa-envelope"></i>
                </div>

                <div class="notice-content">

                    <div class="notice-title">
                        Bạn vẫn có thể xác thực tài khoản
                    </div>

                    <div class="notice-text">
                        Hãy quay lại trang đăng nhập và yêu cầu
                        <strong>gửi lại email xác thực</strong>.
                        Liên kết mới sẽ có hiệu lực trong 60 phút.
                    </div>

                </div>

            </div>


            {{-- Nút --}}
            <div class="actions">

                <a
                    href="{{ route('login') }}"
                    class="btn btn-primary"
                >
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>

                    Quay lại đăng nhập
                </a>


                <a
                    href="{{ url('/') }}"
                    class="btn btn-secondary"
                >
                    <i class="fa-solid fa-house"></i>

                    Về trang chủ
                </a>

            </div>


            <div class="divider">
                CineHome
            </div>


            <p class="generic-message">
                Nếu bạn cho rằng đây là lỗi, vui lòng thử yêu cầu
                một liên kết xác thực mới.
            </p>

        </section>


        <div class="footer">
            © {{ date('Y') }}
            <strong>CineHome</strong>.
            Hệ thống đặt vé xem phim.
        </div>

    </main>

</body>
</html>