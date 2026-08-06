<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\VeXemPhimGhe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SoatVeController extends Controller
{
    /**
     * Hiển thị trang soát vé.
     */
    public function index()
    {
        return view('staff.soat-ve.index');
    }

    /**
     * Xử lý quét QR của từng ghế.
     *
     * Luồng trạng thái:
     * VeXemPhim: da_thanh_toan -> da_in -> da_su_dung
     * VeXemPhimGhe: chua_su_dung -> da_su_dung
     */
    public function check(Request $request)
    {
        $validated = $request->validate([
            'qr_code' => [
                'required',
                'string',
                'max:150',
            ],
        ], [
            'qr_code.required' => 'Không nhận được nội dung QR.',
        ]);

        try {
            $result = DB::transaction(function () use ($validated) {
                $seatTicket = VeXemPhimGhe::query()
                    ->with([
                        'veXemPhim.suatChieu.phim',
                        'veXemPhim.suatChieu.phongChieu',
                    ])
                    ->where(
                        'ma_qr',
                        trim($validated['qr_code'])
                    )
                    ->lockForUpdate()
                    ->first();

                if (!$seatTicket) {
                    throw new \RuntimeException(
                        'Mã QR không tồn tại hoặc không hợp lệ.'
                    );
                }

                $ve = $seatTicket->veXemPhim;

                if (!$ve) {
                    throw new \RuntimeException(
                        'Không tìm thấy thông tin vé gốc.'
                    );
                }

                if ($ve->trang_thai === 'da_huy') {
                    throw new \RuntimeException(
                        'Vé này đã bị hủy.'
                    );
                }

                /*
                 * Chỉ cho phép soát vé sau khi vé đã được in/phát hành.
                 * da_su_dung vẫn được chấp nhận để có thể báo chính xác
                 * trạng thái của từng QR ghế nếu một giao dịch có nhiều ghế.
                 */
                if (!in_array(
                    $ve->trang_thai,
                    ['da_in', 'da_su_dung'],
                    true
                )) {
                    if ($ve->trang_thai === 'da_thanh_toan') {
                        throw new \RuntimeException(
                            'Vé này chưa được in. Vui lòng in vé trước khi soát.'
                        );
                    }

                    throw new \RuntimeException(
                        'Vé chưa ở trạng thái cho phép soát.'
                    );
                }

                if (
                    $seatTicket->trang_thai
                    === VeXemPhimGhe::DA_HUY
                ) {
                    throw new \RuntimeException(
                        'Vé ghế '
                        . $seatTicket->ma_ghe
                        . ' đã bị hủy.'
                    );
                }

                if (
                    $seatTicket->trang_thai
                    === VeXemPhimGhe::DA_SU_DUNG
                ) {
                    $usedAt = optional(
                        $seatTicket->checked_in_at
                    )->format('d/m/Y H:i:s');

                    throw new \RuntimeException(
                        'QR ghế '
                        . $seatTicket->ma_ghe
                        . ' đã được sử dụng'
                        . ($usedAt ? ' lúc ' . $usedAt : '')
                        . '.'
                    );
                }

                $seatTicket->update([
                    'trang_thai' => VeXemPhimGhe::DA_SU_DUNG,
                    'checked_in_at' => now(),
                    'checked_in_by' => auth()->id(),
                ]);

                /*
                 * Chỉ chuyển vé gốc sang da_su_dung khi không còn
                 * ghế nào ở trạng thái chua_su_dung.
                 */
                $remainingSeatCount = VeXemPhimGhe::where(
                    've_xem_phim_id',
                    $ve->id
                )
                    ->where(
                        'trang_thai',
                        VeXemPhimGhe::CHUA_SU_DUNG
                    )
                    ->count();

                if ($remainingSeatCount === 0) {
                    $ve->update([
                        'trang_thai' => 'da_su_dung',
                    ]);
                }

                return [
                    'seat_ticket' => $seatTicket->fresh(),
                    'ticket' => $ve->fresh(),
                    'remaining' => $remainingSeatCount,
                ];
            });

            $seatTicket = $result['seat_ticket'];
            $ve = $result['ticket'];

            return back()->with(
                'success',
                'Soát vé thành công: '
                . $ve->ten_phim
                . ' - Ghế '
                . $seatTicket->ma_ghe
                . '. Còn '
                . $result['remaining']
                . ' ghế chưa vào.'
            );
        } catch (\Throwable $e) {
            report($e);

            return back()->with(
                'error',
                $e->getMessage()
            );
        }
    }
}
