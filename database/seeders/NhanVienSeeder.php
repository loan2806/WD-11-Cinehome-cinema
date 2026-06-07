<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        NguoiDung::create([
            'ho_ten' => 'Nhân Viên 01',
            'email' => 'staff@gmail.com',
            'mat_khau' => Hash::make('123456'),
            'vai_tro' => 'nhan_vien',
            'trang_thai_hoat_dong' => true,
        ]);
    }
}
