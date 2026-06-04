<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuatChieu extends Model
{
    use HasFactory;

    protected $table = 'suat_chieus';

    protected $fillable = [
        'phim_id',
        'rap_chieu_phim_id',
        'thoi_gian_chieu',
        'gia_ve',
    ];

    protected $casts = [
        'thoi_gian_chieu' => 'datetime',
        'gia_ve' => 'decimal:2',
    ];

    public function phim(): BelongsTo
    {
        return $this->belongsTo(Phims::class, 'phim_id');
    }

    public function rapChieuPhim(): BelongsTo
    {
        return $this->belongsTo(RapChieuPhim::class, 'rap_chieu_phim_id');
    }
}