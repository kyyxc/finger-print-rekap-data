<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('default_schedules', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('day_of_week')->unique(); // 0=Minggu, 1=Senin, ..., 6=Sabtu
            $table->string('day_name');
            $table->time('jam_datang')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->boolean('is_holiday')->default(false);
            $table->timestamps();
        });

        // Insert default data
        DB::table('default_schedules')->insert([
            ['day_of_week' => 0, 'day_name' => 'Minggu', 'jam_datang' => null, 'jam_pulang' => null, 'is_holiday' => true, 'created_at' => now(), 'updated_at' => now()],
            ['day_of_week' => 1, 'day_name' => 'Senin', 'jam_datang' => '07:30', 'jam_pulang' => '16:00', 'is_holiday' => false, 'created_at' => now(), 'updated_at' => now()],
            ['day_of_week' => 2, 'day_name' => 'Selasa', 'jam_datang' => '07:30', 'jam_pulang' => '16:00', 'is_holiday' => false, 'created_at' => now(), 'updated_at' => now()],
            ['day_of_week' => 3, 'day_name' => 'Rabu', 'jam_datang' => '07:30', 'jam_pulang' => '16:00', 'is_holiday' => false, 'created_at' => now(), 'updated_at' => now()],
            ['day_of_week' => 4, 'day_name' => 'Kamis', 'jam_datang' => '07:30', 'jam_pulang' => '16:00', 'is_holiday' => false, 'created_at' => now(), 'updated_at' => now()],
            ['day_of_week' => 5, 'day_name' => 'Jumat', 'jam_datang' => '07:30', 'jam_pulang' => '16:30', 'is_holiday' => false, 'created_at' => now(), 'updated_at' => now()],
            ['day_of_week' => 6, 'day_name' => 'Sabtu', 'jam_datang' => null, 'jam_pulang' => null, 'is_holiday' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('default_schedules');
    }
};
