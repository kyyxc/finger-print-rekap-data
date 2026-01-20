<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class WhatsAppBotController extends Controller
{
    private $targetPhone = '85798549635';

    public function testPage()
    {
        $result = session('wa_result');
        return view('wa-test', compact('result'));
    }

    public function sendTestMessage()
    {
        // Set ke true untuk kirim pesan, false untuk skip
        $sendMessage = true;

        if (!$sendMessage) {
            return response()->json([
                'message' => 'Pengiriman pesan dimatikan (sendMessage = false)',
                'phone' => $this->targetPhone,
                'sent' => false
            ]);
        }

        $formattedPhone = $this->formatPhoneNumber($this->targetPhone);
        $message = 'tes bot whatsapp';

        try {
            $response = Http::withHeaders([
                'Authorization' => env('FONNTE_TOKEN')
            ])->post('https://api.fonnte.com/send', [
                'target' => $formattedPhone,
                'message' => $message,
                'countryCode' => '62'
            ]);

            $result = $response->json();

            session(['wa_result' => [
                'phone' => $formattedPhone,
                'success' => $response->successful(),
                'response' => $result
            ]]);

            return redirect('/wa-test');
        } catch (\Exception $e) {
            session(['wa_result' => [
                'phone' => $formattedPhone,
                'success' => false,
                'error' => $e->getMessage()
            ]]);

            return redirect('/wa-test');
        }
    }

    private function formatPhoneNumber($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        return $phone;
    }
}
