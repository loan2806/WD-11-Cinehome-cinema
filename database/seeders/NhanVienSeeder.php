<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\NguoiDung;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class NhanVienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Không dùng create() vì có thể gây lỗi duplicate dữ liệu khi seed lại.
        // updateOrCreate() đảm bảo dữ liệu hệ thống được cập nhật hoặc tạo mới an toàn.
        NguoiDung::updateOrCreate(

            [
                'email' => 'staff@gmail.com'
            ],
            [
                'ho_ten' => 'Nhân Viên 01',
                'mat_khau' => Hash::make('123456'),
                'vai_tro' => 'nhan_vien',
                'trang_thai_hoat_dong' => true,
            ]
        );
    }
}
