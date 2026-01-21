<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private $token;
    private $baseUrl = 'https://api.fonnte.com/send';

    public function __construct()
    {
        $this->token = config('services.fonnte.token');
    }

    public function sendMessage($phone, $message)
    {
        if (!$this->token) {
            throw new \Exception('Token Fonnte tidak ditemukan di konfigurasi');
        }

        $formattedPhone = $this->formatPhoneNumber($phone);
        
        try {
            $response = Http::timeout(30)
                ->withHeaders(['Authorization' => $this->token])
                ->post($this->baseUrl, [
                    'target' => $formattedPhone,
                    'message' => $message,
                    'countryCode' => '62'
                ]);

            $responseData = $response->json();
            $isSuccess = $response->successful() && ($responseData['status'] ?? false);
            
            if (!$isSuccess) {
                Log::warning('WhatsApp send failed', [
                    'phone' => $formattedPhone,
                    'response' => $responseData
                ]);
            }

            return [
                'success' => $isSuccess,
                'phone' => $formattedPhone,
                'status_code' => $response->status(),
                'message' => $isSuccess ? 'Pesan berhasil dikirim' : ($responseData['reason'] ?? 'Gagal mengirim pesan'),
                'response_data' => $responseData
            ];
            
        } catch (\Exception $e) {
            Log::error('WhatsApp send error', [
                'phone' => $formattedPhone,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    public function sendBulkMessage(array $phones, $message)
    {
        $results = [];
        $successCount = 0;

        foreach ($phones as $phone) {
            try {
                $result = $this->sendMessage($phone, $message);
                $results[] = $result;
                
                if ($result['success']) {
                    $successCount++;
                }
            } catch (\Exception $e) {
                $results[] = [
                    'success' => false,
                    'phone' => $this->formatPhoneNumber($phone),
                    'message' => $e->getMessage()
                ];
            }
        }

        return [
            'total_attempted' => count($phones),
            'success_count' => $successCount,
            'results' => $results
        ];
    }

    private function formatPhoneNumber($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (str_starts_with($phone, '0')) {
            return '62' . substr($phone, 1);
        }
        
        if (!str_starts_with($phone, '62')) {
            return '62' . $phone;
        }
        
        return $phone;
    }
}