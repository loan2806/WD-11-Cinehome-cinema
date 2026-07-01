<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GioiThieuThanhVien extends Model
{
    protected $table = 'gioi_thieu_thanh_viens';


    protected $fillable = [
        'thanh_vien_id',
        'nguoi_duoc_gioi_thieu_id',
        'diem_thuong',
        'voucher_id',
        'noi_dung'
    ];


    public function thanhVien()
    {
        return $this->belongsTo(
            ThanhVien::class,
            'thanh_vien_id'
        );
    }


    public function nguoiDuocGioiThieu()
    {
        return $this->belongsTo(
            NguoiDung::class,
            'nguoi_duoc_gioi_thieu_id'
        );
    }


    public function voucher()
    {
        return $this->belongsTo(
            Voucher::class
        );
    }
}