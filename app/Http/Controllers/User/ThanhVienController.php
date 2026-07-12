<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ThanhVien;
use Illuminate\Support\Facades\Auth;

class ThanhVienController extends Controller
{
    /**
     * Hiển thị thông tin thẻ thành viên.
     * Nếu tài khoản cũ chưa có thẻ thì tự động tạo thẻ.
     */
    public function index()
    {
        $nguoiDung = Auth::user();

        $thanhVien = $nguoiDung->thanhVien;


        // Nếu tài khoản cũ chưa có thẻ thì tạo mới
        if (!$thanhVien) {

            $thanhVien = ThanhVien::create([
                'nguoi_dung_id' => $nguoiDung->id,
                'ma_thanh_vien' => 'TV' . str_pad($nguoiDung->id, 6, '0', STR_PAD_LEFT),
                'ma_gioi_thieu' => 'GT' . str_pad($nguoiDung->id, 6, '0', STR_PAD_LEFT),
                'hang_thanh_vien' => 'member',
                'diem_hien_tai' => 0,
                'tong_diem_tich_luy' => 0,
                'ngay_tham_gia' => now(),
            ]);
        }


        // User cũ chưa có mã giới thiệu thì tự cấp
        if (!$thanhVien->ma_gioi_thieu) {

            $thanhVien->update([
                'ma_gioi_thieu' => 'GT' . str_pad(
                    $nguoiDung->id,
                    6,
                    '0',
                    STR_PAD_LEFT
                )
            ]);

            $thanhVien->refresh();
        }


        $lichSuDiem = $thanhVien
            ->lichSuDiems()
            ->latest()
            ->paginate(8);


        $nguoiDaGioiThieu = $nguoiDung
            ->nguoiDuocGioiThieu()
            ->latest()
            ->get();

        $rankConfig = [
            'member' => [
                'label' => 'Member',
                'range' => '0 - 499 điểm',
                'min' => 0,
                'next' => 500,
                'benefit' => 'Tích điểm cơ bản cho mỗi lần đặt vé.',
                'icon' => 'fa-solid fa-medal',
            ],
            'silver' => [
                'label' => 'Silver',
                'range' => '500 - 999 điểm',
                'min' => 500,
                'next' => 1000,
                'benefit' => 'Tích điểm nhanh hơn với hệ số x1.05.',
                'icon' => 'fa-solid fa-award',
            ],
            'gold' => [
                'label' => 'Gold',
                'range' => '1000 - 1999 điểm',
                'min' => 1000,
                'next' => 2000,
                'benefit' => 'Nhận ưu đãi riêng và hệ số tích điểm x1.10.',
                'icon' => 'fa-solid fa-crown',
            ],
            'platinum' => [
                'label' => 'Platinum',
                'range' => 'Từ 2000 điểm',
                'min' => 2000,
                'next' => null,
                'benefit' => 'Quyền lợi cao nhất với hệ số tích điểm x1.15.',
                'icon' => 'fa-solid fa-gem',
            ],
        ];

        $currentRankKey = $thanhVien->hang_thanh_vien ?? 'member';
        $currentRank = $rankConfig[$currentRankKey] ?? $rankConfig['member'];
        $nextRankPoint = $currentRank['next'];
        $rankBasePoint = $currentRank['min'];
        $totalPoint = (int) $thanhVien->tong_diem_tich_luy;

        $rankProgress = $nextRankPoint
            ? min(100, max(0, (($totalPoint - $rankBasePoint) / max(1, $nextRankPoint - $rankBasePoint)) * 100))
            : 100;

        $pointsToNextRank = $nextRankPoint
            ? max(0, $nextRankPoint - $totalPoint)
            : 0;

        $pointSummary = [
            'earned' => $thanhVien->lichSuDiems()->where('loai_giao_dich', 'cong_diem')->sum('so_diem'),
            'spent' => $thanhVien->lichSuDiems()->where('loai_giao_dich', 'tru_diem')->sum('so_diem'),
            'transactions' => $thanhVien->lichSuDiems()->count(),
            'referral_points' => $thanhVien->gioiThieus()->sum('diem_thuong'),
        ];


        return view('user.thanh_vien.index', compact(
            'thanhVien',
            'lichSuDiem',
            'nguoiDaGioiThieu',
            'rankConfig',
            'currentRank',
            'currentRankKey',
            'rankProgress',
            'pointsToNextRank',
            'pointSummary'
        ));
    }
}
