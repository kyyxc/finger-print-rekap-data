<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunSendWhatsappJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-send-whatsapp-job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi WhatsApp untuk absensi yang belum terkirim';

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
        $this->info('Mengambil data absensi yang belum dikirim...');

        // Ambil semua attendance yang is_send = false
        $attendances = Attendance::with('user')
            ->where('is_send', false)
            ->whereHas('user', function ($query) {
                $query->whereNotNull('phone_number')
                      ->where('phone_number', '!=', '');
            })
            ->get();

        if ($attendances->isEmpty()) {
            $this->info('Tidak ada absensi yang perlu dikirim.');
            return;
        }

        $this->info("Ditemukan {$attendances->count()} absensi yang perlu dikirim.");

        $successCount = 0;
        $failCount = 0;

        foreach ($attendances as $attendance) {
            $user = $attendance->user;

            if (!$user || !$user->phone_number) {
                $this->warn("User tidak ditemukan atau tidak punya nomor HP untuk attendance ID: {$attendance->id}");
                continue;
            }

            // Buat pesan yang sopan berdasarkan status
            $message = $this->buildMessage($user, $attendance);

            try {
                $result = $this->whatsappService->sendMessage($user->phone_number, $message);

                if ($result['success']) {
                    // Update is_send menjadi true
                    $attendance->update(['is_send' => true]);
                    $successCount++;
                    $this->info("Berhasil kirim ke {$user->nama} ({$user->phone_number})");
                } else {
                    $failCount++;
                    $this->error("Gagal kirim ke {$user->nama}: " . ($result['message'] ?? 'Unknown error'));
                }
            } catch (\Exception $e) {
                $failCount++;
                $this->error("Error kirim ke {$user->nama}: " . $e->getMessage());
                Log::error('WhatsApp send error', [
                    'attendance_id' => $attendance->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->newLine();
        $this->info("Selesai! Berhasil: {$successCount}, Gagal: {$failCount}");
    }

    /**
     * Buat pesan WhatsApp yang sopan
     */
    private function buildMessage($user, $attendance): string
    {
        $nama = $user->nama;
        $jam = $attendance->record_time->format('H:i');
        $tanggal = $attendance->record_time->format('d/m/Y');
        $status = $attendance->status;

        // Greeting berdasarkan waktu
        $hour = (int) $attendance->record_time->format('H');
        if ($hour < 11) {
            $greeting = 'Selamat Pagi';
        } elseif ($hour < 15) {
            $greeting = 'Selamat Siang';
        } elseif ($hour < 18) {
            $greeting = 'Selamat Sore';
        } else {
            $greeting = 'Selamat Malam';
        }

        // Pesan berdasarkan status
        $statusMessage = match ($status) {
            'masuk', 'hadir' => "*HADIR TEPAT WAKTU*\n\nKami informasikan bahwa *{$nama}* telah hadir di sekolah tepat waktu.",
            'telat' => "*HADIR TERLAMBAT*\n\nKami informasikan bahwa *{$nama}* telah hadir di sekolah, namun terlambat dari jadwal yang ditentukan.",
            'pulang' => "*PULANG*\n\nKami informasikan bahwa *{$nama}* telah pulang dari sekolah.",
            'sakit' => "*SAKIT*\n\nKami informasikan bahwa *{$nama}* tercatat tidak hadir karena sakit. Semoga lekas sembuh.",
            'izin' => "*IZIN*\n\nKami informasikan bahwa *{$nama}* tercatat tidak hadir karena izin.",
            'alpha' => "*TIDAK HADIR (Alpha)*\n\nKami informasikan bahwa *{$nama}* tercatat tidak hadir tanpa keterangan.",
            default => "*INFORMASI KEHADIRAN*\n\nKami informasikan mengenai kehadiran *{$nama}*.",
        };

        $message = "{$greeting}, Bapak/Ibu Wali Murid.\n\n";
        $message .= "━━━━━━━━━━━━━━━━━\n";
        $message .= $statusMessage . "\n";
        $message .= "━━━━━━━━━━━━━━━━━\n\n";
        $message .= "Tanggal: {$tanggal}\n";
        $message .= "Waktu: {$jam} WIB\n\n";
        $message .= "Terima kasih atas perhatian Bapak/Ibu.\n\n";
        $message .= "_Pesan ini dikirim secara otomatis oleh Sistem Absensi Sekolah._";

        return $message;
    }
}
