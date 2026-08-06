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
     * Sinh ghế theo cấu hình từng hàng (wizard).
     *
     * Mỗi phần tử của $cauHinhHang là:
     *  - ten_hang         (string, bắt buộc) - ví dụ "A", "B", "VIP1"...
     *  - la_hang_couple   (bool)              - hàng ghép đôi
     *  - loai_ghe_id      (int)               - loại ghế cho cả hàng
     *  - so_ghe           (int)               - tổng số ghế
     *  - cot_bat_dau      (int, mặc định 1)
     *  - buoc_cot         (int, mặc định 1)
     *  - vi_tri_bo_trong  (int[], mặc định []) - các cột bỏ trống (lối đi)
     *
     * Ví dụ:
     *  [
     *      ['ten_hang' => 'A', 'loai_ghe_id' => 1, 'so_ghe' => 12],
     *      ['ten_hang' => 'B', 'loai_ghe_id' => 1, 'so_ghe' => 12],
     *      ['ten_hang' => 'C', 'loai_ghe_id' => 1, 'so_ghe' => 14, 'vi_tri_bo_trong' => [7]],
     *      ['ten_hang' => 'D', 'loai_ghe_id' => 2, 'so_ghe' => 14, 'vi_tri_bo_trong' => [7]],
     *      ['ten_hang' => 'E', 'la_hang_couple' => true, 'loai_ghe_id' => 3, 'so_ghe' => 10],
     *  ]
     */
    public function generateSeatsFromConfig(PhongChieu $phongChieu, array $cauHinhHang, bool $xoaGheCu = true): array
    {
        $ketQua = [
            'phong_chieu_id' => $phongChieu->id,
            'tong_so_hang' => count($cauHinhHang),
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
                $phongChieu->hangGhes()->delete();
            }

            foreach ($cauHinhHang as $cauHinh) {
                $tenHang = trim((string) ($cauHinh['ten_hang'] ?? ''));
                if ($tenHang === '') {
                    throw new \InvalidArgumentException('Mỗi hàng phải có tên hàng.');
                }
                $laHangCouple = (bool) ($cauHinh['la_hang_couple'] ?? false);
                $loaiGheId = (int) ($cauHinh['loai_ghe_id'] ?? 0);
                $soGhe = (int) ($cauHinh['so_ghe'] ?? 0);
                $cotBatDau = (int) ($cauHinh['cot_bat_dau'] ?? 1);
                $viTriBoTrong = $cauHinh['vi_tri_bo_trong'] ?? [];

                if ($soGhe <= 0) continue;

                $hangGhe = HangGhe::create([
                    'phong_chieu_id' => $phongChieu->id,
                    'ten_hang' => $tenHang,
                    'la_hang_couple' => $laHangCouple,
                    'loai_ghe_mac_dinh_id' => $loaiGheId ?: null,
                ]);

                $soCotCanTao = $soGhe + count($viTriBoTrong);
                $cotHienTai = $cotBatDau;
                $pairIndex = 0;

                for ($i = 0; $i < $soCotCanTao; $i++) {
                    if (in_array($cotHienTai, $viTriBoTrong, true)) {
                        $cotHienTai++;
                        continue;
                    }

                    $coupleGroupId = null;
                    if ($laHangCouple) {
                        $pairIndex++;
                        $coupleGroupId = sprintf(
                            '%s_CPL_P%d_R%d',
                            $hangGhe->ten_hang,
                            $phongChieu->id,
                            $pairIndex
                        );
                    }

                    $ghe = GheNgoi::create([
                        'phong_chieu_id' => $phongChieu->id,
                        'hang_ghe_id' => $hangGhe->id,
                        'loai_ghe_id' => $loaiGheId ?: null,
                        'ma_ghe' => $tenHang . $cotHienTai,
                        'cot' => $cotHienTai,
                        'couple_group_id' => $coupleGroupId,
                        'trang_thai' => 'hoat_dong',
                    ]);

                    $ketQua['da_tao'][] = $ghe->id;
                    $ketQua['tong_so_ghe']++;

                    $loaiGhe = $ghe->loaiGhe;
                    if ($loaiGhe) {
                        if ($loaiGhe->la_couple) $ketQua['ghe_couple']++;
                        elseif (stripos($loaiGhe->ten_loai, 'vip') !== false) $ketQua['ghe_vip']++;
                        else $ketQua['ghe_thuong']++;
                    }

                    $cotHienTai++;
                }
            }

            $phongChieu->update(['suc_chua' => $ketQua['tong_so_ghe']]);

            DB::commit();
            Log::info("Đã tạo ghế theo cấu hình cho phòng {$phongChieu->id}", $ketQua);

            return $ketQua;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi tạo ghế theo cấu hình: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sinh ghế tự động đơn giản (giữ lại cho tương thích ngược nếu nơi khác còn gọi).
     * Hiện tại KHÔNG ép rule cứng nữa - ủy quyền sang wizard cấu hình.
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
        $cauHinh = [];
        for ($i = 0; $i < $soHang; $i++) {
            $tenHang = $this->rowLabels[$i] ?? chr(65 + $i);
            
            // Xác định loại ghế theo vị trí hàng:
            // - 3 hàng đầu (gần màn): Thường
            // - 1 hàng cuối (xa màn): Couple
            // - Hàng giữa: VIP
            $loaiGheId = $loaiGheThuongId; // Mặc định thường
            $laHangCouple = false;
            
            if ($i >= $soHang - 1 && $loaiGheCoupleId) {
                // 1 hàng cuối: Couple
                $loaiGheId = $loaiGheCoupleId;
                $laHangCouple = true;
            } elseif ($i >= 3 && $loaiGheVipId) {
                // Hàng 4 trở đi (không tính 1 hàng cuối): VIP
                $loaiGheId = $loaiGheVipId;
            }
            
            $cauHinh[] = [
                'ten_hang' => $tenHang,
                'la_hang_couple' => $laHangCouple,
                'loai_ghe_id' => $loaiGheId,
                'so_ghe' => $laHangCouple
                    ? max(2, (int) floor($soCot / 2)) * 2
                    : $soCot,
            ];
        }
        return $this->generateSeatsFromConfig($phongChieu, $cauHinh, $xoaGheCu);
    }

    /**
     * Sinh ghế đơn giản - tương thích ngược.
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
            true
        );
    }

    /**
     * Cập nhật loại ghế hàng loạt theo hàng.
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
     * Tự gán couple_group_id cho một ghế couple vừa tạo.
     * Ghép với ghế liền kề cùng hàng (cột lẻ với cột chẵn kế tiếp, hoặc ngược lại).
     */
    public function attachCoupleGroupForSeat(GheNgoi $ghe): ?string
    {
        if (!$ghe->loaiGhe || !$ghe->loaiGhe->la_couple) {
            return null;
        }

        $phaiGhepVoiCot = $ghe->cot % 2 === 0 ? $ghe->cot - 1 : $ghe->cot + 1;
        if ($phaiGhepVoiCot < 1) return null;

        $doiTac = GheNgoi::where('hang_ghe_id', $ghe->hang_ghe_id)
            ->where('cot', $phaiGhepVoiCot)
            ->whereNull('couple_group_id')
            ->first();

        if ($doiTac) {
            $groupId = sprintf('CPL_P%d_H%d_R%d_%d',
                $ghe->phong_chieu_id,
                $ghe->hang_ghe_id,
                (int) (($ghe->cot + 1) / 2),
                $ghe->hang_ghe_id
            );
            $ghe->couple_group_id = $groupId;
            $ghe->save();
            $doiTac->couple_group_id = $groupId;
            $doiTac->save();
            return $groupId;
        }

        return $ghe->couple_group_id;
    }

    /**
     * Khóa/Mở khóa ghế bảo trì.
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
     * Khóa/Mở khóa tất cả ghế trong phòng chiếu.
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
     * Lấy sơ đồ ghế theo phòng chiếu.
     *
     * Ghép cặp couple dựa vào:
     *  - Loại ghế (loaiGhe.la_couple = true), HOẶC
     *  - Cột ghế đã có couple_group_id
     *
     * Không còn ép rule "hàng cuối = couple" - quyết định thuộc về cấu hình bạn đã tạo.
     */
    public function getSeatMap(PhongChieu $phongChieu): array
    {
        $gheNgois = GheNgoi::where('phong_chieu_id', $phongChieu->id)
            ->with(['hangGhe', 'loaiGhe', 'lichBaoTris'])
            ->orderBy('hang_ghe_id')
            ->orderBy('cot')
            ->get();

        $hangGhes = HangGhe::where('phong_chieu_id', $phongChieu->id)->get();

        $grouped = [];
        $maxCol = 0;
        foreach ($gheNgois as $ghe) {
            $rowLabel = $ghe->hangGhe->ten_hang;
            $grouped[$rowLabel][$ghe->cot] = $ghe;
            $maxCol = max($maxCol, (int) $ghe->cot);
        }

        $seatMap = [];
        foreach ($hangGhes as $hangGhe) {
            $rowLabel = $hangGhe->ten_hang;
            $cols = [];

            $hangGhes = $grouped[$rowLabel] ?? [];
            if (!$hangGhes) continue;

            // Lấy danh sách cột trong hàng, sort tăng dần
            $cotArr = array_keys($hangGhes);
            sort($cotArr);

            $i = 0;
            $n = count($cotArr);
            while ($i < $n) {
                $cot = $cotArr[$i];
                $ghe = $hangGhes[$cot];

                $laCouple = ($ghe->loaiGhe && $ghe->loaiGhe->la_couple)
                    || !empty($ghe->couple_group_id);

                if ($laCouple && $i + 1 < $n && $cotArr[$i + 1] === $cot + 1) {
                    $gheSau = $hangGhes[$cotArr[$i + 1]];
                    $coupleGroupId = $ghe->couple_group_id
                        ?? $gheSau->couple_group_id
                        ?? ('CPL_' . $rowLabel . '_' . $cot);

                    $siblings = array_values(array_filter([
                        $ghe->id ?? null,
                        $gheSau->id ?? null,
                    ]));

                    $cols[$cot] = $this->buildSeatNode($ghe, $rowLabel, $cot, true, $cot + 1, $coupleGroupId, $siblings, 'left');
                    $cols[$cot + 1] = $this->buildSeatNode($gheSau, $rowLabel, $cot + 1, true, $cot, $coupleGroupId, $siblings, 'right');
                    $i += 2;
                } else {
                    $cols[$cot] = $this->buildSeatNode($ghe, $rowLabel, $cot, false, null, $ghe->couple_group_id, [], null);
                    $i += 1;
                }
            }

            $seatMap[$rowLabel] = $cols;
        }

        return $seatMap;
    }

    protected function buildSeatNode(
        ?GheNgoi $ghe,
        string $rowLabel,
        int $cot,
        bool $isCouple,
        ?int $cotEnd,
        ?string $coupleGroupId,
        array $siblings,
        ?string $position
    ): array {
        $maGhe = $ghe->ma_ghe ?: ($rowLabel . $cot);
        $loai = $ghe->loaiGhe->ten_loai ?? 'Thường';
        $phuThu = (float) ($ghe->loaiGhe->phu_thu ?? 0);
        $mauSac = $ghe->loaiGhe->mau_sac ?? '#666666';
        $trangThai = $ghe->trang_thai ?? 'hoat_dong';

        // Lấy thông tin bảo trì hiện tại từ lichBaoTris đã load
        $lichId = null;
        $thoiGianKetThuc = null;
        $isUnlimitedMaintenance = false;

        if ($ghe) {
            $now = now();
            $lichBaoTri = $ghe->lichBaoTris
                ->whereIn('trang_thai', ['cho_thuc_hien', 'dang_thuc_hien'])
                ->filter(function ($lich) use ($now) {
                    return $lich->thoi_gian_bat_dau <= $now;
                })
                ->sortByDesc('thoi_gian_bat_dau')
                ->first();

            if ($lichBaoTri) {
                $lichId = $lichBaoTri->id;
                $thoiGianKetThuc = $lichBaoTri->thoi_gian_ket_thuc
                    ? \Carbon\Carbon::parse($lichBaoTri->thoi_gian_ket_thuc)->format('d/m/Y H:i')
                    : null;
                $isUnlimitedMaintenance = !$lichBaoTri->thoi_gian_ket_thuc;
            }
        }

        return [
            'id' => $ghe->id ?? null,
            'ma_ghe' => $maGhe,
            'loai_ghe' => $loai,
            'loai_ghe_id' => $ghe->loai_ghe_id ?? null,
            'mau_sac' => $mauSac,
            'phu_thu' => $phuThu,
            'trang_thai' => $trangThai,
            'is_couple' => $isCouple,
            'cot_end' => $cotEnd,
            'display_number' => $cot,
            'couple_group_id' => $coupleGroupId,
            'couple_siblings' => $siblings,
            'couple_position' => $position,
            // Thông tin bảo trì có thời hạn
            'lich_id' => $lichId,
            'thoi_gian_ket_thuc' => $thoiGianKetThuc,
            'is_unlimited_maintenance' => $isUnlimitedMaintenance,
        ];
    }
}
