<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NhatKyHoatDongHeThong extends Model
{
    // Khai báo ánh xạ chuẩn xác tới tên bảng tiếng Việt
    protected $table = 'nhat_ky_hoat_dong_he_thongs';

    protected $fillable = [
        'nguoi_dung_id',
        'hanh_dong',
        'chuc_nang',
        'mo_ta',
        'dia_chi_ip',
        'user_agent',
        'thuoc_tinh',
    ];

    protected $casts = [
        'thuoc_tinh' => 'array',
    ];

    /**
     * Mối quan hệ: Một dòng nhật ký thuộc về một Người dùng hệ thống
     */
    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }
}