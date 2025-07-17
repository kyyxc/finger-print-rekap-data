<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use CodingLibs\ZktecoPhp\Libs\ZKTeco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function dashboard(Request $request)
    {
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

        // 3. Logika untuk query dan menampilkan data (tetap sama, sudah bagus).
        $tanggal = $request->input('tanggal', now()->toDateString());
        $status = $request->input('status');
        $search = $request->input('search');
        $completeness = $request->input('completeness', 'complete');

        $query = Attendance::query()
            ->with('user')
            ->whereHas('user', fn($q) => $q->whereNull('deleted_at'))
            ->whereDate('record_time', $tanggal)
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->whereHas('user', function ($q) use ($search) {
                    $q->where('nama', 'like', "%$search%")
                        ->orWhere('nis', 'like', "%$search%");
                });
            })
            ->when($completeness, function ($q) use ($completeness) {
                if ($completeness === 'complete') {
                    $q->whereHas('user', fn($sq) => $sq->whereNotNull('nama')->whereNotNull('kelas'));
                } elseif ($completeness === 'incomplete') {
                    $q->whereHas('user', fn($sq) => $sq->whereNull('nama')->orWhereNull('kelas'));
                }
            })
            ->orderBy('record_time', 'asc')
            ->get();

        return view('pages.dashboard', compact('query', 'tanggal', 'status', 'search', 'completeness'));
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

            if ($rolesMap[$userId] == 14 || (in_array($att['type'], [1, 5]) && $timestamp->format('H:i') < '13:00')) {
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
