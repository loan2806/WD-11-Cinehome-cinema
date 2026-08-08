<?php

namespace Database\Seeders;

use App\Models\NguoiDung;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Điều phối toàn bộ quy trình khởi tạo cơ sở dữ liệu gốc CineHome
     */
    public function run(): void
    {
        // Ép xóa sạch cache quyền trước khi chèn dữ liệu để tránh kẹt cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->call([
            // 🌟 1. Phân quyền & Cài đặt hệ thống gốc
            VaiTroAndQuyenSeeder::class,
            CaiDatHeThongSeeder::class,

            // 🌟 2. Danh mục tham chiếu gốc
            QuocGiaSeeder::class,
            TheLoaiSeeder::class,
            LoaiGheSeeder::class,
            DanhMucTinSeeder::class,

            // 🌟 3. Tài khoản hệ thống & Nhân sự
            TaiKhoanSeeder::class,
            NhanVienSeeder::class,
            TinTucSeeder::class,

            // 🌟 4. Cơ sở vật chất rạp & phòng chiếu
            RapChieuPhimSeeder::class,
            PhongChieuSeeder::class,

            // 🌟 5. Cấu hình Menu Đồ ăn / Combo quầy
            FoodCategorySeeder::class,
            FoodSeeder::class,
            FoodVariantSeeder::class,
            ComboFakeSeeder::class,
        ]);

        // Đồng bộ vai trò Spatie cho các tài khoản mặc định
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $admins = NguoiDung::where('vai_tro', 'admin')->orWhere('email', 'like', '%admin%')->get();
        foreach ($admins as $admin) { 
            $admin->assignRole('Quản trị viên'); 
        }

        $managers = NguoiDung::where('vai_tro', 'quan_ly')->orWhere('vai_tro', 'manager')->get();
        foreach ($managers as $manager) { 
            if ($manager->email !== 'cinemamanager@cinehome.vn') {
                $manager->assignRole('Quản lý'); 
            }
        }

        $staffs = NguoiDung::where('vai_tro', 'nhan_vien')->orWhere('vai_tro', 'staff')->get();
        foreach ($staffs as $staff) { 
            $staff->assignRole('Nhân viên'); 
        }

        // Tạo tài khoản Quản lý phòng chiếu gốc
        $this->call(CinemaManagerSeeder::class);
    }
}