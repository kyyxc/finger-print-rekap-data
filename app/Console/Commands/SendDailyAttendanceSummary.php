<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Grade;
use App\Models\User;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendDailyAttendanceSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-daily-attendance-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim ringkasan kehadiran harian ke nomor HP wali kelas';

    private $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        parent::__construct();
        $this->whatsappService = $whatsappService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('===========================================');
        $this->info('Mengirim Ringkasan Kehadiran Harian');
        $this->info('Waktu: ' . now()->format('Y-m-d H:i:s'));
        $this->info('===========================================');

        $today = Carbon::today();
        $grades = Grade::whereNotNull('phone_no')
            ->where('phone_no', '!=', '')
            ->get();

        if ($grades->isEmpty()) {
            $this->info('Tidak ada kelas dengan nomor HP yang terdaftar.');
            return;
        }

        $this->info("Ditemukan {$grades->count()} kelas dengan nomor HP.");
        $this->info('');

        $successCount = 0;
        $failCount = 0;

        foreach ($grades as $grade) {
            $this->info("Memproses kelas: {$grade->name}...");

            // Ambil semua siswa di kelas ini
            $students = User::where('grade_id', $grade->id)->get();
            $totalStudents = $students->count();

            if ($totalStudents === 0) {
                $this->warn("  - Tidak ada siswa di kelas {$grade->name}. Dilewati.");
                continue;
            }

            // Ambil data kehadiran hari ini untuk siswa di kelas ini
            $attendances = Attendance::whereIn('user_id', $students->pluck('id'))
                ->whereDate('record_time', $today)
                ->get();

            // Hitung statistik
            $hadirIds = $attendances->whereIn('status', ['masuk', 'telat'])->pluck('user_id')->unique();
            $telatIds = $attendances->where('status', 'telat')->pluck('user_id')->unique();
            $sakitIds = $attendances->where('status', 'sakit')->pluck('user_id')->unique();
            $izinIds = $attendances->where('status', 'izin')->pluck('user_id')->unique();
            $alphaIds = $attendances->where('status', 'alpha')->pluck('user_id')->unique();

            $hadir = $hadirIds->count();
            $telat = $telatIds->count();
            $sakit = $sakitIds->count();
            $izin = $izinIds->count();
            $alpha = $alphaIds->count();

            // Siswa yang belum absen sama sekali
            $absenIds = $hadirIds->merge($sakitIds)->merge($izinIds)->merge($alphaIds)->unique();
            $belumAbsenIds = $students->pluck('id')->diff($absenIds);
            $belumAbsen = $belumAbsenIds->count();

            // Buat pesan ringkasan
            $message = $this->buildSummaryMessage($grade, $today, $totalStudents, $hadir, $telat, $sakit, $izin, $alpha, $belumAbsen, $students, $belumAbsenIds, $telatIds, $sakitIds, $izinIds, $alphaIds);

            try {
                $result = $this->whatsappService->sendMessage($grade->phone_no, $message);

                if ($result['success']) {
                    $successCount++;
                    $this->info("  ✓ Berhasil kirim ke {$grade->name} ({$grade->phone_no})");
                } else {
                    $failCount++;
                    $this->error("  ✗ Gagal kirim ke {$grade->name}: " . ($result['message'] ?? 'Unknown error'));
                }
            } catch (\Exception $e) {
                $failCount++;
                $this->error("  ✗ Error kirim ke {$grade->name}: " . $e->getMessage());
                Log::error("Gagal kirim ringkasan kehadiran ke {$grade->name}", [
                    'grade_id' => $grade->id,
                    'phone' => $grade->phone_no,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info('');
        $this->info('===========================================');
        $this->info("Selesai! Berhasil: {$successCount}, Gagal: {$failCount}");
        $this->info('===========================================');
    }

    /**
     * Build summary message for WhatsApp
     */
    private function buildSummaryMessage($grade, $today, $totalStudents, $hadir, $telat, $sakit, $izin, $alpha, $belumAbsen, $students, $belumAbsenIds, $telatIds, $sakitIds, $izinIds, $alphaIds)
    {
        $tanggal = $today->translatedFormat('l, d F Y');

        $message = "📋 *RINGKASAN KEHADIRAN*\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "🏫 Kelas: *{$grade->name}*\n";
        $message .= "📅 Tanggal: {$tanggal}\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";

        $message .= "📊 *STATISTIK KEHADIRAN:*\n";
        $message .= "👥 Total Siswa: {$totalStudents}\n";
        $message .= "✅ Hadir: {$hadir}\n";
        if ($telat > 0) {
            $message .= "⏰ Terlambat: {$telat}\n";
        }
        if ($sakit > 0) {
            $message .= "🏥 Sakit: {$sakit}\n";
        }
        if ($izin > 0) {
            $message .= "📝 Izin: {$izin}\n";
        }
        if ($alpha > 0) {
            $message .= "❌ Alpha: {$alpha}\n";
        }
        if ($belumAbsen > 0) {
            $message .= "⚠️ Belum Absen: {$belumAbsen}\n";
        }

        // Daftar siswa yang terlambat
        if ($telat > 0) {
            $message .= "\n⏰ *DAFTAR TERLAMBAT:*\n";
            $telatStudents = $students->whereIn('id', $telatIds);
            $no = 1;
            foreach ($telatStudents as $student) {
                $nama = $student->nama ?? $student->nis;
                $message .= "{$no}. {$nama}\n";
                $no++;
            }
        }

        // Daftar siswa yang sakit
        if ($sakit > 0) {
            $message .= "\n🏥 *DAFTAR SAKIT:*\n";
            $sakitStudents = $students->whereIn('id', $sakitIds);
            $no = 1;
            foreach ($sakitStudents as $student) {
                $nama = $student->nama ?? $student->nis;
                $message .= "{$no}. {$nama}\n";
                $no++;
            }
        }

        // Daftar siswa yang izin
        if ($izin > 0) {
            $message .= "\n📝 *DAFTAR IZIN:*\n";
            $izinStudents = $students->whereIn('id', $izinIds);
            $no = 1;
            foreach ($izinStudents as $student) {
                $nama = $student->nama ?? $student->nis;
                $message .= "{$no}. {$nama}\n";
                $no++;
            }
        }

        // Daftar siswa yang alpha
        if ($alpha > 0) {
            $message .= "\n❌ *DAFTAR ALPHA:*\n";
            $alphaStudents = $students->whereIn('id', $alphaIds);
            $no = 1;
            foreach ($alphaStudents as $student) {
                $nama = $student->nama ?? $student->nis;
                $message .= "{$no}. {$nama}\n";
                $no++;
            }
        }

        // Daftar siswa yang belum absen
        if ($belumAbsen > 0) {
            $message .= "\n⚠️ *BELUM ABSEN:*\n";
            $belumAbsenStudents = $students->whereIn('id', $belumAbsenIds);
            $no = 1;
            foreach ($belumAbsenStudents as $student) {
                $nama = $student->nama ?? $student->nis;
                $message .= "{$no}. {$nama}\n";
                $no++;
            }
        }

        $message .= "\n━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "🤖 _Pesan otomatis dari Sistem Absensi_";

        return $message;
    }
}
