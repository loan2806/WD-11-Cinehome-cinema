<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LichBaoTriGheNgoi extends Model
{
    protected $table = 'lich_bao_tri_ghe_ngois';

    protected $fillable = [
        'ghe_ngoi_id',
        'phong_chieu_id',
        'nguoi_dung_id',
        'thoi_gian_bat_dau',
        'thoi_gian_ket_thuc',
        'ly_do',
        'trang_thai_truoc',
        'trang_thai_sau',
        'trang_thai',
        'ghi_chu',
    ];

    protected $casts = [
        'thoi_gian_bat_dau' => 'datetime',
        'thoi_gian_ket_thuc' => 'datetime',
    ];

    public function gheNgoi(): BelongsTo
    {
        return $this->belongsTo(GheNgoi::class, 'ghe_ngoi_id');
    }

    public function phongChieu(): BelongsTo
    {
        return $this->belongsTo(PhongChieu::class, 'phong_chieu_id');
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }
}
