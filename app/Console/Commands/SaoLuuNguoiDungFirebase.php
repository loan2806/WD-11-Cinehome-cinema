<?php

namespace App\Console\Commands;

use App\Models\NguoiDung;
use Illuminate\Console\Command;
use Kreait\Laravel\Firebase\Facades\Firebase;
use App\Models\User;

class SaoLuuNguoiDungFirebase extends Command
{
    protected $signature = 'firebase:sao-luu';

    protected $description = 'Sao lưu người dùng từ Firebase về hệ thống';

    public function handle()
    {
        $xacThuc = Firebase::auth();

        $danhSachNguoiDung = $xacThuc->listUsers();

        foreach ($danhSachNguoiDung as $nguoiDungFirebase) {

            NguoiDung::updateOrCreate(
                [
                    'firebase_uid' => $nguoiDungFirebase->uid,
                ],
                [
                    'email' => $nguoiDungFirebase->email,
                    'name' => $nguoiDungFirebase->displayName,
                ]
            );
        }

        $this->info('Đã sao lưu thành công!');
    }
}