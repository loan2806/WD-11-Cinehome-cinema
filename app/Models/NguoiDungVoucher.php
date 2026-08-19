<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NguoiDungVoucher extends Model
{
    use HasFactory;

    protected $table = 'nguoi_dung_vouchers';

    protected $fillable = [
        'nguoi_dung_id',
        'voucher_id',
        'ma_voucher_ca_nhan',
        'da_su_dung',
        'ngay_nhan',
        'ngay_su_dung',
        'loai_cap_phat',
        'ly_do_khac',
        'nam_ap_dung',
        'ngay_het_han',
    ];

    protected $casts = [
        'da_su_dung' => 'boolean',
        'ngay_nhan' => 'datetime',
        'ngay_su_dung' => 'datetime',
        'ngay_het_han' => 'datetime',
        'nam_ap_dung' => 'integer',
    ];

    /**
     * Voucher cá nhân thuộc về một người dùng.
     */
    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }

    /**
     * Voucher cá nhân được tạo từ một voucher mẫu.
     */
    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }
}
