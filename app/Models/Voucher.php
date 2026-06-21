<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $table = 'vouchers';

    protected $fillable = [
        'ma_voucher',
        'ten_voucher',
        'gia_tri_giam',
        'diem_can_doi',
        'ngay_het_han',
        'trang_thai',
        'loai_voucher',
    ];

    protected $casts = [
        'gia_tri_giam' => 'decimal:2',
        'diem_can_doi' => 'integer',
        'ngay_het_han' => 'date',
        'trang_thai' => 'boolean',
        'trang_thai' => 'boolean',
    ];

    /**
     * Một voucher mẫu có thể được nhiều khách hàng đổi.
     */
    public function nguoiDungVouchers()
    {
        return $this->hasMany(NguoiDungVoucher::class, 'voucher_id');
    }
}