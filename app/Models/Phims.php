<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\SuatChieu;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
class Phims extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'phims';

        public function suatChieus()
            {
                return $this->hasMany(SuatChieu::class, 'phim_id');
            }
            
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
        'trang_thai',
        'ngay_ra_rap',
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

    public function hasShowtimeStatus(string $status): bool
    {
        return $this->showtimes->contains('trang_thai', $status);
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

    // public function getScheduleStatusAttribute(): string
    // {
    //     if ($this->showtimes->isEmpty()) {
    //         return 'Chưa có suất chiếu';
    //     }

    //     $showtimeStatuses = $this->showtimes->pluck('trang_thai')->unique();

    //     if ($showtimeStatuses->contains(SuatChieu::TRANG_THAI_DANG_CHIEU)) {
    //         return 'Đang chiếu';
    //     }

    //     if ($showtimeStatuses->contains(SuatChieu::TRANG_THAI_SAP_CHIEU)) {
    //         return 'Sắp chiếu';
    //     }

    //     if ($showtimeStatuses->contains(SuatChieu::TRANG_THAI_SAP_RA_MAT)) {
    //         return 'Sắp ra mắt';
    //     }

    //     if ($showtimeStatuses->contains(SuatChieu::TRANG_THAI_DA_CHIEU)) {
    //         return 'Đã chiếu';
    //     }

    //     return 'Không xác định';
    // }
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