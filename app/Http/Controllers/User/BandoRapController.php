<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

class BandoRapController extends Controller
{
    public function __invoke()
    {
        return view('user.cinemas.map');
    }
}