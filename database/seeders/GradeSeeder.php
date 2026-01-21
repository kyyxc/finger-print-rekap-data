<?php

namespace Database\Seeders;

use App\Models\Grade;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $grades = [
            // PPLG (Pengembangan Perangkat Lunak dan Gim)
            'X PPLG 1',
            'X PPLG 2',
            'XI PPLG 1',
            'XI PPLG 2',
            'XII PPLG 1',
            'XII PPLG 2',
            
            // TJKT (Teknik Jaringan Komputer dan Telekomunikasi)
            'X TJKT 1',
            'X TJKT 2',
            'XI TJKT 1',
            'XI TJKT 2',
            'XII TJKT 1',
            'XII TJKT 2',
        ];

        foreach ($grades as $gradeName) {
            // Generate Indonesian phone number format (08xx-xxxx-xxxx)
            $phoneNo = '08' . $faker->numberBetween(11, 99) . $faker->numerify('########');
            
            Grade::firstOrCreate(
                ['name' => $gradeName],
                ['phone_no' => $phoneNo]
            );
        }

        $this->command->info('Berhasil menambahkan ' . count($grades) . ' kelas dengan nomor HP wali kelas.');
    }
}
