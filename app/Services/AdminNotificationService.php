<?php

namespace App\Services;

use App\Models\AdminNotification;

class AdminNotificationService
{
    public static function push($tieuDe, $noiDung, $loai  = 'info')
    {
        return AdminNotification::create([
            'tieu_de' => $tieuDe,
            'noi_dung' => $noiDung,
            'loai' => $loai,
            'doi_tuong' => 'admin',
            'thoi_gian_xuat_ban' => now(),
        ]);
    }
}