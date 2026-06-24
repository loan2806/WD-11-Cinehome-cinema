<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Factory;

class SaoLuuDuLieuService
{
    public static function saoLuu()
    {
        $danhSachBang = DB::select('SHOW TABLES');

        $boQua = [
            'cache',
            'cache_locks',
            'sessions',
            'jobs',
            'job_batches',
            'failed_jobs',
            'migrations'
        ];

        $duLieu = [];

        foreach ($danhSachBang as $bang) {

            $tenBang = array_values((array)$bang)[0];

            if (in_array($tenBang, $boQua)) {
                continue;
            }

            $duLieu[$tenBang] = DB::table($tenBang)->get()->toArray();
        }

        $firebase = (new Factory)
            ->withServiceAccount(
                storage_path('app/firebase/firebase-adminsdk.json')
            )
            ->withDatabaseUri(
                env('FIREBASE_DATABASE_URL')
            )
            ->createDatabase();

        $firebase
            ->getReference('sao_luu_he_thong')
            ->set([
                'thoi_gian' => now()->toDateTimeString(),
                'du_lieu' => $duLieu
            ]);
    }

    public static function dongBo()
    {

        Artisan::call('migrate');
        $firebase = (new Factory)
            ->withServiceAccount(
                storage_path('app/firebase/firebase-adminsdk.json')
            )
            ->withDatabaseUri(
                env('FIREBASE_DATABASE_URL')
            )
            ->createDatabase();

        $duLieu = $firebase
            ->getReference('sao_luu_he_thong/du_lieu')
            ->getValue();

        if (!$duLieu) {
            return false;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($duLieu as $tenBang => $banGhi) {

            DB::table($tenBang)->truncate();

            if (!empty($banGhi)) {

                foreach ($banGhi as $dong) {

                    DB::table($tenBang)->insert(
                        (array)$dong
                    );
                }
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        return true;
    }
}
