<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phản hồi liên hệ - CineHome</title>
</head>
<body style="background-color: #121212; font-family: Arial, sans-serif; color: #ffffff; padding: 20px; margin: 0;">
    <div style="max-width: 600px; margin: 0 auto; background: #1e1e1e; border-radius: 16px; border: 1px solid #333; overflow: hidden;">

        <div style="background: linear-gradient(135deg, #8a4a21, #d99a32); padding: 24px; text-align: center;">
            <h1 style="margin: 0; color: #ffffff; font-size: 24px; text-transform: uppercase; letter-spacing: 1px;">CineHome Cinema</h1>
            <p style="margin: 6px 0 0; color: #fef08a; font-size: 14px;">Phản hồi yêu cầu hỗ trợ của bạn</p>
        </div>

        <div style="padding: 24px;">
            <p style="font-size: 16px; margin-top: 0;">Xin chào <strong>{{ $lienHe->ho_ten }}</strong>,</p>
            <p style="color: #9ca3af; font-size: 14px;">Đội ngũ hỗ trợ CineHome đã xem xét và phản hồi yêu cầu liên hệ #{{ $lienHe->id }} của bạn.</p>

            <div style="background: #2a2a2a; border-radius: 12px; padding: 18px; margin: 20px 0; border-left: 4px solid #555;">
                <p style="margin: 0 0 6px; color: #9ca3af; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Nội dung bạn đã gửi</p>
                <p style="margin: 0; color: #e5e7eb; font-size: 14px; white-space: pre-line;">{{ $lienHe->noi_dung }}</p>
            </div>

            <div style="background: #2a2a2a; border-radius: 12px; padding: 18px; margin-bottom: 20px; border-left: 4px solid #d99a32;">
                <p style="margin: 0 0 6px; color: #d99a32; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Phản hồi từ CineHome</p>
                <p style="margin: 0; color: #ffffff; font-size: 15px; white-space: pre-line;">{{ $lienHe->phan_hoi }}</p>
            </div>

            <p style="color: #6b7280; font-size: 13px; margin-bottom: 0;">
                Nếu vẫn cần hỗ trợ thêm, vui lòng phản hồi lại email này hoặc liên hệ hotline 0123 456 789.
            </p>
        </div>

        <div style="background: #161616; padding: 16px; text-align: center; border-top: 1px solid #333;">
            <p style="margin: 0; color: #6b7280; font-size: 12px;">© {{ date('Y') }} CineHome Cinema. Cảm ơn bạn đã đồng hành cùng chúng tôi.</p>
        </div>
    </div>
</body>
</html>
