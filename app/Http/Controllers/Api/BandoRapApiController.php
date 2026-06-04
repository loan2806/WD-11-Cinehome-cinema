<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RapChieuPhim;
use Illuminate\Http\JsonResponse;

class BandoRapApiController extends Controller
{
    public function __invoke(): JsonResponse
    {
        // Trả về dữ liệu rạp phim theo đúng các trường tiếng Việt cấu trúc mới
        $rapChieuPhims = RapChieuPhim::query()
            ->orderBy('ten_rap')
            ->get(['id', 'ten_rap', 'dia_chi', 'thanh_pho', 'so_dien_thoai', 'hinh_anh', 'vi_do', 'kinh_do']);

        $thanhPhos = $rapChieuPhims->pluck('thanh_pho')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return response()->json([
            'rap_chieu_phims' => $rapChieuPhims,
            'thanh_phos' => $thanhPhos,
        ]);
    }
}