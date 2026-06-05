<?php

namespace Database\Seeders;

use App\Models\NguoiDung;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TaiKhoanSeeder extends Seeder
{
    public function run(): void
    {
        NguoiDung::updateOrCreate(
            ['email' => 'admin@cinehome.vn'],
            [
                'ho_ten' => 'Admin CineHome', // Thay cho 'name'
                'mat_khau' => Hash::make('12345678'), // Thay cho 'password'
                'vai_tro' => 'admin', // Thay cho 'role' (Hãy điều chỉnh lại nếu trong migration vai trò bạn dùng chữ 'quan_tri')
                'trang_thai_hoat_dong' => true, // Thay cho 'is_active'
            ]
        );

        NguoiDung::updateOrCreate(
            ['email' => 'staff@cinehome.vn'],
            [
                'ho_ten' => 'Staff CineHome',
                'mat_khau' => Hash::make('12345678'),
                'vai_tro' => 'nhan_vien', // Thay cho 'staff' để đồng bộ tiếng Việt
                'trang_thai_hoat_dong' => true,
            ]
        );

        NguoiDung::updateOrCreate(
            ['email' => 'user@cinehome.vn'],
            [
                'ho_ten' => 'User CineHome',
                'mat_khau' => Hash::make('12345678'),
                'vai_tro' => 'khach_hang', // Thay cho 'user' (Khớp hoàn toàn với giá trị mặc định ở NguoiDungFactory)
                'trang_thai_hoat_dong' => true,
            ]
        );
    }
}