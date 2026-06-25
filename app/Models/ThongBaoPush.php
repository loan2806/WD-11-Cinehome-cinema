<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ThongBaoPush extends Model
{
    use HasFactory;

    protected $table = 'thong_bao_pushs';

    protected $fillable = [
        'tieu_de',
        'noi_dung',
        'loai',
        'doi_tuong_nhan',
        'nguoi_tao_id',
        'trang_thai',
        'thoi_gian_gui',
    ];

    protected $casts = [
        'thoi_gian_gui' => 'datetime',
    ];

    /**
     * Một thông báo đẩy được tạo bởi một người dùng.
     */
    public function nguoiTao(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_tao_id');
    }

    /**
     * Một thông báo đẩy có thể gửi đến nhiều người dùng cụ thể.
     */
    public function nguoiDungs(): HasMany
    {
        return $this->hasMany(ThongBaoPushNguoiDung::class, 'thong_bao_push_id');
    }
}
