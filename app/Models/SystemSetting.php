<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $table = 'cau_hinh_he_thong';

    protected $fillable = [
        'khoa',
        'gia_tri',
        'nhom',
        'nhan',
        'loai',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $value = static::query()->where('khoa', $key)->value('gia_tri');

        return $value === null ? $default : $value;
    }

    public static function getBoolean(string $key, bool $default = false): bool
    {
        $value = static::getValue($key, $default ? '1' : '0');

        return filter_var($value, FILTER_VALIDATE_BOOL);
    }

    public static function putDefault(string $key, string $label, mixed $value, string $group, string $type = 'text'): void
    {
        static::query()->firstOrCreate(
            ['khoa' => $key],
            [
                'nhan' => $label,
                'gia_tri' => (string) $value,
                'nhom' => $group,
                'loai' => $type,
            ]
        );
    }
}
