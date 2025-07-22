<?php

namespace App\Http\Controllers;

use App\Models\User;
use CodingLibs\ZktecoPhp\Libs\ZKTeco;

abstract class Controller
{
    public function createUser()
    {
        $zk = new ZKTeco(config('services.zkteco.ip'));
        if (!$zk->connect()) {
            throw new \Exception('Gagal terhubung ke mesin absensi. (createUser)');
        }

        $zk->setTime(now()->toDateTimeString());
        $usersFromMachine = collect($zk->getUsers())->where('role', '!=', 14);

        if ($usersFromMachine->isEmpty()) {
            $zk->disconnect();
            return;
        }

        $machineUserNis = $usersFromMachine->pluck('user_id')->toArray();
        $trashedUsersToRestore = User::onlyTrashed()->whereIn('nis', $machineUserNis)->get();

        if ($trashedUsersToRestore->isNotEmpty()) {
            $trashedUsersToRestore->each->restore();
        }

        $usersToSync = $usersFromMachine->map(function ($user) {
            return [
                'nis' => $user['user_id'],
                'uid' => $user['uid'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        if (!empty($usersToSync)) {
            User::upsert(
                $usersToSync,
                ['nis'],
                ['uid', 'updated_at']
            );
        }

        $zk->disconnect();
    }
}
