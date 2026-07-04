<?php

namespace App\Http\Controllers\DatVe;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class SeatLockController extends Controller
{
    // Return locked seats for a showtime
    public function index($suat_chieu)
    {
        $setKey = $this->setKey($suat_chieu);
        $seatIds = Cache::get($setKey, []);

        $result = [];
        foreach ($seatIds as $seatId) {
            $key = $this->key($suat_chieu, $seatId);
            $data = Cache::get($key);
            if ($data) {
                $result[$seatId] = $data;
            }
        }

        return response()->json(['locked' => $result]);
    }

    // Reserve a seat for the current user/session
    public function reserve(Request $request, $suat_chieu, $seat)
    {
        $identifier = Auth::id() ?? session()->getId();
        $key = $this->key($suat_chieu, $seat);
        $setKey = $this->setKey($suat_chieu);

        $existing = Cache::get($key);
        if ($existing && ($existing['identifier'] ?? null) !== $identifier) {
            return response()->json(['ok' => false, 'message' => 'Ghế đã được giữ bởi người khác'], 409);
        }

        $expires = 7 * 60; // seconds
        $payload = [
            'identifier' => $identifier,
            'reserved_at' => now()->timestamp,
            'expires_at' => now()->addSeconds($expires)->timestamp,
        ];

        Cache::put($key, $payload, $expires);

        $seatIds = Cache::get($setKey, []);
        if (!in_array($seat, $seatIds)) {
            $seatIds[] = $seat;
            Cache::put($setKey, $seatIds, now()->addMinutes(30));
        }

        return response()->json(['ok' => true, 'data' => $payload]);
    }

    // Release a seat lock
    public function release(Request $request, $suat_chieu, $seat)
    {
        $identifier = Auth::id() ?? session()->getId();
        $key = $this->key($suat_chieu, $seat);
        $setKey = $this->setKey($suat_chieu);

        $existing = Cache::get($key);
        if ($existing && ($existing['identifier'] ?? null) === $identifier) {
            Cache::forget($key);
            $seatIds = Cache::get($setKey, []);
            $seatIds = array_values(array_filter($seatIds, function ($s) use ($seat) { return (string)$s !== (string)$seat; }));
            Cache::put($setKey, $seatIds, now()->addMinutes(30));
        }

        return response()->json(['ok' => true]);
    }

    private function key($suat, $seat)
    {
        return "seat_lock:suat:{$suat}:seat:{$seat}";
    }

    private function setKey($suat)
    {
        return "seat_lock_set:suat:{$suat}";
    }
}
