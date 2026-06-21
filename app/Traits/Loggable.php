<?php

namespace App\Traits;

use App\Models\NhatKyHoatDongHeThong;
use Illuminate\Http\Request;

trait Loggable
{
    protected function ghiNhatKy(Request $request, string $hanhDong, ?string $chucNang = null, ?string $moTa = null, ?array $thuocTinh = null): void
    {
        if (!auth()->check()) {
            return;
        }

        NhatKyHoatDongHeThong::create([
            'nguoi_dung_id' => auth()->id(),
            'hanh_dong' => $hanhDong,
            'chuc_nang' => $chucNang,
            'mo_ta' => $moTa,
            'dia_chi_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'thuoc_tinh' => $thuocTinh,
        ]);
    }
}
