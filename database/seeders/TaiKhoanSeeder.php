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
        // Khởi tạo các Vai trò hệ thống của Spatie để khớp với Gate và Middleware
        $roleSystem = Role::findOrCreate('Quản lý hệ thống', 'web');
        $roleAdmin  = Role::findOrCreate('Quản trị viên', 'web');
        $roleStaff  = Role::findOrCreate('Nhân viên', 'web');
        $roleCinemaManager = Role::findOrCreate('Quản lý phòng chiếu', 'web');

        // 1. Tài khoản Quản lý hệ thống tách biệt
        $systemUser = NguoiDung::updateOrCreate(
            ['email' => 'system@cinehome.vn'],
            [
                'ho_ten' => 'Quản Lý Hệ Thống CineHome',
                'mat_khau' => Hash::make('12345678'),
                'vai_tro' => 'admin', 
                'trang_thai_hoat_dong' => true,
            ]
        );
        $systemUser->assignRole($roleSystem);

        // 2. Tài khoản Admin điều hành rạp
        $adminUser = NguoiDung::updateOrCreate(
            ['email' => 'admin@cinehome.vn'],
            [
                'ho_ten' => 'Admin CineHome',
                'mat_khau' => Hash::make('12345678'),
                'vai_tro' => 'admin',
                'trang_thai_hoat_dong' => true,
            ]
        );
        $adminUser->assignRole($roleAdmin);

        // 3. Tài khoản Quản lý phòng chiếu
        $cinemaManagerUser = NguoiDung::updateOrCreate(
            ['email' => 'cinemamanager@cinehome.vn'],
            [
                'ho_ten' => 'Quản lý phòng chiếu CineHome',
                'mat_khau' => Hash::make('12345678'),
                'vai_tro' => 'quan_ly',
                'trang_thai_hoat_dong' => true,
            ]
        );
        $cinemaManagerUser->assignRole($roleCinemaManager);

        // 4. Tài khoản Nhân viên quầy
        $staffUser = NguoiDung::updateOrCreate(
            ['email' => 'staff@cinehome.vn'],
            [
                'ho_ten' => 'Staff CineHome',
                'mat_khau' => Hash::make('12345678'),
                'vai_tro' => 'nhan_vien',
                'trang_thai_hoat_dong' => true,
            ]
        );
        $staffUser->assignRole($roleStaff);

        // 5. Tài khoản Khách hàng
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
