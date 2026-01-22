<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jalankan sinkronisasi user & absensi dari mesin fingerprint setiap 10 menit
Schedule::command('app:sync-attendance')->everyTenSeconds();

// Jalankan notifikasi WhatsApp setiap 10 menit
Schedule::command('app:run-send-whatsapp-job')->everyTenSeconds();

// Kirim ringkasan kehadiran harian ke wali kelas setiap jam 8 pagi
Schedule::command('app:send-daily-attendance-summary')->dailyAt('08:00');
