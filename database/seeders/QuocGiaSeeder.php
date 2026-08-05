<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QuocGia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QuocGiaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {   
        // Bỏ qua khóa ngoại để tránh lỗi ràng buộc khi xóa
    Schema::disableForeignKeyConstraints();
    DB::table('quoc_gias')->truncate(); // Đổi 'quoc_gias' thành đúng tên bảng của bạn
    Schema::enableForeignKeyConstraints();
        $countries = [
            ['ten_quoc_gia' => 'Việt Nam', 'ma_quoc_gia' => 'VN', 'trang_thai' => 1],
            ['ten_quoc_gia' => 'Mỹ', 'ma_quoc_gia' => 'US', 'trang_thai' => 1],
            ['ten_quoc_gia' => 'Anh', 'ma_quoc_gia' => 'GB', 'trang_thai' => 1],
            ['ten_quoc_gia' => 'Nhật Bản', 'ma_quoc_gia' => 'JP', 'trang_thai' => 1],
            ['ten_quoc_gia' => 'Hàn Quốc', 'ma_quoc_gia' => 'KR', 'trang_thai' => 1],
            ['ten_quoc_gia' => 'Trung Quốc', 'ma_quoc_gia' => 'CN', 'trang_thai' => 1],
            ['ten_quoc_gia' => 'Pháp', 'ma_quoc_gia' => 'FR', 'trang_thai' => 1],
            ['ten_quoc_gia' => 'Đức', 'ma_quoc_gia' => 'DE', 'trang_thai' => 1],
            ['ten_quoc_gia' => 'Úc', 'ma_quoc_gia' => 'AU', 'trang_thai' => 1],
            ['ten_quoc_gia' => 'Ấn Độ', 'ma_quoc_gia' => 'IN', 'trang_thai' => 1],
        ];

            // Use upsert to avoid duplicate key errors when seeding multiple times
            QuocGia::upsert($countries, ['ma_quoc_gia'], ['ten_quoc_gia', 'trang_thai']);
    }
}
