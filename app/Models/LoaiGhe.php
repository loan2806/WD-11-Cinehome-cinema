<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoaiGhe extends Model
{
    use HasFactory;

    protected $table = 'loai_ghes';

    protected $fillable = [
        'ten_loai',
        'mo_ta',
        'phu_thu',
        'mau_sac',
    ];

    protected $casts = [
        'phu_thu' => 'decimal:2',
    ];

    public function gheNgois(): HasMany
    {
        return $this->hasMany(GheNgoi::class, 'loai_ghe_id');
    }

    public function getGiaVeMacDinhAttribute(): float
    {
        return (float) $this->phu_thu;
    }
}
