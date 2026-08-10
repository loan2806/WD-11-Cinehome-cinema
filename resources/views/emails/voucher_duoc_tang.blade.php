<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Voucher ưu đãi - CineHome</title>
</head>
<body style="background-color:#121212;font-family:Arial,sans-serif;color:#ffffff;padding:20px;margin:0;">
    <div style="max-width:620px;margin:0 auto;background:#1e1e1e;border-radius:16px;border:1px solid #333;overflow:hidden;">

        <div style="background:linear-gradient(135deg,#8a4a21,#d99a32);padding:26px;text-align:center;">
            <h1 style="margin:0;color:#ffffff;font-size:24px;text-transform:uppercase;letter-spacing:1px;">
                CineHome Cinema
            </h1>
            <p style="margin:7px 0 0;color:#fef08a;font-size:14px;">
                Một ưu đãi dành riêng cho bạn
            </p>
        </div>

        <div style="padding:26px;">
            <p style="font-size:16px;margin-top:0;">
                Xin chào <strong>{{ $customer->ho_ten }}</strong>,
            </p>

            <p style="color:#c4c7ce;font-size:14px;line-height:1.7;">
                CineHome vừa gửi tặng bạn
                <strong style="color:#f4c56a;">{{ $issuedVouchers->count() }} voucher</strong>
                <strong>{{ $voucher->ten_voucher }}</strong>.
                Voucher đã được lưu trực tiếp vào tài khoản của bạn và có thể sử dụng khi đặt vé phù hợp.
            </p>

            <div style="background:#2a2a2a;border-radius:14px;padding:20px;margin:22px 0;border-left:4px solid #d99a32;">
                <div style="text-align:center;margin-bottom:16px;">
                    <p style="margin:0 0 6px;color:#9ca3af;font-size:12px;text-transform:uppercase;letter-spacing:.5px;">
                        Giá trị ưu đãi
                    </p>

                    <p style="margin:0;color:#facc15;font-size:24px;font-weight:bold;">
                        Giảm {{ number_format((float) $voucher->gia_tri_giam, 0, ',', '.') }}đ
                    </p>

                    <p style="margin:8px 0 0;color:#ffffff;font-size:16px;">
                        {{ $voucher->ten_voucher }}
                    </p>
                </div>

                <div style="border-top:1px solid #3b3b3b;padding-top:14px;">
                    <p style="margin:0 0 10px;color:#9ca3af;font-size:12px;text-transform:uppercase;letter-spacing:.5px;">
                        Mã voucher cá nhân
                    </p>

                    @foreach ($issuedVouchers as $item)
                        <div style="margin:8px 0;padding:11px 13px;border-radius:10px;background:#181818;color:#d99a32;font-size:16px;font-weight:bold;letter-spacing:.6px;">
                            {{ $item->ma_voucher_ca_nhan }}
                        </div>
                    @endforeach
                </div>

                <p style="margin:14px 0 0;color:#9ca3af;font-size:13px;text-align:center;">
                    Hạn sử dụng:
                    <strong style="color:#ffffff;">
                        {{ optional($issuedVouchers->first()?->ngay_het_han)->format('d/m/Y') ?? $voucher->ngay_het_han->format('d/m/Y') }}
                    </strong>
                </p>
            </div>

            <p style="color:#9ca3af;font-size:13px;line-height:1.65;">
                Bạn có thể đăng nhập CineHome và mở mục <strong style="color:#ffffff;">Voucher của tôi</strong>
                để xem lại mã voucher bất cứ lúc nào.
            </p>

            <p style="color:#6b7280;font-size:13px;margin-bottom:0;line-height:1.65;">
                Cảm ơn bạn đã đồng hành cùng CineHome. Chúc bạn có những trải nghiệm xem phim thật vui vẻ.
            </p>
        </div>

        <div style="background:#161616;padding:16px;text-align:center;border-top:1px solid #333;">
            <p style="margin:0;color:#6b7280;font-size:12px;">
                © {{ date('Y') }} CineHome Cinema.
            </p>
        </div>
    </div>
</body>
</html>