<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực email - CineHome</title>
</head>
<body style="margin: 0; padding: 0; background-color: #0b0f17; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #e2e8f0; -webkit-font-smoothing: antialiased;">

    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #0b0f17; padding: 40px 0;">
        <tr>
            <td align="center">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 520px; background-color: #161d2a; border-radius: 12px; border: 1px solid #232d3f; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">

                    <!-- Logo & Tiêu đề -->
                    <tr>
                        <td align="center" style="padding: 32px 20px 20px 20px; background-color: #121722; border-bottom: 1px solid #1e293b;">
                            <h1 style="margin: 0; font-size: 26px; font-weight: 800; color: #ffffff; letter-spacing: 0.5px;">
                                Cine<span style="color: #e50914;">Home</span>
                            </h1>
                            <p style="margin: 4px 0 0 0; font-size: 12px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600;">Hệ Thống Đặt Vé Rạp Chiếu Phim</p>
                        </td>
                    </tr>

                    <!-- Nội dung chính -->
                    <tr>
                        <td style="padding: 32px 30px;">
                            <div style="text-align: center; margin-bottom: 24px;">
                                <div style="display: inline-flex; align-items: center; justify-content: center; width: 56px; height: 56px; border-radius: 50%; background-color: rgba(234, 179, 8, 0.12); border: 1px solid rgba(234, 179, 8, 0.3);">
                                    <span style="font-size: 26px; line-height: 1;">&#9993;</span>
                                </div>
                            </div>

                            <h2 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 600; color: #ffffff; text-align: center;">
                                Chào mừng, {{ $user->ho_ten ?? 'bạn' }}!
                            </h2>
                            <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 1.6; color: #cbd5e1; text-align: center;">
                                Cảm ơn bạn đã đăng ký tài khoản CineHome bằng địa chỉ email <strong>{{ $user->email }}</strong>. Để bảo vệ tài khoản và bắt đầu đặt vé, vui lòng xác thực email của bạn bằng cách nhấn vào nút bên dưới:
                            </p>

                            <!-- Nút xác thực -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" style="padding-bottom: 28px;">
                                        <a href="{{ $url }}" target="_blank" style="display: inline-block; background: linear-gradient(135deg, #e50914 0%, #b20710 100%); color: #ffffff; font-size: 15px; font-weight: 700; text-decoration: none; padding: 14px 32px; border-radius: 8px; box-shadow: 0 4px 15px rgba(229, 9, 20, 0.4);">
                                            XÁC THỰC EMAIL CỦA TÔI
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <div style="background-color: #0f141e; border-left: 3px solid #eab308; padding: 12px 16px; border-radius: 4px; margin-bottom: 24px;">
                                <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #94a3b8;">
                                    <strong style="color: #eab308;">Lưu ý:</strong> Bạn cần xác thực email trước khi có thể đăng nhập vào tài khoản. Liên kết này sẽ tự động hết hạn sau <strong>60 phút</strong>. Nếu bạn không tạo tài khoản này, vui lòng bỏ qua thư này.
                                </p>
                            </div>

                            <p style="margin: 0; font-size: 12px; color: #64748b; line-height: 1.5; border-top: 1px dashed #232d3f; padding-top: 20px; word-break: break-all;">
                                Nếu nút bấm trên không hoạt động, bạn có thể sao chép đường dẫn sau dán vào trình duyệt web:<br>
                                <a href="{{ $url }}" style="color: #38bdf8; text-decoration: underline;">{{ $url }}</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Chân trang -->
                    <tr>
                        <td align="center" style="padding: 20px; background-color: #0f141e; border-top: 1px solid #1e293b; font-size: 12px; color: #64748b;">
                            <p style="margin: 0 0 6px 0;">© {{ date('Y') }} Rạp chiếu phim CineHome. Bảo lưu mọi quyền.</p>
                            <p style="margin: 0;">Thư này được gửi tự động từ hệ thống, vui lòng không phản hồi trực tiếp.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
