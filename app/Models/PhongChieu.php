<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PhongChieu extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'phong_chieus';

    protected $fillable = [
        'rap_chieu_phim_id',
        'ten_phong',
        'loai_phong',
        'suc_chua',
        'trang_thai',
        'phu_thu',
    ];

    protected $casts = [
        'suc_chua' => 'integer',
        'phu_thu' => 'decimal:2',
    ];

    public const LOAI_PHONG = [
        '2d' => '2D',
        '3d' => '3D',
        'imax' => 'IMAX',
        '4dx' => '4DX',
    ];

    /**
     * Phụ thu vé GỢI Ý theo từng loại phòng — chỉ dùng để tự điền sẵn khi
     * Quản trị viên tạo phòng mới (chọn loại phòng nào thì gợi ý đúng mức phụ
     * thu của loại đó). Sau khi tạo, phụ thu vẫn chỉnh được riêng theo TỪNG
     * phòng ở trang "Giá theo phòng chiếu" — đây chỉ là giá trị khởi tạo.
     */
    public const PHU_THU_GOI_Y = [
        '2d' => 0,
        '3d' => 15000,
        'imax' => 20000,
        '4dx' => 20000,
    ];

    /**
     * Phụ thu HIỆN TẠI đang áp dụng cho một LOẠI phòng — lấy từ phòng bất kỳ
     * đã có của loại đó (tất cả các phòng cùng loại luôn được đồng bộ cùng
     * một mức phụ thu, quản lý ở trang "Giá theo phòng chiếu"). Nếu loại đó
     * chưa có phòng nào, dùng mức gợi ý mặc định làm khởi điểm.
     */
    public static function phuThuTheoLoai(string $loaiPhong): float
    {
        $phuThu = static::where('loai_phong', $loaiPhong)->value('phu_thu');

        return $phuThu !== null ? (float) $phuThu : (float) (self::PHU_THU_GOI_Y[$loaiPhong] ?? 0);
    }

    public const TRANG_THAI = [
        'hoat_dong' => 'Hoạt động',
        'bao_tri' => 'Bảo trì',
        'ngung_hoat_dong' => 'Ngừng hoạt động',
    ];

    public function rapChieuPhim(): BelongsTo
    {
        return $this->belongsTo(RapChieuPhim::class, 'rap_chieu_phim_id');
    }

    public function hangGhes(): HasMany
    {
        return $this->hasMany(HangGhe::class, 'phong_chieu_id');
    }

    public function gheNgois(): HasMany
    {
        return $this->hasMany(GheNgoi::class, 'phong_chieu_id');
    }

    public function suatChieus(): HasMany
    {
        return $this->hasMany(SuatChieu::class, 'phong_chieu_id');
    }

    public function getSoHangAttribute(): int
    {
        return $this->hangGhes()->count();
    }

    public function getSoGheHoatDongAttribute(): int
    {
        return $this->gheNgois()->where('trang_thai', 'hoat_dong')->count();
    }
}
