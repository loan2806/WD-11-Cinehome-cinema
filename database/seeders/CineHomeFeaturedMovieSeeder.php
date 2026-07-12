<?php

namespace Database\Seeders;

use App\Models\Phims;
use App\Models\PhongChieu;
use App\Models\QuocGia;
use App\Models\SuatChieu;
use App\Models\TheLoai;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CineHomeFeaturedMovieSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $country = QuocGia::where('ma_quoc_gia', 'VN')
                ->orWhere('ten_quoc_gia', 'Việt Nam')
                ->first();

            if (! $country) {
                $country = QuocGia::create([
                    'ten_quoc_gia' => 'Việt Nam',
                    'ma_quoc_gia' => 'VN',
                    'trang_thai' => 1,
                ]);
            }

            $genres = collect([
                ['ten_the_loai' => 'Hành động', 'slug' => 'hanh-dong'],
                ['ten_the_loai' => 'Phiêu lưu', 'slug' => 'phieu-luu'],
            ])->map(function (array $genre) {
                $model = TheLoai::where('slug', $genre['slug'])
                    ->orWhere('ten_the_loai', $genre['ten_the_loai'])
                    ->first();

                if (! $model) {
                    return TheLoai::create([
                        'ten_the_loai' => $genre['ten_the_loai'],
                        'slug' => $genre['slug'],
                        'mo_ta' => 'Thể loại nổi bật trên CineHome.',
                        'trang_thai' => 1,
                    ]);
                }

                $model->update([
                    'ten_the_loai' => $genre['ten_the_loai'],
                    'slug' => $genre['slug'],
                    'trang_thai' => 1,
                ]);

                return $model;
            });

            $posterFile = 'anh-sang-thanh-pho-cinehome.svg';
            $posterSource = public_path('assets/images/movie-posters/' . $posterFile);
            $posterTarget = storage_path('app/public/movies/' . $posterFile);

            if (File::exists($posterSource) && ! File::exists($posterTarget)) {
                File::ensureDirectoryExists(dirname($posterTarget));
                File::copy($posterSource, $posterTarget);
            }

            $movie = Phims::updateOrCreate(
                ['slug' => 'anh-sang-thanh-pho'],
                [
                    'ten_phim' => 'Ánh Sáng Thành Phố',
                    'mo_ta' => 'Một nhóm bạn trẻ vô tình phát hiện đường dây tội phạm công nghệ ẩn dưới ánh đèn của thành phố. Khi màn đêm buông xuống, họ phải chạy đua với thời gian để bảo vệ người thân và tìm lại niềm tin vào chính mình.',
                    'poster' => $posterFile,
                    'trailer' => 'https://www.youtube.com/watch?v=TcMBFSGVi1c',
                    'dao_dien' => 'Minh Khải',
                    'dien_vien' => 'Hoàng Duy, An Nhiên, Bảo Long, Linh Chi',
                    'ngon_ngu' => 'Tiếng Việt / Phụ đề tiếng Anh',
                    'thoi_luong' => 128,
                    'gioi_han_tuoi' => 'T13',
                    'quoc_gia_id' => $country->id,
                ]
            );

            $movie->genres()->syncWithoutDetaching($genres->pluck('id')->all());

            $room = PhongChieu::with('rapChieuPhim')
                ->where('trang_thai', 'hoat_dong')
                ->whereHas('rapChieuPhim')
                ->whereHas('gheNgois')
                ->first();

            if (! $room) {
                $room = PhongChieu::with('rapChieuPhim')
                    ->whereHas('rapChieuPhim')
                    ->first();
            }

            if (! $room) {
                $this->command?->warn('Chưa có phòng chiếu phù hợp để tạo suất chiếu cho Ánh Sáng Thành Phố.');
                return;
            }

            $starts = [
                Carbon::now('Asia/Ho_Chi_Minh')->addDay()->setTime(19, 30),
                Carbon::now('Asia/Ho_Chi_Minh')->addDays(2)->setTime(20, 15),
                Carbon::now('Asia/Ho_Chi_Minh')->addDays(3)->setTime(21, 0),
            ];

            foreach ($starts as $start) {
                SuatChieu::updateOrCreate(
                    [
                        'phim_id' => $movie->id,
                        'phong_chieu_id' => $room->id,
                        'thoi_gian_chieu' => $start,
                    ],
                    [
                        'rap_chieu_phim_id' => $room->rap_chieu_phim_id,
                        'thoi_luong' => $movie->thoi_luong,
                        'thoi_gian_ket_thuc' => $start->copy()->addMinutes($movie->thoi_luong + 15),
                        'gia_ve' => 95000,
                        'trang_thai' => SuatChieu::TRANG_THAI_SAP_CHIEU,
                    ]
                );
            }
        });
    }
}
