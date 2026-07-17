<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\LichSuDiem;

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
        'food_items', // 🌟 BỔ SUNG: Cho phép gán dữ liệu đồ ăn JSON vào Model
    ];

    protected $casts = [
        'thoi_gian_chieu' => 'datetime',
        'tong_tien' => 'decimal:2',
        'tien_hoan' => 'decimal:2',
        'food_items' => 'array', // 🌟 BỔ SUNG: Tự động giải mã JSON trong DB thành Array PHP sạch sẽ
    ];

    // 🌟 ĐÃ KHÓA: Trả về false để vô hiệu hóa toàn bộ cơ chế hủy vé của hệ thống
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

    /**
     * Một vé có thể phát sinh lịch sử cộng/trừ điểm.
     */
    public function lichSuDiems()
    {
        return $this->hasMany(LichSuDiem::class, 've_xem_phim_id');
    }

    /**
     * Mối quan hệ Nhiều - Nhiều với bảng DoAn (Đã đồng bộ tên lớp mới).
     */
    public function foods(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(DoAn::class, 've_xem_phim_do_an', 've_xem_phim_id', 'food_id')
            ->withPivot('so_luong')
            ->withTimestamps();
    }

    /**
     * 🌟 BỔ SUNG ACCESSOR: Chuẩn hóa bắp nước từ cột JSON 'food_items' của hệ thống.
     * Giúp lấy danh sách đồ ăn cực kỳ sạch sẽ ở mọi nơi bằng cách gọi trực tiếp: $ticket->foods_list
     */
    public function getFoodsListAttribute(): array
    {
        // Lấy dữ liệu đồ ăn từ Cache giống hệt như Controller phía Client đang làm
        $items = \Illuminate\Support\Facades\Cache::get("ve_foods:{$this->id}", []);

        $foods = [];
        if (is_array($items)) {
            foreach ($items as $item) {
                $foods[] = [
                    'ten_mon' => $item['name'] ?? $item['ten_mon'] ?? 'Đồ ăn',
                    'so_luong' => $item['qty'] ?? $item['quantity'] ?? $item['so_luong'] ?? 1,
                ];
            }
        }

        return $foods;
    }
}