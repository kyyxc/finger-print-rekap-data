<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SekretarisController extends Controller
{
    public function dashboard()
    {
        try {
            $this->createUser();
        } catch (\Exception $e) {
            Log::error('Gagal sinkronisasi user: ' . $e->getMessage());
        }

        // Get sekretaris's assigned grade
        $sekretaris = auth()->guard('role')->user()->load('grade');
        $grade = $sekretaris->grade;
        $gradeId = $grade ? $grade->id : null;
        $kelasName = $grade ? $grade->name : null;
        $kelasPhoneNo = $grade ? $grade->phone_no : null;

        // Statistics
        $today = Carbon::today();

        if ($gradeId) {
            // Total students in the assigned class
            $totalSiswa = User::where('grade_id', $gradeId)->count();

            // Today's attendance for this class
            $todayAttendances = Attendance::whereHas('user', function ($q) use ($gradeId) {
                $q->where('grade_id', $gradeId);
            })->whereDate('record_time', $today)->get();

            $hadirHariIni = $todayAttendances->where('status', 'hadir')->count();
            $sakitHariIni = $todayAttendances->where('status', 'sakit')->count();
            $izinHariIni = $todayAttendances->where('status', 'izin')->count();
            $alphaHariIni = $todayAttendances->where('status', 'alpha')->count();
            $belumAbsen = $totalSiswa - $todayAttendances->count();

            // Monthly attendance data for chart (current month)
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();

            $monthlyData = Attendance::whereHas('user', function ($q) use ($gradeId) {
                $q->where('grade_id', $gradeId);
            })
                ->whereBetween('record_time', [$startOfMonth, $endOfMonth])
                ->select(
                    DB::raw('DATE(record_time) as date'),
                    DB::raw("SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir"),
                    DB::raw("SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit"),
                    DB::raw("SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin"),
                    DB::raw("SUM(CASE WHEN status = 'alpha' THEN 1 ELSE 0 END) as alpha")
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Format data for chart
            $chartLabels = [];
            $chartHadir = [];
            $chartSakit = [];
            $chartIzin = [];
            $chartAlpha = [];

            foreach ($monthlyData as $data) {
                $chartLabels[] = Carbon::parse($data->date)->format('d M');
                $chartHadir[] = (int) $data->hadir;
                $chartSakit[] = (int) $data->sakit;
                $chartIzin[] = (int) $data->izin;
                $chartAlpha[] = (int) $data->alpha;
            }
        } else {
            $totalSiswa = 0;
            $hadirHariIni = 0;
            $sakitHariIni = 0;
            $izinHariIni = 0;
            $alphaHariIni = 0;
            $belumAbsen = 0;
            $chartLabels = [];
            $chartHadir = [];
            $chartSakit = [];
            $chartIzin = [];
            $chartAlpha = [];
        }

        return view('pages.sekretaris.dashboard', compact(
            'kelasName',
            'kelasPhoneNo',
            'totalSiswa',
            'hadirHariIni',
            'sakitHariIni',
            'izinHariIni',
            'alphaHariIni',
            'belumAbsen',
            'chartLabels',
            'chartHadir',
            'chartSakit',
            'chartIzin',
            'chartAlpha'
        ));
    }

    public function users(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');

        // Get sekretaris's assigned grade
        $sekretaris = auth()->guard('role')->user()->load('grade');
        $grade = $sekretaris->grade;
        $gradeId = $grade ? $grade->id : null;
        $kelasName = $grade ? $grade->name : null;

        // Filter users by sekretaris's grade_id only
        $users = User::with('grade')
            ->when($gradeId, function ($query) use ($gradeId) {
                return $query->where('grade_id', $gradeId);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nis', 'like', "%{$search}%")
                      ->orWhere('nama', 'like', "%{$search}%")
                      ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        return view('pages.sekretaris.users', compact('users', 'perPage', 'kelasName', 'search'));
    }

    public function absensi()
    {
        return view('pages.sekretaris.absensi');
    }

    public function kelolaAbsen(Request $request)
    {
        try {
            // Get logged in sekretaris data with grade relationship
            $sekretaris = auth()->guard('role')->user()->load('grade');

            // Get kelas from sekretaris grade
            $grade = $sekretaris->grade;

            if (!$grade) {
                return redirect()->route('sekretaris.dashboard')
                    ->with('error', 'Anda belum ditugaskan ke kelas manapun. Silakan hubungi admin.');
            }

            $gradeId = $grade->id;
            $kelasName = $grade->name;

            // Get selected date (default today)
            $selectedDate = $request->get('tanggal', now()->toDateString());

            // Get students data from sekretaris's class using grade_id
            $students = User::with('grade')
                ->where('grade_id', $gradeId)
                ->orderBy('nama', 'asc')
                ->get()
                ->map(function ($student) use ($selectedDate) {
                    // Get attendance record for the selected date
                    $attendance = Attendance::where('user_id', $student->id)
                        ->whereDate('record_time', $selectedDate)
                        ->first();

                    $student->attendance_status = $attendance ? $attendance->status : null;
                    $student->attendance_id = $attendance ? $attendance->id : null;
                    $student->record_time = $attendance ? $attendance->record_time : null;

                    return $student;
                });

            return view('pages.sekretaris.kelola-absen', compact('students', 'kelasName', 'selectedDate'));
        } catch (\Exception $e) {
            \Log::error('Error in kelolaAbsen: ' . $e->getMessage());
            return redirect()->route('sekretaris.dashboard')
                ->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    public function updateAbsen(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:hadir,izin,sakit,alpha',
        ]);

        $userId = $request->user_id;
        $tanggal = $request->tanggal;
        $status = $request->status;
        $waktu = $request->waktu ?? now()->format('H:i:s');

        // Check if attendance record exists
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('record_time', $tanggal)
            ->first();

        $recordTime = $tanggal . ' ' . $waktu;

        if ($attendance) {
            // Update existing record
            $attendance->update([
                'status' => $status,
                'record_time' => $recordTime,
            ]);
            $message = 'Status absensi berhasil diperbarui';
        } else {
            // Create new record
            Attendance::create([
                'user_id' => $userId,
                'status' => $status,
                'record_time' => $recordTime,
            ]);
            $message = 'Absensi berhasil ditambahkan';
        }

        return redirect()->back()->with('message', $message);
    }

    public function deleteAbsen($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();

        return redirect()->back()->with('message', 'Absensi berhasil dihapus');
    }
}
