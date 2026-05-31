<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use Illuminate\Http\JsonResponse;

/**
 * API danh sách rạp cho trang bản đồ (định dạng JSON, dùng cho JavaScript).
 */
class CinemaMapApiController extends Controller
{
    /**
     * Trả về các rạp đang hoạt động (đủ field cho danh sách); marker chỉ vẽ khi có tọa độ.
     */
    public function __invoke(): JsonResponse
    {
        $cinemas = Cinema::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'address', 'city', 'latitude', 'longitude', 'status']);

        $cities = $cinemas->pluck('city')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return response()->json([
            'cinemas' => $cinemas,
            'cities' => $cities,
        ]);
    }
}
