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
        'kieu_giam',
        'doi_tuong_su_dung',
    ];

    protected $casts = [
        'gia_tri_giam' => 'decimal:2',
        'diem_can_doi' => 'integer',
        'ngay_het_han' => 'date',
        'trang_thai' => 'boolean',
    ];

    /**
     * Một voucher mẫu có thể được nhiều khách hàng đổi.
     */
    public function nguoiDungVouchers()
    {
        return $this->hasMany(NguoiDungVoucher::class, 'voucher_id');
    }

    /**
     * Voucher có thể được sử dụng bởi User.
     * Voucher đặc biệt Staff luôn bị loại khỏi nhóm User,
     * kể cả khi dữ liệu đối tượng sử dụng bị cấu hình sai.
     */
    public function scopeForUser($query)
    {
        return $query
            ->whereIn('doi_tuong_su_dung', ['user', 'all'])
            ->where('loai_voucher', '!=', 'staff_dac_biet');
    }

    /**
     * Voucher có thể được sử dụng bởi Staff.
     */
    public function scopeForStaff($query)
    {
        return $query->whereIn('doi_tuong_su_dung', ['staff', 'all']);
    }

    public function isForUser(): bool
    {
        return in_array($this->doi_tuong_su_dung, ['user', 'all'], true)
            && $this->loai_voucher !== 'staff_dac_biet';
    }

    public function isForStaff(): bool
    {
        return in_array($this->doi_tuong_su_dung, ['staff', 'all'], true);
    }
}
