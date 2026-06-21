<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\LichSuDiem;

class VeXemPhim extends Model
{
    use HasFactory;

    protected $table = 've_xem_phims';

    protected $fillable = [
        'nguoi_dung_id',
        'nhan_vien_id',
        'suat_chieu_id',
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

    public function canCancel(): bool
    {
        $minutes = (int) SystemSetting::getValue('ticket_cancel_minutes', 5);

        return $this->trang_thai === 'da_thanh_toan'
            && $this->created_at
            && $this->created_at->diffInMinutes(now()) <= $minutes;
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }

    public function nhanVien(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nhan_vien_id');
    }

    public function suatChieu(): BelongsTo
    {
        return $this->belongsTo(SuatChieu::class, 'suat_chieu_id');
    }

    /**
     * Một vé có thể phát sinh lịch sử cộng/trừ điểm.
     */
    public function lichSuDiems()
    {
        return $this->hasMany(LichSuDiem::class, 've_xem_phim_id');
    }
}
