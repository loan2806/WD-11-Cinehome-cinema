<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Phims;
use App\Models\TheLoai;
use App\Models\QuocGia;
use App\Models\SuatChieu;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PhimsController extends Controller
{
    /**
     * Lấy trạng thái của phim dựa trên lịch chiếu
     */
    private function getStatus($movie)
    {
        $now = now('Asia/Ho_Chi_Minh');
        $oneMonthLater = $now->copy()->addMonth();

        $showtimes = $movie->showtimes
            ->whereNotNull('thoi_gian_chieu')
            ->sortBy('thoi_gian_chieu');

        if ($showtimes->isEmpty()) {
            return SuatChieu::TRANG_THAI_SAP_RA_MAT;
        }

        // Kiểm tra xem đã có suất chiếu nào bắt đầu/đang chạy chưa
        $hasStartedShowtime = $showtimes->contains(function ($st) use ($now) {
            return Carbon::parse($st->thoi_gian_chieu)->lte($now);
        });

        // Kiểm tra xem vẫn còn suất chiếu chưa kết thúc
        $hasUpcomingOrCurrentShowtime = $showtimes->contains(function ($st) use ($now, $movie) {
            $start = Carbon::parse($st->thoi_gian_chieu);
            $end = $st->thoi_gian_ket_thuc 
                ? Carbon::parse($st->thoi_gian_ket_thuc) 
                : $start->copy()->addMinutes((int)($movie->thoi_luong ?? 90));
            return $end->gte($now);
        });

        // 1. ĐANG CHIẾU: Đã có suất chiếu khởi chạy VÀ vẫn còn suất chiếu chưa kết thúc
        if ($hasStartedShowtime && $hasUpcomingOrCurrentShowtime) {
            return SuatChieu::TRANG_THAI_DANG_CHIEU;
        }

        // 2. ĐÃ CHIẾU: Tất cả suất chiếu đã kết thúc trong quá khứ
        if (!$hasUpcomingOrCurrentShowtime) {
            return SuatChieu::TRANG_THAI_DA_CHIEU;
        }

        // 3. CHƯA CHIẾU: Kiểm tra thời gian của suất chiếu sớm nhất trong tương lai
        $firstFutureShowtime = $showtimes->first(function ($st) use ($now) {
            return Carbon::parse($st->thoi_gian_chieu)->gt($now);
        });

        if ($firstFutureShowtime) {
            $firstStartTime = Carbon::parse($firstFutureShowtime->thoi_gian_chieu);

            // Suất chiếu nằm trong vòng 1 tháng tới -> SẮP CHIẾU
            if ($firstStartTime->lte($oneMonthLater)) {
                return SuatChieu::TRANG_THAI_SAP_CHIEU;
            }

            // Suất chiếu từ 1 tháng sau trở đi -> SẮP RA MẮT
            return SuatChieu::TRANG_THAI_SAP_RA_MAT;
        }

        return SuatChieu::TRANG_THAI_SAP_RA_MAT;
    }

    /*
    |--------------------------------------------------------------------------
    | HOME PAGE
    |--------------------------------------------------------------------------
    */
    public function home()
    {
        $now = Carbon::now('Asia/Ho_Chi_Minh');
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $movies = Phims::with([
            'showtimes',
            'genres',
            'country'
        ])
            ->visibleToUsers()
            ->orderBy('created_at', 'desc')
            ->get();

        $nowShowingMovies = $movies->filter(
            fn($movie) => $this->getStatus($movie) === SuatChieu::TRANG_THAI_DANG_CHIEU
        );

        $comingSoonMovies = $movies->filter(
            fn($movie) => $this->getStatus($movie) === SuatChieu::TRANG_THAI_SAP_CHIEU
        );

        $comingLaterMovies = $movies->filter(
            fn($movie) => $this->getStatus($movie) === SuatChieu::TRANG_THAI_SAP_RA_MAT
        );

        $bannerMovies = $nowShowingMovies->take(5);

        // 🌟 LẤY TOP 3 PHIM HOT CỦA THÁNG (TỰ ĐỘNG LÀM MỚI THEO THÁNG HIỆN TẠI)
        $hotMovies = Phims::with(['genres', 'country', 'showtimes'])
            ->visibleToUsers()
            ->withCount(['showtimes as tong_ve_thang' => function ($query) use ($startOfMonth, $endOfMonth) {
                $query->join('ve_xem_phims', 'suat_chieus.id', '=', 've_xem_phims.suat_chieu_id')
                      ->whereIn('ve_xem_phims.trang_thai', ['da_thanh_toan', 'da_su_dung', 'cho_thanh_toan', 'da_dat'])
                      ->whereBetween('ve_xem_phims.created_at', [$startOfMonth, $endOfMonth]);
            }])
            ->orderByDesc('tong_ve_thang')
            ->take(3)
            ->get();

        $tenThangHienTai = $now->format('m/Y');

        return view(
            'user.home',
            compact(
                'bannerMovies',
                'nowShowingMovies',
                'comingSoonMovies',
                'comingLaterMovies',
                'hotMovies',
                'tenThangHienTai'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | MOVIE LIST
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = Phims::with([
            'showtimes',
            'genres',
            'country'
        ])->visibleToUsers();

        // SEARCH
        if ($request->filled('tim_kiem')) {
            $query->where('ten_phim', 'like', '%' . $request->tim_kiem . '%');
        }

        if ($request->filled('the_loai')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('ten_the_loai', $request->the_loai);
            });
        }

        if ($request->filled('quoc_gia')) {
            $query->whereHas('country', function ($q) use ($request) {
                $q->where('ten_quoc_gia', $request->quoc_gia);
            });
        }

        $movies = $query->orderBy('created_at', 'desc')->get();

        // Gán trạng thái đã được tính toán đồng bộ cho từng phim
        foreach ($movies as $movie) {
            $movie->calculated_status = $this->getStatus($movie);
        }

        // LỌC THEO TRẠNG THÁI
        if ($request->filled('status')) {
            $movies = $movies->filter(
                fn($movie) => $movie->calculated_status === $request->status
            );
        } else {
            // Mặc định ẩn các phim đã kết thúc chiếu
            $movies = $movies->filter(
                fn($movie) => $movie->calculated_status !== SuatChieu::TRANG_THAI_DA_CHIEU
            );
        }

        $genres = TheLoai::where('trang_thai', 1)->get();
        $countries = QuocGia::where('trang_thai', 1)->get();

        return view('user.phims.index', compact(
            'movies',
            'genres',
            'countries'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | MOVIE DETAIL
    |--------------------------------------------------------------------------
    */
    public function show(Phims $movie)
    {
        $now = Carbon::now('Asia/Ho_Chi_Minh');
        
        // Mốc thời gian giới hạn: Thời gian hiện tại + 5 phút
        $bookingCutoff = $now->copy()->addMinutes(5);

        $showtimes = SuatChieu::with([
            'rapChieuPhim',
            'phongChieu',
            'phim'
        ])
            ->where('phim_id', $movie->id)
            ->orderBy('thoi_gian_chieu')
            ->get()
            ->filter(function ($showtime) use ($bookingCutoff) {

                if (!$showtime->thoi_gian_chieu) {
                    return false;
                }

                $start = Carbon::parse($showtime->thoi_gian_chieu);

                // 🎬 CHỈ GIỮ LẠI CÁC SUẤT CHIẾU CÓ THỜI GIAN BẮT ĐẦU > (HIỆN TẠI + 5 PHÚT)
                return $start->gt($bookingCutoff);
            });

        $relatedMovies = Phims::with(['genres', 'country', 'showtimes'])
            ->where('id', '!=', $movie->id)
            ->whereHas('genres', function ($query) use ($movie) {
                $query->whereIn('id', $movie->genres->pluck('id'));
            })
            ->visibleToUsers()
            ->distinct()
            ->take(6)
            ->get();

        $status = $this->getStatus($movie);

        return view('user.phims.show', compact(
            'movie',
            'showtimes',
            'now',
            'relatedMovies',
            'status'
        ));
    }
}