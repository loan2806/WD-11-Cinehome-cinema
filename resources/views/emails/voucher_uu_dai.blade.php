<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Voucher ưu đãi - CineHome</title>
</head>
<body style="background-color: #121212; font-family: Arial, sans-serif; color: #ffffff; padding: 20px; margin: 0;">
    <div style="max-width: 600px; margin: 0 auto; background: #1e1e1e; border-radius: 16px; border: 1px solid #333; overflow: hidden;">

        <div style="background: linear-gradient(135deg, #8a4a21, #d99a32); padding: 24px; text-align: center;">
            <h1 style="margin: 0; color: #ffffff; font-size: 24px; text-transform: uppercase; letter-spacing: 1px;">CineHome Cinema</h1>
            <p style="margin: 6px 0 0; color: #fef08a; font-size: 14px;">Món quà nhỏ dành cho bạn</p>
        </div>

        <div style="padding: 24px;">
            <p style="font-size: 16px; margin-top: 0;">Xin chào <strong>{{ $lienHe->ho_ten }}</strong>,</p>

            <p style="color: #9ca3af; font-size: 14px;">
                Liên quan đến yêu cầu hỗ trợ #{{ $lienHe->id }} bạn đã gửi, đội ngũ CineHome xin gửi tặng bạn một voucher ưu đãi
                như một lời xin lỗi vì trải nghiệm chưa tốt vừa qua.
            </p>

            <div style="background: #262626; border-radius: 10px; padding: 14px 16px; margin: 16px 0; border: 1px solid #3f3f46;">
                <p style="margin: 0 0 6px; color: #9ca3af; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    Lý do nhận voucher
                </p>
                <p style="margin: 0; color: #ffffff; font-size: 14px; line-height: 1.6;">
                    {{ $lyDoNhan }}
                </p>
            </div>

            <div style="background: #2a2a2a; border-radius: 12px; padding: 20px; margin: 20px 0; border-left: 4px solid #d99a32; text-align: center;">
                <p style="margin: 0 0 6px; color: #9ca3af; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Mã voucher của bạn</p>
                <p style="margin: 0 0 10px; color: #d99a32; font-size: 24px; font-weight: bold; letter-spacing: 1px;">{{ $nguoiDungVoucher->ma_voucher_ca_nhan }}</p>
                <p style="margin: 0; color: #ffffff; font-size: 16px;">{{ $nguoiDungVoucher->voucher->ten_voucher }}</p>
                <p style="margin: 6px 0 0; color: #facc15; font-size: 18px; font-weight: bold;">
                    Giảm {{ number_format((float) $nguoiDungVoucher->voucher->gia_tri_giam, 0, ',', '.') }}đ
                </p>
                <p style="margin: 10px 0 0; color: #6b7280; font-size: 13px;">
                    Hạn sử dụng: {{ $nguoiDungVoucher->ngay_het_han->format('d/m/Y') }}
                </p>
            </div>

            <p style="color: #9ca3af; font-size: 13px;">
                Voucher đã được lưu sẵn vào mục "Voucher của tôi" trong tài khoản CineHome của bạn, sẵn sàng sử dụng cho lần đặt vé tiếp theo.
            </p>

            <p style="color: #6b7280; font-size: 13px; margin-bottom: 0;">
                Cảm ơn bạn đã phản hồi để CineHome ngày càng hoàn thiện hơn.
            </p>
        </div>

        <div style="background: #161616; padding: 16px; text-align: center; border-top: 1px solid #333;">
            <p style="margin: 0; color: #6b7280; font-size: 12px;">© {{ date('Y') }} CineHome Cinema.</p>
        </div>
    </div>
</body>
</html>