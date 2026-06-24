<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThanhVien extends Model
{
    use HasFactory;

    protected $table = 'thanh_viens';

    protected $fillable = [
        'nguoi_dung_id',
        'ma_thanh_vien',
        'hang_thanh_vien',
        'diem_hien_tai',
        'tong_diem_tich_luy',
        'ngay_tham_gia',
    ];

    protected $casts = [
        'ngay_tham_gia' => 'datetime',
        'diem_hien_tai' => 'integer',
        'tong_diem_tich_luy' => 'integer',
    ];

    /**
     * Một thẻ thành viên thuộc về một người dùng.
     */
    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }

    /**
     * Một thẻ thành viên có nhiều lịch sử điểm.
     */
    public function lichSuDiems()
    {
        return $this->hasMany(LichSuDiem::class, 'thanh_vien_id');
    }

    /**
     * Cộng điểm cho thành viên.
     *
     * Dùng khi:
     * - Khách mua vé.
     * - Admin tặng điểm đền bù.
     * - Admin tặng điểm tri ân khách hàng.
     *
     * Khi cộng điểm:
     * - Tăng điểm hiện tại.
     * - Tăng tổng điểm tích lũy.
     * - Ghi lịch sử điểm.
     * - Tự cập nhật hạng thành viên.
     */
    public function congDiem(int $soDiem, ?VeXemPhim $veXemPhim = null, ?string $noiDung = null): void
    {
        if ($soDiem <= 0) {
            return;
        }

        $this->increment('diem_hien_tai', $soDiem);
        $this->increment('tong_diem_tich_luy', $soDiem);

        $this->lichSuDiems()->create([
            've_xem_phim_id' => $veXemPhim?->id,
            'loai_giao_dich' => 'cong_diem',
            'so_diem' => $soDiem,
            'noi_dung' => $noiDung ?? 'Cộng điểm khi mua vé xem phim.',
        ]);

        $this->refresh();
        $this->capNhatHangThanhVien();
    }


    /**
     * Cộng điểm thưởng sử dụng nhưng không tính vào tổng điểm tích lũy.
     *
     * Dùng cho:
     * - Đền bù lỗi đặt vé.
     * - Đền bù lỗi thanh toán.
     * - Chăm sóc khách hàng sau sự cố.
     *
     * Lưu ý:
     * - Chỉ tăng điểm hiện tại.
     * - Không tăng tổng điểm tích lũy.
     * - Không làm thay đổi hạng thành viên.
     */
    public function congDiemKhongXetHang(int $soDiem, ?string $noiDung = null): void
    {
        if ($soDiem <= 0) {
            return;
        }

        $this->increment('diem_hien_tai', $soDiem);

        $this->lichSuDiems()->create([
            've_xem_phim_id' => null,
            'loai_giao_dich' => 'cong_diem',
            'so_diem' => $soDiem,
            'noi_dung' => $noiDung ?? 'Cộng điểm hỗ trợ khách hàng.',
        ]);

        $this->refresh();
    }

    /**
     * Trừ điểm hiện tại khi khách sử dụng điểm.
     *
     * Dùng cho:
     * - Đổi điểm lấy voucher.
     * - Sử dụng điểm để đổi ưu đãi.
     *
     * Lưu ý:
     * - Chỉ trừ điểm hiện tại.
     * - Không trừ tổng điểm tích lũy.
     * - Vì tổng điểm tích lũy dùng để xét hạng thành viên.
     */
    public function truDiem(int $soDiem, ?VeXemPhim $veXemPhim = null, ?string $noiDung = null): void
    {
        if ($soDiem <= 0) {
            return;
        }

        $diemSauKhiTru = max(0, $this->diem_hien_tai - $soDiem);

        $this->update([
            'diem_hien_tai' => $diemSauKhiTru,
        ]);

        $this->lichSuDiems()->create([
            've_xem_phim_id' => $veXemPhim?->id,
            'loai_giao_dich' => 'tru_diem',
            'so_diem' => $soDiem,
            'noi_dung' => $noiDung ?? 'Trừ điểm.',
        ]);

        $this->refresh();
        $this->capNhatHangThanhVien();
    }

    /**
     * Thu hồi điểm khi Admin cộng nhầm hoặc cần xử lý gian lận.
     *
     * Dùng cho:
     * - Admin tặng nhầm điểm.
     * - Thu hồi điểm cộng sai.
     * - Xử lý gian lận điểm.
     *
     * Lưu ý:
     * - Trừ điểm hiện tại.
     * - Trừ cả tổng điểm tích lũy.
     * - Việc trừ tổng điểm tích lũy giúp hạng thành viên quay về đúng.
     */
    public function thuHoiDiem(int $soDiem, ?string $noiDung = null): void
    {
        if ($soDiem <= 0) {
            return;
        }

        $diemSauKhiTru = max(0, $this->diem_hien_tai - $soDiem);
        $tongDiemSauKhiTru = max(0, $this->tong_diem_tich_luy - $soDiem);

        $this->update([
            'diem_hien_tai' => $diemSauKhiTru,
            'tong_diem_tich_luy' => $tongDiemSauKhiTru,
        ]);

        $this->lichSuDiems()->create([
            've_xem_phim_id' => null,
            'loai_giao_dich' => 'tru_diem',
            'so_diem' => $soDiem,
            'noi_dung' => $noiDung ?? 'Admin thu hồi điểm.',
        ]);

        $this->refresh();
        $this->capNhatHangThanhVien();
    }

    /**
     * Tự động cập nhật hạng thành viên theo tổng điểm tích lũy.
     *
     * MEMBER   : 0 - 499 điểm
     * SILVER   : 500 - 999 điểm
     * GOLD     : 1000 - 1999 điểm
     * PLATINUM : từ 2000 điểm
     */
    public function capNhatHangThanhVien(): void
    {
        $tongDiem = $this->tong_diem_tich_luy;

        $hangMoi = match (true) {
            $tongDiem >= 2000 => 'platinum',
            $tongDiem >= 1000 => 'gold',
            $tongDiem >= 500 => 'silver',
            default => 'member',
        };

        if ($this->hang_thanh_vien !== $hangMoi) {
            $this->update([
                'hang_thanh_vien' => $hangMoi,
            ]);
        }
    }

    /**
     * Hệ số tích điểm theo hạng thành viên.
     *
     * Member   : x1.0
     * Silver   : x1.05
     * Gold     : x1.10
     * Platinum : x1.15
     */
    public function heSoTichDiem(): float
    {
        return match ($this->hang_thanh_vien) {
            'silver' => 1.05,
            'gold' => 1.10,
            'platinum' => 1.15,
            default => 1.0,
        };
    }

    /**
     * Tên hạng hiển thị.
     */
    public function getTenHangAttribute(): string
    {
        return match ($this->hang_thanh_vien) {
            'silver' => 'Silver',
            'gold' => 'Gold',
            'platinum' => 'Platinum',
            default => 'Member',
        };
    }
}
