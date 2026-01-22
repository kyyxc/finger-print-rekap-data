<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jalankan sinkronisasi user & absensi dari mesin fingerprint setiap 10 menit
Schedule::command('app:sync-attendance')->everyMinute();

// Jalankan notifikasi WhatsApp setiap 10 menit
Schedule::command('app:run-send-whatsapp-job')->everyMinute();
