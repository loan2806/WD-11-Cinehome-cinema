<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PhongChieu extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'phong_chieus';

    protected $fillable = [
        'rap_chieu_phim_id',
        'ten_phong',
        'loai_phong',
        'suc_chua',
        'trang_thai',
    ];

    protected $casts = [
        'suc_chua' => 'integer',
    ];

    public const LOAI_PHONG = [
        '2d' => '2D',
        '3d' => '3D',
        'imax' => 'IMAX',
        '4dx' => '4DX',
    ];

    public const TRANG_THAI = [
        'hoat_dong' => 'Hoạt động',
        'bao_tri' => 'Bảo trì',
        'ngung_hoat_dong' => 'Ngừng hoạt động',
    ];

    public function rapChieuPhim(): BelongsTo
    {
        return $this->belongsTo(RapChieuPhim::class, 'rap_chieu_phim_id');
    }

    public function hangGhes(): HasMany
    {
        return $this->hasMany(HangGhe::class, 'phong_chieu_id');
    }

    public function gheNgois(): HasMany
    {
        return $this->hasMany(GheNgoi::class, 'phong_chieu_id');
    }

    public function suatChieus(): HasMany
    {
        return $this->hasMany(SuatChieu::class, 'phong_chieu_id');
    }

    public function getSoHangAttribute(): int
    {
        return $this->hangGhes()->count();
    }

    public function getSoGheHoatDongAttribute(): int
    {
        return $this->gheNgois()->where('trang_thai', 'hoat_dong')->count();
    }
}
