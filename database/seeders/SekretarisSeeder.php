<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Role;
use Illuminate\Database\Seeder;

class SekretarisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all grades
        $grades = Grade::all();
        
        if ($grades->isEmpty()) {
            $this->command->error('Tidak ada kelas. Jalankan GradeSeeder terlebih dahulu.');
            return;
        }

        $created = 0;

        foreach ($grades as $grade) {
            // Generate username from grade name (e.g., "X PPLG 1" -> "sekretaris_x_pplg_1")
            $username = 'sekretaris_' . strtolower(str_replace(' ', '_', $grade->name));
            
            // Check if sekretaris already exists for this grade
            $exists = Role::where('grade_id', $grade->id)
                ->where('role', 'sekretaris')
                ->exists();

            if (!$exists) {
                Role::create([
                    'username' => $username,
                    'password' => bcrypt('password123'), // Default password
                    'role' => 'sekretaris',
                    'grade_id' => $grade->id,
                ]);
                $created++;
            }
        }

        $this->command->info("Berhasil menambahkan {$created} akun sekretaris.");
        $this->command->info('Username: sekretaris_[nama_kelas] (contoh: sekretaris_x_pplg_1)');
        $this->command->info('Password default: password123');
    }
}
