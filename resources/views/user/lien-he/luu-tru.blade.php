@extends('layouts.user')

@section('title', 'Liên hệ của tôi')

@section('content')

<style>
    .contact-page {
        min-height: 100vh;
        padding: 112px 0 70px;
    }

    .contact-shell {
        width: min(1180px, calc(100% - 32px));
        margin: 0 auto;
    }

    /* =========================
       HERO
    ========================= */

    .contact-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 285px;
        gap: 18px;
        margin-bottom: 20px;
    }

    .contact-hero-main,
    .contact-hero-side {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, .12);
        border-radius: 22px;
        background:
            radial-gradient(circle at 0% 0%,
                rgba(239, 68, 68, .28),
                transparent 40%),
            linear-gradient(135deg,
                #42121d 0%,
                #151921 48%,
                #10141b 100%);
    }

    .contact-hero-main {
        min-height: 305px;
        padding: 38px 36px;
    }

    .contact-hero-main::after {
        content: "";
        position: absolute;
        right: -100px;
        bottom: -130px;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        background: rgba(239, 68, 68, .08);
        pointer-events: none;
    }

    .contact-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 13px;
        border-radius: 999px;
        border: 1px solid rgba(250, 204, 21, .3);
        background: rgba(250, 204, 21, .08);
        color: #facc15;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .8px;
        text-transform: uppercase;
    }

    .contact-hero-main h1 {
        position: relative;
        z-index: 1;
        margin: 18px 0 10px;
        color: #fff;
        font-size: clamp(38px, 5vw, 58px);
        line-height: 1;
        font-weight: 900;
        letter-spacing: -1.5px;
    }

    .contact-hero-main>p {
        position: relative;
        z-index: 1;
        max-width: 700px;
        margin: 0;
        color: #cbd5e1;
        font-size: 14px;
        line-height: 1.7;
    }

    .contact-hero-actions {
        position: relative;
        z-index: 2;
        display: flex;
        gap: 10px;
        margin-top: 24px;
        flex-wrap: wrap;
    }

    .contact-primary-btn,
    .contact-secondary-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        padding: 0 18px;
        border-radius: 12px;
        text-decoration: none !important;
        font-size: 13px;
        font-weight: 800;
        transition: .25s ease;
    }

    .contact-primary-btn {
        color: #fff !important;
        background: linear-gradient(135deg, #ff3b30, #ef4444);
        box-shadow: 0 10px 25px rgba(239, 68, 68, .25);
    }

    .contact-primary-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(239, 68, 68, .35);
    }

    .contact-secondary-btn {
        color: #e2e8f0 !important;
        border: 1px solid rgba(255, 255, 255, .14);
        background: rgba(255, 255, 255, .06);
    }

    .contact-secondary-btn:hover {
        color: #facc15 !important;
        border-color: rgba(250, 204, 21, .5);
        background: rgba(250, 204, 21, .08);
    }

    /* =========================
       HERO SIDE
    ========================= */

    .contact-hero-side {
        padding: 38px 22px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background:
            radial-gradient(circle at 100% 0%,
                rgba(250, 204, 21, .09),
                transparent 45%),
            #13171e;
    }

    .contact-hero-side>span {
        color: #94a3b8;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .6px;
    }

    .contact-side-icon {
        width: 42px;
        height: 42px;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        color: #facc15;
        background: rgba(250, 204, 21, .1);
        border: 1px solid rgba(250, 204, 21, .18);
        font-size: 18px;
    }

    .contact-hero-side h3 {
        margin: 12px 0 7px;
        color: #fff;
        font-size: 22px;
        line-height: 1.2;
        font-weight: 900;
    }

    .contact-hero-side p {
        margin: 0;
        color: #facc15;
        font-size: 12px;
        line-height: 1.6;
        font-weight: 700;
    }

    .contact-side-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-top: 18px;
        color: #cbd5e1;
        font-size: 12px;
        font-weight: 700;
    }

    /* Chờ xử lý - ĐỎ */
    .contact-status.waiting {
        color: #f87171;
        background: rgba(239, 68, 68, .1);
        border: 1px solid rgba(239, 68, 68, .2);
    }

    /* Đang xử lý - VÀNG */
    .contact-status.processing {
        color: #facc15;
        background: rgba(250, 204, 21, .1);
        border: 1px solid rgba(250, 204, 21, .2);
    }

    /* Đã phản hồi - XANH */
    .contact-status.replied {
        color: #4ade80;
        background: rgba(34, 197, 94, .1);
        border: 1px solid rgba(34, 197, 94, .2);
    }

    /* Đã đóng - XÁM */
    .contact-status.closed {
        color: #94a3b8;
        background: rgba(148, 163, 184, .1);
        border: 1px solid rgba(148, 163, 184, .15);
    }

    .contact-side-status i {
        color: #22c55e;
    }

    /* =========================
       STATS
    ========================= */

    .contact-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 20px;
    }

    .contact-stat {
        min-height: 112px;
        padding: 18px 16px;
        border: 1px solid rgba(255, 255, 255, .1);
        border-radius: 17px;
        background: #13171f;
    }

    .contact-stat span {
        display: block;
        margin-bottom: 12px;
        color: #94a3b8;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .contact-stat strong {
        display: block;
        color: #fff;
        font-size: 28px;
        line-height: 1;
        font-weight: 900;
    }

    .contact-stat small {
        display: block;
        margin-top: 8px;
        color: #facc15;
        font-size: 11px;
        font-weight: 700;
    }

    /* =========================
       CONTENT
    ========================= */

    .contact-board {
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, .1);
        border-radius: 20px;
        background: #12161e;
    }

    .contact-board-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 24px 20px 18px;
        border-bottom: 1px solid rgba(255, 255, 255, .07);
    }

    .contact-board-head>div span {
        display: block;
        margin-bottom: 6px;
        color: #facc15;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .6px;
    }

    .contact-board-head h2 {
        margin: 0;
        color: #fff;
        font-size: 24px;
        font-weight: 900;
    }

    .contact-count {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 13px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .06);
        border: 1px solid rgba(255, 255, 255, .1);
        color: #cbd5e1;
        font-size: 12px;
        font-weight: 800;
    }

    /* =========================
       CONTACT CARDS
    ========================= */

    .contact-list {
        padding: 16px;
        display: grid;
        gap: 12px;
    }

    .contact-card {
        display: grid;
        grid-template-columns: 52px minmax(0, 1fr) auto;
        align-items: center;
        gap: 15px;
        padding: 18px;
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 15px;
        background: rgba(255, 255, 255, .025);
        transition: .25s ease;
    }

    .contact-card:hover {
        border-color: rgba(250, 204, 21, .28);
        background: rgba(255, 255, 255, .045);
        transform: translateY(-1px);
    }

    .contact-card-icon {
        width: 52px;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: rgba(239, 68, 68, .1);
        border: 1px solid rgba(239, 68, 68, .18);
        color: #f87171;
        font-size: 19px;
    }

    .contact-card-content {
        min-width: 0;
    }

    .contact-card-top {
        display: flex;
        align-items: center;
        gap: 9px;
        flex-wrap: wrap;
        margin-bottom: 7px;
    }

    .contact-card-top h3 {
        margin: 0;
        color: #f8fafc;
        font-size: 15px;
        font-weight: 800;
    }

    .contact-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 8px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 800;
    }

    .contact-status.pending {
        color: #facc15;
        background: rgba(250, 204, 21, .1);
        border: 1px solid rgba(250, 204, 21, .18);
    }

    .contact-status.replied {
        color: #4ade80;
        background: rgba(34, 197, 94, .1);
        border: 1px solid rgba(34, 197, 94, .18);
    }

    .contact-status.closed {
        color: #94a3b8;
        background: rgba(148, 163, 184, .1);
        border: 1px solid rgba(148, 163, 184, .15);
    }

    .contact-card-content p {
        margin: 0;
        color: #94a3b8;
        font-size: 12px;
        line-height: 1.6;
    }

    .contact-card-meta {
        display: flex;
        gap: 14px;
        margin-top: 7px;
        color: #64748b;
        font-size: 11px;
    }

    .contact-card-meta span {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .contact-card-action a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 38px;
        padding: 0 14px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, .12);
        background: rgba(255, 255, 255, .04);
        color: #e2e8f0;
        text-decoration: none;
        font-size: 12px;
        font-weight: 800;
        transition: .2s ease;
    }

    .contact-card-action a:hover {
        color: #facc15;
        border-color: #facc15;
        background: rgba(250, 204, 21, .08);
    }

    /* =========================
       EMPTY
    ========================= */

    .contact-empty {
        padding: 70px 20px;
        text-align: center;
    }

    .contact-empty-icon {
        width: 70px;
        height: 70px;
        margin: 0 auto 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 18px;
        color: #facc15;
        background: rgba(250, 204, 21, .08);
        border: 1px solid rgba(250, 204, 21, .15);
        font-size: 27px;
    }

    .contact-empty h3 {
        margin: 0 0 8px;
        color: #fff;
        font-size: 19px;
        font-weight: 900;
    }

    .contact-empty p {
        max-width: 430px;
        margin: 0 auto 20px;
        color: #94a3b8;
        font-size: 13px;
        line-height: 1.6;
    }

    /* =========================
       RESPONSIVE
    ========================= */

    @media (max-width: 850px) {
        .contact-hero {
            grid-template-columns: 1fr;
        }

        .contact-stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 600px) {
        .contact-page {
            padding-top: 25px;
        }

        .contact-hero-main {
            padding: 28px 22px;
        }

        .contact-hero-main h1 {
            font-size: 38px;
        }

        .contact-stats {
            grid-template-columns: 1fr 1fr;
        }

        .contact-card {
            grid-template-columns: 44px minmax(0, 1fr);
        }

        .contact-card-icon {
            width: 44px;
            height: 44px;
        }

        .contact-card-action {
            grid-column: 2;
        }

        .contact-card-action a {
            width: 100%;
        }

        .contact-board-head {
            align-items: flex-start;
        }
    }
</style>

@php
/*
* Nếu controller đã truyền $lienHes và $contactStats
* thì dùng trực tiếp.
*/
$contacts = $lienHes ?? collect();

$totalContacts = $contactStats['total'] ?? $contacts->count();
$pendingContacts = $contactStats['pending'] ?? 0;
$repliedContacts = $contactStats['replied'] ?? 0;
$closedContacts = $contactStats['closed'] ?? 0;

$latestContact = $contacts->first();
@endphp

<section class="contact-page">
    <div class="contact-shell">

        {{-- ================= HERO ================= --}}
        <div class="contact-hero">

            <div class="contact-hero-main">

                <span class="contact-eyebrow">
                    <i class="fa-solid fa-headset"></i>
                    MY CINEHOME CONTACTS
                </span>

                <h1>Liên hệ của tôi</h1>

                <p>
                    Theo dõi các yêu cầu liên hệ, phản hồi từ CineHome
                    và lịch sử hỗ trợ của bạn tại đây.
                </p>

                <div class="contact-hero-actions">

                    <a
                        href="{{ route('user.lien-he.index') }}"
                        class="contact-primary-btn">
                        <i class="fa-solid fa-plus"></i>
                        Gửi liên hệ mới
                    </a>

                    <a
                        href="{{ route('dat_ve.chon_phim') }}"
                        class="contact-secondary-btn">
                        <i class="fa-solid fa-ticket"></i>
                        Đặt vé
                    </a>

                </div>

            </div>

            {{-- Ô bên phải --}}
            <aside class="contact-hero-side">

                <div class="contact-side-icon">
                    <i class="fa-solid fa-comments"></i>
                </div>

                <span>LIÊN HỆ GẦN NHẤT</span>

                @if($latestContact)

                <h3>
                    {{ $latestContact->tieu_de ?? 'Yêu cầu hỗ trợ' }}
                </h3>

                <p>
                    {{ \Illuminate\Support\Str::limit(
                            $latestContact->noi_dung ?? '',
                            75
                        ) }}
                </p>

                <div class="contact-side-status">
                    <i class="fa-solid fa-circle-check"></i>

                    {{ $latestContact->created_at?->format('d/m/Y H:i') }}

                </div>

                @else

                <h3>Chưa có liên hệ</h3>

                <p>
                    Gửi yêu cầu hỗ trợ nếu bạn cần CineHome giải đáp.
                </p>

                <div class="contact-side-status">
                    <i class="fa-solid fa-circle-check"></i>
                    Bộ phận hỗ trợ luôn sẵn sàng
                </div>

                @endif

            </aside>

        </div>


        {{-- ================= STATS ================= --}}
        <div class="contact-stats">

            <article class="contact-stat">
                <span>Tổng liên hệ</span>

                <strong>
                    {{ number_format($totalContacts) }}
                </strong>

                <small>
                    Tất cả yêu cầu
                </small>
            </article>


            <article class="contact-stat">
                <span>Đang xử lý</span>

                <strong>
                    {{ number_format($pendingContacts) }}
                </strong>

                <small>
                    Đang chờ phản hồi
                </small>
            </article>


            <article class="contact-stat">
                <span>Đã phản hồi</span>

                <strong>
                    {{ number_format($repliedContacts) }}
                </strong>

                <small>
                    Đã nhận phản hồi
                </small>
            </article>


            <article class="contact-stat">
                <span>Đã đóng</span>

                <strong>
                    {{ number_format($closedContacts) }}
                </strong>

                <small>
                    Lịch sử hỗ trợ
                </small>
            </article>

        </div>


        {{-- ================= CONTACT LIST ================= --}}
        <section class="contact-board">

            <div class="contact-board-head">

                <div>
                    <span>KHO LIÊN HỆ</span>

                    <h2>
                        Tất cả liên hệ
                    </h2>
                </div>

                <div class="contact-count">
                    <i class="fa-solid fa-inbox"></i>

                    {{ number_format($totalContacts) }}
                </div>

            </div>


            <div class="contact-list">

                @forelse($contacts as $lienHe)

                @php
                $status = $lienHe->trang_thai ?? 'cho_xu_ly';

                $statusMap = [
                'cho_xu_ly' => [
                'label' => 'Chờ xử lý',
                'class' => 'waiting',
                'icon' => 'fa-clock',
                ],

                'dang_xu_ly' => [
                'label' => 'Đang xử lý',
                'class' => 'processing',
                'icon' => 'fa-spinner',
                ],

                // Admin lưu da_xu_ly => User hiển thị Đã phản hồi
                'da_xu_ly' => [
                'label' => 'Đã phản hồi',
                'class' => 'replied',
                'icon' => 'fa-circle-check',
                ],

                'da_phan_hoi' => [
                'label' => 'Đã phản hồi',
                'class' => 'replied',
                'icon' => 'fa-circle-check',
                ],
                ];

                $statusInfo = $statusMap[$status] ?? [
                'label' => 'Chờ xử lý',
                'class' => 'waiting',
                'icon' => 'fa-clock',
                ];
                @endphp



                <article class="contact-card">

                    <div class="contact-card-icon">
                        <i class="fa-solid fa-envelope"></i>
                    </div>


                    <div class="contact-card-content">

                        <div class="contact-card-top">

                            <h3>
                                {{ $lienHe->tieu_de ?? 'Yêu cầu liên hệ' }}
                            </h3>

                            <span class="contact-status {{ $statusInfo['class'] }}">

                                <i class="fa-solid {{ $statusInfo['icon'] }}"></i>

                                {{ $statusInfo['label'] }}

                            </span>

                        </div>


                        <p>
                            {{ \Illuminate\Support\Str::limit(
                                    $lienHe->noi_dung ?? 'Không có nội dung',
                                    150
                                ) }}
                        </p>


                        <div class="contact-card-meta">

                            <span>
                                <i class="fa-regular fa-calendar"></i>

                                {{ $lienHe->created_at?->format('d/m/Y') }}
                            </span>

                            <span>
                                <i class="fa-regular fa-clock"></i>

                                {{ $lienHe->created_at?->format('H:i') }}
                            </span>

                        </div>

                    </div>

                </article>

                @empty

                <div class="contact-empty">

                    <div class="contact-empty-icon">
                        <i class="fa-regular fa-comments"></i>
                    </div>

                    <h3>Chưa có liên hệ nào</h3>

                    <p>
                        Bạn chưa gửi yêu cầu hỗ trợ nào.
                        Hãy liên hệ với CineHome nếu bạn cần được hỗ trợ.
                    </p>

                    <a
                        href="{{ route('user.lien-he.index') }}"
                        class="contact-primary-btn">
                        <i class="fa-solid fa-plus"></i>
                        Gửi liên hệ đầu tiên
                    </a>

                </div>

                @endforelse

            </div>

        </section>

    </div>
</section>

@endsection