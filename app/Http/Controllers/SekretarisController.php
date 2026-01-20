<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SekretarisController extends Controller
{
    public function dashboard()
    {
        try {
            $this->createUser();
        } catch (\Exception $e) {
            Log::error('Gagal sinkronisasi user: ' . $e->getMessage());
        }
        return view('pages.sekretaris.dashboard');
    }
}
