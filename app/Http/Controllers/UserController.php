<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
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
        $existingAttendances = Attendance::whereDate('record_time', today())
            ->get(['user_id', 'status'])
            ->map(fn($att) => $att->user_id . '-' . $att->status)
            ->flip();
        // hasil = [
        // 'user_id-status' => 0,
        // 'user_id-status' => 1,
        // 'user_id-status' => 2,
        // ];

        $attendancesToInsert = [];
        foreach ($attendancesFromMachine as $att) {
            $userId = $att['user_id'];
            $timestamp = Carbon::parse($att['record_time']);
            
            $role = $rolesMap[$userId] ?? null;
            
            if ($role == 14 || (in_array($att['type'], [1, 5]) && $timestamp->hour < 13)) {
                continue;
            }
                
            if (!$localUsers->has($userId)) {
                continue;
            }

            $user = $localUsers->get($userId);

            $status = match ($att['type']) {
                1, 5 => 'pulang',
                default => $timestamp->format('H:i') > '08:00' ? 'telat' : 'masuk',
            };

            $uniqueKey = $user->id . '-' . $status;
            if (isset($existingAttendances[$uniqueKey])) {
                continue;
            }

            $attendancesToInsert[] = [
                'user_id' => $user->id,
                'record_time' => $timestamp,
                'status' => $status,
            ];

        }

        // Masukkan semua data baru dalam SATU KALI PERINTAH (Bulk Insert)
        if (!empty($attendancesToInsert)) {
            Attendance::insert($attendancesToInsert);
        }
        // dd(Attendance::all());

        // Opsional tapi sangat disarankan: Hapus data setelah diambil
        $zk->clearAttendance();
        $zk->disconnect();
    }
}
