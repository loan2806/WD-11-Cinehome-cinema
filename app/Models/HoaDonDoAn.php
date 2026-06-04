<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HoaDonDoAn extends Model
{
    use HasFactory;

    // Khai báo liên kết với bảng tiếng Việt
    protected $table = 'hoa_don_do_ans';

    protected $fillable = [
        'nguoi_dung_id',
        'tong_tien',
        'trang_thai',
    ];

    protected $casts = [
        'tong_tien' => 'decimal:2',
    ];

    /**
     * Mối quan hệ: Một hóa đơn đồ ăn thuộc về một Người dùng
     */
    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }
}