<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\DefaultSchedule;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use CodingLibs\ZktecoPhp\Libs\ZKTeco;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncAttendanceFromMachine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi user dan data absensi dari mesin fingerprint';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('===========================================');
        $this->info('Memulai sinkronisasi data dari mesin...');
        $this->info('Waktu: ' . now()->format('Y-m-d H:i:s'));
        $this->info('===========================================');

        // Sync Users
        try {
            $this->info('');
            $this->info('[1/2] Sinkronisasi User...');
            $this->createUser();
            $this->info('✓ Sinkronisasi user selesai.');
        } catch (\Exception $e) {
            $this->error('✗ Gagal sinkronisasi user: ' . $e->getMessage());
            Log::error('Gagal sinkronisasi user: ' . $e->getMessage());
        }

        // Sync Attendances
        try {
            $this->info('');
            $this->info('[2/2] Sinkronisasi Absensi...');
            $this->syncAttendancesFromMachine();
            $this->info('✓ Sinkronisasi absensi selesai.');
        } catch (\Exception $e) {
            $this->error('✗ Gagal sinkronisasi absensi: ' . $e->getMessage());
            Log::error('Gagal sinkronisasi absensi: ' . $e->getMessage());
        }

        $this->info('');
        $this->info('===========================================');
        $this->info('Sinkronisasi selesai!');
        $this->info('===========================================');
    }

    /**
     * Sinkronisasi user dari mesin fingerprint
     */
    private function createUser()
    {
        $zk = new ZKTeco(config('services.zkteco.ip'));
        if (!$zk->connect()) {
            throw new \Exception('Gagal terhubung ke mesin absensi. (createUser)');
        }

        $zk->setTime(now()->toDateTimeString());
        $usersFromMachine = collect($zk->getUsers())->where('role', '!=', 14);

        if ($usersFromMachine->isEmpty()) {
            $this->info('  - Tidak ada user baru dari mesin.');
            $zk->disconnect();
            return;
        }

        $this->info('  - Ditemukan ' . $usersFromMachine->count() . ' user dari mesin.');

        $machineUserNis = $usersFromMachine->pluck('user_id')->toArray();
        $trashedUsersToRestore = User::onlyTrashed()->whereIn('nis', $machineUserNis)->get();

        if ($trashedUsersToRestore->isNotEmpty()) {
            $trashedUsersToRestore->each->restore();
            $this->info('  - Restored ' . $trashedUsersToRestore->count() . ' user dari trash.');
        }

        $usersToSync = $usersFromMachine->map(function ($user) {
            return [
                'nis' => $user['user_id'],
                'uid' => $user['uid'],
                'nama' => $user['name'] == $user['user_id'] ? null : $user['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        if (!empty($usersToSync)) {
            User::upsert(
                $usersToSync,
                ['nis'],
                ['uid', 'updated_at', 'nama']
            );
            $this->info('  - Berhasil sync ' . count($usersToSync) . ' user.');
        }

        $zk->disconnect();
    }

    /**
     * Sinkronisasi data absensi dari mesin fingerprint
     */
    private function syncAttendancesFromMachine()
    {
        $zk = new ZKTeco(config('services.zkteco.ip'));

        if (!$zk->connect()) {
            throw new \Exception('Gagal terhubung ke mesin absensi. (syncAttendancesFromMachine)');
        }

        $attendancesFromMachine = collect($zk->getAttendances());

        if ($attendancesFromMachine->isEmpty()) {
            $this->info('  - Tidak ada data absensi baru dari mesin.');
            $zk->disconnect();
            return;
        }

        $this->info('  - Ditemukan ' . $attendancesFromMachine->count() . ' data absensi dari mesin.');

        $rolesMap = collect($zk->getUsers())->pluck('role', 'user_id');
        $attUserIds = $attendancesFromMachine->pluck('user_id')->unique()->toArray();
        $localUsers = User::whereIn('nis', $attUserIds)->get()->keyBy('nis');

        // Ambil semua tanggal unik dari attendance mesin
        $uniqueDates = $attendancesFromMachine->map(fn($att) => Carbon::parse($att['record_time'])->toDateString())->unique()->toArray();

        // Ambil existing attendances untuk semua tanggal yang relevan
        $existingAttendances = Attendance::whereIn(DB::raw('DATE(record_time)'), $uniqueDates)
            ->get(['user_id', 'status', 'record_time'])
            ->groupBy(function ($item) {
                return Carbon::parse($item->record_time)->toDateString() . '_' . $item->user_id;
            });

        $attendancesToInsert = [];

        foreach ($attendancesFromMachine as $att) {
            $userId = $att['user_id'];
            $timestamp = Carbon::parse($att['record_time']);
            $dateString = $timestamp->toDateString();
            $role = $rolesMap[$userId] ?? null;

            // Skip jika role 14
            if ($role == 14) {
                continue;
            }

            // Skip jika user tidak ada di database lokal
            if (!$localUsers->has($userId)) {
                continue;
            }

            $user = $localUsers->get($userId);

            // ============================================
            // CEK APAKAH HARI LIBUR
            // ============================================
            $specificSchedule = Schedule::where('date', $dateString)->first();
            if ($specificSchedule && $specificSchedule->type === 'libur') {
                continue;
            }

            $dayOfWeek = $timestamp->dayOfWeek;
            $defaultSchedule = DefaultSchedule::getByDayOfWeek($dayOfWeek);
            if ($defaultSchedule && $defaultSchedule->is_holiday) {
                continue;
            }

            // ============================================
            // AMBIL JAM KERJA DARI SCHEDULE
            // ============================================
            $workingHours = Schedule::getWorkingHours($dateString);
            $jamDatang = $workingHours['jam_datang'] ?? '08:00';
            $jamPulang = $workingHours['jam_pulang'] ?? '16:00';

            // ============================================
            // CEK STATUS ATTENDANCE USER PADA TANGGAL INI
            // ============================================
            $attendanceKey = $dateString . '_' . $user->id;
            $userAttendances = $existingAttendances->get($attendanceKey, collect());
            $hasCheckedIn = $userAttendances->whereIn('status', ['masuk', 'telat'])->isNotEmpty();
            $hasCheckedOut = $userAttendances->where('status', 'pulang')->isNotEmpty();

            // Cek dari $attendancesToInsert yang baru ditambahkan
            $pendingAttendances = collect($attendancesToInsert)
                ->filter(function ($item) use ($user, $dateString) {
                    return $item['user_id'] == $user->id
                        && Carbon::parse($item['record_time'])->toDateString() == $dateString;
                });
            $pendingCheckedIn = $pendingAttendances->whereIn('status', ['masuk', 'telat'])->isNotEmpty();
            $pendingCheckedOut = $pendingAttendances->where('status', 'pulang')->isNotEmpty();

            $hasCheckedIn = $hasCheckedIn || $pendingCheckedIn;
            $hasCheckedOut = $hasCheckedOut || $pendingCheckedOut;

            // ============================================
            // TENTUKAN STATUS BERDASARKAN KONDISI
            // ============================================
            $currentTime = $timestamp->format('H:i');

            if (!$hasCheckedIn) {
                if ($currentTime >= $jamPulang) {
                    $attendancesToInsert[] = [
                        'user_id' => $user->id,
                        'record_time' => $timestamp,
                        'status' => 'telat',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $attendancesToInsert[] = [
                        'user_id' => $user->id,
                        'record_time' => $timestamp,
                        'status' => 'pulang',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $hasCheckedIn = true;
                    $hasCheckedOut = true;
                    continue;
                } elseif ($currentTime > $jamDatang) {
                    $status = 'telat';
                } else {
                    $status = 'masuk';
                }
            } elseif ($hasCheckedIn && !$hasCheckedOut) {
                $status = 'pulang';
            } else {
                continue;
            }

            $attendancesToInsert[] = [
                'user_id' => $user->id,
                'record_time' => $timestamp,
                'status' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Bulk Insert
        if (!empty($attendancesToInsert)) {
            Attendance::insert($attendancesToInsert);
            $this->info('  - Berhasil insert ' . count($attendancesToInsert) . ' data absensi.');
        } else {
            $this->info('  - Tidak ada data absensi baru yang perlu disimpan.');
        }

        // Hapus data dari mesin setelah diambil
        $zk->clearAttendance();
        $zk->disconnect();
    }
}
