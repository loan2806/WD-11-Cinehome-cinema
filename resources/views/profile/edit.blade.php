<x-profile-layout>

    @php
        $displayName = $user->ho_ten ?? 'Thành viên CineHome';

        $initial = mb_substr($displayName, 0, 1);

        $roleLabel = $user->vai_tro === 'admin'
            ? 'Quản trị viên'
            : ($user->vai_tro === 'nhan_vien'
                ? 'Nhân viên rạp'
                : 'Thành viên CineHome');

        $birthDate = $user->ngay_sinh
            ? \Carbon\Carbon::parse($user->ngay_sinh)->format('d/m/Y')
            : 'Chưa thiết lập';

        $joinedAt = $user->created_at
            ? $user->created_at->format('d/m/Y')
            : 'Đang cập nhật';
    @endphp


    <section class="profile-page">

        {{-- ==========================================================
             HERO
        =========================================================== --}}
        <div class="profile-hero">

            <div class="profile-hero-copy">

                <span class="profile-eyebrow">
                    <i class="fa-solid fa-id-card-clip"></i>
                    Tài khoản CineHome
                </span>

                <h1>
                    Quản lý hồ sơ cá nhân của bạn.
                </h1>

                <p>
                    Cập nhật thông tin liên hệ, đổi mật khẩu bảo mật
                    và kiểm soát tài khoản tại một nơi gọn gàng.
                </p>

            </div>


            <div class="profile-hero-card">

                <div class="profile-avatar-lg">
                    {{ $initial }}
                </div>

                <div>

                    <strong>
                        {{ $displayName }}
                    </strong>

                    <span>
                        {{ $roleLabel }}
                    </span>

                    <small>
                        {{ $user->email }}
                    </small>

                </div>

            </div>

        </div>


        {{-- ==========================================================
             PROFILE GRID
        =========================================================== --}}
        <div class="profile-grid">


            {{-- ======================================================
                 SIDEBAR THÔNG TIN
            ======================================================= --}}
            <aside class="profile-summary-card">

                <div class="profile-summary-head">

                    <div class="profile-avatar-xl">
                        {{ $initial }}
                    </div>

                    <h2>
                        {{ $displayName }}
                    </h2>

                    <p>
                        {{ $roleLabel }}
                    </p>

                </div>


                <div class="profile-summary-list">


                    {{-- EMAIL --}}
                    <div>

                        <i class="fa-solid fa-envelope"></i>

                        <span>
                            Email
                        </span>

                        <strong>
                            {{ $user->email }}
                        </strong>


                        {{-- ==========================================
                             TRẠNG THÁI XÁC THỰC EMAIL
                        =========================================== --}}
                        @if ($user->email_verified_at)

                            <small
                                style="
                                    display:flex;
                                    align-items:center;
                                    gap:6px;
                                    margin-top:6px;
                                    color:#4ade80;
                                    font-weight:700;
                                ">

                                <i class="fa-solid fa-circle-check"></i>

                                Email đã xác thực

                            </small>

                        @else

                            <small
                                style="
                                    display:flex;
                                    align-items:center;
                                    gap:6px;
                                    margin-top:6px;
                                    color:#facc15;
                                    font-weight:700;
                                ">

                            </small>

                        @endif

                    </div>


                    {{-- NGÀY SINH --}}
                    <div>

                        <i class="fa-solid fa-cake-candles"></i>

                        <span>
                            Ngày sinh
                        </span>

                        <strong>
                            {{ $birthDate }}
                        </strong>

                    </div>


                    {{-- NGÀY THAM GIA --}}
                    <div>

                        <i class="fa-solid fa-calendar-check"></i>

                        <span>
                            Tham gia
                        </span>

                        <strong>
                            {{ $joinedAt }}
                        </strong>

                    </div>

                </div>


                {{-- VÉ ĐÃ ĐẶT --}}
                <a
                    href="{{ route('user.ve_xem_phim.index') }}"
                    class="profile-summary-link">

                    <i class="fa-solid fa-ticket"></i>

                    Vé đã đặt

                </a>


                {{-- ==================================================
                     XÁC THỰC EMAIL
                =================================================== --}}
                @if (!$user->email_verified_at)

                    <div
                        style="
                            margin-top:18px;
                            padding:16px;
                            border-radius:16px;
                            background:rgba(250,204,21,0.08);
                            border:1px solid rgba(250,204,21,0.20);
                        ">

                        <div
                            style="
                                display:flex;
                                align-items:flex-start;
                                gap:12px;
                                margin-bottom:14px;
                            ">

                            <div
                                style="
                                    width:38px;
                                    height:38px;
                                    min-width:38px;
                                    display:flex;
                                    align-items:center;
                                    justify-content:center;
                                    border-radius:12px;
                                    background:rgba(250,204,21,0.12);
                                    color:#facc15;
                                ">

                                <i class="fa-solid fa-envelope"></i>

                            </div>


                            <div>

                                <strong
                                    style="
                                        display:block;
                                        color:#fff;
                                        font-size:14px;
                                        margin-bottom:4px;
                                    ">

                                    Email chưa được xác thực

                                </strong>

                                <span
                                    style="
                                        display:block;
                                        color:#9ca3af;
                                        font-size:12px;
                                        line-height:1.5;
                                    ">

                                    Xác thực email để bảo vệ tài khoản
                                    và sử dụng đầy đủ các chức năng.

                                </span>

                            </div>

                        </div>


                        {{-- FORM GỬI EMAIL XÁC THỰC --}}
                        <form
                            method="POST"
                            action="{{ route('verification.send') }}">

                            @csrf

                            <button
                                type="submit"
                                style="
                                    width:100%;
                                    display:flex;
                                    align-items:center;
                                    justify-content:center;
                                    gap:8px;
                                    padding:11px 16px;
                                    border:none;
                                    border-radius:12px;
                                    background:#d99a32;
                                    color:#000;
                                    font-weight:800;
                                    cursor:pointer;
                                    transition:all .2s ease;
                                "
                                onmouseover="this.style.background='#f4c56a'"
                                onmouseout="this.style.background='#d99a32'">

                                <i class="fa-solid fa-paper-plane"></i>

                                Gửi email xác thực

                            </button>

                        </form>

                    </div>

                @else

                    <div
                        style="
                            margin-top:18px;
                            padding:16px;
                            border-radius:16px;
                            background:rgba(34,197,94,0.08);
                            border:1px solid rgba(34,197,94,0.20);
                        ">

                        <div
                            style="
                                display:flex;
                                align-items:center;
                                gap:10px;
                                color:#4ade80;
                                font-weight:800;
                                font-size:14px;
                            ">

                            <i class="fa-solid fa-envelope-circle-check"></i>

                            Email đã được xác thực

                        </div>

                    </div>

                @endif

            </aside>



            {{-- ======================================================
                 CONTENT
            ======================================================= --}}
            <div class="profile-content">


                {{-- ==================================================
                     THÔNG BÁO THÀNH CÔNG
                =================================================== --}}
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


                {{-- ==================================================
                     EMAIL ĐÃ GỬI
                =================================================== --}}
                @if (session('status') === 'verification-link-sent')

                    <div class="profile-toast is-success">

                        <i class="fa-solid fa-envelope-circle-check"></i>

                        Email xác thực đã được gửi.
                        Vui lòng kiểm tra hộp thư của bạn.

                    </div>

                @endif


                {{-- ==================================================
                     THÔNG TIN CÁ NHÂN
                =================================================== --}}
                <div class="profile-card">

                    @include('profile.partials.cap_nhat_thong_tin_form')

                </div>


                {{-- ==================================================
                     ĐỔI MẬT KHẨU
                =================================================== --}}
                <div class="profile-card">

                    @include('profile.partials.doi_mat_khau_form')

                </div>


                {{-- ==================================================
                     XÓA TÀI KHOẢN
                =================================================== --}}
                <div class="profile-card profile-card-danger">

                    @include('profile.partials.xoa_tai_khoan_form')

                </div>

            </div>

        </div>

    </section>

</x-profile-layout>