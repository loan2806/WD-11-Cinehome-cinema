<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'trang_thai',
    ];

    protected $casts = [
        'thoi_gian_chieu' => 'datetime',
        'thoi_gian_ket_thuc' => 'datetime',
        'thoi_luong' => 'integer',
        'gia_ve' => 'decimal:2',
        'trang_thai' => 'string',
    ];

    public const TRANG_THAI_SAP_RA_MAT = 'sap_ra_mat';
    public const TRANG_THAI_SAP_CHIEU = 'sap_chieu';
    public const TRANG_THAI_DANG_CHIEU = 'dang_chieu';
    public const TRANG_THAI_DA_CHIEU = 'da_chieu';
    public const TRANG_THAI_HUY = 'huy';

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
     * Tự động tính thời gian kết thúc dựa trên thời gian bắt đầu và thời lượng phim
     */
    public function tinhThoiGianKetThuc(): void
    {
        if ($this->thoi_gian_chieu && $this->thoi_luong) {
            $this->thoi_gian_ket_thuc = $this->thoi_gian_chieu->copy()->addMinutes($this->thoi_luong);
        }
    }

    /**
     * Kiểm tra xem suất chiếu này có chồng lấn với suất chiếu khác không
     */
    public function kiemTraChongLan(SuatChieu $suatChieuKhac): bool
    {
        if (!$this->thoi_gian_chieu || !$this->thoi_gian_ket_thuc || 
            !$suatChieuKhac->thoi_gian_chieu || !$suatChieuKhac->thoi_gian_ket_thuc) {
            return false;
        }

        return $this->thoi_gian_chieu < $suatChieuKhac->thoi_gian_ket_thuc 
            && $this->thoi_gian_ket_thuc > $suatChieuKhac->thoi_gian_chieu;
    }
}