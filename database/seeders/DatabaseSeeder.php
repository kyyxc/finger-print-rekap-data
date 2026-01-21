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
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Seed default grades/classes
        $grades = [
            'X PPLG 1', 'X PPLG 2',
            'XI RPL 1', 'XI RPl 2',
            'XII RPL 1', 'XII RPL 2',
            'X TJKT 1', 'X TJKT 2',
            'XI TKJ 1', 'XI TKJ 2',
            'XII TKJ 1', 'XII TKJ 2',
        ];

        foreach ($grades as $gradeName) {
            Grade::firstOrCreate(['name' => $gradeName]);
        }

        // Get first grade for sekretaris
        $firstGrade = Grade::first();

        Role::create([
            'username' => config('app.admin_username'),
            'password' => bcrypt(config('app.admin_password')),
            'role' => 'admin',
        ]);

        Role::create([
            'username' => config('app.sekretaris_username'),
            'password' => bcrypt(config('app.sekretaris_password')),
            'role' => 'sekretaris',
            'grade_id' => $firstGrade?->id,
        ]);
    }
}
