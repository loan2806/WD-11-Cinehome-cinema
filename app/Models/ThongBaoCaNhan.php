<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThongBaoCaNhan extends Model
{
    use HasFactory;

    protected $table = 'thong_bao_ca_nhans';

    protected $fillable = [
        'nguoi_dung_id',
        'tieu_de',
        'noi_dung',
        'loai_thong_bao',
        'duong_dan',
        'da_doc',
        'doc_luc',
    ];

    protected $casts = [
        'da_doc' => 'boolean',
        'doc_luc' => 'datetime',
    ];

    /**
     * Một thông báo thuộc về một người dùng.
     */
    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class);
    }
}