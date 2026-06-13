<?php

namespace Database\Seeders;

use App\Models\NguoiDung;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TaiKhoanSeeder extends Seeder
{
    public function run(): void
    {
        // Khởi tạo vai trò của Spatie để phục vụ rẽ nhánh phân hệ chuyên sâu
        $vaiTroHeThong = Role::findOrCreate('Quản lý hệ thống', 'web');

        // 1. Tài khoản Quản lý hệ thống tách biệt hoàn toàn
        // Giải pháp: Đặt 'vai_tro' là 'admin' để khớp cấu hình dữ liệu cũ, dùng Role Spatie để phân tách Dashboard
        $systemUser = NguoiDung::updateOrCreate(
            ['email' => 'system@cinehome.vn'],
            [
                'ho_ten' => 'Quản Lý Hệ Thống CineHome',
                'mat_khau' => Hash::make('12345678'),
                'vai_tro' => 'admin', 
                'trang_thai_hoat_dong' => true,
            ]
        );
        // Gán vai trò tối cao để điều hướng sang layout system.blade.php độc lập
        $systemUser->assignRole($vaiTroHeThong);

        // 2. Tài khoản Admin điều hành rạp thông thường (Sub-Admin)
        NguoiDung::updateOrCreate(
            ['email' => 'admin@cinehome.vn'],
            [
                'ho_ten' => 'Admin CineHome',
                'mat_khau' => Hash::make('12345678'),
                'vai_tro' => 'admin',
                'trang_thai_hoat_dong' => true,
            ]
        );

        // 3. Tài khoản Nhân viên tác nghiệp tại quầy rạp
        NguoiDung::updateOrCreate(
            ['email' => 'staff@cinehome.vn'],
            [
                'ho_ten' => 'Staff CineHome',
                'mat_khau' => Hash::make('12345678'),
                'vai_tro' => 'nhan_vien',
                'trang_thai_hoat_dong' => true,
            ]
        );

        // 4. Tài khoản Khách hàng mua vé trực tuyến công cộng
        NguoiDung::updateOrCreate(
            ['email' => 'user@cinehome.vn'],
            [
                'ho_ten' => 'User CineHome',
                'mat_khau' => Hash::make('12345678'),
                'vai_tro' => 'khach_hang',
                'trang_thai_hoat_dong' => true,
            ]
        );
    }
}