<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppBotService
{
    private $apiUrl;
    private $token;

    public function __construct()
    {
        // Fonnte API
        $this->apiUrl = 'https://api.fonnte.com/send';
        $this->token = env('FONNTE_TOKEN', 'your-fonnte-token-here');
    }

    public function sendMessage($phoneNumber, $message)
    {
        try {
            $formattedPhone = $this->formatPhoneNumber($phoneNumber);
            
            // Kirim via Fonnte
            $response = Http::withHeaders([
                'Authorization' => $this->token
            ])->post($this->apiUrl, [
                'target' => $formattedPhone,
                'message' => $message,
                'countryCode' => '62'
            ]);
            
            $responseData = $response->json();
            
            Log::info("WhatsApp Bot - Mengirim ke: {$formattedPhone}");
            Log::info("WhatsApp Bot - Pesan: {$message}");
            Log::info("WhatsApp Bot - Response: " . $response->body());
            
            return [
                'success' => $response->successful(),
                'response' => $responseData,
                'status_code' => $response->status(),
                'formatted_phone' => $formattedPhone
            ];
            
        } catch (\Exception $e) {
            Log::error("WhatsApp Bot Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
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