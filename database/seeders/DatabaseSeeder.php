<?php

namespace Database\Seeders;

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

        Role::create([
            'username' => config('app.admin_username'),
            'password' => bcrypt(config('app.admin_password')),
            'role' => 'admin',
        ]);

        Role::create([
            'username' => config('app.sekretaris_username'),
            'password' => bcrypt(config('app.sekretaris_password')),
            'role' => 'sekretaris'
        ]);
    }
}
