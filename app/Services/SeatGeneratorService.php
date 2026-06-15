<?php

namespace App\Services;

use App\Models\GheNgoi;
use App\Models\HangGhe;
use App\Models\LoaiGhe;
use App\Models\PhongChieu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SeatGeneratorService
{
    protected array $rowLabels = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T'];

    /**
     * Tạo ghế tự động cho một phòng chiếu
     *
     * Rule:
     * - 3 hàng đầu: ghế thường
     * - các hàng giữa (trừ hàng cuối): ghế VIP
     * - hàng cuối: ghế couple, gộp 2 ghế thường thành 1 ô couple
     */
    public function generateSeats(
        PhongChieu $phongChieu,
        int $soHang = 8,
        int $soCot = 10,
        int $loaiGheThuongId = 1,
        ?int $loaiGheVipId = null,
        ?int $loaiGheCoupleId = null,
        bool $xoaGheCu = true
    ): array {
        $soCotCouple = max(2, (int) floor($soCot / 2));
        $soCotThuongVip = $soCot - $soCotCouple;

        $ketQua = [
            'phong_chieu_id' => $phongChieu->id,
            'tong_so_hang' => $soHang,
            'tong_so_cot' => $soCot,
            'so_cot_thuong_vip' => $soCotThuongVip,
            'so_cot_couple' => $soCotCouple,
            'tong_so_ghe' => 0,
            'ghe_thuong' => 0,
            'ghe_vip' => 0,
            'ghe_couple' => 0,
            'da_tao' => [],
        ];

        DB::beginTransaction();

        try {
            if ($xoaGheCu) {
                $phongChieu->gheNgois()->delete();
                Log::info("Đã xóa ghế cũ của phòng chiếu: {$phongChieu->id}");
            }

            $loaiGheThuong = LoaiGhe::find($loaiGheThuongId);
            $loaiGheVip = $loaiGheVipId ? LoaiGhe::find($loaiGheVipId) : null;
            $loaiGheCouple = $loaiGheCoupleId ? LoaiGhe::find($loaiGheCoupleId) : null;

            if ($soHang < 3) {
                throw new \InvalidArgumentException('Phòng cần ít nhất 3 hàng để chia thường / VIP / couple.');
            }

            for ($i = 0; $i < $soHang; $i++) {
                $tenHang = $this->rowLabels[$i] ?? chr(65 + $i);
                $hangIndex = $i + 1;

                $hangGhe = HangGhe::create([
                    'phong_chieu_id' => $phongChieu->id,
                    'ten_hang' => $tenHang,
                ]);

                $isThuongRow = $hangIndex <= 3;
                $isCoupleRow = $hangIndex === $soHang;

                if ($isThuongRow) {
                    for ($j = 1; $j <= $soCot; $j++) {
                        $maGhe = $tenHang . $j;

                        $ghe = GheNgoi::create([
                            'phong_chieu_id' => $phongChieu->id,
                            'hang_ghe_id' => $hangGhe->id,
                            'loai_ghe_id' => $loaiGheThuongId,
                            'ma_ghe' => $maGhe,
                            'cot' => $j,
                            'trang_thai' => 'hoat_dong',
                        ]);

                        $ketQua['da_tao'][] = $ghe->id;
                        $ketQua['tong_so_ghe']++;
                        $ketQua['ghe_thuong']++;
                    }
                } elseif ($isCoupleRow) {
                    for ($pairIndex = 1; $pairIndex <= $soCotCouple; $pairIndex++) {
                        $cotStart = ($pairIndex - 1) * 2 + 1;
                        $cotEnd = $pairIndex * 2;
                        // Mã nhóm couple - 2 ghế cùng nhóm
                        $coupleGroupId = $hangGhe->ky_hieu . '_CPL_' . $pairIndex . '_P' . $phongChieu->id;

                        // Tạo ghế thứ 1 (cột start)
                        $ghe1 = GheNgoi::create([
                            'phong_chieu_id' => $phongChieu->id,
                            'hang_ghe_id' => $hangGhe->id,
                            'loai_ghe_id' => $loaiGheCoupleId ?? $loaiGheThuongId,
                            'ma_ghe' => $tenHang . $cotStart,
                            'cot' => $cotStart,
                            'couple_group_id' => $coupleGroupId,
                            'trang_thai' => 'hoat_dong',
                        ]);

                        // Tạo ghế thứ 2 (cột end) - cùng nhóm couple
                        $ghe2 = GheNgoi::create([
                            'phong_chieu_id' => $phongChieu->id,
                            'hang_ghe_id' => $hangGhe->id,
                            'loai_ghe_id' => $loaiGheCoupleId ?? $loaiGheThuongId,
                            'ma_ghe' => $tenHang . $cotEnd,
                            'cot' => $cotEnd,
                            'couple_group_id' => $coupleGroupId,
                            'trang_thai' => 'hoat_dong',
                        ]);

                        $ketQua['da_tao'][] = $ghe1->id;
                        $ketQua['da_tao'][] = $ghe2->id;
                        $ketQua['tong_so_ghe'] += 2;
                        $ketQua['ghe_couple'] += 2;
                    }
                } else {
                    for ($j = 1; $j <= $soCotThuongVip; $j++) {
                        $maGhe = $tenHang . $j;

                        $ghe = GheNgoi::create([
                            'phong_chieu_id' => $phongChieu->id,
                            'hang_ghe_id' => $hangGhe->id,
                            'loai_ghe_id' => $loaiGheVipId ?? $loaiGheThuongId,
                            'ma_ghe' => $maGhe,
                            'cot' => $j,
                            'trang_thai' => 'hoat_dong',
                        ]);

                        $ketQua['da_tao'][] = $ghe->id;
                        $ketQua['tong_so_ghe']++;
                        $ketQua['ghe_vip']++;
                    }
                }
            }

            $phongChieu->update([
                'suc_chua' => $ketQua['tong_so_ghe'],
            ]);

            DB::commit();

            Log::info("Đã tạo ghế thành công cho phòng chiếu: {$phongChieu->id}", $ketQua);

            return $ketQua;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi tạo ghế: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Tạo ghế với cấu hình tự động đơn giản
     * Hàng giữa (5-7) là VIP, 2 ghế cuối mỗi hàng là Couple
     */
    public function generateSeatsSimple(
        PhongChieu $phongChieu,
        int $soHang = 8,
        int $soCot = 10,
        int $loaiGheThuongId = 1,
        ?int $loaiGheVipId = null,
        ?int $loaiGheCoupleId = null
    ): array {
        return $this->generateSeats(
            $phongChieu,
            $soHang,
            $soCot,
            $loaiGheThuongId,
            $loaiGheVipId,
            $loaiGheCoupleId,
            [5, 6, 7],
            true
        );
    }

    /**
     * Cập nhật loại ghế hàng loạt theo hàng
     */
    public function updateLoaiGheByRow(HangGhe $hangGhe, int $loaiGheId): int
    {
        $updated = $hangGhe->gheNgois()->update([
            'loai_ghe_id' => $loaiGheId,
        ]);

        Log::info("Đã cập nhật loại ghế cho hàng {$hangGhe->ten_hang}", [
            'so_ghe' => $updated,
            'loai_ghe_id' => $loaiGheId,
        ]);

        return $updated;
    }

    /**
     * Khóa/Mở khóa ghế bảo trì
     */
    public function toggleMaintenance(GheNgoi $ghe, bool $isMaintenance = true): GheNgoi
    {
        $ghe->update([
            'trang_thai' => $isMaintenance ? 'bao_tri' : 'hoat_dong',
        ]);

        Log::info("Đã cập nhật trạng thái ghế {$ghe->ma_ghe}", [
            'trang_thai' => $ghe->trang_thai,
        ]);

        return $ghe;
    }

    /**
     * Khóa/Mở khóa tất cả ghế trong phòng chiếu
     */
    public function toggleMaintenanceAll(PhongChieu $phongChieu, bool $isMaintenance = true): int
    {
        $updated = $phongChieu->gheNgois()->update([
            'trang_thai' => $isMaintenance ? 'bao_tri' : 'hoat_dong',
        ]);

        Log::info("Đã cập nhật trạng thái tất cả ghế trong phòng {$phongChieu->ten_phong}", [
            'so_ghe' => $updated,
            'trang_thai' => $isMaintenance ? 'bao_tri' : 'hoat_dong',
        ]);

        return $updated;
    }

    /**
     * Lấy sơ đồ ghế theo phòng chiếu (dạng ma trận)
     *
     * Luôn hiển thị theo pattern:
     * - 3 hàng đầu: thường
     * - hàng cuối: couple gộp 2 ghế
     * - còn lại: vip
     *
     * Nếu DB chưa có cấu trúc couple thì vẫn hiển thị đều,
     * chỉ gộp cặp khi có dữ liệu couple/cot_end.
     */
    public function getSeatMap(PhongChieu $phongChieu): array
    {
        $phongChieu->loadMissing('hangGhes');
        $totalRows = max($phongChieu->hangGhes->count(), 1);

        $gheNgois = $phongChieu->gheNgois()
            ->with(['hangGhe', 'loaiGhe'])
            ->orderBy('hang_ghe_id')
            ->orderBy('cot')
            ->get();

        $maxCol = 0;
        $grouped = [];
        foreach ($gheNgois as $ghe) {
            $rowLabel = $ghe->hangGhe->ten_hang;
            $grouped[$rowLabel][$ghe->cot] = $ghe;
            $maxCol = max($maxCol, (int) $ghe->cot);
        }

        $seatMap = [];
        foreach ($phongChieu->hangGhes as $hangGhe) {
            $rowLabel = $hangGhe->ten_hang;
            $rowIndex = $this->getRowIndex($rowLabel);
            $isRegular = $rowIndex <= 3 || $totalRows < 3;
            $isCouple = !$isRegular && $rowIndex === $totalRows;

            $cols = [];
            if ($isCouple && $maxCol >= 2) {
                $pairCount = (int) floor($maxCol / 2);
                for ($p = 1; $p <= $pairCount; $p++) {
                    $start = ($p - 1) * 2 + 1;
                    $end = $p * 2;

                    // Lấy 2 ghế trong cùng 1 cặp couple
                    $ghe1 = $grouped[$rowLabel][$start] ?? null;
                    $ghe2 = $grouped[$rowLabel][$end] ?? null;

                    // Lấy couple_group_id (ưu tiên từ DB, nếu không có thì tạo mới)
                    $coupleGroupId = $ghe1->couple_group_id
                        ?? $ghe2->couple_group_id
                        ?? ('CPL_' . $rowLabel . '_' . $p);

                    // Siblings là cả 2 ghế cùng nhóm
                    $siblings = [];
                    if ($ghe1) $siblings[] = $ghe1->id;
                    if ($ghe2) $siblings[] = $ghe2->id;

                    // Lấy thông tin loại ghế - ưu tiên từ ghế1
                    $refGhe = $ghe1 ?? $ghe2;
                    $loai = 'Thường';
                    $phuThu = 0;
                    $trangThai = 'hoat_dong';
                    $loaiGheId = null;
                    $mauSac = '#666666';
                    $maGheLeft = $rowLabel . $start;
                    $maGheRight = $rowLabel . $end;

                    if ($refGhe) {
                        $loai = $refGhe->loaiGhe->ten_loai ?? 'Thường';
                        $phuThu = $refGhe->loaiGhe->phu_thu ?? 0;
                        $loaiGheId = $refGhe->loai_ghe_id;
                        $mauSac = $refGhe->loaiGhe->mau_sac ?? '#666666';
                        $trangThai = $refGhe->trang_thai;
                    }

                    // === Ghế 1 (trái) ===
                    if ($ghe1) {
                        $cols[$start] = [
                            'id' => $ghe1->id,
                            'ma_ghe' => $ghe1->ma_ghe ?: $maGheLeft,
                            'loai_ghe' => $ghe1->loaiGhe->ten_loai ?? $loai,
                            'loai_ghe_id' => $ghe1->loai_ghe_id ?? $loaiGheId,
                            'mau_sac' => $ghe1->loaiGhe->mau_sac ?? $mauSac,
                            'phu_thu' => $ghe1->loaiGhe->phu_thu ?? $phuThu,
                            'trang_thai' => $ghe1->trang_thai,
                            'is_couple' => true,
                            'cot_end' => $end,
                            'display_number' => $start,
                            'couple_group_id' => $coupleGroupId,
                            'couple_siblings' => $siblings,
                            'couple_position' => 'left',
                        ];
                    } else {
                        // Ghế trống chưa có trong DB
                        $cols[$start] = [
                            'id' => null,
                            'ma_ghe' => $maGheLeft,
                            'loai_ghe' => $loai,
                            'loai_ghe_id' => $loaiGheId,
                            'mau_sac' => $mauSac,
                            'phu_thu' => $phuThu,
                            'trang_thai' => $trangThai,
                            'is_couple' => true,
                            'cot_end' => $end,
                            'display_number' => $start,
                            'couple_group_id' => $coupleGroupId,
                            'couple_siblings' => $siblings,
                            'couple_position' => 'left',
                        ];
                    }

                    // === Ghế 2 (phải) ===
                    if ($ghe2) {
                        $cols[$end] = [
                            'id' => $ghe2->id,
                            'ma_ghe' => $ghe2->ma_ghe ?: $maGheRight,
                            'loai_ghe' => $ghe2->loaiGhe->ten_loai ?? $loai,
                            'loai_ghe_id' => $ghe2->loai_ghe_id ?? $loaiGheId,
                            'mau_sac' => $ghe2->loaiGhe->mau_sac ?? $mauSac,
                            'phu_thu' => $ghe2->loaiGhe->phu_thu ?? $phuThu,
                            'trang_thai' => $ghe2->trang_thai,
                            'is_couple' => true,
                            'cot_end' => $start,
                            'display_number' => $end,
                            'couple_group_id' => $coupleGroupId,
                            'couple_siblings' => $siblings,
                            'couple_position' => 'right',
                        ];
                    } else {
                        $cols[$end] = [
                            'id' => null,
                            'ma_ghe' => $maGheRight,
                            'loai_ghe' => $loai,
                            'loai_ghe_id' => $loaiGheId,
                            'mau_sac' => $mauSac,
                            'phu_thu' => $phuThu,
                            'trang_thai' => $trangThai,
                            'is_couple' => true,
                            'cot_end' => $start,
                            'display_number' => $end,
                            'couple_group_id' => $coupleGroupId,
                            'couple_siblings' => $siblings,
                            'couple_position' => 'right',
                        ];
                    }
                }
            } elseif ($isRegular) {
                foreach ($grouped[$rowLabel] ?? [] as $cot => $ghe) {
                    $cols[$cot] = [
                        'id' => $ghe->id,
                        'ma_ghe' => $ghe->ma_ghe,
                        'loai_ghe' => $ghe->loaiGhe->ten_loai ?? 'Thường',
                        'loai_ghe_id' => $ghe->loai_ghe_id,
                        'mau_sac' => $ghe->loaiGhe->mau_sac ?? '#666666',
                        'phu_thu' => $ghe->loaiGhe->phu_thu ?? 0,
                        'trang_thai' => $ghe->trang_thai,
                        'is_couple' => false,
                        'cot_end' => null,
                        'display_number' => $cot,
                    ];
                }
            } else {
                $vipMax = $maxCol > 0 ? $maxCol : 10;
                for ($j = 1; $j <= $vipMax; $j++) {
                    $ghe = $grouped[$rowLabel][$j] ?? null;
                    if ($ghe) {
                        $cols[$j] = [
                            'id' => $ghe->id,
                            'ma_ghe' => $ghe->ma_ghe,
                            'loai_ghe' => $ghe->loaiGhe->ten_loai ?? 'Thường',
                            'loai_ghe_id' => $ghe->loai_ghe_id,
                            'mau_sac' => $ghe->loaiGhe->mau_sac ?? '#666666',
                            'phu_thu' => $ghe->loaiGhe->phu_thu ?? 0,
                            'trang_thai' => $ghe->trang_thai,
                            'is_couple' => false,
                            'cot_end' => null,
                        ];
                    }
                }
            }

            if ($cols) {
                $seatMap[$rowLabel] = $cols;
            }
        }

        return $seatMap;
    }

    private function getRowIndex(string $rowLabel): int
    {
        $index = array_search($rowLabel, $this->rowLabels, true);
        if ($index === false) {
            $index = ord($rowLabel) - 65;
        }

        return $index + 1;
    }
}
