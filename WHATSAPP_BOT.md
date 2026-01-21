# WhatsApp Bot Documentation

## Setup

1. Tambahkan token Fonnte di file `.env`:
```
FONNTE_TOKEN=your_fonnte_token_here
```

2. Pastikan konfigurasi sudah benar di `config/services.php`:
```php
'fonnte' => [
    'token' => env('FONNTE_TOKEN'),
],
```

## Penggunaan

### 1. Test Page
Akses `/wa-test` untuk testing manual pengiriman pesan WhatsApp.

### 2. Programmatic Usage
```php
use App\Services\WhatsAppService;

$whatsappService = new WhatsAppService();

// Kirim pesan tunggal
$result = $whatsappService->sendMessage('081234567890', 'Hello World');

// Kirim pesan ke multiple nomor
$phones = ['081234567890', '085678901234'];
$result = $whatsappService->sendBulkMessage($phones, 'Broadcast message');
```

### 3. Dari Controller Lain
```php
use App\Http\Controllers\WhatsAppBotController;

$whatsappBot = new WhatsAppBotController(new WhatsAppService());
$result = $whatsappBot->sendNotification(['081234567890'], 'Notification message');
```

## Format Response
```php
[
    'total_attempted' => 2,
    'success_count' => 1,
    'results' => [
        [
            'success' => true,
            'phone' => '6281234567890',
            'status_code' => 200,
            'message' => 'Pesan berhasil dikirim'
        ],
        [
            'success' => false,
            'phone' => '6285678901234',
            'message' => 'Invalid phone number'
        ]
    ]
]
```

## Error Handling
- Semua error akan di-log ke Laravel log
- Response selalu konsisten dengan format yang sama
- Timeout diset 30 detik untuk setiap request
- Validasi nomor telepon otomatis (format Indonesia)

## Features
- ✅ Clean & simple code
- ✅ Proper error handling
- ✅ Logging untuk debugging
- ✅ Validation input
- ✅ Service pattern untuk reusability
- ✅ Bulk message support
- ✅ Auto phone number formatting
- ✅ User-friendly test interface