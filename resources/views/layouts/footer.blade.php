<footer class="cine-footer">
    <div class="container-fluid px-5">
        <div class="row gy-4">

            <div class="col-md-4">
                <div class="footer-logo">
                    <span class="footer-logo-mark cinehome-logo-sparkle">
                        <img src="{{ asset('assets/images/LOGO copy.png') }}" alt="CineHome Logo" class="cinehome-logo-img">
                    </span>
                    <strong>Cine<span>Home</span></strong>
                </div>

                <p>
                    CineHome là hệ thống đặt vé xem phim trực tuyến, hỗ trợ chọn ghế,
                    thanh toán QR và vé điện tử tiện lợi.
                </p>
            </div>

            <div class="col-md-2">
                <h5 class="text-white fw-bold">Menu</h5>
                <p>Phim</p>
                <p>Rạp</p>
            </div>

            <div class="col-md-3">
                <h5 class="text-white fw-bold">Hỗ trợ</h5>
                <p>Điều khoản sử dụng</p>
                <p>Chính sách hủy vé</p>
                <p><a href="{{ route('user.lien-he.index') }}" class="text-decoration-none text-reset">Liên hệ</a></p>
            </div>

            <div class="col-md-3">
                <h5 class="text-white fw-bold">Liên hệ</h5>
                <p><i class="fa-solid fa-envelope"></i> support@cinehome.vn</p>
                <p><i class="fa-solid fa-phone"></i> 0123 456 789</p>
            </div>

        </div>

        <hr class="border-secondary">

        <div class="text-center">
            © {{ date('Y') }} CineHome. All rights reserved.
        </div>
    </div>
</footer>
