<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn <?php echo e($ve->ma_ve); ?></title>
    <style>
        * {
            box-sizing: border-box
        }

        body {
            margin: 0;
            padding: 20px;
            background: #eee;
            color: #111;
            font-family: Arial, sans-serif
        }

        .paper {
            width: 80mm;
            margin: auto;
            padding: 12px;
            background: #fff
        }

        .center {
            text-align: center
        }

        .brand {
            font-size: 25px;
            font-weight: 900
        }

        .title {
            margin-top: 8px;
            font-size: 17px;
            font-weight: 900
        }

        .small {
            font-size: 11px;
            line-height: 1.5
        }

        .line {
            margin: 11px 0;
            border-top: 1px dashed #111
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 4px 0;
            font-size: 12.5px
        }

        .row strong {
            max-width: 60%;
            text-align: right;
            overflow-wrap: anywhere
        }

        .total {
            font-size: 16px;
            font-weight: 900
        }

        .actions {
            width: 80mm;
            margin: 14px auto;
            display: flex;
            gap: 8px
        }

        .actions button,
        .actions a {
            flex: 1;
            padding: 10px;
            border: 0;
            border-radius: 6px;
            background: #111;
            color: #fff;
            text-align: center;
            text-decoration: none
        }

        @media print {
            @page {
                size: 80mm auto;
                margin: 0
            }

            body {
                padding: 0;
                background: #fff
            }

            .paper {
                margin: 0
            }

            .actions {
                display: none
            }
        }

    </style>
</head>
<body>
    <?php
    $foods=collect($ve->foods_list ?? []);$foodTotal=(float)($ve->food_total ?? 0);if($foodTotal<=0){$foodTotal=$foods->sum(fn($food)=>(float)($food['don_gia']??0)*(int)($food['so_luong']??1));}$seatTotal=(float)($ve->seat_total??0);if($seatTotal<=0){$seatTotal=max((float)$ve->tong_tien-$foodTotal,0);}$staffName=$ve->nhanVien->ho_ten??$ve->nhanVien->ten??'Nhân viên';
            ?>
            <div class="paper">
                <div class="center">
                    <div class="brand">CineHome</div>
                    <div class="small"><?php echo e($ve->ten_rap); ?></div>
                    <div class="title">HÓA ĐƠN BÁN HÀNG</div>
                </div>
                <div class="line"></div>
                <div class="row"><span>Mã đơn</span><strong><?php echo e($ve->ma_ve); ?></strong></div>
                <div class="row"><span>Ngày lập</span><strong><?php echo e(optional($ve->created_at)->format('d/m/Y H:i')); ?></strong></div>
                <div class="row"><span>Nhân viên</span><strong><?php echo e($staffName); ?></strong></div>
                <div class="row"><span>Thanh toán</span><strong><?php echo e($ve->payment_method === 'vietqr' ? 'VietQR' : 'Tiền mặt'); ?></strong></div>
                <div class="line"></div>
                <div class="row"><span>Phim</span><strong><?php echo e($ve->ten_phim); ?></strong></div>
                <div class="row"><span>Suất chiếu</span><strong><?php echo e(optional($ve->thoi_gian_chieu)->format('d/m/Y H:i')); ?></strong></div>
                <div class="row"><span>Phòng / ghế</span><strong><?php echo e($ve->ten_phong); ?> / <?php echo e(str_replace(',', ', ', $ve->ma_ghe)); ?></strong></div>
                <div class="row"><span>Tiền vé</span><strong><?php echo e(number_format($seatTotal,0,',','.')); ?>đ</strong></div>
                <?php if($foods->isNotEmpty()): ?><div class="line"></div><strong class="small">ĐỒ ĂN & COMBO</strong><?php $__currentLoopData = $foods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $food): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php $q=(int)($food['so_luong']??1);$p=(float)($food['don_gia']??0); ?><div class="row"><span><?php echo e($food['ten_mon']??'Đồ ăn'); ?> x<?php echo e($q); ?></span><strong><?php echo e(number_format($p*$q,0,',','.')); ?>đ</strong></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><div class="row"><span>Tiền đồ ăn</span><strong><?php echo e(number_format($foodTotal,0,',','.')); ?>đ</strong></div><?php endif; ?>
                <div class="line"></div>
                <div class="row total"><span>TỔNG CỘNG</span><strong><?php echo e(number_format((float)$ve->tong_tien,0,',','.')); ?>đ</strong></div><?php if($ve->payment_method!=='vietqr'): ?><div class="row"><span>Khách đưa</span><strong><?php echo e(number_format((float)$ve->received_amount,0,',','.')); ?>đ</strong></div>
                <div class="row"><span>Tiền thừa</span><strong><?php echo e(number_format((float)$ve->change_amount,0,',','.')); ?>đ</strong></div><?php endif; ?><div class="line"></div>
                <div class="center small">Cảm ơn quý khách đã lựa chọn CineHome!</div>
            </div>
            <div class="actions"><button onclick="window.print()">In hóa đơn</button><a href="<?php echo e(route('staff.ban-ve.success', ['id' => $ve->id])); ?>">Quay lại</a></div>
            <script>
                window.addEventListener('load', () => window.print())

            </script>
</body>
</html>
<?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/staff/ban-ve/print-invoice.blade.php ENDPATH**/ ?>