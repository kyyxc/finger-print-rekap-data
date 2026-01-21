<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $phones;
    public $message;

    public function __construct($phones, $message)
    {
        $this->phones = $phones;
        $this->message = $message;
    }

    public function handle(WhatsAppService $whatsappService)
    {
        $whatsappService->sendBulkMessage($this->phones, $this->message);
    }
}