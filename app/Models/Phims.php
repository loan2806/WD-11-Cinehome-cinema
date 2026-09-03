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
        'banner',
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