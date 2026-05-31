<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

/**
 * Trang bản đồ rạp: giao diện + Leaflet, dữ liệu lấy qua API JSON.
 */
class CinemaMapController extends Controller
{
    public function __invoke()
    {
        return view('user.cinemas.map');
    }
}
