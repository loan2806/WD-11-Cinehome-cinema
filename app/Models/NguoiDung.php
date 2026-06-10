<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class NguoiDung extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'nguoi_dungs';

    protected $fillable = [
        'ho_ten',
        'email',
        'mat_khau',
        'vai_tro',
        'trang_thai_hoat_dong',
    ];

    protected $hidden = [
        'mat_khau',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'mat_khau' => 'hashed',
        'trang_thai_hoat_dong' => 'boolean',
    ];

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
}
