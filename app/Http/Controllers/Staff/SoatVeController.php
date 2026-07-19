<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\VeXemPhim;
use Illuminate\Http\Request;
use App\Models\VeXemPhimGhe;
use Illuminate\Support\Facades\DB;

class SoatVeController extends Controller
{
    /**
     * Hiển thị trang soát vé.
     * Trang này cho phép nhân viên nhập mã vé hoặc mã lấy từ QR để kiểm tra.
     */
    public function index()
    {
        return view('staff.soat-ve.index');
    }

    /**
     * Xử lý kiểm tra vé.
     * Nếu vé hợp lệ thì hệ thống sẽ chuyển trạng thái vé sang "đã sử dụng".
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
            'qr_code.required' =>
            'Không nhận được nội dung QR.',
        ]);

        try {
            $result = DB::transaction(function () use ($validated) {
                $seatTicket = VeXemPhimGhe::query()
                    ->with([
                        'veXemPhim.suatChieu.phim',
                        'veXemPhim.suatChieu.phongChieu',
                    ])
                    ->where('ma_qr', trim($validated['qr_code']))
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

                if ($seatTicket->trang_thai === 'da_huy') {
                    throw new \RuntimeException(
                        'Vé ghế '
                            . $seatTicket->ma_ghe
                            . ' đã bị hủy.'
                    );
                }

                if ($seatTicket->trang_thai === 'da_su_dung') {
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

                /*
             * Có thể bổ sung giới hạn chỉ cho vào trước giờ chiếu.
             */
                $seatTicket->update([
                    'trang_thai' =>
                    VeXemPhimGhe::DA_SU_DUNG,

                    'checked_in_at' => now(),

                    'checked_in_by' => auth()->id(),
                ]);

                /*
             * Chỉ đánh dấu vé gốc đã sử dụng khi tất cả ghế
             * trong giao dịch đều đã được quét.
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
                    'seat_ticket' => $seatTicket,
                    'ticket' => $ve,
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
