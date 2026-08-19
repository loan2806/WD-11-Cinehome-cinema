<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LichSuDiem extends Model
{
    use HasFactory;

    protected $table = 'lich_su_diems';

    protected $fillable = [
        'thanh_vien_id',
        've_xem_phim_id',
        'loai_giao_dich',
        'so_diem',
        'diem_con_lai',
        'ngay_het_han',
        'noi_dung',
    ];

    protected $casts = [
        'so_diem' => 'integer',
        'diem_con_lai' => 'integer',
        'ngay_het_han' => 'datetime',
    ];

    /**
     * Một lịch sử điểm thuộc về một thẻ thành viên.
     */
    public function thanhVien()
    {
        return $this->belongsTo(ThanhVien::class, 'thanh_vien_id');
    }

    /**
     * Một lịch sử điểm có thể liên kết với một vé xem phim.
     */
    public function veXemPhim()
    {
        return $this->belongsTo(VeXemPhim::class, 've_xem_phim_id');
    }
}
