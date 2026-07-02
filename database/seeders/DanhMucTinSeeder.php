<?php

namespace Database\Seeders;

use App\Models\DanhMucTin;
use Illuminate\Database\Seeder;

class DanhMucTinSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'ten_danh_muc' => 'Tin tức phim',
                'slug' => 'tin-tuc-phim',
                'mo_ta' => 'Cập nhật tin tức, tin Hollywood, trailer và các thông tin mới nhất về phim ảnh',
                'icon' => 'fa-solid fa-film',
                'mau_sac' => '#d99a32',
                'thu_tu' => 1,
                'trang_thai' => true,
            ],
            [
                'ten_danh_muc' => 'Sự kiện',
                'slug' => 'su-kien',
                'mo_ta' => 'Thông tin về các sự kiện, lễ hội phim và hoạt động tại CineHome',
                'icon' => 'fa-solid fa-calendar-star',
                'mau_sac' => '#e74c3c',
                'thu_tu' => 2,
                'trang_thai' => true,
            ],
            [
                'ten_danh_muc' => 'Khuyến mãi',
                'slug' => 'khuyen-mai',
                'mo_ta' => 'Các chương trình khuyến mãi đặc biệt dành cho khách hàng',
                'icon' => 'fa-solid fa-tags',
                'mau_sac' => '#9b59b6',
                'thu_tu' => 3,
                'trang_thai' => true,
            ],
            [
                'ten_danh_muc' => 'Review phim',
                'slug' => 'review-phim',
                'mo_ta' => 'Bài viết review, đánh giá chi tiết về các bộ phim hot',
                'icon' => 'fa-solid fa-star',
                'mau_sac' => '#f39c12',
                'thu_tu' => 4,
                'trang_thai' => true,
            ],
            [
                'ten_danh_muc' => 'Hướng dẫn',
                'slug' => 'huong-dan',
                'mo_ta' => 'Hướng dẫn sử dụng dịch vụ, mẹo đặt vé và thông tin rạp chiếu',
                'icon' => 'fa-solid fa-circle-question',
                'mau_sac' => '#3498db',
                'thu_tu' => 5,
                'trang_thai' => true,
            ],
        ];

        foreach ($categories as $category) {
            DanhMucTin::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
