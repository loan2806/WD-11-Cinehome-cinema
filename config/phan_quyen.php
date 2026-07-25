<?php

return [
    // 🌟 3 Vai trò chuẩn của hệ thống rạp CineHome
    'vai_tro' => [
        'super_admin' => 'Quản Trị Viên (Super-admin)',
        'quan_ly_rap' => 'Quản Lý Rạp',
        'nhan_vien'   => 'Nhân Viên Quầy',
    ],

    // 🌟 Mạng lưới quyền hạn đã được Việt hóa mã quyền (Permission Keys)
    'nhom_quyen' => [
        'tong_quan' => [
            'tieu_de' => 'TỔNG QUAN HỆ THỐNG',
            'danh_sach_quyen' => [
                'tong_quan.xem' => 'Xem Dashboard tổng quan',
            ],
        ],
        'phim' => [
            'tieu_de' => 'QUẢN LÝ NỘI DUNG PHIM',
            'danh_sach_quyen' => [
                'phim.xem'         => 'Xem danh sách phim',
                'phim.them'        => 'Thêm phim mới',
                'phim.sua'         => 'Sửa thông tin phim',
                'phim.xoa'         => 'Xóa phim',
                'suat_chieu.xem'   => 'Quản lý lịch suất chiếu',
                'the_loai.xem'     => 'Quản lý thể loại phim',
                'quoc_gia.xem'     => 'Quản lý quốc gia sản xuất',
            ],
        ],
        'co_so_vat_chat' => [
            'tieu_de' => 'CƠ SỞ VẬT CHẤT PHÒNG',
            'danh_sach_quyen' => [
                'phong_chieu.xem' => 'Quản lý phòng chiếu',
                'loai_ghe.xem'    => 'Quản lý danh mục loại ghế',
            ],
        ],
        'quay_ve' => [
            'tieu_de' => 'NGHIỆP VỤ QUẦY VÉ',
            'danh_sach_quyen' => [
                'kho_ve.xem'       => 'Quản lý kho dữ liệu vé',
                'quay_ve.ban_ve'   => 'Bán vé trực tiếp tại rạp',
                'soat_ve.quet_qr'  => 'Soát vé QR & In vé cứng',
                'do_an.hoa_don'    => 'Quản lý hóa đơn đồ ăn & Combo',
                'do_an.cau_hinh'   => 'Cấu hình Menu & Kho hàng',
                'khuyen_mai.xem'   => 'Quản lý Khuyến mãi & Voucher',
            ],
        ],
        'nhan_luc' => [
            'tieu_de' => 'TÀI KHOẢN & NHÂN LỰC',
            'danh_sach_quyen' => [
                'nhan_vien.xem'     => 'Xem danh sách nhân viên',
                'nhan_vien.quan_ly' => 'Thêm/Sửa/Xóa nhân viên',
                'phan_quyen.ma_tran' => 'Xem & Thiết lập ma trận phân quyền',
                'khach_hang.xem'    => 'Quản lý tài khoản khách hàng',
                'thanh_vien.xem'    => 'Quản lý thẻ thành viên & điểm',
            ],
        ],
        'bao_cao' => [
            'tieu_de' => 'BÁO CÁO VẬN HÀNH',
            'danh_sach_quyen' => [
                'bao_cao.doanh_thu' => 'Xem thống kê doanh thu',
                'nhat_ky.he_thong'  => 'Xem nhật ký vết hệ thống',
            ],
        ],
        'cai_dat' => [
            'tieu_de' => 'CÀI ĐẶT THAM SỐ GỐC',
            'danh_sach_quyen' => [
                'thong_bao.gui'    => 'Gửi thông báo đẩy',
                'cai_dat.he_thong'  => 'Cấu hình tham số gốc hệ thống',
            ],
        ],
    ],
];