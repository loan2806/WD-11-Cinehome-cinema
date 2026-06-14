<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HangGhe extends Model
{
    use HasFactory;

    protected $table = 'hang_ghes';

    protected $fillable = [
        'phong_chieu_id',
        'ten_hang',
        'la_hang_couple',
        'loai_ghe_mac_dinh_id',
    ];

    protected $casts = [
        'la_hang_couple' => 'boolean',
    ];

    public function phongChieu(): BelongsTo
    {
        return $this->belongsTo(PhongChieu::class, 'phong_chieu_id');
    }

    public function gheNgois(): HasMany
    {
        return $this->hasMany(GheNgoi::class, 'hang_ghe_id');
    }

    public function loaiGheMacDinh(): BelongsTo
    {
        return $this->belongsTo(LoaiGhe::class, 'loai_ghe_mac_dinh_id');
    }

    public function getSoGheAttribute(): int
    {
        return $this->gheNgois()->count();
    }

    public function getSoGheHoatDongAttribute(): int
    {
        return $this->gheNgois()->where('trang_thai', 'hoat_dong')->count();
    }
}
