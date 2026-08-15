<?php

namespace App\Console\Commands;

use App\Models\ThongBaoCaNhan;
use App\Models\VeXemPhim;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HetHanVietQrStaff extends Command
{
    protected $signature = 'staff:vietqr-het-han';

    protected $description = 'Tự động hết hạn các vé VietQR tại quầy đã quá thời gian giữ ghế và tạo thông báo cho nhân viên';

    public function handle(): int
    {
        $expiredIds = VeXemPhim::query()
            ->where('loai_ve', 'tai_quay')
            ->where('payment_method', 'vietqr')
            ->where('trang_thai', 'cho_thanh_toan')
            ->whereNotNull('thoi_gian_het_han')
            ->where('thoi_gian_het_han', '<=', now())
            ->pluck('id');

        if ($expiredIds->isEmpty()) {
            $this->info('Không có giao dịch VietQR nào hết hạn.');
            return self::SUCCESS;
        }

        $processed = 0;

        foreach ($expiredIds as $id) {
            DB::transaction(function () use ($id, &$processed) {
                $ve = VeXemPhim::query()
                    ->lockForUpdate()
                    ->find($id);

                // Có thể trong lúc scheduler chạy, PayOS callback đã thanh toán vé.
                if (
                    !$ve
                    || $ve->trang_thai !== 'cho_thanh_toan'
                    || !$ve->thoi_gian_het_han
                    || $ve->thoi_gian_het_han->isFuture()
                ) {
                    return;
                }

                $ve->update([
                    'trang_thai' => 'het_han',
                    'thoi_gian_het_han' => null,
                ]);

                $staffId = $ve->nhan_vien_id;

                if ($staffId) {
                    $tieuDe = 'Giao dịch VietQR hết hạn';
                    $noiDung = 'Giao dịch VietQR của vé ' . $ve->ma_ve
                        . ' - Phim: ' . $ve->ten_phim
                        . ' - Ghế: ' . $ve->ma_ghe
                        . ' đã hết thời gian thanh toán. Ghế đã được giải phóng.';

                    $daTonTai = ThongBaoCaNhan::query()
                        ->where('nguoi_dung_id', $staffId)
                        ->where('tieu_de', $tieuDe)
                        ->where('noi_dung', $noiDung)
                        ->exists();

                    if (!$daTonTai) {
                        ThongBaoCaNhan::create([
                            'nguoi_dung_id' => $staffId,
                            'tieu_de' => $tieuDe,
                            'noi_dung' => $noiDung,
                            'loai_thong_bao' => 've',
                            'duong_dan' => route('staff.ban-ve.show', $ve->suat_chieu_id),
                            'da_doc' => false,
                            'doc_luc' => null,
                        ]);
                    }
                }

                $processed++;
            });
        }

        $this->info("Đã xử lý {$processed} giao dịch VietQR hết hạn.");

        return self::SUCCESS;
    }
}
