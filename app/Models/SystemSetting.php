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
        $setting = static::query()
            ->where('khoa', $key)
            ->first();

        return $setting ? $setting->gia_tri : $default;
    }

    public static function getBoolean(string $key, bool $default = false): bool
    {
        $value = static::getValue($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        $boolean = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $boolean ?? $default;
    }

    public static function putDefault(
        string $key,
        string $label,
        mixed $value,
        string $group = 'general',
        string $type = 'text'
    ): self {
        return static::query()->firstOrCreate(
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
