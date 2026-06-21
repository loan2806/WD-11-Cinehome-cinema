<?php

namespace App\Http\Controllers;

use App\Services\SaoLuuDuLieuService;

class DongBoDuLieuController extends Controller
{
    public function dongBo()
    {
        SaoLuuDuLieuService::dongBo();

        return redirect()
            ->back()
            ->with('success', 'Đồng bộ dữ liệu thành công!');
    }
}