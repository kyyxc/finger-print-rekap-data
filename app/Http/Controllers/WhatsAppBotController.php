<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WhatsAppBotController extends Controller
{
    private $whatsappService;
    private $targetTime = '14:57'; // Ubah disini untuk ganti jadwal

    private $defaultPhones = [
        '081958749289',
        '085798549635',
        '083808087144',
        // '085860090810'
    ];

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    public function testPage()
    {
        $result = session('wa_result');
        $isScheduled = session('is_scheduled', false);
        $scheduledTime = session('scheduled_time');
        
        return view('wa-test', compact('result'), [
            'targetTime' => $this->targetTime,
            'isScheduled' => $isScheduled,
            'scheduledTime' => $scheduledTime
        ]);
    }
    
    public function checkSchedule()
    {
        $isScheduled = session('is_scheduled', false);
        $scheduledTime = session('scheduled_time');
        
        if ($isScheduled && $scheduledTime) {
            $now = Carbon::now('Asia/Jakarta');
            $targetDateTime = Carbon::parse($scheduledTime);
            
            if ($now->gte($targetDateTime)) {
                $result = $this->executeScheduledSend();
                session()->forget(['is_scheduled', 'scheduled_time']);
                return response()->json([
                    'sent' => true, 
                    'result' => $result,
                    'message' => 'Pesan berhasil dikirim!'
                ]);
            }
        }
        
        return response()->json(['sent' => false]);
    }

    public function sendTestMessage()
    {
        $now = Carbon::now('Asia/Jakarta');
        $targetDateTime = Carbon::createFromFormat('H:i', $this->targetTime, 'Asia/Jakarta');
        
        if ($targetDateTime->isPast()) {
            $targetDateTime->addDay();
        }
        
        // Simpan jadwal di session
        session([
            'is_scheduled' => true,
            'scheduled_time' => $targetDateTime->toDateTimeString()
        ]);
        
        return redirect()->route('wa-test')->with('status', 
            "Pesan dijadwalkan untuk dikirim pada {$targetDateTime->format('d/m/Y H:i')} WIB. Refresh halaman saat waktunya tiba."
        );
    }
    
    private function executeScheduledSend()
    {
        $now = Carbon::now('Asia/Jakarta');
        $message = 'Ananda telah hadir pada pukul ' . $this->targetTime . ' - ' . $now->format('d/m/Y H:i');
        
        try {
            $result = $this->whatsappService->sendBulkMessage($this->defaultPhones, $message);
            session(['wa_result' => $result]);
            return $result;
        } catch (\Exception $e) {
            $errorResult = ['error' => $e->getMessage()];
            session(['wa_result' => $errorResult]);
            return $errorResult;
        }
    }

    public function sendNotification($phones, $message)
    {
        return $this->whatsappService->sendBulkMessage($phones, $message);
    }
}
