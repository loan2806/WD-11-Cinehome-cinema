<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VeXemPhimGhe extends Model
{
    use HasFactory;

    public const CHUA_SU_DUNG = 'chua_su_dung';
    public const DA_SU_DUNG = 'da_su_dung';
    public const DA_HUY = 'da_huy';

    protected $table = 've_xem_phim_ghes';

    protected $fillable = [
        've_xem_phim_id',
        'ma_ghe',
        'ma_qr',
        'trang_thai',
        'checked_in_at',
        'checked_in_by',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
    ];

    public function veXemPhim(): BelongsTo
    {
        return $this->belongsTo(
            VeXemPhim::class,
            've_xem_phim_id'
        );
    }

    public function nguoiSoat(): BelongsTo
    {
        return $this->belongsTo(
            NguoiDung::class,
            'checked_in_by'
        );
    }

    public function daSuDung(): bool
    {
        return $this->trang_thai === self::DA_SU_DUNG;
    }

    public function coTheSuDung(): bool
    {
        return $this->trang_thai === self::CHUA_SU_DUNG;
    }
}