<?php

namespace App\Http\Controllers;

use App\Exports\AttendancesExport;
use App\Imports\UserImport;
use App\Models\Admin;
use App\Models\Role;
use App\Models\User;
use CodingLibs\ZktecoPhp\Libs\ZKTeco;
use Illuminate\Http\Request;
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
        return view('pages.admins.dashboard1');
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

        $users = User::query()
            ->orderBy('nama', 'asc')
            ->get();

        return view('pages.admins.users', compact('users'));
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

        return redirect()->route('admins.users')->with('message', 'User berhasil dihapus (soft delete).');
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

        return redirect()->back()->with('message', '✅ Data pengguna berhasil diimpor!');
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



    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.dashboard')->withErrors($validator)->withInput();
        }

        $admin = auth()->guard('role')->user();
        if (!Hash::check($request->current_password, $admin->password)) {
            return redirect()->route('admin.dashboard')->withErrors(['current_password' => 'Password saat ini salah.']);
        }

        if ($request->current_password == $request->new_password) {
            return redirect()->route('admin.dashboard')->withErrors(['new_password' => 'Password baru harus berbeda.']);
        }

        $admin->password = Hash::make($request->new_password);
        $admin->save();

        return back()->with('message', '✅ Password berhasil diubah.');
    }

    /**
     * Membuat admin baru.
     */
    public function createAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:admins,username',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.dashboard')->withErrors($validator)->withInput();
        }

        Role::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        return back()->with('message', '✅ Admin baru berhasil dibuat.');
    }

    /**
     * Kelola Admin
     */
    public function admins()
    {
        return view('pages.admins.admins');
    }

    /**
     * Kelola Sekretaris
     */
    public function sekretaris()
    {
        return view('pages.admins.sekretaris');
    }

    /**
     * Kelola Kelas/Grade
     */
    public function grades()
    {
        return view('pages.admins.grades');
    }
}
