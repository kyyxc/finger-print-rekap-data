<?php

namespace App\Imports;

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
                throw new \Exception("❌ Header '$header' tidak ditemukan. Pastikan file memiliki header: nis, nama, kelas.");
            }
        }

        $nis = $row['nis'];

        // Cari user yang sudah pernah ada, termasuk yang terhapus
        // $user = User::withTrashed()->where('nis', $nis)->first();
        $user = User::where('nis', $nis)->first();

        if ($user) {
            // Kalau user soft deleted, restore
            // if ($user->trashed()) {
            //     $user->restore();
            // }

            // Update nama & kelas
            $user->update([
                'nama' => $row['nama'] ?? null,
                'kelas' => $row['kelas'] ?? null,
            ]);

            return $user;
        }

        // Kalau user belum ada sama sekali, buat baru
        //     return User::create([
        //         'nis' => $nis,
        //         'nama' => $row['nama'] ?? null,
        //         'kelas' => $row['kelas'] ?? null,
        //     ]);
    }
}
