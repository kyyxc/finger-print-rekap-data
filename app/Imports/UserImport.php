<?php

namespace App\Imports;

use App\Models\Grade;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UserImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $requiredHeaders = ['nis', 'nama', 'kelas'];

        foreach ($requiredHeaders as $header) {
            if (!array_key_exists($header, $row)) {
                throw new \Exception("❌ Header '$header' tidak ditemukan. Pastikan file memiliki header: nis, nama, kelas, phone_number.");
            }
        }

        $nis = $row['nis'];
        $kelasName = $row['kelas'] ?? null;
        $gradeId = null;

        // Cari grade berdasarkan nama kelas
        if ($kelasName) {
            $grade = Grade::where('name', $kelasName)->first();
            if (!$grade) {
                throw new \Exception("❌ Kelas '$kelasName' tidak ditemukan di database. Pastikan kelas sudah ditambahkan terlebih dahulu.");
            }
            $gradeId = $grade->id;
        }

        // Cari user yang sudah pernah ada
        $user = User::where('nis', $nis)->first();

        if ($user) {
            // Update nama, grade_id & phone_number
            $user->update([
                'nama' => $row['nama'] ?? null,
                'grade_id' => $gradeId,
                'phone_number' => $row['phone_number'] ?? null,
            ]);

            return $user;
        }

        // Kalau user belum ada sama sekali, buat baru
        return User::create([
            'uid' => $nis,
            'nis' => $nis,
            'nama' => $row['nama'] ?? null,
            'grade_id' => $gradeId,
            'phone_number' => $row['phone_number'] ?? null,
        ]);
    }
}
