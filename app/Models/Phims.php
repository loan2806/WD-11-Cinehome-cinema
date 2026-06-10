<?php

namespace App\Models;

use App\Models\SuatChieu;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
            return 'Đang cập nhật';
        }

        return $this->genres->pluck('ten_the_loai')->filter()->join(', ');
    }

    public function getCountryAttribute()
    {
        return $this->country?->ten_quoc_gia;
    }

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

    public function hasShowtimeStatus(string $status): bool
    {
        return $this->showtimes->contains('trang_thai', $status);
    }

    public function country()
    {
        return $this->belongsTo(QuocGia::class, 'quoc_gia_id');
    }

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
        if (! $this->relationLoaded('showtimes')) {
            $this->load('showtimes');
        }

        if ($this->showtimes->isEmpty()) {
            return 'Chưa có suất chiếu';
        }

        $showtimeStatuses = $this->showtimes->pluck('trang_thai')->unique();

        if ($showtimeStatuses->contains(SuatChieu::TRANG_THAI_DANG_CHIEU)) {
            return 'Đang chiếu';
        }

        if ($showtimeStatuses->contains(SuatChieu::TRANG_THAI_SAP_CHIEU)) {
            return 'Sắp chiếu';
        }

        if ($showtimeStatuses->contains(SuatChieu::TRANG_THAI_SAP_RA_MAT)) {
            return 'Sắp ra mắt';
        }

        if ($showtimeStatuses->contains(SuatChieu::TRANG_THAI_DA_CHIEU)) {
            return 'Đã chiếu';
        }

        return 'Không xác định';
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