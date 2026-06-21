<?php

namespace App\Console\Commands;

use App\Models\NguoiDung;
use App\Models\User;
use Illuminate\Console\Command;
use Kreait\Laravel\Firebase\Facades\Firebase;

class BackupFirebaseUsers extends Command
{
    protected $signature = 'firebase:backup-users';

    protected $description = 'Backup Firebase users to database';

    public function handle()
    {
        $auth = Firebase::auth();

        $users = $auth->listUsers();

        foreach ($users as $firebaseUser) {

            NguoiDung::updateOrCreate(
                [
                    'firebase_uid' => $firebaseUser->uid,
                ],
                [
                    'email' => $firebaseUser->email,
                    'name' => $firebaseUser->displayName,
                ]
            );
        }

        $this->info('Backup completed');
    }
}