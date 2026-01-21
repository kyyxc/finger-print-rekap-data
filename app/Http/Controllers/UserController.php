<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\DefaultSchedule;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use CodingLibs\ZktecoPhp\Libs\ZKTeco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class UserController extends Controller
{
    public function dashboard(Request $request)
    {
        // ==============================
        // Sync user & attendance
        // ==============================
        try {
            $this->createUser();
        } catch (\Exception $e) {
            Log::error('Gagal sinkronisasi user: ' . $e->getMessage());
        }

        try {
            $this->syncAttendancesFromMachine();
        } catch (\Exception $e) {
            Log::error('Gagal sinkronisasi absensi: ' . $e->getMessage());
        }

        // ==============================
        // Request input
        // ==============================
        $dateType = $request->input('date_type', 'single');

        // STATUS selalu array (fix utama)
        $statuses = $request->input('status', []);

        if (!is_array($statuses)) {
            $statuses = [$statuses];
        }

        // Default jika kosong dan tidak ada parameter status sama sekali
        if (!$request->has('status')) {
            $statuses = ['masuk', 'telat', 'pulang'];
        }

        // ==============================
        // Base query attendance
        // ==============================
        $query = Attendance::query()
            ->with('user')
            ->whereHas('user', fn($q) => $q->whereNull('deleted_at'));

        // ==============================
        // Date filter
        // ==============================
        if ($dateType === 'range') {

            $startDate = $request->input('tanggal_mulai');
            $endDate   = $request->input('tanggal_akhir');

            if ($startDate && $endDate) {

                $realStartDate = min($startDate, $endDate);
                $realEndDate   = max($startDate, $endDate);

                $query->whereBetween('record_time', [
                    Carbon::parse($realStartDate)->startOfDay(),
                    Carbon::parse($realEndDate)->endOfDay(),
                ]);
            }
        } else {

            $singleDate = $request->input('tanggal_tunggal', now()->toDateString());
            $query->whereDate('record_time', $singleDate);
        }

        // ==============================
        // Status filter
        // ==============================
        if (!empty($statuses)) {
            $query->where(function ($q) use ($statuses) {

                if (in_array('tidak_hadir', $statuses)) {

                    $q->whereNull('status')
                        ->orWhereIn('status', array_diff($statuses, ['tidak_hadir']));
                } else {

                    $q->whereIn('status', $statuses);
                }
            });
        }

        // ==============================
        // Get attendance results
        // ==============================
        $results = $query
            ->orderBy('record_time', 'desc')
            ->get();


        // ===================================================
        // Tambahkan USER YANG BELUM HADIR (SINGLE DATE ONLY)
        // ===================================================
        if ($dateType === 'single') {

            $singleDate = $request->input('tanggal_tunggal', now()->toDateString());

            $allUsers = User::whereNull('deleted_at')->get();
            $presentUserIds = $results->pluck('user_id')->unique();

            $absentAttendances = $allUsers
                ->whereNotIn('id', $presentUserIds)
                ->map(function ($user) {

                    $attendance = new Attendance();
                    $attendance->user_id = $user->id;
                    $attendance->user = $user;
                    $attendance->status = null;
                    $attendance->record_time = null;

                    return $attendance;
                });

            if (in_array('tidak_hadir', $statuses)) {

                $results = $results
                    ->concat($absentAttendances)
                    ->sortByDesc(
                        fn($item) =>
                        $item->record_time
                            ? $item->record_time->timestamp
                            : 0
                    )
                    ->values();
            }
        }

        // ==============================
        // Pagination
        // ==============================
        $perPage = (int) $request->input('per_page', 10);
        $page = (int) $request->input('page', 1);
        $total = $results->count();

        $paginatedResults = new LengthAwarePaginator(
            $results->forPage($page, $perPage)->values(),
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        // ==============================
        // Return view
        // ==============================
        return view('pages.dashboard', [
            'query' => $paginatedResults,
            'perPage' => $perPage,
        ]);
    }

    private function syncAttendancesFromMachine()
    {
        $zk = new ZKTeco(config('services.zkteco.ip'));

        if (!$zk->connect()) {
            throw new \Exception('Gagal terhubung ke mesin absensi. (SyndAttendanceFromMachine)');
        }

        // dd($zk->getAttendances());
        // dd($zk->getUsers());
        $attendancesFromMachine = collect($zk->getAttendances());

        if ($attendancesFromMachine->isEmpty()) {
            $zk->disconnect();
            return;
        }

        $rolesMap = collect($zk->getUsers())->pluck('role', 'user_id');
        // hasil = [
        // 'user_id' => 'role',
        // 'user_id' => 'role',
        // 'user_id' => 'role',
        // ];
        $attUserIds = $attendancesFromMachine->pluck('user_id')->unique()->toArray();
        // hasil = [ 'user_id', 'user_id', 'user_id' ];

        $localUsers = User::whereIn('nis', $attUserIds)->get()->keyBy('nis');
        // hasil = [
        // 'nis' => UserModel,
        // 'nis' => UserModel,
        // 'nis' => UserModel,
        // ];

        // Ambil semua tanggal unik dari attendance mesin
        $uniqueDates = $attendancesFromMachine->map(fn($att) => Carbon::parse($att['record_time'])->toDateString())->unique()->toArray();

        // Ambil existing attendances untuk semua tanggal yang relevan, grouped by date dan user_id
        $existingAttendances = Attendance::whereIn(\DB::raw('DATE(record_time)'), $uniqueDates)
            ->get(['user_id', 'status', 'record_time'])
            ->groupBy(function ($item) {
                return Carbon::parse($item->record_time)->toDateString() . '_' . $item->user_id;
            });
        // hasil = [
        //   '2025-01-21_1' => [attendance1, attendance2, ...],
        //   '2025-01-21_2' => [attendance1, attendance2, ...],
        //   ...
        // ]

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
            // 1. Cek dari Schedule (tanggal merah / libur khusus)
            $specificSchedule = Schedule::where('date', $dateString)->first();
            if ($specificSchedule && $specificSchedule->type === 'libur') {
                continue; // Skip jika tanggal merah
            }


            // 2. Cek dari DefaultSchedule (libur default seperti Sabtu/Minggu)
            $dayOfWeek = $timestamp->dayOfWeek; // 0=Minggu, 1=Senin, ..., 6=Sabtu
            $defaultSchedule = DefaultSchedule::getByDayOfWeek($dayOfWeek);
            if ($defaultSchedule && $defaultSchedule->is_holiday) {
                continue; // Skip jika hari libur default
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

            // Juga cek dari $attendancesToInsert yang baru ditambahkan (untuk tanggal yang sama)
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
                // Belum ada absen masuk
                if ($currentTime >= $jamPulang) {
                    // Absen pertama kali saat jam pulang -> buat 2 record: telat + pulang
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
                    // Update flag supaya tidak duplikat
                    $hasCheckedIn = true;
                    $hasCheckedOut = true;
                    continue;
                } elseif ($currentTime > $jamDatang) {
                    $status = 'telat';
                } else {
                    $status = 'masuk';
                }
            } elseif ($hasCheckedIn && !$hasCheckedOut) {
                // Sudah absen masuk, belum absen pulang
                // Izinkan absen pulang kapan saja setelah absen masuk
                $status = 'pulang';
            } else {
                // Sudah absen masuk DAN sudah absen pulang, skip
                continue;
            }

            // ============================================
            // TAMBAHKAN KE ARRAY INSERT
            // ============================================
            $attendancesToInsert[] = [
                'user_id' => $user->id,
                'record_time' => $timestamp,
                'status' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Masukkan semua data baru dalam SATU KALI PERINTAH (Bulk Insert)
        if (!empty($attendancesToInsert)) {
            Attendance::insert($attendancesToInsert);
        }

        // Hapus data dari mesin setelah diambil
        // $zk->clearAttendance();
        $zk->disconnect();
    }
}
