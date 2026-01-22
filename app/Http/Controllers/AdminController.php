<?php

namespace App\Http\Controllers;

use App\Exports\AttendancesExport;
use App\Imports\UserImport;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use CodingLibs\ZktecoPhp\Libs\ZKTeco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

class AdminController extends Controller
{
    public function dashboard()
    {
        try {
            $this->createUser();
        } catch (\Exception $e) {
            Log::error('Gagal sinkronisasi user: ' . $e->getMessage());
        }

        $today = Carbon::today();

        // Statistik
        $totalUsers = User::count();
        $totalGrades = Grade::count();

        // Hitung kehadiran hari ini (user yang sudah absen minimal 1x hari ini)
        $hadirHariIni = Attendance::whereDate('record_time', $today)
            ->distinct('user_id')
            ->count('user_id');

        $belumHadir = $totalUsers - $hadirHariIni;

        // Data kehadiran mingguan (7 hari terakhir, Senin-Minggu)
        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        $weeklyData = $this->getWeeklyAttendanceData($startOfWeek);

        // Data kelas untuk dropdown
        $grades = Grade::orderBy('name')->get();

        // Data kehadiran per kelas
        $classData = $this->getClassAttendanceData($startOfWeek, $grades);

        return view('pages.admins.dashboard', compact(
            'totalUsers',
            'totalGrades',
            'hadirHariIni',
            'belumHadir',
            'weeklyData',
            'grades',
            'classData'
        ));
    }

    /**
     * Get weekly attendance data
     */
    private function getWeeklyAttendanceData($startOfWeek)
    {
        $labels = [];
        $hadir = [];
        $tidakHadir = [];
        $dayNames = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        $totalUsers = User::count();

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $labels[] = $dayNames[$i];

            // Skip hari yang belum terjadi
            if ($date->isFuture()) {
                $hadir[] = 0;
                $tidakHadir[] = 0;
                continue;
            }

            $hadirCount = Attendance::whereDate('record_time', $date)
                ->distinct('user_id')
                ->count('user_id');

            $hadir[] = $hadirCount;
            $tidakHadir[] = $totalUsers - $hadirCount;
        }

        return [
            'labels' => $labels,
            'hadir' => $hadir,
            'tidakHadir' => $tidakHadir
        ];
    }

    /**
     * Get class attendance data
     */
    private function getClassAttendanceData($startOfWeek, $grades)
    {
        $classData = [];
        $dayNames = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        // Data untuk semua kelas
        $totalUsers = User::count();
        $allHadir = [];
        $allTidakHadir = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);

            if ($date->isFuture()) {
                $allHadir[] = 0;
                $allTidakHadir[] = 0;
                continue;
            }

            $hadirCount = Attendance::whereDate('record_time', $date)
                ->distinct('user_id')
                ->count('user_id');

            $allHadir[] = $hadirCount;
            $allTidakHadir[] = $totalUsers - $hadirCount;
        }

        $classData['all'] = [
            'hadir' => $allHadir,
            'tidakHadir' => $allTidakHadir
        ];

        // Data per kelas
        foreach ($grades as $grade) {
            $gradeKey = str_replace(' ', '-', $grade->name);
            $usersInGrade = User::where('kelas', $grade->name)->pluck('id');
            $totalInGrade = $usersInGrade->count();

            $gradeHadir = [];
            $gradeTidakHadir = [];

            for ($i = 0; $i < 7; $i++) {
                $date = $startOfWeek->copy()->addDays($i);

                if ($date->isFuture() || $totalInGrade == 0) {
                    $gradeHadir[] = 0;
                    $gradeTidakHadir[] = 0;
                    continue;
                }

                $hadirCount = Attendance::whereDate('record_time', $date)
                    ->whereIn('user_id', $usersInGrade)
                    ->distinct('user_id')
                    ->count('user_id');

                $gradeHadir[] = $hadirCount;
                $gradeTidakHadir[] = $totalInGrade - $hadirCount;
            }

            $classData[$gradeKey] = [
                'hadir' => $gradeHadir,
                'tidakHadir' => $gradeTidakHadir
            ];
        }

        return $classData;
    }

    public function profile()
    {
        return view('pages.admins.profile');
    }

    /**
     * Mengubah password akun admin yang sedang login.
     */
    public function patchProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.profile')->withErrors($validator)->withInput();
        }

        $admin = auth()->guard('role')->user();
        if (!Hash::check($request->current_password, $admin->password)) {
            return redirect()->route('admin.profile')->withErrors(['current_password' => 'Password saat ini salah.']);
        }

        if ($request->current_password == $request->new_password) {
            return redirect()->route('admin.profile')->withErrors(['new_password' => 'Password baru harus berbeda.']);
        }

        $admin->password = Hash::make($request->new_password);
        $admin->save();

        return back()->with('message', 'Password berhasil diubah.');
    }

    /**
     * Menghapus akun admin yang sedang login.
     */
    public function deleteProfile(Request $request)
    {
        $validator = Validator::make($request->all(), ['password' => 'required|string']);
        if ($validator->fails()) {
            return redirect()->route('admin.profile')->withErrors($validator)->withInput();
        }

        $admin = auth()->guard('role')->user();
        if (!Hash::check($request->password, $admin->password)) {
            return redirect()->route('admin.profile')->withErrors(['password' => 'Password yang Anda masukkan salah.']);
        }

        $allAdmins = Role::count();
        if ($allAdmins <= 1) {
            return redirect()->route('admin.profile')->withErrors(['deleteAccount' => 'Anda tidak dapat menghapus akun ini karena hanya ada satu admin yang tersisa.']);
        }

        $admin->delete();
        auth()->guard('role')->logout();

        return redirect()->route('login')->with('message', 'Akun Anda telah berhasil dihapus.');
    }


    public function users(Request $request)
    {
        try {
            $this->createUser();
        } catch (\Exception $e) {
            Log::error('Gagal sinkronisasi user: ' . $e->getMessage());
        }

        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');
        $grades = Grade::orderBy('name', 'asc')->get();

        $users = User::query()
            ->with('grade')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nis', 'like', "%{$search}%")
                      ->orWhere('nama', 'like', "%{$search}%")
                      ->orWhere('phone_number', 'like', "%{$search}%")
                      ->orWhereHas('grade', function ($gradeQuery) use ($search) {
                          $gradeQuery->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->orderBy('nama', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        return view('pages.admins.users', compact('users', 'grades', 'perPage', 'search'));
    }

    /**
     * Update a user
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nis' => 'required|string|max:50|unique:users,nis,' . $id,
            'nama' => 'required|string|max:255',
            'grade_id' => 'nullable|exists:grades,id',
            'phone_number' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ], [
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS sudah digunakan.',
            'nama.required' => 'Nama wajib diisi.',
            'grade_id.exists' => 'Kelas tidak valid.',
            'phone_number.max' => 'Nomor telepon maksimal 20 karakter.',
            'photo.image' => 'File harus berupa gambar.',
            'photo.mimes' => 'Format gambar harus jpeg, png, atau jpg.',
            'photo.max' => 'Ukuran gambar maksimal 5MB.',
        ]);

        $user->nis = $request->nis;
        $user->nama = $request->nama;
        $user->grade_id = $request->grade_id;
        $user->phone_number = $request->phone_number;

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }

            // Store new photo
            $photo = $request->file('photo');
            $filename = $user->nis . '.' . $photo->getClientOriginalExtension();
            $path = $photo->storeAs('photos', $filename, 'public');
            $user->photo = $path;
        }

        $user->save();

        return redirect()->route('admin.users')->with('message', 'Data siswa berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $zk = new ZKTeco(config('services.zkteco.ip'));
        if (!$zk->connect()) {
            throw new \Exception('Gagal terhubung ke mesin absensi. (destroyUser)');
        }

        // dd($zk->getUsers());

        $user = User::findOrFail($id);
        try {
            $zk->removeUser($user->uid);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus user dari mesin absensi: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->withErrors(['error' => 'Gagal menghapus user dari mesin fingerprint']);
        }
        $user->delete();

        return redirect()->route('admin.users')->with('message', '✅ User berhasil dihapus.');
    }

    public function importUsers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            Excel::import(new UserImport, $request->file('excel_file'));

        } catch (\Exception $e) {
            Log::error('Gagal sinkronisasi user: ' . $e->getMessage());
            return redirect()->back()->withErrors(['excel_file' => $e->getMessage()]);
        }

        return redirect()->back()->with('message', 'Data pengguna berhasil diimpor!');
    }

    public function importPhotos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'zip_file' => 'required|file|mimes:zip',
        ]);


        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $userId = User::pluck('nis')->toArray();

        $zipFile = $request->file('zip_file');
        $zip = new ZipArchive;
        $successCount = 0;

        if ($zip->open($zipFile->getRealPath()) === TRUE) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                $fileInfo = pathinfo($filename);

                // dd(['fileInfo' => $fileInfo, 'in_array' => in_array($filename, $userId), 'filename' => explode('.', $filename), 'userId' => $userId]);
                if (!in_array(explode('.', $filename)[0], $userId)) {
                    continue;
                }

                $ext = strtolower($fileInfo['extension'] ?? '');
                if (!in_array($ext, ['jpg', 'jpeg', 'png']))
                    continue;

                $fileContent = $zip->getFromIndex($i);
                if (strlen($fileContent) > 5 * 1024 * 1024)
                    continue;

                $nis = $fileInfo['filename'];
                $storagePath = 'photos/' . basename($filename);

                Storage::disk('public')->put($storagePath, $fileContent);
                User::where('nis', $nis)->update(['photo' => $storagePath]);
                $successCount++;
            }

            $zip->close();

            return redirect()->back()->with('message', "$successCount Foto berhasil diimpor dan diperbarui!");
        }

        return redirect()->back()->withErrors(['zip_file' => 'Gagal membuka file ZIP.']);
    }

    public function exportAttendances(Request $request)
    {
        // Buat nama file yang dinamis berdasarkan tanggal
        $fileName = 'laporan-absensi-' . now()->format('Y-m-d') . '.xlsx';

        // Panggil class export, kirim filter dari request, dan download
        return Excel::download(new AttendancesExport($request), $fileName);
    }

    /**
     * Membuat admin baru.
     */
    public function createAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:roles,username',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,sekretaris',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.admins')->withErrors($validator)->withInput();
        }

        Role::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return back()->with('message', 'Admin baru berhasil dibuat.');
    }

    /**
     * Kelola Admin
     */
    public function admins(Request $request)
    {
        $currentUserId = auth()->guard('role')->user()->id;
        $perPage = $request->get('per_page', 10);

        $admins = Role::where('role', 'admin')
            ->where('id', '!=', $currentUserId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends($request->query());

        return view('pages.admins.admins', compact('admins', 'perPage'));
    }

    /**
     * Hapus Admin
     */
    public function deleteAdmin($id)
    {
        $currentUserId = auth()->guard('role')->user()->id;

        // Tidak boleh hapus diri sendiri
        if ($id == $currentUserId) {
            return redirect()->route('admin.admins')->withErrors(['error' => 'Anda tidak dapat menghapus akun sendiri dari sini.']);
        }

        $admin = Role::findOrFail($id);
        $admin->delete();

        return redirect()->route('admin.admins')->with('message', '✅ Admin berhasil dihapus.');
    }

    /**
     * Kelola Sekretaris
     */
    public function sekretaris(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $sekretaris = Role::with('grade')
            ->where('role', 'sekretaris')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends($request->query());

        $grades = Grade::orderBy('name', 'asc')->get();

        return view('pages.admins.sekretaris', compact('sekretaris', 'grades', 'perPage'));
    }

    /**
     * Create a new sekretaris
     */
    public function createSekretaris(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:roles,username',
            'password' => 'required|string|min:6|confirmed',
            'grade_id' => 'required|exists:grades,id',
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'grade_id.required' => 'Kelas wajib dipilih.',
            'grade_id.exists' => 'Kelas tidak valid.',
        ]);

        Role::create([
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'role' => 'sekretaris',
            'grade_id' => $request->grade_id,
        ]);

        return redirect()->route('admin.sekretaris')->with('message', '✅ Sekretaris berhasil ditambahkan.');
    }

    /**
     * Update a sekretaris
     */
    public function updateSekretaris(Request $request, $id)
    {
        $sekretaris = Role::where('role', 'sekretaris')->findOrFail($id);

        $rules = [
            'username' => 'required|string|max:255|unique:roles,username,' . $id,
            'grade_id' => 'required|exists:grades,id',
        ];

        $messages = [
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'grade_id.required' => 'Kelas wajib dipilih.',
            'grade_id.exists' => 'Kelas tidak valid.',
        ];

        // Only validate password if it's provided
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:6|confirmed';
            $messages['password.min'] = 'Password minimal 6 karakter.';
            $messages['password.confirmed'] = 'Konfirmasi password tidak cocok.';
        }

        $request->validate($rules, $messages);

        $sekretaris->username = $request->username;
        $sekretaris->grade_id = $request->grade_id;

        if ($request->filled('password')) {
            $sekretaris->password = bcrypt($request->password);
        }

        $sekretaris->save();

        return redirect()->route('admin.sekretaris')->with('message', '✅ Sekretaris berhasil diperbarui.');
    }

    /**
     * Delete a sekretaris
     */
    public function deleteSekretaris($id)
    {
        $currentUser = auth()->guard('role')->user();

        if ($currentUser->id == $id) {
            return redirect()->route('admin.sekretaris')->withErrors(['error' => 'Anda tidak dapat menghapus akun sendiri.']);
        }

        $sekretaris = Role::where('role', 'sekretaris')->findOrFail($id);
        $sekretaris->delete();

        return redirect()->route('admin.sekretaris')->with('message', '✅ Sekretaris berhasil dihapus.');
    }

    /**
     * Kelola Kelas/Grade
     */
    public function grades(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $grades = Grade::withCount(['users' => function ($query) {
                $query->whereNull('deleted_at');
            }])
            ->orderBy('name', 'asc')
            ->paginate($perPage)
            ->appends($request->query());

        return view('pages.admins.grades', compact('grades', 'perPage'));
    }

    /**
     * Create a new grade
     */
    public function createGrade(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:grades,name',
        ], [
            'name.required' => 'Nama kelas wajib diisi.',
            'name.unique' => 'Nama kelas sudah ada.',
        ]);

        Grade::create([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.grades')->with('message', 'Kelas berhasil ditambahkan.');
    }

    /**
     * Update a grade
     */
    public function updateGrade(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:grades,name,' . $id,
        ], [
            'name.required' => 'Nama kelas wajib diisi.',
            'name.unique' => 'Nama kelas sudah ada.',
        ]);

        $grade = Grade::findOrFail($id);
        $oldName = $grade->name;
        $grade->update(['name' => $request->name]);

        // Update users with old grade name to new grade name
        User::where('kelas', $oldName)->update(['kelas' => $request->name]);

        return redirect()->route('admin.grades')->with('message', 'Kelas berhasil diperbarui.');
    }

    /**
     * Delete a grade
     */
    public function deleteGrade($id)
    {
        $grade = Grade::findOrFail($id);

        // Check if there are users in this grade
        $usersCount = User::where('kelas', $grade->name)->count();
        if ($usersCount > 0) {
            return redirect()->route('admin.grades')->withErrors(['delete' => 'Tidak dapat menghapus kelas yang masih memiliki siswa.']);
        }

        $grade->delete();

        return redirect()->route('admin.grades')->with('message', 'Kelas berhasil dihapus.');
    }
}
