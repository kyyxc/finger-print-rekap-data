<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Get all grades
        $grades = Grade::all();

        if ($grades->isEmpty()) {
            $this->command->error('Tidak ada kelas. Jalankan GradeSeeder terlebih dahulu.');
            return;
        }

        $totalStudents = 0;

        foreach ($grades as $grade) {
            // Generate 30-36 students per class
            $studentsPerClass = rand(30, 36);

            for ($i = 1; $i <= $studentsPerClass; $i++) {
                // Generate NIS: Year (4) + Random (4) = 8 digits
                $nis = '2024' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

                // Make sure NIS is unique
                while (User::where('nis', $nis)->exists()) {
                    $nis = '2024' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                }

                // Generate unique UID
                $uid = 'UID' . strtoupper(uniqid()) . rand(100, 999);

                User::create([
                    'uid' => $uid,
                    'nis' => $nis,
                    'nama' => $faker->name(),
                    'grade_id' => $grade->id,
                    'photo' => null,
                ]);

                $totalStudents++;
            }
        }

        $this->command->info("Berhasil menambahkan {$totalStudents} siswa ke {$grades->count()} kelas.");
    }
}
