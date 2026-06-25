<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThongBaoPushNguoiDung extends Model
{
    use HasFactory;

    protected $table = 'thong_bao_push_nguoi_dungs';

    protected $fillable = [
        'thong_bao_push_id',
        'nguoi_dung_id',
        'da_doc',
        'doc_luc',
    ];

    protected $casts = [
        'da_doc' => 'boolean',
        'doc_luc' => 'datetime',
    ];

    /**
     * Một bản ghi trung gian thuộc về một thông báo đẩy.
     */
    public function thongBaoPush(): BelongsTo
    {
        return $this->belongsTo(ThongBaoPush::class, 'thong_bao_push_id');
    }

    /**
     * Một bản ghi trung gian thuộc về một người dùng.
     */
    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }
}
