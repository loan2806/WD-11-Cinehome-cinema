<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VeXemPhim extends Model
{
    use HasFactory;

    protected $table = 've_xem_phims';

    protected $fillable = [
        'nguoi_dung_id',
        'ma_ve',
        'ten_phim',
        'ten_rap',
        'ten_phong',
        'ma_ghe',
        'thoi_gian_chieu',
        'tong_tien',
        'tien_hoan',
        'loai_ve',
        'trang_thai',
    ];

    protected $casts = [
        'thoi_gian_chieu' => 'datetime',
        'tong_tien' => 'decimal:2',
        'tien_hoan' => 'decimal:2',
    ];

    /**
     * MỚI: Kiểm tra xem vé có được phép hủy hay không (Trong vòng 5 phút kể từ lúc tạo)
     */
    public function canCancel(): bool
    {
        return $this->created_at->diffInMinutes(now()) <= 5;
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }
}