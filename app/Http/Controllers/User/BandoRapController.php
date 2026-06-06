<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RapChieuPhim;

class BandoRapController extends Controller
{
    public function __invoke()
    {
        return redirect()->route('user.cinemas.index');
    }
}
