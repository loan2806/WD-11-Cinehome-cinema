<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class SuatChieu extends Model
{
    use HasFactory;

    protected $table = 'suat_chieus';

    protected $fillable = [
        'phim_id',
        'rap_chieu_phim_id',
        'phong_chieu_id',
        'thoi_gian_chieu',
        'thoi_luong',
        'thoi_gian_ket_thuc',
        'gia_ve',
        'gia_ve_tu_dong',
        'trang_thai',
    ];

    protected $casts = [
        'thoi_gian_chieu' => 'datetime',
        'thoi_gian_ket_thuc' => 'datetime',
        'thoi_luong' => 'integer',
        'gia_ve' => 'decimal:2',
        'gia_ve_tu_dong' => 'boolean',
        'trang_thai' => 'string',
    ];

    public const TRANG_THAI_SAP_RA_MAT = 'sap_ra_mat';
    public const TRANG_THAI_SAP_CHIEU = 'sap_chieu';
    public const TRANG_THAI_DANG_CHIEU = 'dang_chieu';
    public const TRANG_THAI_DA_CHIEU = 'da_chieu';
    public const TRANG_THAI_HUY = 'huy';

    // 💡 KHÔI PHỤC LẠI HẰNG SỐ NÀY ĐỂ SỬA LỖI 500 Ở INDEX VIEW
    public const TRANG_THAI_LIST = [
        self::TRANG_THAI_SAP_RA_MAT => 'Sắp ra mắt',
        self::TRANG_THAI_SAP_CHIEU => 'Sắp chiếu',
        self::TRANG_THAI_DANG_CHIEU => 'Đang chiếu',
        self::TRANG_THAI_DA_CHIEU => 'Đã chiếu',
        self::TRANG_THAI_HUY => 'Hủy',
    ];

    public function phim(): BelongsTo
    {
        return $this->belongsTo(Phims::class, 'phim_id');
    }

    public function rapChieuPhim(): BelongsTo
    {
        return $this->belongsTo(RapChieuPhim::class, 'rap_chieu_phim_id');
    }

    public function phongChieu(): BelongsTo
    {
        return $this->belongsTo(PhongChieu::class, 'phong_chieu_id');
    }

    /**
     * Giá vé CUỐI CÙNG dùng để đặt vé/hiển thị: nếu suất đang ở chế độ tự
     * động, cộng SỐNG phụ thu hiện tại của phòng vào "gia_ve" (giá gốc ngày
     * thường/cuối tuần) — nhờ vậy phòng đổi phụ thu là mọi suất chiếu (kể cả
     * đã tạo trước đó) đều cập nhật giá ngay, không cần sửa lại từng suất.
     * Nếu suất đã bị ghi đè giá tùy chỉnh, dùng đúng giá đã lưu, không cộng
     * thêm phụ thu phòng.
     */
    public function getGiaVeCuoiCungAttribute(): float
    {
        if (! $this->gia_ve_tu_dong) {
            return (float) $this->gia_ve;
        }

        return (float) $this->gia_ve + (float) ($this->phongChieu->phu_thu ?? 0);
    }
    public function veXemPhims(): HasMany
{
    return $this->hasMany(VeXemPhim::class, 'suat_chieu_id');
}
    public function tinhSoGhe(): array
{
    $tongGhe = $this->phongChieu?->gheNgois?->where('trang_thai', 'hoat_dong')->count() ?? 0;

    $validTickets = $this->veXemPhims->filter(function ($ve) {
        if (in_array($ve->trang_thai, ['da_dat', 'da_thanh_toan', 'da_su_dung'])) {
            return true;
        }
        if ($ve->trang_thai === 'cho_thanh_toan' && $ve->thoi_gian_het_han && \Carbon\Carbon::parse($ve->thoi_gian_het_han)->isFuture()) {
            return true;
        }
        return false;
    });

    $bookedSeats = collect();
    foreach ($validTickets as $ve) {
        if (!empty($ve->ma_ghe)) {
            foreach (explode(',', (string) $ve->ma_ghe) as $code) {
                $seatCode = strtoupper(trim($code));
                if ($seatCode !== '') {
                    $bookedSeats->push($seatCode);
                }
            }
        }
    }

    $gheDaDat = $bookedSeats->unique()->count();
    $gheTrong = max(0, $tongGhe - $gheDaDat);

    return [
        'tong_ghe' => $tongGhe,
        'ghe_da_dat' => $gheDaDat,
        'ghe_trong' => $gheTrong,
    ];
}
}