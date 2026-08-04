<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class VaiTroAndQuyenSeeder extends Seeder
{
    /**
     * Khởi tạo ma trận phân bổ quyền hạn tối ưu cho rạp phim CineHome
     */
    public function run(): void
    {
        // Xóa sạch bộ nhớ đệm phân quyền của Spatie trước khi thực thi
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Làm sạch toàn bộ dữ liệu phân quyền cũ để đồng bộ cấu trúc mới không bị trùng lặp khóa
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table(config('permission.table_names.role_has_permissions'))->truncate();
        DB::table(config('permission.table_names.model_has_roles'))->truncate();
        DB::table(config('permission.table_names.model_has_permissions'))->truncate();
        DB::table(config('permission.table_names.roles'))->truncate();
        DB::table(config('permission.table_names.permissions'))->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Định nghĩa danh mục chức năng phân hệ rạp phim
        $permissionsData = [
            // Cụm 1: Quản trị tài khoản tối cao (Admin bảo mật)
            ['name' => 'phan_quyen_he_thong', 'description' => 'Cấu hình ma trận phân quyền hệ thống'],
            ['name' => 'xem_nhat_ky_hoat_dong', 'description' => 'Xem nhật ký hoạt động hệ thống'],
            ['name' => 'quan_ly_nhan_vien', 'description' => 'Quản lý hồ sơ, tài khoản và trạng thái nhân viên rạp'],
            ['name' => 'quan_ly_khach_hang', 'description' => 'Xem và quản lý danh sách tài khoản khách hàng (Người dùng)'],

            ['name' => 'quan_ly_cau_hinh_he_thong', 'description' => 'Cấu hình thông số hệ thống, thông báo và đánh giá phim'],
            ['name' => 'quan_ly_thong_bao_day', 'description' => 'Quản lý thông báo đẩy đến người dùng'],

            // 🌟 Cụm Mới: Quản lý chăm sóc khách hàng & Phản hồi
            ['name' => 'quan_ly_lien_he', 'description' => 'Xem, cập nhật trạng thái và xử lý các yêu cầu liên hệ từ khách hàng'],

            // Cụm 3: Vận hành rạp kinh doanh
            ['name' => 'thong_ke_doanh_thu', 'description' => 'Xem báo cáo và thống kê doanh thu rạp phim'],
            ['name' => 'quan_ly_ca_lam', 'description' => 'Quản lý lịch và chia ca làm việc cho nhân viên'],
            ['name' => 'quan_ly_phim_suat_chieu', 'description' => 'Quản lý phim, thể loại, quốc gia và lịch suất chiếu phim'],
            ['name' => 'quan_ly_phong_ghe', 'description' => 'Quản lý phòng chiếu, thiết lập sơ đồ hàng ghế và loại ghế'],
            ['name' => 'ban_ve_tai_quay', 'description' => 'Thao tác bán vé và in hóa đơn trực tiếp tại quầy'],
            ['name' => 'quan_ly_do_an_combo', 'description' => 'Quản lý thực đơn hóa đơn đồ ăn, bắp nước combo'],
            ['name' => 'soat_ve_vao_cua', 'description' => 'Kiểm tra trạng thái vé (Quét mã/Soát vé) khi khách vào phòng'],

            // Cụm 4: Đặc quyền dịch vụ khách hàng công cộng ngoài Frontend
            ['name' => 'khach_hang_dat_ve', 'description' => 'Truy cập đặt vé trực tuyến trên Website công cộng'],
            ['name' => 'khach_hang_xem_lich_su', 'description' => 'Xem danh sách và chi tiết mã QR vé đã mua cá nhân'],
            ['name' => 'khach_hang_huy_ve', 'description' => 'Thao tác hoàn tác/Hủy vé đã đặt theo chính sách rạp'],
        ];

        $createdPermissions = [];

        // Lưu danh sách các quyền hạn vào cơ sở dữ liệu
        foreach ($permissionsData as $p) {
            $createdPermissions[$p['name']] = Permission::create([
                'name' => $p['name'],
                'guard_name' => 'web',
                'description' => $p['description']
            ]);
        }

        // Khởi tạo chuẩn xác cấu trúc các vai trò
        $adminRole = Role::create(['name' => 'Quản trị viên', 'guard_name' => 'web']);
        $systemManagerRole = Role::create(['name' => 'Quản lý hệ thống', 'guard_name' => 'web']);
        $managerRole = Role::create(['name' => 'Quản lý', 'guard_name' => 'web']);
        $staffRole = Role::create(['name' => 'Nhân viên', 'guard_name' => 'web']);
        $customerRole = Role::create(['name' => 'Khách hàng', 'guard_name' => 'web']);
        $cinemaManagerRole = Role::create(['name' => 'Quản lý phòng chiếu', 'guard_name' => 'web']);

        // 1. Quản trị viên: Nhận toàn quyền tối cao hệ thống
        $adminRole->syncPermissions(array_values($createdPermissions));

        // 2. Quản lý hệ thống
        $systemManagerRole->syncPermissions([
            $createdPermissions['quan_ly_cau_hinh_he_thong'],
            $createdPermissions['quan_ly_thong_bao_day'],
            $createdPermissions['quan_ly_lien_he'], // 🌟 Thêm quyền liên hệ
            $createdPermissions['thong_ke_doanh_thu'],
            $createdPermissions['quan_ly_ca_lam'],
            $createdPermissions['quan_ly_phim_suat_chieu'],
            $createdPermissions['quan_ly_phong_ghe'],
            $createdPermissions['ban_ve_tai_quay'],
            $createdPermissions['quan_ly_do_an_combo'],
            $createdPermissions['soat_ve_vao_cua'],
            $createdPermissions['khach_hang_dat_ve'],
            $createdPermissions['khach_hang_xem_lich_su'],
            $createdPermissions['khach_hang_huy_ve'],
            $createdPermissions['xem_nhat_ky_hoat_dong'],
        ]);

        // 3. Quản lý rạp
        $managerRole->syncPermissions([
            $createdPermissions['quan_ly_nhan_vien'],
            $createdPermissions['quan_ly_khach_hang'],
            $createdPermissions['quan_ly_lien_he'], // 🌟 Thêm quyền liên hệ
            $createdPermissions['thong_ke_doanh_thu'],
            $createdPermissions['quan_ly_ca_lam'],
            $createdPermissions['quan_ly_phim_suat_chieu'],
            $createdPermissions['quan_ly_phong_ghe'],
            $createdPermissions['ban_ve_tai_quay'],
            $createdPermissions['quan_ly_do_an_combo'],
            $createdPermissions['soat_ve_vao_cua'],
            $createdPermissions['khach_hang_dat_ve'],
            $createdPermissions['khach_hang_xem_lich_su'],
            $createdPermissions['khach_hang_huy_ve'],
        ]);

        // 4. Nhân viên quầy
        $staffRole->syncPermissions([
            $createdPermissions['ban_ve_tai_quay'],
            $createdPermissions['quan_ly_do_an_combo'],
            $createdPermissions['soat_ve_vao_cua'],
        ]);

        // 5. Khách hàng
        $customerRole->syncPermissions([
            $createdPermissions['khach_hang_dat_ve'],
            $createdPermissions['khach_hang_xem_lich_su'],
            $createdPermissions['khach_hang_huy_ve'],
        ]);

        // 6. Quản lý phòng chiếu
        $cinemaManagerRole->syncPermissions([
            $createdPermissions['quan_ly_phim_suat_chieu'],
            $createdPermissions['quan_ly_phong_ghe'],
            $createdPermissions['ban_ve_tai_quay'],
            $createdPermissions['quan_ly_do_an_combo'],
            $createdPermissions['soat_ve_vao_cua'],
            $createdPermissions['quan_ly_nhan_vien'],
            $createdPermissions['quan_ly_khach_hang'],
            $createdPermissions['quan_ly_lien_he'], // 🌟 Thêm quyền liên hệ
            $createdPermissions['thong_ke_doanh_thu'],
        ]);
    }
}