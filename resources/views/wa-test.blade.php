<!DOCTYPE html>
<html>
<head>
    <title>WhatsApp Test</title>
    <style>
        body { font-family: Arial; margin: 50px; text-align: center; }
        button { background: #25D366; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
        button:hover { background: #128C7E; }
        .result { margin-top: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { border-color: #28a745; background: #d4edda; }
        .error { border-color: #dc3545; background: #f8d7da; }
    </style>
</head>
<body>
    <h1>WhatsApp Bot Test</h1>
    
    <form action="/whatsapp-bot/send-test" method="POST">
        @csrf
        <button type="submit">📱 Kirim Pesan</button>
    </form>
    
    @if($result)
        <div class="result {{ $result['success'] ? 'success' : 'error' }}">
            <p><strong>Nomor:</strong> {{ $result['phone'] }}</p>
            <p><strong>Status:</strong> {{ $result['success'] ? 'Berhasil' : 'Gagal' }}</p>
            <p><strong>Response:</strong> {{ json_encode($result['response'] ?? $result['error']) }}</p>
        </div>
    @endif
</body>
</html>