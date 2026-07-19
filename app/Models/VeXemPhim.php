<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VeXemPhim extends Model
{
    use HasFactory;

    protected $table = 've_xem_phims';

    protected $fillable = [
        'nguoi_dung_id',
        'nhan_vien_id',
        'suat_chieu_id',
        'ma_ve',
        'ten_phim',
        'ten_rap',
        'ten_phong',
        'ma_ghe',
        'thoi_gian_chieu',
        'tong_tien',
        'tien_hoan',
        'loai_ve',
        'trang_thai',
        'food_items',
        'payment_method',
        'received_amount',
        'change_amount',
        'seat_total',
        'food_total',
    ];

    protected $casts = [
        'thoi_gian_chieu' => 'datetime',
        'tong_tien' => 'decimal:2',
        'tien_hoan' => 'decimal:2',
        'food_items' => 'array',
        'received_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'seat_total' => 'decimal:2',
        'food_total' => 'decimal:2',
    ];

    public function canCancel(): bool
    {
        return false;
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }

    public function nhanVien(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nhan_vien_id');
    }

    public function suatChieu(): BelongsTo
    {
        return $this->belongsTo(SuatChieu::class, 'suat_chieu_id');
    }

    public function lichSuDiems()
    {
        return $this->hasMany(LichSuDiem::class, 've_xem_phim_id');
    }

    public function foods(): BelongsToMany
    {
        return $this->belongsToMany(
            DoAn::class,
            've_xem_phim_do_an',
            've_xem_phim_id',
            'food_id'
        )
            ->withPivot('so_luong')
            ->withTimestamps();
    }

    /**
     * Ưu tiên dữ liệu JSON trong DB; chỉ dùng cache cho vé cũ.
     */
    public function getFoodsListAttribute(): array
    {
        $items = $this->food_items;

        if (!is_array($items) || empty($items)) {
            $items = \Illuminate\Support\Facades\Cache::get(
                "ve_foods:{$this->id}",
                []
            );
        }

        return collect(is_array($items) ? $items : [])
            ->map(function ($item) {
                return [
                    'id' => $item['id'] ?? null,
                    'ten_mon' =>
                    $item['name']
                        ?? $item['ten_mon']
                        ?? 'Đồ ăn',
                    'so_luong' => (int) (
                        $item['qty']
                        ?? $item['quantity']
                        ?? $item['so_luong']
                        ?? 1
                    ),
                    'don_gia' => (float) (
                        $item['price']
                        ?? $item['don_gia']
                        ?? 0
                    ),
                ];
            })
            ->values()
            ->all();
    }

    public function gheVes(): HasMany
    {
        return $this->hasMany(
            VeXemPhimGhe::class,
            've_xem_phim_id'
        );
    }
}
