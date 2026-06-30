<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaiDatHeThong extends Model
{
    protected $table = 'cai_dat_he_thongs';

    protected $fillable = [
        'ten_rap', 'logo', 'anh_nen_login', 'hotline', 'email', 'dia_chi',
        'thoi_gian_giu_ghe', 'so_ve_toi_da_don',
        'thoi_gian_don_phong', 'so_ngay_duoc_dat_ve_truoc',
        'gia_ngay_thuong', 'gia_cuoi_tuan', 'phu_thu_ghe_vip',
        'bat_tat_vnpay', 'vnpay_tmn_code', 'vnpay_hash_secret',
        'bat_tat_momo', 'momo_partner_code', 'momo_access_key', 'momo_secret_key',
        'so_phut_truoc_chieu_dang_chieu', 'so_ngay_truoc_chieu_sap_ra_mat'
    ];

    protected $casts = [
        'bat_tat_vnpay' => 'boolean',
        'bat_tat_momo' => 'boolean',
    ];
}