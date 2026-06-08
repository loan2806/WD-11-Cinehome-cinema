<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Phims extends Model
{
    use HasFactory;

    protected $table = 'phims';

    protected $fillable = [
        'ten_phim',
        'slug',
        'mo_ta',
        'poster',
        'trailer',
        'quoc_gia_id',
        'dao_dien',
        'dien_vien',
        'ngon_ngu',
        'thoi_luong',
        'gioi_han_tuoi',
    ];

    protected $appends = [
        'schedule_status',
    ];

    /**
     * TỐI ƯU: Đổi sang hàm boot() tiêu chuẩn đảm bảo an toàn tuyệt đối
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($movie) {
            if (empty($movie->slug)) {
                $movie->slug = Str::slug($movie->ten_phim) . '-' . uniqid();
            }
        });
    }

    public function showtimes()
    {
        return $this->hasMany(SuatChieu::class, 'phim_id');
    }

    public function country()
    {
        return $this->belongsTo(QuocGia::class, 'quoc_gia_id');
    }

    /*
    |--------------------------------------------------------------------------
    | CẢNH BÁO LỖI TƯƠNG LAI Ở ĐÂY:
    |--------------------------------------------------------------------------
    | Khi bạn tiến hành Việt hóa bảng liên kết nhiều-nhiều giữa Phim và Thể Loại,
    | bạn bắt buộc phải sửa tên bảng trung gian 'movie_genre' thành tên bảng mới của bạn (Ví dụ: 'phim_the_loai')
    | và đổi các khóa ngoại 'movie_id', 'genre_id' thành 'phim_id', 'the_loai_id'.
    |*/
    public function genres()
    {
        return $this->belongsToMany(
            TheLoai::class,
            'phim_the_loai',
            'phim_id',
            'the_loai_id'
        );
    }

    public function scopeVisibleToUsers($query)
    {
        return $query->whereHas('showtimes');
    }

    public function scopeWithAvailableShowtimes($query)
    {
        return $query->whereHas('showtimes');
    }

    public function getScheduleStatusAttribute(): string
    {
        $now = now(config('app.timezone'));

        if ($this->showtimes->isEmpty()) {
            return 'Sắp ra mắt';
        }

        $minThoiGianChieu = $this->showtimes->min('thoi_gian_chieu');

        if (!$minThoiGianChieu) {
            return 'Sắp ra mắt';
        }

        $releaseDate = Carbon::parse($minThoiGianChieu)->startOfDay();
        $nowDate = $now->copy()->startOfDay();
        $daysUntilRelease = $nowDate->diffInDays($releaseDate, false);

        if ($daysUntilRelease > 10) {
            return 'Sắp ra mắt';
        }

        if ($daysUntilRelease > 0) {
            return 'Sắp chiếu';
        }

        $hasNowShowing = false;
        $hasFuture = false;

        foreach ($this->showtimes as $showtime) {
            $startTime = Carbon::parse($showtime->thoi_gian_chieu);
            $endTime = $startTime->copy()->addMinutes($this->thoi_luong ?? 90);

            if ($now->between($startTime, $endTime)) {
                $hasNowShowing = true;
            }

            if ($startTime->gt($now)) {
                $hasFuture = true;
            }
        }

        if ($hasNowShowing) {
            return 'Đang chiếu';
        }

        if ($hasFuture) {
            return 'Sắp chiếu';
        }

        return 'Đã kết thúc';
    }
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getNgayKhoiChieuAttribute()
    {
        $firstShowtime = $this->showtimes()->min('thoi_gian_chieu');
        return $firstShowtime ? Carbon::parse($firstShowtime)->toDateString() : null;
    }
}
