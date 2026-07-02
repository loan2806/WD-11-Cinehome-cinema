<?php

namespace Database\Seeders;

use App\Models\DanhMucTin;
use App\Models\TinTuc;
use Illuminate\Database\Seeder;

class TinTucSeeder extends Seeder
{
    public function run(): void
    {
        $danhMucPhim = DanhMucTin::where('slug', 'tin-tuc-phim')->first();
        $danhMucSuKien = DanhMucTin::where('slug', 'su-kien')->first();
        $danhMucKhuyenMai = DanhMucTin::where('slug', 'khuyen-mai')->first();
        $danhMucReview = DanhMucTin::where('slug', 'review-phim')->first();
        $danhMucHuongDan = DanhMucTin::where('slug', 'huong-dan')->first();

        $tinTucs = [
            // Tin tức phim
            [
                'danh_muc_tin_id' => $danhMucPhim?->id,
                'tieu_de' => 'Avengers: Doomsday công bố ngày khởi chiếu chính thức',
                'slug' => 'avengers-doomsday-ngay-khoi-chieu',
                'mo_ta_ngan' => 'Marvel Studios chính thức công bố ngày ra mắt Avengers: Doomsday với sự trở lại của nhiều siêu sao.',
                'noi_dung' => 'Marvel Studios vừa chính thức công bố ngày khởi chiếu của Avengers: Doomsday, bộ phim được mong đợi nhất năm 2026.

Theo thông báo, Avengers: Doomsday sẽ chính thức ra rạp vào ngày 7 tháng 5 năm 2026 tại tất cả các rạp chiếu phim trên toàn quốc.

Bộ phim đánh dấu sự trở lại của Robert Downey Jr. trong vai Doctor Doom, cùng với sự tham gia của dàn diễn viên từ các phần phim Avengers trước đó.

Đạo diễn Shawn Levy hứa hẹn sẽ mang đến một trải nghiệm điện ảnh đỉnh cao với những cảnh hành động hoành tráng và cốt truyện đầy bất ngờ.

Đặt vé sớm ngay hôm nay để không bỏ lỡ cơ hội xem phim đầu tiên!',
                'hinh_anh' => 'images/news/avengers-doomsday.jpg',
                'noi_bat' => true,
                'trang_thai' => true,
                'tac_gia' => 'CineHome',
                'luot_xem' => 1250,
                'ngay_dang' => now()->subDays(2),
            ],
            [
                'danh_muc_tin_id' => $danhMucPhim?->id,
                'tieu_de' => 'Top 10 bộ phim kinh dị được mong đợi nhất năm 2026',
                'slug' => 'top-10-phim-kinh-di-2026',
                'mo_ta_ngan' => 'Danh sách những bộ phim kinh dị hứa hẹn sẽ khiến bạn rùng mình trong năm 2026.',
                'noi_dung' => 'Năm 2026 hứa hẹn sẽ là một năm đáng nhớ cho các tín đồ phim kinh dị. Dưới đây là top 10 bộ phim được mong đợi nhất:

1. The Conjuring 4 - Quỷ dữ trở lại với những bí ẩn chưa được giải đáp
2. IT Chapter Three - Phần cuối của câu chuyện về con clown đáng sợ
3. Silent Hill 3 - Live-action được chuyển thể từ game kinh điển
4. The Grudge Remake - Tái hiện lại nỗi kinh hoàng từ Nhật Bản
5. A Quiet Place: Day One - Tiền truyện của thương hiệu thành công nhất
6. Insidious: Beyond the Door - Phần tiếp theo của franchise kinh dị
7. The Nun III - Tiếp nối câu chuyện về nun xấu số
8. Annabelle 3 - Kết thúc câu chuyện về con búp bê quỷ quái
9. Saw XI - Phần mới nhất của thương hiệu khủng long
10. The Babadook 2 - Trở lại với ác梦',

                'hinh_anh' => 'images/news/horror-2026.jpg',
                'noi_bat' => false,
                'trang_thai' => true,
                'tac_gia' => 'CineHome',
                'luot_xem' => 890,
                'ngay_dang' => now()->subDays(5),
            ],
            // Sự kiện
            [
                'danh_muc_tin_id' => $danhMucSuKien?->id,
                'tieu_de' => 'Lễ hội phim quốc tế CineHome 2026',
                'slug' => 'le-hoi-phim-quoc-te-cinehome-2026',
                'mo_ta_ngan' => 'Tham gia Lễ hội phim quốc tế CineHome 2026 để trải nghiệm những bộ phim đặc sắc từ khắp thế giới.',
                'noi_dung' => 'Lễ hội phim quốc tế CineHome 2026 sẽ diễn ra từ ngày 15 đến 30 tháng 6 năm 2026 tại tất cả các cụm rạp CineHome trên toàn quốc.

Những điểm nổi bật của lễ hội:

- Chiếu phim đặc sắc từ 20 quốc gia
- Gặp gỡ đạo diễn và diễn viên quốc tế
- Workshop làm phim cho sinh viên
- Cuộc thi phim ngắn dành cho tài năng trẻ
- Ưu đãi đặc biệt: Giảm 50% vé cho sinh viên

Đặc biệt, tại CineHome TP.HCM sẽ có buổi chiếu ra mắt độc quyền bộ phim đoạt giải Palme d\'Or tại Cannes 2026.

Đăng ký tham gia ngay hôm nay!',
                'hinh_anh' => 'images/news/festival-2026.jpg',
                'noi_bat' => true,
                'trang_thai' => true,
                'tac_gia' => 'CineHome Events',
                'luot_xem' => 2100,
                'ngay_dang' => now()->subDays(1),
            ],
            // Khuyến mãi
            [
                'danh_muc_tin_id' => $danhMucKhuyenMai?->id,
                'tieu_de' => 'Thứ 3 vui vẻ - Giảm 50% mọi loại vé',
                'slug' => 'thu-3-vui-ve-giam-50',
                'mo_ta_ngan' => 'Mỗi thứ 3 hàng tuần, hãy đến CineHome để được giảm 50% giá vé xem phim!',
                'noi_dung' => 'Chương trình "Thứ 3 vui vẻ" đã chính thức trở lại!

Nội dung chương trình:
- Thứ 3 hàng tuần: Giảm 50% giá vé tất cả các suất chiếu
- Không giới hạn số lượng vé
- Áp dụng cho tất cả các phim đang chiếu
- Kết hợp được với thẻ thành viên CineHome

Điều kiện:
- Chỉ áp dụng vào thứ 3 hàng tuần
- Vé đã mua không được hoàn tiền hoặc đổi
- Số lượng vé có hạn tùy theo từng rạp

Đừng bỏ lỡ cơ hội xem phim với giá chỉ từ 45.000đ!',
                'hinh_anh' => 'images/news/tuesday-deal.jpg',
                'noi_bat' => true,
                'trang_thai' => true,
                'tac_gia' => 'CineHome',
                'luot_xem' => 3500,
                'ngay_dang' => now(),
            ],
            [
                'danh_muc_tin_id' => $danhMucKhuyenMai?->id,
                'tieu_de' => 'Combo bắp nước siêu tiết kiệm - Tiết kiệm đến 35%',
                'slug' => 'combo-bap-nuoc-sieu-tiet-kiem',
                'mo_ta_ngan' => 'Mua combo bắp nước cỡ lớn, tiết kiệm đến 35% so với mua lẻ. Áp dụng mọi ngày!',
                'noi_dung' => 'Combo bắp nước siêu tiết kiệm đã có mặt tại tất cả các rạp CineHome!

Các loại combo:

1. COMBO CƠ BẢN - 79.000đ
   - 1 bắp rang cỡ M (150g)
   - 1 nước ngọt cỡ M

2. COMBO GIA ĐÌNH - 159.000đ
   - 2 bắp rang cỡ L (200g)
   - 2 nước ngọt cỡ M
   - 1 snack hoặc kẹo

3. COMBO PREMIUM - 199.000đ
   - 1 bắp rang cỡ XL (300g)
   - 2 nước ngọt cỡ M
   - 2 kem ốc quế
   - 1 snack premium

Tiết kiệm đến 35% khi mua combo thay vì mua lẻ!',
                'hinh_anh' => 'images/news/combo-deal.jpg',
                'noi_bat' => false,
                'trang_thai' => true,
                'tac_gia' => 'CineHome F&B',
                'luot_xem' => 1800,
                'ngay_dang' => now()->subDays(3),
            ],
            // Review phim
            [
                'danh_muc_tin_id' => $danhMucReview?->id,
                'tieu_de' => 'Review: Dune 3 - Kỷ nguyên mới của điện ảnh khoa học viễn tưởng',
                'slug' => 'review-dune-3',
                'mo_ta_ngan' => 'Dune 3 tiếp tục chuỗi thành công của franchise với hình ảnh hoành tráng và cốt truyện sâu sắc.',
                'noi_dung' => 'Điểm phim: 9.5/10

Denis Villeneuve một lần nữa chứng minh tài năng đạo diễn của mình với Dune 3 - phần phim kết thúc câu chuyện về Paul Atreides.

ĐIỂM MẠNH:
- Hình ảnh: Thiết kế production xuất sắc, những cảnh desert shot khiến người xem choáng ngợp
- Âm nhạc: Hans Zimmer tiếp tục sáng tác nhạc nền điệu nghệ, góp phần tạo nên không khí căng thẳng
- Diễn xuất: Timothée Chalamet thể hiện xuất sắc sự trưởng thành của nhân vật
- Cốt truyện: Kết thúc hợp lý, không quá kéo dài

ĐIỂM YẾU:
- Một số phân đoạn hành động có phần kéo dài
- Nhịp phim có chỗ hơi chậm

KẾT LUẬN:
Dune 3 là một tuyệt tác điện ảnh, xứng đáng là phần kết hoàn hảo cho trilogy. Đây là bộ phim bạn không nên bỏ lỡ trên màn ảnh rộng.',
                'hinh_anh' => 'images/news/dune3-review.jpg',
                'noi_bat' => false,
                'trang_thai' => true,
                'tac_gia' => 'Reviewer CineHome',
                'luot_xem' => 4200,
                'ngay_dang' => now()->subDays(4),
            ],
            // Hướng dẫn
            [
                'danh_muc_tin_id' => $danhMucHuongDan?->id,
                'tieu_de' => 'Hướng dẫn đặt vé online tại CineHome',
                'slug' => 'huong-dan-dat-ve-online',
                'mo_ta_ngan' => 'Từng bước đặt vé xem phim online dễ dàng và nhanh chóng tại CineHome.',
                'noi_dung' => 'Hướng dẫn đặt vé online tại CineHome:

BƯỚC 1: Đăng nhập/Đăng ký
- Truy cập website CineHome hoặc tải ứng dụng
- Đăng nhập hoặc tạo tài khoản mới

BƯỚC 2: Chọn phim và suất chiếu
- Chọn phim bạn muốn xem
- Chọn rạp CineHome gần bạn
- Chọn ngày và suất chiếu phù hợp

BƯỚC 3: Chọn ghế
- Xem sơ đồ phòng chiếu
- Chọn ghế trống (ghế đã bán sẽ hiển thị màu xám)
- Ưu tiên ghế giữa và ghế cao để có trải nghiệm tốt nhất

BƯỚC 4: Chọn combo (tùy chọn)
- Thêm bắp nước hoặc snack nếu cần

BƯỚC 5: Thanh toán
- Chọn phương thức thanh toán (VNPay, MoMo, ZaloPay, thẻ ATM)
- Nhập mã voucher nếu có
- Xác nhận thanh toán

BƯỚC 6: Nhận vé
- Vé điện tử sẽ được gửi qua email và SMS
- Đến rạp, quét mã QR tại quầy để nhận vé

MẸO:
- Đặt vé sớm để có nhiều lựa chọn ghế
- Theo dõi trang khuyến mãi để nhận voucher giảm giá
- Đăng ký thẻ thành viên để tích điểm và nhận ưu đãi',
                'hinh_anh' => 'images/news/guide-booking.jpg',
                'noi_bat' => false,
                'trang_thai' => true,
                'tac_gia' => 'CineHome',
                'luot_xem' => 5600,
                'ngay_dang' => now()->subDays(7),
            ],
        ];

        foreach ($tinTucs as $tinTuc) {
            TinTuc::updateOrCreate(
                ['slug' => $tinTuc['slug']],
                $tinTuc
            );
        }
    }
}
