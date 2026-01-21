<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Role;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed kelas terlebih dahulu
        $this->call(GradeSeeder::class);

        // Seed siswa
        $this->call(UserSeeder::class);

        // Create default admin
        Role::firstOrCreate(
            ['username' => config('app.admin_username', 'admin')],
            [
                'password' => bcrypt(config('app.admin_password', 'admin123')),
                'role' => 'admin',
            ]
        );

        // Seed sekretaris untuk setiap kelas
        $this->call(SekretarisSeeder::class);
    }
}
