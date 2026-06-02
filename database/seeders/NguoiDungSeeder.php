<?php

namespace Database\Seeders;

use App\Models\NguoiDung;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo tài khoản mẫu sử dụng Model NguoiDung và các trường tiếng Việt
        NguoiDung::updateOrCreate(
            ['email' => 'staff@cinehome.vn'],
            [
                'ho_ten'             => 'Nhân Viên CineHome',
                'mat_khau'           => Hash::make('12345678'),
                'vai_tro'            => 'nhan_vien', // Theo cấu trúc enum đã sửa của bạn
                'trang_thai_hoat_dong'=> true,
            ]
        );
    }
}