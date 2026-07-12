<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NguoiDung;
use App\Models\RapChieuPhim;

class CapNhatDuLieuNhanVienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy chi nhánh đầu tiên trong database
        $rap = RapChieuPhim::first();
        
        if (!$rap) {
            // Nếu chưa có rạp nào, tạo một rạp mặc định để test
            $rap = RapChieuPhim::create([
                'ten_rap' => 'Cinehome Cinema Mặc Định',
                'dia_chi' => '123 Nguyễn Văn Linh, Quận 7, TP.HCM',
                'thanh_pho' => 'TP. Hồ Chí Minh',
                'so_dien_thoai' => '028 1234 5678',
                'vi_do' => 10.7290257,
                'kinh_do' => 106.6968571,
            ]);
        }

        // Cập nhật Sub-Admin (Quản lý chi nhánh)
        $admin = NguoiDung::where('email', 'admin@cinehome.vn')->first();
        if ($admin) {
            $admin->update([
                'rap_chieu_phim_id' => $rap->id,
                'luong_co_ban' => 15000000.00,
            ]);
        }

        // Cập nhật Nhân viên quầy 1
        $staff1 = NguoiDung::where('email', 'staff@cinehome.vn')->first();
        if ($staff1) {
            $staff1->update([
                'rap_chieu_phim_id' => $rap->id,
                'luong_co_ban' => 7000000.00,
            ]);
        }

        // Cập nhật Nhân viên quầy 2
        $staff2 = NguoiDung::where('email', 'staff@gmail.com')->first();
        if ($staff2) {
            $staff2->update([
                'rap_chieu_phim_id' => $rap->id,
                'luong_co_ban' => 6500000.00,
            ]);
        }
    }
}
