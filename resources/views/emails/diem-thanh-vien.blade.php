<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        {{ $loai === 'tang' ? 'Bạn vừa được tặng điểm' : 'Điểm thành viên được cập nhật' }}
    </title>
</head>

<body style="
    background-color: #121212;
    font-family: Arial, Helvetica, sans-serif;
    color: #ffffff;
    padding: 20px;
    margin: 0;
">

    <div style="
        max-width: 600px;
        margin: 0 auto;
        background: #1e1e1e;
        border-radius: 16px;
        border: 1px solid #333333;
        overflow: hidden;
    ">

        {{-- HEADER --}}
        <div style="
            background: linear-gradient(135deg, #8a4a21, #d99a32);
            padding: 28px 24px;
            text-align: center;
        ">

            <h1 style="
                margin: 0;
                color: #ffffff;
                font-size: 25px;
                text-transform: uppercase;
                letter-spacing: 1.5px;
            ">
                CineHome Cinema
            </h1>

            <p style="
                margin: 8px 0 0;
                color: #fef08a;
                font-size: 14px;
            ">
                Thông báo tài khoản thành viên
            </p>

        </div>


        {{-- CONTENT --}}
        <div style="padding: 28px 24px;">

            <p style="
                font-size: 16px;
                margin: 0 0 10px;
                color: #ffffff;
            ">
                Xin chào
                <strong>
                    {{ $thanhVien->nguoiDung->ho_ten ?? 'Quý khách' }}
                </strong>,
            </p>

            @if($loai === 'tang')

                <p style="
                    color: #9ca3af;
                    font-size: 14px;
                    line-height: 1.6;
                    margin-bottom: 22px;
                ">
                    CineHome xin thông báo rằng tài khoản thành viên của bạn
                    vừa được cộng điểm.
                </p>

                {{-- ĐIỂM ĐƯỢC TẶNG --}}
                <div style="
                    background: linear-gradient(
                        135deg,
                        rgba(34, 197, 94, 0.12),
                        rgba(22, 163, 74, 0.05)
                    );
                    border: 1px solid rgba(34, 197, 94, 0.35);
                    border-radius: 16px;
                    padding: 24px;
                    text-align: center;
                    margin: 20px 0;
                ">

                    <div style="
                        font-size: 13px;
                        color: #9ca3af;
                        margin-bottom: 8px;
                    ">
                        🎁 ĐIỂM ĐƯỢC TẶNG
                    </div>

                    <div style="
                        font-size: 40px;
                        font-weight: bold;
                        color: #22c55e;
                        line-height: 1.2;
                    ">
                        +{{ number_format($soDiem) }}
                    </div>

                    <div style="
                        margin-top: 6px;
                        color: #86efac;
                        font-size: 14px;
                    ">
                        điểm thành viên
                    </div>

                </div>

            @else

                <p style="
                    color: #9ca3af;
                    font-size: 14px;
                    line-height: 1.6;
                    margin-bottom: 22px;
                ">
                    CineHome xin thông báo rằng một phần điểm trong
                    tài khoản thành viên của bạn đã được thu hồi.
                </p>

                {{-- ĐIỂM BỊ THU HỒI --}}
                <div style="
                    background: linear-gradient(
                        135deg,
                        rgba(239, 68, 68, 0.12),
                        rgba(220, 38, 38, 0.05)
                    );
                    border: 1px solid rgba(239, 68, 68, 0.35);
                    border-radius: 16px;
                    padding: 24px;
                    text-align: center;
                    margin: 20px 0;
                ">

                    <div style="
                        font-size: 13px;
                        color: #9ca3af;
                        margin-bottom: 8px;
                    ">
                        🔄 ĐIỂM ĐÃ THU HỒI
                    </div>

                    <div style="
                        font-size: 40px;
                        font-weight: bold;
                        color: #ef4444;
                        line-height: 1.2;
                    ">
                        -{{ number_format($soDiem) }}
                    </div>

                    <div style="
                        margin-top: 6px;
                        color: #fca5a5;
                        font-size: 14px;
                    ">
                        điểm thành viên
                    </div>

                </div>

            @endif


            {{-- THÔNG TIN GIAO DỊCH --}}
            <div style="
                background: #2a2a2a;
                border-radius: 12px;
                padding: 18px;
                margin: 22px 0;
                border-left: 4px solid #d99a32;
            ">

                <div style="
                    font-size: 15px;
                    font-weight: bold;
                    color: #d99a32;
                    margin-bottom: 14px;
                ">
                    📋 Thông tin cập nhật
                </div>

                <table style="
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 14px;
                ">

                    <tr>
                        <td style="
                            padding: 7px 0;
                            color: #9ca3af;
                        ">
                            Loại giao dịch
                        </td>

                        <td style="
                            padding: 7px 0;
                            text-align: right;
                            font-weight: bold;
                            color: #ffffff;
                        ">
                            @if($loai === 'tang')
                                Tặng điểm
                            @else
                                Thu hồi điểm
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td style="
                            padding: 7px 0;
                            color: #9ca3af;
                        ">
                            Số điểm
                        </td>

                        <td style="
                            padding: 7px 0;
                            text-align: right;
                            font-weight: bold;
                            color: {{ $loai === 'tang' ? '#22c55e' : '#ef4444' }};
                        ">
                            {{ $loai === 'tang' ? '+' : '-' }}{{ number_format($soDiem) }} điểm
                        </td>
                    </tr>

                    <tr>
                        <td style="
                            padding: 7px 0;
                            color: #9ca3af;
                        ">
                            Người thực hiện
                        </td>

                        <td style="
                            padding: 7px 0;
                            text-align: right;
                            color: #ffffff;
                        ">
                            Admin CineHome
                        </td>
                    </tr>

                </table>

            </div>


            {{-- LÝ DO --}}
            <div style="
                background: #252525;
                border-radius: 12px;
                padding: 18px;
                margin-bottom: 22px;
            ">

                <div style="
                    color: #d99a32;
                    font-size: 14px;
                    font-weight: bold;
                    margin-bottom: 8px;
                ">
                    📝 Nội dung
                </div>

                <div style="
                    color: #e5e7eb;
                    font-size: 14px;
                    line-height: 1.6;
                ">
                    {{ $noiDung }}
                </div>

            </div>


            {{-- SỐ DƯ ĐIỂM --}}
            <div style="
                background: #2a2a2a;
                border-radius: 14px;
                padding: 20px;
                text-align: center;
                border: 1px solid #3a3a3a;
            ">

                <div style="
                    color: #9ca3af;
                    font-size: 13px;
                    margin-bottom: 8px;
                ">
                    ⭐ SỐ DƯ ĐIỂM HIỆN TẠI
                </div>

                <div style="
                    font-size: 30px;
                    font-weight: bold;
                    color: #d99a32;
                ">
                    {{ number_format($diemHienTai) }}
                    <span style="
                        font-size: 15px;
                        color: #facc15;
                        font-weight: normal;
                    ">
                        điểm
                    </span>
                </div>

            </div>


            {{-- LƯU Ý --}}
            <div style="
                background: rgba(217, 154, 50, 0.1);
                border: 1px solid rgba(217, 154, 50, 0.3);
                border-radius: 10px;
                padding: 14px;
                margin-top: 22px;
                text-align: center;
                font-size: 13px;
                color: #fde68a;
                line-height: 1.5;
            ">
                💡 Bạn có thể sử dụng điểm thành viên để nhận
                các quyền lợi và ưu đãi từ CineHome.
            </div>

        </div>


        {{-- FOOTER --}}
        <div style="
            background: #181818;
            padding: 18px 16px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-top: 1px solid #2a2a2a;
        ">

            <div style="
                color: #9ca3af;
                margin-bottom: 6px;
            ">
                CineHome Cinema
            </div>

            <div>
                Chúc bạn có những trải nghiệm tuyệt vời cùng CineHome! 🎬
            </div>

            <div style="
                margin-top: 8px;
                color: #555555;
            ">
                Đây là email tự động, vui lòng không trả lời email này.
            </div>

        </div>

    </div>

</body>
</html>
