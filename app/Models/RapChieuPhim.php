<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RapChieuPhim extends Model
{
    use HasFactory;

    protected $table = 'rap_chieu_phims';

    protected $fillable = [
        'ten_rap',
        'dia_chi',
        'thanh_pho',
        'so_dien_thoai',
        'hinh_anh',
        'vi_do',
        'kinh_do',
    ];

    public function suatChieus(): HasMany
    {
        return $this->hasMany(SuatChieu::class, 'rap_chieu_phim_id');
    }
}