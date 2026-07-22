<!DOCTYPE html>
<lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xác nhận đặt vé - CineHome</title>
</head>
<body style="background-color: #121212; font-family: Arial, sans-serif; color: #ffffff; padding: 20px; margin: 0;">
    <div style="max-width: 600px; margin: 0 auto; background: #1e1e1e; border-radius: 16px; border: 1px solid #333; overflow: hidden;">
        
        <div style="background: linear-gradient(135deg, #8a4a21, #d99a32); padding: 24px; text-align: center;">
            <h1 style="margin: 0; color: #ffffff; font-size: 24px; text-transform: uppercase; letter-spacing: 1px;">CineHome Cinema</h1>
            <p style="margin: 6px 0 0; color: #fef08a; font-size: 14px;">Xác nhận thanh toán vé xem phim thành công</p>
        </div>

        <div style="padding: 24px;">
            <p style="font-size: 16px; margin-top: 0;">Xin chào <strong><?php echo e($veXemPhim->nguoiDung->ho_ten ?? 'Quý khách'); ?></strong>,</p>
            <p style="color: #9ca3af; font-size: 14px;">Cảm ơn bạn đã đặt vé tại CineHome. Dưới đây là thông tin vé điện tử của bạn:</p>

            <div style="background: #2a2a2a; border-radius: 12px; padding: 18px; margin: 20px 0; border-left: 4px solid #d99a32;">
                <table style="width: 100%; color: #ffffff; font-size: 14px; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 6px 0; color: #9ca3af;">Mã vé:</td>
                        <td style="padding: 6px 0; font-weight: bold; color: #d99a32; text-align: right;"><?php echo e($veXemPhim->ma_ve); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #9ca3af;">Phim:</td>
                        <td style="padding: 6px 0; font-weight: bold; text-align: right;"><?php echo e($veXemPhim->ten_phim); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #9ca3af;">Suất chiếu:</td>
                        <td style="padding: 6px 0; font-weight: bold; text-align: right;">
                            <?php echo e($veXemPhim->thoi_gian_chieu ? \Carbon\Carbon::parse($veXemPhim->thoi_gian_chieu)->format('H:i - d/m/Y') : '-'); ?>

                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #9ca3af;">Phòng & Rạp:</td>
                        <td style="padding: 6px 0; text-align: right;"><?php echo e($veXemPhim->ten_phong); ?> - <?php echo e($veXemPhim->ten_rap); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #9ca3af;">Ghế ngồi:</td>
                        <td style="padding: 6px 0; font-weight: bold; color: #facc15; text-align: right;"><?php echo e($veXemPhim->ma_ghe); ?></td>
                    </tr>
                </table>
            </div>

            <?php if(!empty($foodItems) && count($foodItems) > 0): ?>
                <div style="background: #2a2a2a; border-radius: 12px; padding: 18px; margin-bottom: 20px;">
                    <h3 style="margin: 0 0 12px; color: #d99a32; font-size: 15px;">🍿 Đồ ăn & Bắp nước kèm theo:</h3>
                    <ul style="margin: 0; padding-left: 20px; color: #e5e7eb; font-size: 14px;">
                        <?php $__currentLoopData = $foodItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li style="margin-bottom: 6px;">
                                <?php echo e($item['name'] ?? $item['ten_mon'] ?? 'Đồ ăn'); ?> 
                                <strong>x<?php echo e($item['qty'] ?? $item['so_luong'] ?? 1); ?></strong>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            
            <div style="text-align: center; margin: 25px 0; padding: 20px; background: #252525; border-radius: 16px;">
                <div style="background: #ffffff; padding: 12px; display: inline-block; border-radius: 12px;">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?php echo e(urlencode($veXemPhim->ma_ve)); ?>" 
                         alt="Mã QR vé" 
                         style="width: 180px; height: 180px; display: block;" />
                </div>
                <div style="margin-top: 10px; color: #9ca3af; font-size: 13px;">
                    Mã số vé: <strong style="color: #ffffff; font-family: monospace; font-size: 16px;"><?php echo e($veXemPhim->ma_ve); ?></strong>
                </div>
            </div>

            <div style="border-top: 1px dashed #444; padding-top: 16px; margin-top: 20px;">
                <p style="margin: 0; font-size: 16px; font-weight: bold; text-align: right; color: #22c55e;">
                    Tổng tiền: <?php echo e(number_format($veXemPhim->tong_tien)); ?>đ
                </p>
            </div>

            <div style="background: rgba(217, 154, 50, 0.1); border: 1px solid rgba(217, 154, 50, 0.3); border-radius: 10px; padding: 14px; margin-top: 20px; text-align: center; font-size: 13px; color: #fde68a;">
                💡 Vui lòng xuất trình Mã vé hoặc QR trên email này cho nhân viên tại quầy soát vé trước giờ chiếu 15 phút.
            </div>
        </div>

        <div style="background: #181818; padding: 16px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #2a2a2a;">
            CineHome Cinema - Chúc bạn có những giây phút xem phim vui vẻ!
        </div>
    </div>
</body>
</html><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/emails/ve_xem_phim.blade.php ENDPATH**/ ?>