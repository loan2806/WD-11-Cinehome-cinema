<?php

namespace App\Http\Controllers\DatVe;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class SeatLockController extends Controller
{
    public function index($suat_chieu)
{
    // Giải phóng Session lock lập tức để không làm nghẽn các Request chuyển trang
    if (session()->isStarted()) {
        session()->save();
    }

    $identifier = Auth::id() ?? session()->getId();
    $setKey = $this->setKey($suat_chieu);
    $seatIds = Cache::get($setKey, []);

    $result = [];
    $validSeatIds = [];

    foreach ($seatIds as $seatId) {
        $normSeatId = strtoupper(trim($seatId));
        $key = $this->key($suat_chieu, $normSeatId);
        $data = Cache::get($key);

        if ($data) {
            if (($data['identifier'] ?? null) !== $identifier && ($data['expires_at'] ?? 0) > now()->timestamp) {
                $result[$normSeatId] = $data;
            }
            $validSeatIds[] = $normSeatId;
        }
    }

    Cache::put($setKey, array_unique($validSeatIds), now()->addMinutes(30));

    return response()->json([
        'locked' => $result
    ]);
}

    public function reserve(Request $request, $suat_chieu, $seat)
    {
        $seat = strtoupper(trim($seat));
        $identifier = Auth::id() ?? session()->getId();
        $key = $this->key($suat_chieu, $seat);
        $setKey = $this->setKey($suat_chieu);

        $existing = Cache::get($key);

        if ($existing) {
            if (
                ($existing['expires_at'] ?? 0) > now()->timestamp &&
                ($existing['identifier'] ?? null) !== $identifier
            ) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Ghế đã được người khác giữ.'
                ], 409);
            }
        }

        $expires = 7 * 60; // 7 phút

        $payload = [
            'identifier' => $identifier,
            'reserved_at' => now()->timestamp,
            'expires_at' => now()->addSeconds($expires)->timestamp,
        ];

        Cache::put($key, $payload, now()->addSeconds($expires));

        $seatIds = Cache::get($setKey, []);
        if (!in_array($seat, $seatIds)) {
            $seatIds[] = $seat;
            Cache::put($setKey, array_unique($seatIds), now()->addMinutes(30));
        }

        return response()->json(['ok' => true, 'data' => $payload]);
    }

    public function release(Request $request, $suat_chieu, $seat)
    {
        $seat = strtoupper(trim($seat));
        $identifier = Auth::id() ?? session()->getId();
        $key = $this->key($suat_chieu, $seat);
        $setKey = $this->setKey($suat_chieu);

        $existing = Cache::get($key);
        if ($existing && ($existing['identifier'] ?? null) === $identifier) {
            Cache::forget($key);
            $seatIds = collect(Cache::get($setKey, []))
                ->reject(fn($s) => strtoupper(trim($s)) === $seat)
                ->values()
                ->all();

            Cache::put($setKey, array_unique($seatIds), now()->addMinutes(30));
        }

        return response()->json(['ok' => true]);
    }

    public function releaseAll(Request $request, $suat_chieu)
    {
        $identifier = Auth::id() ?? session()->getId();
        $setKey = $this->setKey($suat_chieu);
        $seatIds = Cache::get($setKey, []);
        $remain = [];

        foreach ($seatIds as $seat) {
            $normSeat = strtoupper(trim($seat));
            $key = $this->key($suat_chieu, $normSeat);
            $existing = Cache::get($key);

            if (!$existing) {
                continue;
            }

            if (($existing['identifier'] ?? null) == $identifier) {
                Cache::forget($key);
            } else {
                $remain[] = $normSeat;
            }
        }

        Cache::put($setKey, array_unique($remain), now()->addMinutes(30));

        return response()->json(['ok' => true]);
    }

    private function key($suat, $seat)
    {
        $seat = strtoupper(trim($seat));
        return "seat_lock:suat:{$suat}:seat:{$seat}";
    }

    private function setKey($suat)
    {
        return "seat_lock_set:suat:{$suat}";
    }
}