<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DanhGiaPhim extends Model
{
    // Liên kết với bảng danh_gia_phims tiếng Việt mới
    protected $table = 'danh_gia_phims';

    protected $fillable = [
        'phim_id',
        'nguoi_dung_id',
        'diem_danh_gia',
        'noi_dung',
        'trang_thai',
    ];

    /**
     * Mối quan hệ: Một bản đánh giá thuộc về một Bộ phim
     */
    public function phim(): BelongsTo
    {
        return $this->belongsTo(Phims::class, 'phim_id');
    }

    /**
     * Mối quan hệ: Một bản đánh giá được viết bởi một Người dùng
     */
    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }
}