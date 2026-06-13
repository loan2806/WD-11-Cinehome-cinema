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
        ]);

        // --- TỰ ĐỘNG ĐỒNG BỘ VAI TRÒ SPATIE CHO TẤT CẢ TÀI KHOẢN VỪA TẠO ---
        
        // 1. Quét và gán quyền Quản trị viên cho những tài khoản là admin
        $admins = NguoiDung::where('vai_tro', 'admin')->orWhere('email', 'like', '%admin%')->get();
        foreach ($admins as $admin) {
            $admin->assignRole('Quản trị viên');
        }

        // 2. Quét và gán quyền Quản lý cho những tài khoản quản lý rạp
        $managers = NguoiDung::where('vai_tro', 'quan_ly')->orWhere('vai_tro', 'manager')->get();
        foreach ($managers as $manager) {
            $manager->assignRole('Quản lý');
        }

        // 3. Quét và gán quyền Nhân viên cho những tài khoản nhân viên quầy bán vé
        $staffs = NguoiDung::where('vai_tro', 'nhan_vien')->orWhere('vai_tro', 'staff')->get();
        foreach ($staffs as $staff) {
            $staff->assignRole('Nhân viên');
        }

        // Ép buộc xóa sạch bộ nhớ đệm phân quyền một lần nữa để đồng bộ ngay lập tức
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}