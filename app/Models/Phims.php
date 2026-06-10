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
        'ngay_khoi_chieu',
        'gioi_han_tuoi',
    ];

    protected $casts = [
        'ngay_khoi_chieu' => 'date',
    ];

    protected $appends = [
        'schedule_status',
    ];

    public function getTitleAttribute()
    {
        return $this->ten_phim;
    }

    public function getDescriptionAttribute()
    {
        return $this->mo_ta;
    }

    public function getTrailerUrlAttribute()
    {
        return $this->trailer;
    }

    public function getDurationAttribute()
    {
        return $this->thoi_luong;
    }

    public function getAgeRatingAttribute()
    {
        return $this->gioi_han_tuoi;
    }

    public function getReleaseDateAttribute()
    {
        return $this->ngay_khoi_chieu;
    }

    public function getCastAttribute()
    {
        return $this->dien_vien;
    }

    public function getGenreAttribute()
    {
        if (! $this->relationLoaded('genres')) {
            return 'Dang cap nhat';
        }

        return $this->genres->pluck('ten_the_loai')->filter()->join(', ');
    }

    public function getCountryAttribute()
    {
        return $this->country?->ten_quoc_gia;
    }

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
            'movie_genre', // Hãy nhớ đổi tên bảng này khi tiến hành việt hóa bảng trung gian!
            'movie_id',    // Hãy nhớ đổi tên cột này thành 'phim_id'
            'genre_id'     // Hãy nhớ đổi tên cột này thành 'the_loai_id'
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
        $showtimes = $this->showtimes;

        if ($showtimes->isEmpty()) {
            return 'Sắp ra mắt';
        }

        $hasNowShowing = false;
        $hasFutureWithin10 = false;
        $hasFutureBeyond10 = false;

        foreach ($showtimes as $showtime) {
            $startTime = $showtime->thoi_gian_chieu; 
            $endTime = $startTime->copy()->addMinutes($this->thoi_luong ?? 90);

            if ($now->between($startTime, $endTime)) {
                $hasNowShowing = true;
            }

            if ($startTime->gt($now)) {
                $days = $now->diffInDays($startTime);

                if ($days > 10) {
                    $hasFutureBeyond10 = true;
                } else {
                    $hasFutureWithin10 = true;
                }
            }
        }

        if ($hasNowShowing) {
            return 'Đang chiếu';
        }

        if ($hasFutureBeyond10) {
            return 'Sắp ra mắt';
        }

        if ($hasFutureWithin10) {
            return 'Sắp chiếu';
        }

        return 'Đã kết thúc';
    }
}
