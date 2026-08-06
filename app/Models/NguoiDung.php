<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\ThanhVien;
use App\Notifications\ResetPasswordNotification;

class NguoiDung extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $table = 'nguoi_dungs';

    protected $fillable = [
        'ho_ten',
        'email',
        'ngay_sinh',
        'mat_khau',
        'vai_tro',
        'rap_chieu_phim_id',
        'luong_co_ban',
        'trang_thai_hoat_dong',
        'so_dien_thoai',
    ];

    protected $hidden = [
        'mat_khau',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'ngay_sinh' => 'date',
        'mat_khau' => 'hashed',
        'trang_thai_hoat_dong' => 'boolean',
    ];

    /**
     * Magic Accessor cho 'password' ảo
     */
    public function getPasswordAttribute()
    {
        return $this->mat_khau;
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['mat_khau'] = $value;
    }

    public function getRoleAttribute()
    {
        return $this->vai_tro;
    }

    public function getAuthPassword()
    {
        return $this->mat_khau;
    }

    public function veXemPhims()
    {
        return $this->hasMany(VeXemPhim::class, 'nguoi_dung_id');
    }

    /**
     * Quan hệ: một người dùng có một thẻ thành viên.
     */
    public function thanhVien()
    {
        return $this->hasOne(ThanhVien::class, 'nguoi_dung_id');
    }

    /**
     * Quan hệ: một người dùng có thể sở hữu nhiều voucher cá nhân.
     */
    public function vouchersCaNhan()
    {
        return $this->hasMany(NguoiDungVoucher::class, 'nguoi_dung_id');
    }

    /**
     * Một người dùng có nhiều thông báo cá nhân.
     */
    public function thongBaoCaNhans()
    {
        return $this->hasMany(ThongBaoCaNhan::class);
    }

    /**
     * Danh sách tài khoản được người dùng này giới thiệu
     */
    public function nguoiDuocGioiThieu()
    {
        return $this->hasMany(
            NguoiDung::class,
            'nguoi_gioi_thieu_id',
            'id'
        );
    }


    /**
     * Người giới thiệu tài khoản này
     */
    public function nguoiGioiThieu()
    {
        return $this->belongsTo(
            NguoiDung::class,
            'nguoi_gioi_thieu_id'
        );
    }

    public function rapChieuPhim()
    {
        return $this->belongsTo(RapChieuPhim::class, 'rap_chieu_phim_id');
    }

    public function chamCongs()
    {
        return $this->hasMany(ChamCong::class, 'nguoi_dung_id');
    }

    public function bangLuongs()
    {
        return $this->hasMany(BangLuong::class, 'nguoi_dung_id');
    }
    public function sendPasswordResetNotification($token)
{
    $this->notify(new ResetPasswordNotification($token));
}
}
