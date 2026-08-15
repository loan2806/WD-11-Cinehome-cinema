<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ThanhVien extends Model
{
    use HasFactory;

    protected $table = 'thanh_viens';

    protected $fillable = [
        'nguoi_dung_id',
        'ma_thanh_vien',
        'ma_gioi_thieu',
        'nguoi_gioi_thieu_id',
        'da_nhan_thuong',
        'hang_thanh_vien',
        'diem_hien_tai',
        'tong_diem_tich_luy',
        'ngay_tham_gia',
    ];

    protected $casts = [
        'ngay_tham_gia' => 'datetime',
        'diem_hien_tai' => 'integer',
        'tong_diem_tich_luy' => 'integer',
        'da_nhan_thuong' => 'boolean',
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
    public function congDiem(
        int $soDiem,
        ?VeXemPhim $veXemPhim = null,
        ?string $noiDung = null
    ): void {
        if ($soDiem <= 0) {
            return;
        }

        $ngayHetHan = Carbon::now()->addDays(45);

        $this->increment('diem_hien_tai', $soDiem);
        $this->increment('tong_diem_tich_luy', $soDiem);

        $this->lichSuDiems()->create([
            've_xem_phim_id' => $veXemPhim?->id,
            'loai_giao_dich' => 'cong_diem',
            'so_diem' => $soDiem,
            'diem_con_lai' => $soDiem,
            'ngay_het_han' => $ngayHetHan,
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
    public function congDiemKhongXetHang(
        int $soDiem,
        ?string $noiDung = null
    ): void {
        if ($soDiem <= 0) {
            return;
        }

        $ngayHetHan = Carbon::now()->addDays(45);

        $this->increment('diem_hien_tai', $soDiem);

        $this->lichSuDiems()->create([
            've_xem_phim_id' => null,
            'loai_giao_dich' => 'cong_diem',
            'so_diem' => $soDiem,
            'diem_con_lai' => $soDiem,
            'ngay_het_han' => $ngayHetHan,
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
    public function truDiem(
        int $soDiem,
        ?VeXemPhim $veXemPhim = null,
        ?string $noiDung = null
    ): void {
        if ($soDiem <= 0) {
            return;
        }

        if ($soDiem > $this->diem_hien_tai) {
            throw new \InvalidArgumentException(
                'Số điểm sử dụng không được lớn hơn số điểm hiện tại.'
            );
        }

        $soDiemConCanTru = $soDiem;

        /*
    |--------------------------------------------------------------------------
    | Trừ các khoản điểm sắp hết hạn trước
    |--------------------------------------------------------------------------
    */

        $cacKhoanDiem = $this->lichSuDiems()
            ->where('loai_giao_dich', 'cong_diem')
            ->where('diem_con_lai', '>', 0)
            ->where(function ($query) {
                $query->whereNull('ngay_het_han')
                    ->orWhere('ngay_het_han', '>', now());
            })
            ->orderByRaw('CASE WHEN ngay_het_han IS NULL THEN 1 ELSE 0 END')
            ->orderBy('ngay_het_han')
            ->orderBy('id')
            ->get();

        foreach ($cacKhoanDiem as $khoanDiem) {
            if ($soDiemConCanTru <= 0) {
                break;
            }

            $diemConLai = (int) $khoanDiem->diem_con_lai;

            $diemTru = min(
                $diemConLai,
                $soDiemConCanTru
            );

            $khoanDiem->decrement(
                'diem_con_lai',
                $diemTru
            );

            $soDiemConCanTru -= $diemTru;
        }

        /*
    |--------------------------------------------------------------------------
    | Cập nhật điểm hiện tại
    |--------------------------------------------------------------------------
    */

        $this->decrement(
            'diem_hien_tai',
            $soDiem
        );

        /*
    |--------------------------------------------------------------------------
    | Ghi lịch sử sử dụng điểm
    |--------------------------------------------------------------------------
    */

        $this->lichSuDiems()->create([
            've_xem_phim_id' => $veXemPhim?->id,
            'loai_giao_dich' => 'tru_diem',
            'so_diem' => $soDiem,
            'diem_con_lai' => 0,
            'ngay_het_han' => null,
            'noi_dung' => $noiDung ?? 'Sử dụng điểm.',
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
    /**
     * Thu hồi điểm khi Admin cộng nhầm hoặc cần xử lý gian lận.
     *
     * Dùng cho:
     * - Admin tặng nhầm điểm.
     * - Thu hồi điểm cộng sai.
     * - Xử lý gian lận điểm.
     *
     * Lưu ý:
     * - Không được thu hồi quá điểm hiện tại.
     * - Không được thu hồi quá tổng điểm tích lũy.
     * - Trừ điểm hiện tại.
     * - Trừ cả tổng điểm tích lũy.
     * - Sau khi trừ sẽ cập nhật lại hạng thành viên.
     */
    public function thuHoiDiem(
        int $soDiem,
        ?string $noiDung = null
    ): void {
        // Số điểm phải lớn hơn 0
        if ($soDiem <= 0) {
            return;
        }

        // Không cho thu hồi vượt quá điểm hiện tại
        if ($soDiem > $this->diem_hien_tai) {
            throw new \InvalidArgumentException(
                'Số điểm thu hồi không được lớn hơn số điểm hiện tại.'
            );
        }


        $soDiemConCanThuHoi = $soDiem;

        /*
    |--------------------------------------------------------------------------
    | Trừ điểm trong các khoản điểm đã cộng
    |--------------------------------------------------------------------------
    |
    | Ưu tiên trừ khoản điểm gần hết hạn trước.
    |
    */

        $cacKhoanDiem = $this->lichSuDiems()
            ->where('loai_giao_dich', 'cong_diem')
            ->where('diem_con_lai', '>', 0)
            ->orderByRaw(
                'CASE WHEN ngay_het_han IS NULL THEN 1 ELSE 0 END'
            )
            ->orderBy('ngay_het_han')
            ->orderBy('id')
            ->get();

        foreach ($cacKhoanDiem as $khoanDiem) {

            if ($soDiemConCanThuHoi <= 0) {
                break;
            }

            $diemConLai = (int) $khoanDiem->diem_con_lai;

            $diemThuHoi = min(
                $diemConLai,
                $soDiemConCanThuHoi
            );

            $khoanDiem->decrement(
                'diem_con_lai',
                $diemThuHoi
            );

            $soDiemConCanThuHoi -= $diemThuHoi;
        }

        /*
    |--------------------------------------------------------------------------
    | Cập nhật điểm hiện tại
    |--------------------------------------------------------------------------
    */

        $this->decrement(
            'diem_hien_tai',
            $soDiem
        );


        $this->lichSuDiems()->create([
            've_xem_phim_id' => null,
            'loai_giao_dich' => 'tru_diem',
            'so_diem' => $soDiem,
            'diem_con_lai' => 0,
            'ngay_het_han' => null,
            'noi_dung' => $noiDung ?? 'Admin thu hồi điểm.',
        ]);

        /*
    |--------------------------------------------------------------------------
    | Cập nhật lại hạng thành viên
    |--------------------------------------------------------------------------
    */

        $this->refresh();

        $this->capNhatHangThanhVien();
    }
    public function xuLyDiemHetHan(): void
    {
        $cacKhoanDiemHetHan = $this->lichSuDiems()
            ->where('loai_giao_dich', 'cong_diem')
            ->where('diem_con_lai', '>', 0)
            ->whereNotNull('ngay_het_han')
            ->where('ngay_het_han', '<=', now())
            ->orderBy('ngay_het_han')
            ->orderBy('id')
            ->get();

        foreach ($cacKhoanDiemHetHan as $khoanDiem) {

            $soDiemHetHan = (int) $khoanDiem->diem_con_lai;

            if ($soDiemHetHan <= 0) {
                continue;
            }

            /*
         * Chỉ trừ điểm hiện tại.
         *
         * Không được trừ tong_diem_tich_luy.
         */
            $soDiemTru = min(
                $soDiemHetHan,
                (int) $this->diem_hien_tai
            );

            if ($soDiemTru > 0) {
                $this->decrement('diem_hien_tai', $soDiemTru);
            }

            /*
         * Đánh dấu khoản điểm này đã hết hạn.
         */
            $khoanDiem->update([
                'diem_con_lai' => 0,
            ]);
        }

        $this->refresh();
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

    /**
     * Người giới thiệu thành viên này
     */
    public function nguoiGioiThieu()
    {
        return $this->belongsTo(
            ThanhVien::class,
            'nguoi_gioi_thieu_id'
        );
    }


    /**
     * Danh sách người được thành viên này giới thiệu
     */
    public function thanhVienDuocGioiThieu()
    {
        return $this->hasMany(
            ThanhVien::class,
            'nguoi_gioi_thieu_id'
        );
    }


    /**
     * Tạo mã giới thiệu
     */
    public static function taoMaGioiThieu($id): string
    {
        return 'GT-TV' . str_pad(
            $id,
            6,
            '0',
            STR_PAD_LEFT
        );
    }

    public function gioiThieus()
    {
        return $this->hasMany(
            GioiThieuThanhVien::class,
            'thanh_vien_id'
        );
    }
}
