<!DOCTYPE html>
<html>

<head>
    <title>WhatsApp Test</title>
    <style>
        body {
            font-family: Arial;
            margin: 50px;
            background: #f5f5f5;
            text-align: center;
        }

        .container {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #25D366;
            margin-bottom: 20px;
            font-size: 24px;
        }

        button {
            background: #25D366;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background: #128C7E;
        }

        .alert {
            padding: 10px;
            margin: 15px 0;
            border-radius: 5px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }

        .info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            font-size: 14px;
        }

        .status {
            background: #fff3cd;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-size: 13px;
        }

        .scheduled {
            background: #d4edda;
            color: #155724;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>📱 WhatsApp Bot</h1>

        <div class="info">
            <strong>Jadwal:</strong> {{ $targetTime }} WIB<br>
        </div>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @if ($isScheduled ?? false)
            <div class="status scheduled">
                ✅ Dijadwalkan jam {{ $targetTime }} WIB
            </div>
        @else
            <div class="status">
                ⏳ Siap dijadwalkan
            </div>
        @endif

        <form action="/whatsapp-bot/send-test" method="POST">
            @csrf
            <button type="submit">
                {{ $isScheduled ?? false ? '🔄 Sudah Dijadwalkan' : '🚀 Jadwalkan Pesan' }}
            </button>
        </form>

        @if ($result && isset($result['results']))
            <div style="margin-top: 20px; text-align: left; background: #f8f9fa; padding: 15px; border-radius: 5px;">
                <strong>Hasil:</strong> {{ $result['success_count'] ?? 0 }}/{{ $result['total_attempted'] ?? 0 }}
                berhasil
            </div>
        @endif
    </div>
    
    @if($isScheduled ?? false)
    <script>
        let isScheduled = true;
        
        function checkSchedule() {
            if (!isScheduled) return;
            
            fetch('/wa-check')
                .then(response => response.json())
                .then(data => {
                    if (data.sent) {
                        isScheduled = false;
                        
                        // Update status
                        document.querySelector('.status').innerHTML = '✅ Pesan berhasil dikirim!';
                        document.querySelector('.status').className = 'status scheduled';
                        
                        // Update button
                        document.querySelector('button').innerHTML = '✅ Pesan Terkirim';
                        document.querySelector('button').disabled = true;
                        
                        // Show result
                        if (data.result && data.result.success_count !== undefined) {
                            const resultDiv = document.createElement('div');
                            resultDiv.style = 'margin-top: 20px; text-align: left; background: #f8f9fa; padding: 15px; border-radius: 5px;';
                            resultDiv.innerHTML = `<strong>Hasil:</strong> ${data.result.success_count}/${data.result.total_attempted} berhasil`;
                            document.querySelector('.container').appendChild(resultDiv);
                        }
                    }
                })
                .catch(error => console.log('Check error:', error));
        }
        
        // Check setiap 5 detik
        setInterval(checkSchedule, 5000);
        
        // Check pertama kali setelah 3 detik
        setTimeout(checkSchedule, 3000);
    </script>
    @endif
</body>

</html>
