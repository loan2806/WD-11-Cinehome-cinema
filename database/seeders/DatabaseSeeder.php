<?php

namespace Database\Seeders;

use App\Models\NguoiDung;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Điều phối toàn bộ quy trình khởi tạo cơ sở dữ liệu mẫu CineHome
     */
    public function run(): void
    {
        // Ép xóa sạch cache quyền trước khi chèn dữ liệu để tránh hiện tượng kẹt cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->call([
            VaiTroAndQuyenSeeder::class,
            QuocGiaSeeder::class,
            TheLoaiSeeder::class,
            PhimSeeder::class,
            TaiKhoanSeeder::class,
            LoaiGheSeeder::class,
            RapChieuPhimSeeder::class,
            PhongChieuSeeder::class,
            SuatChieuSeeder::class,
            VeXemPhimSeeder::class,
            NhanVienSeeder::class,
            CaiDatHeThongSeeder::class,
            FoodSeeder::class,
            DanhMucTinSeeder::class,
            TinTucSeeder::class,
            VoucherSeeder::class,
        ]);

        // Đồng bộ vai trò Spatie cho tài khoản
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $admins = NguoiDung::where('vai_tro', 'admin')->orWhere('email', 'like', '%admin%')->get();
        foreach ($admins as $admin) { $admin->assignRole('Quản trị viên'); }
        $managers = NguoiDung::where('vai_tro', 'quan_ly')->orWhere('vai_tro', 'manager')->get();
        foreach ($managers as $manager) { $manager->assignRole('Quản lý'); }
        $staffs = NguoiDung::where('vai_tro', 'nhan_vien')->orWhere('vai_tro', 'staff')->get();
        foreach ($staffs as $staff) { $staff->assignRole('Nhân viên'); }
    }
}
