<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class NguoiDung extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'nguoi_dungs';

    /**
     * Các thuộc tính có thể gán hàng loạt.
     */
    protected $fillable = [
        'ho_ten',
        'email',
        'mat_khau',
        'vai_tro',
        'trang_thai_hoat_dong',
    ];

    /**
     * Các thuộc tính cần ẩn khi chuyển sang dạng mảng hoặc JSON.
     */
    protected $hidden = [
        'mat_khau',
        'remember_token',
    ];

    /**
     * Ép kiểu dữ liệu cho các thuộc tính.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
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

    /**
     * Magic Mutator cho 'password' ảo
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['mat_khau'] = $value;
    }

    /**
     * Ghi đè phương thức lấy mật khẩu tiêu chuẩn của Authenticatable
     */
    public function getAuthPassword()
    {
        return $this->mat_khau;
    }
}