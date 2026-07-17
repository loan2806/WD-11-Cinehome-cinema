<?php

namespace App\Services;

use App\Models\VeXemPhim;

class TicketCheckInService
{
    public function inspect(string $rawCode): array
    {
        $ticket = $this->findTicket($rawCode);

        if (is_array($ticket)) {
            return $ticket;
        }

        if ($ticket->trang_thai === 'da_huy') {
            return $this->failed('Vé này đã bị hủy, không thể sử dụng.', $ticket);
        }

        if ($ticket->trang_thai === 'da_su_dung') {
            return $this->failed('Vé này đã được sử dụng trước đó.', $ticket);
        }

        if ($ticket->trang_thai !== 'da_thanh_toan') {
            return $this->failed('Vé chưa được thanh toán hoặc trạng thái không hợp lệ.', $ticket);
        }

        return [
            'success' => true,
            'message' => 'Vé hợp lệ. Bấm xác nhận sử dụng khi khách vào rạp.',
            'ticket' => $ticket,
            'ma_ve' => $ticket->ma_ve,
        ];
    }

    public function checkIn(string $rawCode): array
    {
        $result = $this->inspect($rawCode);

        if (! $result['success']) {
            return $result;
        }

        $ticket = $result['ticket'];

        $ticket->update([
            'trang_thai' => 'da_su_dung',
            'tien_hoan' => 0,
        ]);

        $ticket->refresh();

        return [
            'success' => true,
            'message' => 'Đã xác nhận sử dụng vé. Khách có thể vào phòng chiếu.',
            'ticket' => $ticket,
            'ma_ve' => $ticket->ma_ve,
        ];
    }

    /**
     * 🌟 ĐÃ CẬP NHẬT: Đóng gói dữ liệu vé gửi về Client qua AJAX quét QR,
     * tự động tích hợp danh sách đồ ăn bắp nước chuẩn hóa từ Accessor của Model.
     */
    public function ticketPayload(?VeXemPhim $ticket): ?array
    {
        if (! $ticket) {
            return null;
        }

        return [
            'ma_ve' => $ticket->ma_ve,
            'ten_phim' => $ticket->ten_phim,
            'ten_rap' => $ticket->ten_rap,
            'ten_phong' => $ticket->ten_phong,
            'ma_ghe' => $ticket->ma_ghe,
            'thoi_gian_chieu' => $ticket->thoi_gian_chieu?->format('d/m/Y H:i'),
            'tong_tien' => number_format((float) $ticket->tong_tien, 0, ',', '.').'đ',
            'loai_ve' => $ticket->loai_ve,
            'trang_thai' => $ticket->trang_thai,
            'trang_thai_label' => $this->statusLabel($ticket->trang_thai),
            'loai_ve_label' => $this->typeLabel($ticket->loai_ve),
            'can_check_in' => $ticket->trang_thai === 'da_thanh_toan',
            'foods' => $ticket->foods_list, // 🌟 ĐỒNG BỘ: Sử dụng Accessor mới tự động kéo từ Cache
        ];
    }

    public function extractTicketCode(string $rawCode): string
    {
        $rawCode = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $rawCode) ?? '');

        if ($rawCode === '') {
            return '';
        }

        $json = json_decode($rawCode, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            foreach (['ma_ve', 'ticket_code', 'code'] as $key) {
                if (! empty($json[$key]) && is_string($json[$key])) {
                    return trim($json[$key]);
                }
            }
        }

        $url = parse_url($rawCode);

        if (is_array($url)) {
            if (! empty($url['query'])) {
                parse_str($url['query'], $query);

                foreach (['ma_ve', 'ticket_code', 'code'] as $key) {
                    if (! empty($query[$key]) && is_string($query[$key])) {
                        return trim($query[$key]);
                    }
                }
            }

            if (! empty($url['path']) && str_contains($rawCode, '://')) {
                $segments = array_values(array_filter(explode('/', $url['path'])));

                if (! empty($segments)) {
                    return trim(end($segments));
                }
            }
        }

        return $rawCode;
    }

    private function findTicket(string $rawCode): VeXemPhim|array
    {
        $maVe = $this->extractTicketCode($rawCode);

        if ($maVe === '') {
            return $this->failed('Mã QR không hợp lệ. Vui lòng quét lại hoặc nhập mã vé.');
        }

        $ticket = VeXemPhim::where('ma_ve', $maVe)->first();

        if (! $ticket) {
            return $this->failed('Không tìm thấy vé trong hệ thống.');
        }

        return $ticket;
    }

    private function failed(string $message, ?VeXemPhim $ticket = null): array
    {
        return [
            'success' => false,
            'message' => $message,
            'ticket' => $ticket,
            'ma_ve' => $ticket?->ma_ve,
        ];
    }

    private function statusLabel(?string $status): string
    {
        return [
            'da_thanh_toan' => 'Đã thanh toán',
            'da_su_dung' => 'Đã sử dụng',
            'da_huy' => 'Đã hủy',
        ][$status] ?? 'Không rõ';
    }

    private function typeLabel(?string $type): string
    {
        return [
            'truc_tuyen' => 'Trực tuyến',
            'tai_quay' => 'Tại quầy',
        ][$type] ?? 'Không rõ';
    }
}
